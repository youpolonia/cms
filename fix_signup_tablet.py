import sys

old = '''    /* Signup module responsive */
    .tb4-canvas[data-device="mobile"] .tb4-signup-preview {
        flex-direction: column;
        gap: 8px;
    }
    .tb4-canvas[data-device="mobile"] .tb4-signup-preview input,
    .tb4-canvas[data-device="mobile"] .tb4-signup-preview button {
        width: 100% !important;
        flex: none !important;
    }'''

new = '''    /* Signup module responsive */
    .tb4-canvas[data-device="tablet"] .tb4-signup-preview,
    .tb4-canvas[data-device="mobile"] .tb4-signup-preview {
        flex-direction: column;
        gap: 8px;
    }
    .tb4-canvas[data-device="tablet"] .tb4-signup-preview input,
    .tb4-canvas[data-device="tablet"] .tb4-signup-preview button,
    .tb4-canvas[data-device="mobile"] .tb4-signup-preview input,
    .tb4-canvas[data-device="mobile"] .tb4-signup-preview button {
        width: 100% !important;
        flex: none !important;
    }'''

filepath = sys.argv[1]
with open(filepath, 'r') as f:
    content = f.read()

if old in content:
    content = content.replace(old, new)
    with open(filepath, 'w') as f:
        f.write(content)
    print("Signup tablet CSS fixed")
else:
    print("Pattern not found")
