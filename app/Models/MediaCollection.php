<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaCollection extends Model
{
    use SoftDeletes;

    protected $fillable = ['name'];

    public function media()
    {
        return $this->belongsToMany(Media::class);
    }

    public function addMedia(Media $media)
    {
        return $this->media()->attach($media);
    }

    public function search($query)
    {
        return $this->media()
            ->where('filename', 'like', "%{$query}%")
            ->orWhereJsonContains('metadata->tags', $query)
            ->get();
    }
}
