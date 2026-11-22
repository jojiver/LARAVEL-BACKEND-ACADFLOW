<?php

namespace App\Support;

class ScheduleTextParser
{
    private const DAY_PATTERN = '/\b(mon(?:day)?|tue(?:sday)?|wed(?:nesday)?|thu(?:rsday)?|fri(?:day)?|sat(?:urday)?)\b/i';

    private const COMPACT_DAYS_PATTERN = '/\b(MWEF|MWF|TTh|MW|WF|MTh|TF|MTWThF|MTWTF|TThF)\b/i';

    private const SLASH_DAYS_PATTERN = '/\b([MTWFS](?:Th|[mtwfs])?(?:\s*[\/,&]\s*(?:Th|[mtwfs])){1,5})\b/i';

    private const RANGE_PATTERN = '/(\d{1,2})(?::(\d{2}))?\s*([ap]\.?m\.?)?\s*(?:-|–|—|to)\s*(\d{1,2})(?::(\d{2}))?\s*([ap]\.?m\.?)?/i';

    private const ROOM_PATTERN = '/\b((?:lab(?:oratory)?|room|rm\.?|bldg\.?|building|tent)\s*[-#]?\s*[a-z0-9][a-z0-9-]*|online|virtual|hybrid)\b/i';

    /** Parses raw schedule text into structured class rows. */
    public function parse(string $text): array
    {
        $lines = preg_split('/\R/', $text) ?: [];
        $classes = [];
        $currentDays = [];
        $seen = [];
        $duplicates = 0;

        foreach ($lines as $line) {
            $line = $this->clean($line);

            if ($line === '') {
                continue;
            }

            $lineDays = $this->days($line);

            if ($lineDays !== [] && ! $this->hasRange($line)) {
                $currentDays = $lineDays;
                continue;
            }

            if (! preg_match(self::RANGE_PATTERN, $line, $range, PREG_OFFSET_CAPTURE)) {
                continue;
            }

            $times = $this->times($range);

            if ($times === null) {
                continue;
            }

            $days = $lineDays !== [] ? $lineDays : $currentDays;

            if ($days === []) {
                continue;
            }

            $remainder = substr($line, 0, $range[0][1])
                .' '.substr($line, $range[0][1] + strlen($range[0][0]));
            $remainder = $this->removeDays($remainder);
            [$subject, $room, $roomNote] = $this->subjectRoom($remainder);

            $confidence = $subject === 'Untitled class'
                ? 'Low'
                : ($room !== null ? 'High' : 'Medium');

            foreach ($days as $day) {
                $key = strtolower($day.'|'.$times[0].'|'.$subject);

                if (isset($seen[$key])) {
                    $duplicates++;
                    continue;
                }

                $seen[$key] = true;

                $classes[] = [
                    'subject' => $subject,
                    'day' => $day,
                    'startTime' => $times[0],
                    'endTime' => $times[1],
                    'room' => $room,
                    'time' => $this->display($times[0]).' – '.$this->display($times[1]),
                    'confidence' => $confidence,
                    'note' => $roomNote,
                    'conflict' => false,
                    'selected' => true,
                ];
            }

            if ($lineDays !== []) {
                $currentDays = $lineDays;
            }
        }

        foreach ($classes as $index => &$class) {
            $class['id'] = 'imp-'.($index + 1);
        }
        unset($class);

        return [
            'classes' => $classes,
            'duplicates' => $duplicates,
        ];
    }

    /** Normalizes a line of OCR or PDF text. */
    private function clean(string $line): string
    {
        $line = str_replace(["\xC2\xA0", "\t"], ' ', $line);
        $line = preg_replace('/\s{3,}/u', ' | ', $line) ?? $line;
        $line = preg_replace('/\s+/u', ' ', $line) ?? $line;

        return trim($line);
    }

    /** Checks whether a line contains a time range. */
    private function hasRange(string $line): bool
    {
        return (bool) preg_match(self::RANGE_PATTERN, $line);
    }

    /** Extracts weekday tokens from a line. */
    private function days(string $line): array
    {
        $days = [];

        if (preg_match_all(self::DAY_PATTERN, $line, $matches)) {
            foreach ($matches[1] as $token) {
                $days[] = $this->dayName($token);
            }
        }

        if ($days !== []) {
            return array_values(array_unique($days));
        }

        if (preg_match_all(self::COMPACT_DAYS_PATTERN, $line, $matches)) {
            foreach ($matches[1] as $token) {
                foreach ($this->compactDayTokens($token) as $day) {
                    $days[] = $day;
                }
            }
        }

        if ($days === [] && preg_match(self::SLASH_DAYS_PATTERN, $line, $match)) {
            foreach (preg_split('/\s*[\/,&]\s*/', $match[1]) as $token) {
                foreach ($this->compactDayTokens($token) as $day) {
                    $days[] = $day;
                }
            }
        }

        return array_values(array_unique($days));
    }

