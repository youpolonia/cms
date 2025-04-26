<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'filename',
        'path',
        'metadata',
        'uploader_id'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function collections()
    {
        return $this->belongsToMany(MediaCollection::class);
    }
}
