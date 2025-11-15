<?php
/**
 * Event Bus Monitor View
 * @var array $listeners
 * @var array $events
 * @var bool $isLive
 */
?><div class="dev-tools-container">
    <h2>Event Bus Monitor</h2>
    
    <div class="controls">
        <button id="toggle-live" class="btn <?= $isLive ? 'btn-danger' : 'btn-primary' ?>">
            <?= $isLive ? 'Stop Live' : 'Start Live'  ?>
        </button>
    </div>

    <div class="row">
        <div class="col-md-6">
            <h3>Registered Listeners</h3>
            <div class="listeners-container">
                <?php foreach ($listeners as $event => $handlers): ?>
                    <div class="event-group">
                        <h4><?= htmlspecialchars($event) ?></h4>
                        <ul>
                            <?php foreach ($handlers as $handler): ?>
                                <li>
                                    <?= is_array($handler['handler']) ?
                                        implode('::', $handler['handler']) :
                                        (is_string($handler['handler']) ? $handler['handler'] : 'closure') ?>                                    <span class="badge">Priority: <?= $handler['priority'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-md-6">
            <h3>Event Log</h3>
            <div id="events-container" class="events-container">
                <?php foreach ($events as $event): ?>
                    <div class="event-item <?= $event['success'] ? 'success' : 'error' ?>">
                        <strong><?= htmlspecialchars($event['event']) ?></strong>
                        <span><?= date('Y-m-d H:i:s', (int)$event['timestamp']) ?></span>
                        <div class="handler">Handler: <?= htmlspecialchars($event['handler']) ?></div>
                        <?php if (!$event['success']): ?>
                            <div class="error-msg">Error: <?= htmlspecialchars($event['error']) ?></div>
                        <?php endif; ?>
                        <pre><?= htmlspecialchars(json_encode($event['payload'], JSON_PRETTY_PRINT)) ?></pre>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="/admin/dev-tools/event-monitor/live-view.js"></script>