    /** Maps a day token to its short weekday name. */
    private function dayName(string $token): string
    {
        return match (strtolower(substr($token, 0, 3))) {
            'mon' => 'Mon',
            'tue' => 'Tue',
            'wed' => 'Wed',
            'thu' => 'Thu',
            'fri' => 'Fri',
            default => 'Sat',
        };
    }

    /** Expands compact tokens such as MWF or TTH. */
    private function compactDayTokens(string $token): array
    {
        $token = trim($token);
        $upper = strtoupper($token);

        if ($upper === 'TH') {
            return ['Thu'];
        }

        $map = [
            'MWEF' => ['Mon', 'Wed', 'Fri'],
            'MWF' => ['Mon', 'Wed', 'Fri'],
            'TTH' => ['Tue', 'Thu'],
            'MW' => ['Mon', 'Wed'],
            'WF' => ['Wed', 'Fri'],
            'MTH' => ['Mon', 'Thu'],
            'TF' => ['Tue', 'Fri'],
            'MTWTHF' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            'MTWTF' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            'TTHF' => ['Tue', 'Thu', 'Fri'],
        ];

        if (isset($map[$upper])) {
            return $map[$upper];
        }

        $days = [];
        $remaining = $token;

        while ($remaining !== '') {
            if (stripos($remaining, 'Th') === 0) {
                $days[] = 'Thu';
                $remaining = substr($remaining, 2);
                continue;
            }

            $letter = strtoupper($remaining[0]);
            $remaining = substr($remaining, 1);

            $day = match ($letter) {
                'M' => 'Mon',
                'T' => 'Tue',
                'W' => 'Wed',
                'F' => 'Fri',
                'S' => 'Sat',
                default => null,
            };

            if ($day !== null) {
                $days[] = $day;
            }
        }

        return array_values(array_unique($days));
    }

    /** Removes day tokens from extracted text. */
    private function removeDays(string $text): string
    {
        $text = preg_replace(self::DAY_PATTERN, ' ', $text) ?? $text;
        $text = preg_replace(self::COMPACT_DAYS_PATTERN, ' ', $text) ?? $text;
        $text = preg_replace('/\b(M|T|W|Th|F|S)(?:\s*[\/,&]\s*(?:M|T|W|Th|F|S)){1,5}\b/i', ' ', $text) ?? $text;
        $text = preg_replace('/\b(time|schedule|subject|day|instructor|units?)\b/i', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text, " \t\n\r\0\x0B-–—|,:;•");
    }

    /** Parses and normalizes a start/end time range. */
    private function times(array $range): ?array
    {
        $group = fn (int $index) => isset($range[$index]) ? (string) $range[$index][0] : '';

        $startHour = (int) $group(1);
        $startMinute = $group(2) === '' ? 0 : (int) $group(2);
        $endHour = (int) $group(4);
        $endMinute = $group(5) === '' ? 0 : (int) $group(5);

        $startMeridiem = $this->meridiem($group(3));
        $endMeridiem = $this->meridiem($group(6));

        if ($startMeridiem === null && $endMeridiem !== null) {
            $startMeridiem = $startHour === 12
                ? $endMeridiem
                : ($startHour <= $endHour
                    ? $endMeridiem
                    : ($endMeridiem === 'pm' ? 'am' : 'pm'));
        }

        if ($endMeridiem === null && $startMeridiem !== null) {
            $endMeridiem = $startMeridiem;
        }

        $start = $this->to24($startHour, $startMinute, $startMeridiem);
        $end = $this->to24($endHour, $endMinute, $endMeridiem);

        if ($start === null || $end === null) {
            return null;
        }

        if ($end <= $start && $endHour < 12 && $end + 12 <= 23 * 60 + 59) {
            $end += 12 * 60;
        }

        if ($end <= $start) {
            return null;
        }

        return [$this->format24($start), $this->format24($end)];
    }

    /** Normalizes an AM/PM marker. */
    private function meridiem(string $value): ?string
    {
        $value = strtolower(str_replace('.', '', trim($value)));

        if ($value === '') {
            return null;
        }

        return str_starts_with($value, 'p') ? 'pm' : 'am';
    }

    /** Converts an hour and minute to 24-hour minutes. */
    private function to24(int $hour, int $minute, ?string $meridiem): ?int
    {
        if ($hour > 23 || $minute > 59) {
            return null;
        }

        if ($meridiem === 'pm' && $hour < 12) {
            $hour += 12;
        } elseif ($meridiem === 'am' && $hour === 12) {
            $hour = 0;
        } elseif ($meridiem === null && $hour > 0 && $hour < 7) {
            $hour += 12;
        }

        return $hour * 60 + $minute;
    }

