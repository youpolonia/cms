<div class="alert alert-{{ $type }}" role="alert">
    {{ $message }}
</div>

<style>
.alert {
    padding: 1rem;
    border-radius: 0.25rem;
    margin-bottom: 1rem;
}
.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}
.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.alert-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeeba;
}
.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>