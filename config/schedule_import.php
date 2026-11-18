<?php

return [
    'binaries' => [
        'pdftotext' => env('PDFTOTEXT_PATH'),
        'pdftoppm' => env('PDFTOPPM_PATH'),
        'tesseract' => env('TESSERACT_PATH'),
    ],

    'max_size_kb' => env('SCHEDULE_IMPORT_MAX_KB', 10240),

    'max_ocr_pages' => env('SCHEDULE_IMPORT_MAX_OCR_PAGES', 10),

    'code_aliases' => [
        'HCL' => 'HCI',
        'TELEC' => 'IT ELEC',
    ],

    'colors' => [
        '#6266F0',
        '#0EA5E9',
        '#10B981',
        '#F59E0B',
        '#EF4444',
        '#8B5CF6',
        '#EC4899',
        '#14B8A6',
    ],
];
