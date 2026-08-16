<?php

return [
    'default' => env('FILESYSTEM_DISK', 'public'),
    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => false,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_PUBLIC_STORAGE_URL', '/media'),
            'serve' => true,
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

    ],
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
