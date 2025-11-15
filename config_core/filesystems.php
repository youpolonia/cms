<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => 'public',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => __DIR__.'/../../storage/app/private',
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],
'public' => [
    'driver' => 'local',
    'root' => __DIR__.'/../../storage/app/public',
    'url' => (getenv('APP_URL') ?: 'http://localhost').'/storage',
    'visibility' => 'public',
    'throw' => false,
    'report' => false,
],

'pages' => [
    'driver' => 'local',
    'root' => __DIR__.'/../../storage/app/pages',
    'throw' => false,
    'report' => false,
],



    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        \CMS_ROOT . '/public/storage' => \CMS_ROOT . '/storage/app/public',
    ],

];
