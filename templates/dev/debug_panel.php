<?php
/**
 * Developer Debug Panel
 * Only visible when DEV_MODE is true
 */
if (!defined('DEV_MODE') || !DEV_MODE) return;
?><div class="debug-panel" style="
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #333;
    color: #fff;
    padding: 10px;
    font-family: monospace;
    font-size: 12px;
    z-index: 9999;
    border-top: 2px solid #f00;
">
    <h3 style="margin: 0 0 10px 0;">Developer Debug Panel</h3>
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
        <div>
            <strong>System Info:</strong><br>
            PHP: <?= phpversion() ?><br>
            Memory: <?= round(memory_get_usage()/1024/1024, 2) ?>MB / <?= round(memory_get_peak_usage()/1024/1024, 2) ?>MB<br>
            Extensions: <?= implode(', ', get_loaded_extensions())  ?>
        </div>
        <div>
            <strong>Request:</strong><br>
            Method: <?= $_SERVER['REQUEST_METHOD'] ?><br>
            URI: <?= $_SERVER['REQUEST_URI'] ?><br>
            IP: <?= $_SERVER['REMOTE_ADDR']  ?>
        </div>
        <div>
            <strong>Configuration:</strong><br>
            DEV_MODE: <?= DEV_MODE ? 'ON' : 'OFF' ?><br>
            ENVIRONMENT: <?= defined('ENVIRONMENT') ? ENVIRONMENT : 'not set' ?><br>
            CMS_VERSION: <?= defined('CMS_VERSION') ? CMS_VERSION : 'not set' 
?>        </div>
    </div>
</div>