    /** Formats minutes as HH:MM. */
    private function format24(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    /** Formats 24-hour time as a readable time. */
    private function display(string $time): string
    {
        [$hour, $minute] = array_map('intval', explode(':', $time));
        $suffix = $hour >= 12 ? 'PM' : 'AM';
        $hour = $hour % 12 ?: 12;

        return $hour.':'.str_pad((string) $minute, 2, '0', STR_PAD_LEFT).' '.$suffix;
    }

    /** Extracts the subject, room and room note. */
    private function subjectRoom(string $text): array
    {
        $rooms = [];
        $cut = null;

        if (preg_match_all(self::ROOM_PATTERN, $text, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $rooms[] = trim($match[0]);
                $cut ??= $match[1];
            }
        }

        if ($rooms === [] && preg_match('/\s([A-Z]?\d{3,4})\s+([A-Z]?\d{3,4})\s+\d+\s+/u', $text, $match, PREG_OFFSET_CAPTURE)) {
            $rooms[] = $match[1][0];
            $cut = $match[0][1];

            if ($match[1][0] !== $match[2][0]) {
                $rooms[] = $match[2][0];
            }
        }

        if ($cut !== null) {
            $text = substr($text, 0, $cut);
        }

        $segments = array_values(array_filter(
            array_map('trim', explode('|', $text)),
            fn (string $segment) => $segment !== '',
        ));

        $subject = $this->subjectFromSegments($segments);
        $subject = preg_replace('/\s+/u', ' ', $subject) ?? $subject;
        $subject = trim($subject, " \t\n\r\0\x0B-–—|,:;•");

        $unique = array_values(array_unique(array_map('strtoupper', $rooms)));
        $note = count($unique) > 1 ? 'Rooms: '.implode(' / ', $unique) : null;

        return [$subject !== '' ? $subject : 'Untitled class', $rooms[0] ?? null, $note];
    }

    /** Chooses the subject code or title from parsed columns. */
    private function subjectFromSegments(array $segments): string
    {
        if ($segments === []) {
            return '';
        }

        if (count($segments) > 1 && $this->looksLikeSubjectCode($segments[0])) {
            return $this->normalizeSubjectCode($segments[0]);
        }

        $sorted = $segments;
        usort($sorted, fn (string $a, string $b) => mb_strlen($b) <=> mb_strlen($a));
        $subject = $sorted[0];

        if (preg_match('/^([A-Za-z]{2,6}(?:\s+[A-Za-z]{2,6})?\s*\d{1,2})\s+\S/u', $subject, $match)) {
            return $this->normalizeSubjectCode($match[1]);
        }

        return $this->stripSubjectCode($subject);
    }

    /** Applies known OCR code corrections. */
    private function normalizeSubjectCode(string $code): string
    {
        $code = mb_strtoupper(preg_replace('/\s+/u', ' ', trim($code)) ?? $code);
        $parts = explode(' ', $code);
        $first = $parts[0] ?? '';
        $aliases = config('schedule_import.code_aliases', []);

        if (isset($aliases[$first])) {
            array_shift($parts);

            return trim($aliases[$first].' '.implode(' ', $parts));
        }

        return $code;
    }

    /** Detects whether a text segment looks like a code. */
    private function looksLikeSubjectCode(string $value): bool
    {
        $value = trim($value);

        if ($value === '' || mb_strlen($value) > 24) {
            return false;
        }

        if (! preg_match('/^[A-Za-z]{2,10}(?:\s+[A-Za-z]{2,8}){0,2}\s*\d{0,2}$/', $value)) {
            return false;
        }

        return (bool) preg_match('/\d/', $value)
            || $value === mb_strtoupper($value)
            || mb_strlen($value) === 2;
    }

    /** Removes a leading subject code from a title. */
    private function stripSubjectCode(string $subject): string
    {
        $patterns = [
            '/^(?:IT\s+TRACK|IT\s+ELEC(?:TIVE)?)\s+(?:EL\s+)?\d+\s+/i',
            '/^(?:IT\s+TRACK|IT\s+ELEC(?:TIVE)?)\s+/i',
            '/^[A-Za-z]{2,6}\s+[A-Za-z]{2,6}\s+\d{1,2}\s+/',
            '/^[A-Za-z]{2,6}\s+\d{1,2}\s+/',
        ];

        foreach ($patterns as $pattern) {
            $stripped = preg_replace($pattern, '', $subject);

            if ($stripped !== null && $stripped !== $subject) {
                return trim($stripped);
            }
        }

        return trim($subject);
    }
}
