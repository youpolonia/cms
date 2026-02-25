<?php /** Analytics Admin Dashboard */ ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Analytics — Admin</title>
<style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;line-height:1.6}.wrap{max-width:1200px;margin:0 auto;padding:2rem}.top{display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem}h1{font-size:1.5rem;background:linear-gradient(135deg,#8b5cf6,#06b6d4);-webkit-background-clip:text;-webkit-text-fill-color:transparent}.nav a{color:#94a3b8;text-decoration:none;margin-left:1rem;font-size:.875rem}.nav a:hover{color:#e2e8f0}.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:2rem}.card{background:#1e293b;border:1px solid #334155;border-radius:.75rem;padding:1.25rem}.card .num{font-size:1.75rem;font-weight:700;color:#8b5cf6}.card .label{font-size:.75rem;color:#94a3b8;text-transform:uppercase}table{width:100%;border-collapse:collapse;background:#1e293b;border:1px solid #334155;border-radius:.75rem;overflow:hidden}th{background:#334155;text-align:left;padding:.75rem 1rem;font-size:.75rem;text-transform:uppercase;color:#94a3b8}td{padding:.75rem 1rem;border-top:1px solid #334155;font-size:.875rem}.back{display:inline-block;margin-bottom:1rem;color:#8b5cf6;text-decoration:none;font-size:.875rem}</style>
</head><body><div class="wrap"><a href="/admin" class="back">← Admin</a>
<div class="top"><h1>📊 Analytics — Dashboard</h1><div class="nav"><a href="/admin/analytics">Dashboard</a><a href="/admin/analytics/users">Users</a></div></div>
<div class="cards"><div class="card"><div class="num"><?= number_format($totalEvents) ?></div><div class="label">Total Events</div></div><div class="card"><div class="num"><?= number_format($todayEvents) ?></div><div class="label">Today</div></div><div class="card"><div class="num"><?= $totalGoals ?></div><div class="label">Goals</div></div><div class="card"><div class="num"><?= $totalReports ?></div><div class="label">Reports</div></div></div>
<?php if (!empty($topTypes)): ?>
<h2 style="margin-bottom:1rem;font-size:1.1rem">Event Types</h2>
<table><thead><tr><th>Event Type</th><th>Count</th></tr></thead><tbody>
<?php foreach ($topTypes as $t): ?><tr><td><?= htmlspecialchars($t['event_type']) ?></td><td><?= number_format($t['cnt']) ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</div></body></html>
