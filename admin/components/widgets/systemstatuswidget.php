<?php
class SystemStatusWidget {
    public static function render() {
        $systemStatus = [
            'CMS Version' => '1.0.0',
            'PHP Version' => phpversion(),
            'Database' => 'Connected',
            'Storage' => '85% free'
        ];

        ob_start();
        ?>
        <div class="dashboard-widget system-status">
            <h3>System Status</h3>
            <table>
                <?php foreach($systemStatus as $key => $value): ?>
                    <tr>
                    <th><?= $key ?></th>
                    <td><?= $value ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }
}
