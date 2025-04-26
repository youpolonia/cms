<div class="spinner spinner-{{ $size }} text-{{ $color }}">
    <div class="spinner-inner"></div>
</div>

<style>
.spinner {
    display: inline-block;
    position: relative;
}
.spinner-sm { width: 1rem; height: 1rem; }
.spinner-md { width: 2rem; height: 2rem; }
.spinner-lg { width: 3rem; height: 3rem; }
.spinner-inner {
    border: 2px solid currentColor;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
    width: 100%;
    height: 100%;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>