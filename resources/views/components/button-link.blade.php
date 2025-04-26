<a href="{{ $href }}" class="btn-link btn-link-{{ $type }}">
    {{ $text }}
</a>

<style>
.btn-link {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
}
.btn-link-primary {
    background-color: #2563eb;
    color: white;
    border: 1px solid #1d4ed8;
}
.btn-link-secondary {
    background-color: #6b7280;
    color: white;
    border: 1px solid #4b5563;
}
.btn-link-success {
    background-color: #059669;
    color: white;
    border: 1px solid #047857;
}
.btn-link-danger {
    background-color: #dc2626;
    color: white;
    border: 1px solid #b91c1c;
}
</style>