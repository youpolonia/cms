@props(['content' => null, 'categories', 'contentTypes'])

<div class="mb-3">
    <label for="title" class="form-label">Title</label>
    <input type="text" class="form-control" id="title" name="title" 
           value="{{ old('title', $content?->title) }}" required>
</div>

<div class="mb-3">
    <label for="content_type" class="form-label">Content Type</label>
    <select class="form-select" id="content_type" name="content_type" required>
        @foreach($contentTypes as $type)
            <option value="{{ $type }}" 
                {{ old('content_type', $content?->content_type) == $type ? 'selected' : '' }}>
                {{ ucfirst($type) }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="content" class="form-label">Content</label>
    <textarea class="form-control" id="content" name="content" rows="10" required>{{ old('content', $content?->content) }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label">Categories</label>
    <div class="row">
        @foreach($categories as $category)
        <div class="col-md-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" 
                       name="categories[]" 
                       value="{{ $category->id }}" 
                       id="category-{{ $category->id }}"
                       {{ in_array($category->id, old('categories', $content?->categories->pluck('id')->toArray() ?? [])) ? 'checked' : '' }}>
                <label class="form-check-label" for="category-{{ $category->id }}">
                    {{ $category->name }}
                </label>
            </div>
        </div>
        @endforeach
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">SEO Settings</div>
    <div class="card-body">
        <div class="mb-3">
            <label for="seo_title" class="form-label">SEO Title</label>
            <input type="text" class="form-control" id="seo_title" name="seo_title" 
                   value="{{ old('seo_title', $content?->seo_title) }}">
        </div>
        <div class="mb-3">
            <label for="seo_description" class="form-label">SEO Description</label>
            <textarea class="form-control" id="seo_description" name="seo_description" rows="3">{{ old('seo_description', $content?->seo_description) }}</textarea>
        </div>
        <div class="mb-3">
            <label for="seo_keywords" class="form-label">SEO Keywords (comma separated)</label>
            <input type="text" class="form-control" id="seo_keywords" name="seo_keywords" 
                   value="{{ old('seo_keywords', $content?->seo_keywords ? implode(',', $content?->seo_keywords) : '') }}">
        </div>
    </div>
</div>