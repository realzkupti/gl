<?php

return [
    'show_warnings' => false,
    'public_path' => public_path(),
    'convert_entities' => true,
    'options' => [
        'isRemoteEnabled' => true,
        'isHtml5ParserEnabled' => true,
        'defaultMediaType' => 'print',
        'defaultPaperSize' => 'a4',
        'defaultFont' => 'Sarabun',
        'fontDir' => resource_path('fonts'),
        'fontCache' => storage_path('fonts'),
        'chroot' => base_path(),
    ],
    // Explicit font family mapping for Thai
    'font_dir' => resource_path('fonts'),
    'font_cache' => storage_path('fonts'),
    'font_family' => [
        'Sarabun' => [
            'R' => resource_path('fonts/Sarabun-Regular.ttf'),
            'B' => resource_path('fonts/Sarabun-Bold.ttf'),
        ],
    ],
];

