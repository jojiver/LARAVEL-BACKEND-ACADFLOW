<?php

namespace App\Services;

use App\Models\Subject;
use App\Repositories\Contracts\ScheduleRepositoryInterface;
use App\Repositories\Contracts\SubjectRepositoryInterface;
use App\Support\ScheduleTextParser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Smalot\PdfParser\Parser;
use Spatie\PdfToText\Pdf;
use Symfony\Component\Process\Process;
use thiagoalessio\TesseractOCR\TesseractOCR;

class ScheduleImportService
{
    public function __construct(
        private readonly ScheduleTextParser $parser,
        private readonly SubjectRepositoryInterface $subjects,
        private readonly ScheduleRepositoryInterface $schedules,
    ) {}

    public function preview(int $userId, UploadedFile $file): array
    {
        $text = $this->extractText($file);

        if (trim($text) === '') {
            throw ValidationException::withMessages([
                'file' => 'No readable text was found. Upload a clear text-based PDF or a sharper timetable image.',
            ]);
        }

        $parsed = $this->parser->parse($text);
        $classes = $parsed['classes'];
        $existing = $this->schedules->allForUser($userId);

        $this->markConflicts($classes, $existing);

        $subjects = collect($classes)
            ->map(fn (array $class) => strtolower($class['subject']))
            ->unique()
            ->count();

        return [
            'classes' => $classes,
            'summary' => [
                'classes' => count($classes),
                'subjects' => $subjects,
                'conflicts' => collect($classes)->where('conflict', true)->count(),
                'duplicates' => $parsed['duplicates'],
            ],
            'source' => [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => $file->getMimeType(),
            ],
        ];
    }

    public function confirm(int $userId, array $classes): array
    {
        $existingSubjects = $this->subjects->allForUser($userId);
        $existingSchedules = $this->schedules->allForUser($userId);
        $palette = config('schedule_import.colors', ['#6266F0']);

        $created = 0;
        $skipped = 0;
        $subjectsCreated = 0;

        foreach ($classes as $row) {
            if (! ($row['selected'] ?? true)) {
                continue;
            }

            $name = trim((string) ($row['subject'] ?? ''));
            $day = (string) ($row['day'] ?? '');
            $start = (string) ($row['startTime'] ?? '');
            $end = (string) ($row['endTime'] ?? '');

            if ($name === '' || $name === 'Untitled class') {
                $skipped++;
                continue;
            }

            if (! $this->validTimeRange($day, $start, $end)) {
                $skipped++;
                continue;
            }

            $subject = $existingSubjects->first(
                fn (Subject $item) => strcasecmp($item->name, $name) === 0
            );

            if (! $subject) {
                $color = $palette[count($existingSubjects) % count($palette)];

                $subject = $this->subjects->create($userId, [
                    'name' => $name,
                    'category' => 'Core',
                    'color' => $color,
                ]);

                $existingSubjects->push($subject);
                $subjectsCreated++;
            }

            $duplicate = $existingSchedules->first(
                fn ($item) => $item->subject_id === $subject->id
                    && $item->day === $day
                    && $item->start_time === $start
            );

            if ($duplicate) {
                $skipped++;
                continue;
            }

            $createdSchedule = $this->schedules->create($userId, [
                'subject_id' => $subject->id,
                'day' => $day,
                'start_time' => $start,
                'end_time' => $end,
                'room' => ($row['room'] ?? null) ?: null,
            ]);

            $existingSchedules->push($createdSchedule);
            $created++;
        }

        return [
            'created' => $created,
            'skipped' => $skipped,
            'subjectsCreated' => $subjectsCreated,
        ];
    }

    private function extractText(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        return match ($extension) {
            'pdf' => $this->pdfText($path) ?: $this->ocrPdf($path),
            'jpg', 'jpeg', 'png' => $this->ocrImage($path),
            default => throw ValidationException::withMessages([
                'file' => 'Unsupported file type. Upload a PDF, JPG, or PNG.',
            ]),
        };
    }

    private function pdfText(string $path): string
    {
        $binary = config('schedule_import.binaries.pdftotext');

        try {
            $pdf = $binary ? new Pdf($binary) : new Pdf();
            $text = $pdf->setPdf($path)->setOptions(['-layout'])->text();

            if (trim($text) !== '') {
                return $text;
            }
        } catch (\Throwable) {
            // Fall through to the pure PHP parser.
        }

        try {
            return (new Parser())->parseFile($path)->getText();
        } catch (\Throwable) {
            return '';
        }
    }

