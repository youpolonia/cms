<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/temp-schema-check', function() {
    return [
        'lines_added_exists' => Schema::hasColumn('content_version_diffs', 'lines_added'),
        'lines_removed_exists' => Schema::hasColumn('content_version_diffs', 'lines_removed')
    ];
});
