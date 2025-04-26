<div class="mb-3">
    <label for="title" class="form-label">Title</label>
    <input type="text" class="form-control @error('title') is-invalid @enderror" 
           id="title" name="title" value="{{ old('title', $content->title ?? '') }}" required>
    @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="content" class="form-label">Content</label>
    <textarea class="form-control @error('content') is-invalid @enderror" 
              id="content" name="content" rows="10" required>{{ old('content', $content->content ?? '') }}</textarea>
    @error('content')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="content_type" class="form-label">Content Type</label>
    <select class="form-select @error('content_type') is-invalid @enderror" 
            id="content_type" name="content_type" required>
        <option value="page" @selected(old('content_type', $content->content_type ?? '') === 'page')>Page</option>
        <option value="post" @selected(old('content_type', $content->content_type ?? '') === 'post')>Post</option>
        <option value="article" @selected(old('content_type', $content->content_type ?? '') === 'article')>Article</option>
    </select>
    @error('content_type')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="card mb-3">
    <div class="card-header">SEO Settings</div>
    <div class="card-body">
        <div class="mb-3">
            <label for="seo_title" class="form-label">SEO Title</label>
            <input type="text" class="form-control @error('seo_title') is-invalid @enderror" 
                   id="seo_title" name="seo_title" value="{{ old('seo_title', $content->seo_title ?? '') }}">
            @error('seo_title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="seo_description" class="form-label">SEO Description</label>
            <textarea class="form-control @error('seo_description') is-invalid @enderror" 
                      id="seo_description" name="seo_description" rows="3">{{ old('seo_description', $content->seo_description ?? '') }}</textarea>
            @error('seo_description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="seo_keywords" class="form-label">SEO Keywords (comma separated)</label>
            <input type="text" class="form-control @error('seo_keywords') is-invalid @enderror" 
                   id="seo_keywords" name="seo_keywords" 
                   value="{{ old('seo_keywords', isset($content->seo_keywords) ? implode(',', $content->seo_keywords) : '' }}">
            @error('seo_keywords')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>