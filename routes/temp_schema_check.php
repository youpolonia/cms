<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Route::get('/temp-schema-check', function() {
    try {
        $columns = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableColumns('content_versions');
            
        return array_map(function($column) {
            return [
                'name' => $column->getName(),
                'type' => $column->getType()->getName(),
                'notnull' => $column->getNotnull(),
                'default' => $column->getDefault(),
            ];
        }, $columns);
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
});
