<button type="button" class="btn btn-{{ $type }}">
    {{ $text }}
</button>

<style>
.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
}
.btn-primary {
    background-color: #2563eb;
    color: white;
    border: 1px solid #1d4ed8;
}
.btn-secondary {
    background-color: #6b7280;
    color: white;
    border: 1px solid #4b5563;
}
.btn-success {
    background-color: #059669;
    color: white;
    border: 1px solid #047857;
}
.btn-danger {
    background-color: #dc2626;
    color: white;
    border: 1px solid #b91c1c;
}
</style>