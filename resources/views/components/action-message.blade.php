<div class="action-message action-message-{{ $type }}">
    {{ $message }}
</div>

<style>
.action-message {
    padding: 0.75rem 1rem;
    border-radius: 0.375rem;
    margin: 0.5rem 0;
    font-size: 0.875rem;
}
.action-message-info {
    background-color: #e0f2fe;
    color: #0369a1;
    border-left: 4px solid #38bdf8;
}
.action-message-success {
    background-color: #dcfce7;
    color: #166534;
    border-left: 4px solid #4ade80;
}
.action-message-warning {
    background-color: #fef9c3;
    color: #854d0e;
    border-left: 4px solid #facc15;
}
.action-message-error {
    background-color: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #f87171;
}
</style>