    private function ocrImage(string $path): string
    {
        $binary = config('schedule_import.binaries.tesseract');
        $prepared = $this->prepareImage($path);

        try {
            $ocr = new TesseractOCR($prepared);

            if ($binary) {
                $ocr->executable($binary);
            }

            return $ocr->lang('eng')->psm(4)->preserve_interword_spaces(1)->run();
        } catch (\Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'file' => 'Image OCR is unavailable on this server. Upload a text-based PDF or install Tesseract.',
            ]);
        } finally {
            if ($prepared !== $path) {
                @unlink($prepared);
            }
        }
    }

    private function prepareImage(string $path): string
    {
        if (! extension_loaded('gd')) {
            return $path;
        }

        $contents = @file_get_contents($path);
        $image = $contents ? @imagecreatefromstring($contents) : false;

        if (! $image) {
            return $path;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $scale = $width < 1600 ? min(3, max(2, (int) ceil(1600 / max(1, $width)))) : 1;
        $canvas = imagecreatetruecolor($width * $scale, $height * $scale);

        imagecopyresampled(
            $canvas,
            $image,
            0,
            0,
            0,
            0,
            $width * $scale,
            $height * $scale,
            $width,
            $height,
        );
        imagefilter($canvas, IMG_FILTER_GRAYSCALE);

        $output = tempnam(sys_get_temp_dir(), 'acadflow-ocr').'.png';

        imagepng($canvas, $output);
        imagedestroy($image);
        imagedestroy($canvas);

        return $output;
    }

    private function ocrPdf(string $path): string
    {
        $binary = config('schedule_import.binaries.pdftoppm') ?: $this->findBinary('pdftoppm');

        if (! $binary) {
            return '';
        }

        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'acadflow-'.Str::random(10);

        if (! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return '';
        }

        try {
            $process = new Process([
                $binary,
                '-png',
                '-r',
                '200',
                '-l',
                (string) config('schedule_import.max_ocr_pages', 10),
                $path,
                $directory.DIRECTORY_SEPARATOR.'page',
            ]);
            $process->setTimeout(120)->run();

            if (! $process->isSuccessful()) {
                return '';
            }

            $text = '';

            foreach (glob($directory.DIRECTORY_SEPARATOR.'*.png') ?: [] as $image) {
                $text .= $this->ocrImage($image)."\n";
            }

            return $text;
        } finally {
            $this->deleteDirectory($directory);
        }
    }

    private function findBinary(string $name): ?string
    {
        $candidates = [
            '/usr/bin/'.$name,
            '/usr/local/bin/'.$name,
            '/opt/homebrew/bin/'.$name,
        ];

        foreach ($candidates as $candidate) {
            if (is_executable($candidate)) {
                return $candidate;
            }
        }

        $command = DIRECTORY_SEPARATOR === '\\' ? 'where '.$name : 'command -v '.$name;

        try {
            $process = Process::fromShellCommandline($command);
            $process->run();

            if ($process->isSuccessful()) {
                return trim(strtok($process->getOutput(), "\r\n") ?: '');
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }

    private function markConflicts(array &$classes, Collection $existing): void
    {
        foreach ($classes as &$class) {
            foreach ($existing as $schedule) {
                if ($schedule->day !== $class['day']) {
                    continue;
                }

                if (! $this->overlaps($class['startTime'], $class['endTime'], $schedule->start_time, $schedule->end_time)) {
                    continue;
                }

                $class['conflict'] = true;
                $class['note'] = 'Overlaps existing '.($schedule->subject?->name ?? 'class')
                    .' '.$schedule->start_time.'–'.$schedule->end_time;
                $class['confidence'] = $class['confidence'] === 'High' ? 'Medium' : $class['confidence'];
                break;
            }
        }
        unset($class);

        $count = count($classes);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if ($classes[$i]['day'] !== $classes[$j]['day']) {
                    continue;
                }

                if (! $this->overlaps(
                    $classes[$i]['startTime'],
                    $classes[$i]['endTime'],
                    $classes[$j]['startTime'],
                    $classes[$j]['endTime'],
                )) {
                    continue;
                }

                $classes[$i]['conflict'] = true;
                $classes[$j]['conflict'] = true;
                $classes[$i]['note'] ??= 'Overlaps '.$classes[$j]['subject'].' in this import';
                $classes[$j]['note'] ??= 'Overlaps '.$classes[$i]['subject'].' in this import';
            }
        }
    }

    private function overlaps(string $start, string $end, string $otherStart, string $otherEnd): bool
    {
        return $start < $otherEnd && $end > $otherStart;
    }

    private function validTimeRange(string $day, string $start, string $end): bool
    {
        if (! in_array($day, ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], true)) {
            return false;
        }

        if (! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $start)) {
            return false;
        }

        if (! preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $end)) {
            return false;
        }

        return $end > $start;
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory.DIRECTORY_SEPARATOR.$item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($directory);
    }
}
