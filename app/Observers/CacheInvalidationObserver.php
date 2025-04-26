<?php

namespace App\Observers;

use App\Traits\CacheableRepository;

class CacheInvalidationObserver
{
    use CacheableRepository;

    public function created($model)
    {
        $this->invalidateCache([$this->getModelTag($model)]);
    }

    public function updated($model)
    {
        $this->invalidateCache([$this->getModelTag($model)]);
    }

    public function deleted($model)
    {
        $this->invalidateCache([$this->getModelTag($model)]);
    }

    protected function getModelTag($model): string
    {
        return strtolower(class_basename($model)) . '_' . $model->getKey();
    }
}