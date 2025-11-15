<?php

class ContentVersion extends Model {
    protected $table = 'content_versions';
    protected $fillable = [
        'content_id',
        'version_number',
        'data',
        'is_autosave',
        'author_id',
        'tenant_id'
    ];

    public function content() {
        return $this->belongsTo(Content::class);
    }

    public function author() {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tenant() {
        return $this->belongsTo(Tenant::class);
    }

    public function getDataAttribute($value) {
        return json_decode($value, true);
    }

    public function setDataAttribute($value) {
        $this->attributes['data'] = json_encode($value);
    }
}
