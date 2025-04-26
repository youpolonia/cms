<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiffComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'content_id',
        'version1_id',
        'version2_id',
        'content1_hash',
        'content2_hash',
        'comment',
        'diff_range'
    ];

    protected $casts = [
        'diff_range' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function version1()
    {
        return $this->belongsTo(ContentVersion::class, 'version1_id');
    }

    public function version2()
    {
        return $this->belongsTo(ContentVersion::class, 'version2_id');
    }
}