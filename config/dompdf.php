<?php

return [

    'show_warnings' => false,

    'default_paper' => 'a4',

    'default_font' => 'sans-serif',

    'dpi' => 96,

    'enable_php' => false,

    'enable_javascript' => true,

    'enable_remote' => false,

    'font_height_ratio' => 1.1,

    'is_html5_parser_enabled' => true,

    'is_php_syntax_enabled' => false,

    'font_dir' => storage_path('fonts'),

    'font_cache' => storage_path('fonts'),

    'log_output_file' => storage_path('logs/dompdf.html'),

    'chroot' => realpath(base_path()),

    'temp_dir' => sys_get_temp_dir(),

    'allowed_protocols' => [
        'file://' => ['rules' => []],
        'http://' => ['rules' => []],
        'https://' => ['rules' => []],
    ],

    'pdfBackend' => 'CPDF',

];
