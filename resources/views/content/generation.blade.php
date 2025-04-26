@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Content Generation</h1>
    
    <form id="contentGenerationForm" class="space-y-4">
        @csrf
        
        <div>
            <x-select-input
                name="content_type"
                label="Content Type"
                :options="[
                    'blog_post' => 'Blog Post',
                    'product_description' => 'Product Description',
                    'faq' => 'FAQ',
                    'news_article' => 'News Article'
                ]"
                required
            />
        </div>
        
        <div>
            <x-textarea-input
                name="prompt"
                label="Content Prompt"
                rows="4"
                required
            />
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-select-input
                name="tone"
                label="Tone"
                :options="[
                    'informative' => 'Informative',
                    'persuasive' => 'Persuasive',
                    'friendly' => 'Friendly',
                    'neutral' => 'Neutral'
                ]"
            />
            
            <x-select-input
                name="style"
                label="Style"
                :options="[
                    'concise' => 'Concise',
                    'detailed' => 'Detailed',
                    'technical' => 'Technical',
                    'creative' => 'Creative'
                ]"
            />
        </div>
        
        <div class="flex items-center space-x-4">
            <x-checkbox-input
                name="seo_optimized"
                label="SEO Optimized"
            />
            
            <x-checkbox-input
                name="async"
                label="Process in Background"
            />
        </div>
        
        <div class="pt-4">
            <x-button type="submit">
                Generate Content
            </x-button>
        </div>
    </form>
    
    <div id="resultContainer" class="mt-8 hidden">
        <h2 class="text-xl font-semibold mb-4">Generated Content</h2>
        <div id="generatedContent" class="prose max-w-none"></div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('contentGenerationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const response = await fetch('/mcp/content/generate', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    });
    
    const result = await response.json();
    
    if (response.ok) {
        document.getElementById('generatedContent').innerHTML = result.content || result;
        document.getElementById('resultContainer').classList.remove('hidden');
    } else {
        alert('Error: ' + (result.error || 'Content generation failed'));
    }
});
</script>
@endpush
@endsection