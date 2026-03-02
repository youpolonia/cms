<?php
/**
 * Booking Page — Full multi-step booking wizard with Payment Gateway
 * Steps: 1. Select Service → 2. Choose Date/Time → 3. Your Details → 4. Payment (if paid) → Confirmation
 * Routes: /booking, /booking/success, /booking/cancel
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 4)); }
require_once CMS_ROOT . '/db.php';
require_once __DIR__ . '/../../includes/class-booking-service.php';
require_once __DIR__ . '/../../includes/class-booking-staff.php';
require_once __DIR__ . '/../../includes/class-booking-calendar.php';
require_once __DIR__ . '/../../includes/class-booking-appointment.php';
require_once CMS_ROOT . '/core/payment-gateway.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$services = BookingService::getAll('active');
$siteName = function_exists('get_setting') ? get_setting('site_name', 'Our Business') : 'Our Business';
$currency = function_exists('get_setting') ? get_setting('payment_currency', 'USD') : 'USD';
$paymentMethods = PaymentGateway::getAvailableMethods();
$hasOnlinePayment = PaymentGateway::hasOnlinePayment();

if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
if (empty($_SESSION['booking_csrf'])) { $_SESSION['booking_csrf'] = bin2hex(random_bytes(16)); }
$csrfToken = $_SESSION['booking_csrf'];

$action = $bookingAction ?? '';

// ─── PAYMENT SUCCESS CALLBACK ───
$paymentVerified = false;
$paymentError = '';
$confirmedAppt = null;
if ($action === 'success') {
    $provider = $_GET['provider'] ?? '';
    $apptId = (int)($_GET['appt'] ?? 0);
    $verifyParams = [];
    if ($provider === 'stripe') {
        $verifyParams['session_id'] = $_GET['session_id'] ?? '';
    } elseif ($provider === 'paypal') {
        $verifyParams['order_id'] = $_GET['token'] ?? '';
    }
    if ($apptId > 0 && $provider) {
        $result = PaymentGateway::verifyAndComplete($provider, $verifyParams);
        if (!empty($result['success'])) {
            BookingAppointment::update($apptId, ['status' => 'confirmed']);
            $confirmedAppt = BookingAppointment::get($apptId);
            $paymentVerified = true;
            if (file_exists(__DIR__ . '/../../includes/class-booking-notifications.php')) {
                require_once __DIR__ . '/../../includes/class-booking-notifications.php';
                BookingNotifications::sendConfirmation($apptId);
            }
        } else {
            $paymentError = $result['error'] ?? 'Payment verification failed';
        }
    } else {
        $paymentError = 'Invalid callback parameters';
    }
}

// ─── PAYMENT CANCEL ───
$paymentCancelled = ($action === 'cancel');
$cancelApptId = (int)($_GET['appt'] ?? 0);
if ($paymentCancelled && $cancelApptId > 0) {
    BookingAppointment::update($cancelApptId, ['status' => 'cancelled']);
}

// ─── FORM POST HANDLER ───
$success = false;
$error = '';
$appointmentId = 0;
$offlineInstructions = '';

if ($action === '' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrfToken, $_POST['csrf_token'] ?? '')) {
        $error = 'Session expired. Please try again.';
    } else {
        $serviceId = (int)($_POST['service_id'] ?? 0);
        $staffId = !empty($_POST['staff_id']) ? (int)$_POST['staff_id'] : null;
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? '';

        if ($serviceId < 1 || empty($date) || empty($time)) {
            $error = 'Please select a service, date and time.';
        } elseif (!BookingCalendar::isSlotAvailable($date, $time, $serviceId, $staffId)) {
            $error = 'This time slot is no longer available. Please choose another.';
        } else {
            $service = BookingService::get($serviceId);
            $duration = (int)($service['duration'] ?? 30);
            $endTime = date('H:i', strtotime("{$time} + {$duration} minutes"));
            $price = (float)($service['price'] ?? 0);
            $needsPayment = $price > 0 && !empty($paymentMethods);

            if ($needsPayment && in_array($paymentMethod, ['stripe', 'paypal'])) {
                $initialStatus = 'pending_payment';
            } elseif ($needsPayment && $paymentMethod === 'bank_transfer') {
                $initialStatus = 'pending';
            } else {
                $initialStatus = 'confirmed';
            }

            $appointmentId = BookingAppointment::create([
                'service_id'     => $serviceId,
                'staff_id'       => $staffId,
                'customer_name'  => trim($_POST['customer_name'] ?? ''),
                'customer_email' => trim($_POST['customer_email'] ?? ''),
                'customer_phone' => trim($_POST['customer_phone'] ?? ''),
                'date'           => $date,
                'start_time'     => $time,
                'end_time'       => $endTime,
                'notes'          => trim($_POST['notes'] ?? ''),
                'status'         => $initialStatus,
            ]);

            if ($appointmentId > 0) {
                if ($needsPayment && in_array($paymentMethod, ['stripe', 'paypal'])) {
                    $siteUrl = rtrim(function_exists('get_setting') ? get_setting('site_url', '') : '', '/');
                    $payResult = PaymentGateway::processPayment($paymentMethod, $price, [
                        'items' => [['name' => $service['name'], 'price' => $price, 'quantity' => 1]],
                        'customer_email' => trim($_POST['customer_email'] ?? ''),
                        'reference_id'   => 'booking_' . $appointmentId,
                        'description'    => 'Booking: ' . $service['name'],
                        'metadata'       => ['appointment_id' => (string)$appointmentId, 'type' => 'booking'],
                        'success_url'    => $siteUrl . '/booking/success?provider=' . $paymentMethod . '&appt=' . $appointmentId . ($paymentMethod === 'stripe' ? '&session_id={CHECKOUT_SESSION_ID}' : ''),
                        'cancel_url'     => $siteUrl . '/booking/cancel?appt=' . $appointmentId,
                    ]);
                    if (!empty($payResult['redirect'])) {
                        header('Location: ' . $payResult['redirect']);
                        exit;
                    } elseif (!empty($payResult['error'])) {
                        $error = 'Payment error: ' . $payResult['error'];
                        BookingAppointment::update($appointmentId, ['status' => 'cancelled']);
                    }
                } elseif ($needsPayment && $paymentMethod === 'bank_transfer') {
                    $offlineInstructions = PaymentGateway::processPayment('bank_transfer', $price, [])['instructions'] ?? '';
                    $success = true;
                } else {
                    $success = true;
                    if (file_exists(__DIR__ . '/../../includes/class-booking-notifications.php')) {
                        require_once __DIR__ . '/../../includes/class-booking-notifications.php';
                        BookingNotifications::sendConfirmation($appointmentId);
                    }
                }
                $_SESSION['booking_csrf'] = bin2hex(random_bytes(16));
                $csrfToken = $_SESSION['booking_csrf'];
            } else {
                $error = 'Failed to create appointment. Please try again.';
            }
        }
    }
}

$servicePrices = [];
foreach ($services as $s) { $servicePrices[(int)$s['id']] = (float)($s['price'] ?? 0); }
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book an Appointment — <?= h($siteName) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#f8fafc;--card:#fff;--border:#e2e8f0;--text:#1e293b;--muted:#64748b;--primary:#6366f1;--primary-light:rgba(99,102,241,.08);--success:#22c55e;--danger:#ef4444;--warning:#f59e0b}
*{box-sizing:border-box;margin:0;padding:0}body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);line-height:1.5}
.bk-wrap{max-width:780px;margin:0 auto;padding:40px 20px}
.bk-header{text-align:center;margin-bottom:36px}.bk-header h1{font-size:1.8rem;font-weight:700;margin-bottom:8px}.bk-header p{color:var(--muted);font-size:.95rem}
.bk-stepper{display:flex;justify-content:center;gap:0;margin-bottom:36px;flex-wrap:wrap}
.bk-step{display:flex;align-items:center;gap:8px;padding:10px 18px;font-size:.85rem;font-weight:600;color:var(--muted)}
.bk-step .num{width:28px;height:28px;border-radius:50%;background:var(--border);display:flex;align-items:center;justify-content:center;font-size:.8rem;color:var(--muted);transition:.2s}
.bk-step.active .num{background:var(--primary);color:#fff}.bk-step.active{color:var(--primary)}
.bk-step.done .num{background:var(--success);color:#fff}.bk-step.done{color:var(--success)}
.bk-step-line{width:40px;height:2px;background:var(--border);align-self:center}.bk-step-line.done{background:var(--success)}
.bk-panel{display:none}.bk-panel.active{display:block}
.bk-card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:24px;margin-bottom:16px}
.bk-services{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px}
.bk-service{background:var(--card);border:2px solid var(--border);border-radius:12px;padding:20px;cursor:pointer;transition:.15s;text-align:center}
.bk-service:hover{border-color:#cbd5e1;transform:translateY(-2px)}.bk-service.selected{border-color:var(--primary);background:var(--primary-light)}
.bk-service-name{font-size:1rem;font-weight:600;margin-bottom:6px}.bk-service-meta{font-size:.8rem;color:var(--muted)}
.bk-service-price{font-size:1.1rem;font-weight:700;color:var(--primary);margin-top:8px}
.bk-calendar-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px}.bk-calendar-header h3{font-size:1rem}
.bk-nav-btn{background:none;border:1px solid var(--border);border-radius:8px;padding:6px 14px;cursor:pointer;font-size:.85rem}.bk-nav-btn:hover{background:var(--primary-light)}
.bk-days{display:grid;grid-template-columns:repeat(7,1fr);gap:6px;text-align:center;margin-bottom:20px}
.bk-day-label{font-size:.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;padding:6px 0}
.bk-day{padding:10px 4px;border-radius:8px;font-size:.85rem;cursor:pointer;transition:.1s}
.bk-day:hover:not(.disabled):not(.empty){background:var(--primary-light)}.bk-day.selected{background:var(--primary);color:#fff;font-weight:600}
.bk-day.disabled{color:#cbd5e1;cursor:not-allowed}.bk-day.today{font-weight:700;box-shadow:inset 0 -2px 0 var(--primary)}.bk-day.empty{cursor:default}
.bk-slots{display:flex;flex-wrap:wrap;gap:8px}
.bk-slot{padding:8px 16px;border:1px solid var(--border);border-radius:8px;font-size:.85rem;cursor:pointer;transition:.15s}
.bk-slot:hover{border-color:var(--primary);background:var(--primary-light)}.bk-slot.selected{background:var(--primary);color:#fff;border-color:var(--primary)}
.bk-no-slots{color:var(--muted);font-size:.9rem;padding:20px;text-align:center}
.bk-form-group{margin-bottom:16px}.bk-form-group label{display:block;font-size:.85rem;font-weight:600;margin-bottom:6px}
.bk-form-group input,.bk-form-group textarea{width:100%;padding:12px 14px;border:1px solid var(--border);border-radius:10px;font-size:.9rem;font-family:inherit}
.bk-form-group input:focus,.bk-form-group textarea:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(99,102,241,.1)}
.bk-form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.bk-payment-methods{display:grid;gap:10px}
.bk-pay-method{background:var(--card);border:2px solid var(--border);border-radius:12px;padding:16px 20px;cursor:pointer;transition:.15s;display:flex;align-items:center;gap:14px}
.bk-pay-method:hover{border-color:#cbd5e1}.bk-pay-method.selected{border-color:var(--primary);background:var(--primary-light)}
.bk-pay-method input[type=radio]{display:none}
.bk-pay-icon{font-size:1.5rem;width:40px;text-align:center}
.bk-pay-info{flex:1}.bk-pay-name{font-weight:600;font-size:.95rem}.bk-pay-desc{font-size:.8rem;color:var(--muted)}
.bk-price-summary{background:var(--primary-light);border:1px solid rgba(99,102,241,.2);border-radius:10px;padding:16px 20px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center}
.bk-price-label{font-size:.9rem;color:var(--muted)}.bk-price-amount{font-size:1.4rem;font-weight:700;color:var(--primary)}
.bk-confirm{text-align:center}.bk-confirm-icon{font-size:3rem;margin-bottom:16px}
.bk-confirm h2{font-size:1.4rem;margin-bottom:8px;color:var(--success)}
.bk-confirm-details{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:20px;margin:20px auto;max-width:400px;text-align:left;font-size:.9rem}
.bk-confirm-details dt{color:var(--muted);font-size:.75rem;text-transform:uppercase;font-weight:600;margin-top:10px}
.bk-confirm-details dt:first-child{margin-top:0}.bk-confirm-details dd{font-weight:600;margin-bottom:4px}
.bk-instructions{background:#fef3c7;border:1px solid #fde68a;border-radius:12px;padding:20px;margin:20px auto;max-width:500px;text-align:left;font-size:.9rem;color:#92400e}
.bk-summary{background:var(--primary-light);border:1px solid rgba(99,102,241,.2);border-radius:10px;padding:14px 18px;margin-bottom:20px;display:none;font-size:.85rem}
.bk-summary span{font-weight:600;color:var(--primary)}
.bk-actions{display:flex;gap:12px;margin-top:24px}
.bk-btn{padding:12px 28px;border-radius:10px;font-size:.9rem;font-weight:600;cursor:pointer;transition:.15s;border:none}
.bk-btn-primary{background:var(--primary);color:#fff;flex:1}.bk-btn-primary:hover{opacity:.9}.bk-btn-primary:disabled{opacity:.5;cursor:not-allowed}
.bk-btn-ghost{background:none;border:1px solid var(--border);color:var(--text)}.bk-btn-ghost:hover{background:#f1f5f9}
.bk-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--danger);padding:12px;border-radius:10px;margin-bottom:16px;font-size:.85rem;text-align:center}
.bk-warning{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);color:#b45309;padding:12px;border-radius:10px;margin-bottom:16px;font-size:.85rem;text-align:center}
.bk-staff-list{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}
.bk-staff-chip{padding:8px 16px;border:1px solid var(--border);border-radius:20px;font-size:.8rem;cursor:pointer;transition:.15s}
.bk-staff-chip:hover{border-color:var(--primary)}.bk-staff-chip.selected{background:var(--primary);color:#fff;border-color:var(--primary)}
@media(max-width:600px){.bk-stepper{gap:4px}.bk-step-line{width:20px}.bk-step{padding:8px 10px;font-size:.75rem}.bk-form-row{grid-template-columns:1fr}.bk-services{grid-template-columns:1fr}}
</style>
</head><body>
<div class="bk-wrap">
    <div class="bk-header">
        <h1>📅 Book an Appointment</h1>
        <p>Choose a service, pick a time, and we'll take care of the rest.</p>
    </div>

    <?php if ($action === 'success'): ?>
        <?php if ($paymentVerified && $confirmedAppt): ?>
        <div class="bk-confirm">
            <div class="bk-confirm-icon">✅</div>
            <h2>Payment Successful — Booking Confirmed!</h2>
            <p style="color:var(--muted)">Your payment has been processed and your appointment is confirmed.</p>
            <?php $svc = BookingService::get((int)($confirmedAppt['service_id'] ?? 0)); ?>
            <div class="bk-confirm-details"><dl>
                <dt>Service</dt><dd><?= h($svc['name'] ?? '') ?></dd>
                <dt>Date</dt><dd><?= date('l, F j, Y', strtotime($confirmedAppt['date'] ?? '')) ?></dd>
                <dt>Time</dt><dd><?= date('g:i A', strtotime($confirmedAppt['start_time'] ?? '')) ?> — <?= date('g:i A', strtotime($confirmedAppt['end_time'] ?? '')) ?></dd>
                <dt>Name</dt><dd><?= h($confirmedAppt['customer_name'] ?? '') ?></dd>
                <dt>Booking #</dt><dd><?= (int)$confirmedAppt['id'] ?></dd>
            </dl></div>
            <a href="/booking" class="bk-btn bk-btn-primary" style="display:inline-block;text-decoration:none;margin-top:16px">Book Another</a>
        </div>
        <?php else: ?>
        <div class="bk-error"><?= h($paymentError ?: 'Payment verification failed.') ?></div>
        <div style="text-align:center"><a href="/booking" class="bk-btn bk-btn-primary" style="display:inline-block;text-decoration:none;margin-top:16px">Try Again</a></div>
        <?php endif; ?>

    <?php elseif ($paymentCancelled): ?>
        <div class="bk-warning">⚠️ Payment was cancelled. Your appointment has not been confirmed.</div>
        <div style="text-align:center"><a href="/booking" class="bk-btn bk-btn-primary" style="display:inline-block;text-decoration:none;margin-top:16px">Start Over</a></div>

    <?php elseif ($success): ?>
        <div class="bk-confirm">
            <div class="bk-confirm-icon">✅</div>
            <h2>Booking <?= $offlineInstructions ? 'Received' : 'Confirmed' ?>!</h2>
            <p style="color:var(--muted)"><?= $offlineInstructions ? 'Your appointment is pending payment confirmation.' : 'Your appointment has been booked. A confirmation has been sent to your email.' ?></p>
            <?php $appt = BookingAppointment::get($appointmentId); $svc = BookingService::get((int)($appt['service_id'] ?? 0)); ?>
            <div class="bk-confirm-details"><dl>
                <dt>Service</dt><dd><?= h($svc['name'] ?? '') ?></dd>
                <dt>Date</dt><dd><?= date('l, F j, Y', strtotime($appt['date'] ?? '')) ?></dd>
                <dt>Time</dt><dd><?= date('g:i A', strtotime($appt['start_time'] ?? '')) ?> — <?= date('g:i A', strtotime($appt['end_time'] ?? '')) ?></dd>
                <dt>Name</dt><dd><?= h($appt['customer_name'] ?? '') ?></dd>
                <dt>Booking #</dt><dd><?= $appointmentId ?></dd>
            </dl></div>
            <?php if ($offlineInstructions): ?>
            <div class="bk-instructions">
                <strong>🏦 Payment Instructions:</strong><br><?= nl2br(h($offlineInstructions)) ?>
                <br><br><strong>Amount:</strong> <?= h($currency) ?> <?= number_format((float)($svc['price'] ?? 0), 2) ?>
                <br><strong>Reference:</strong> BOOKING-<?= $appointmentId ?>
            </div>
            <?php endif; ?>
            <a href="/booking" class="bk-btn bk-btn-primary" style="display:inline-block;text-decoration:none;margin-top:16px">Book Another</a>
        </div>

    <?php else: ?>
    <?php if ($error): ?><div class="bk-error"><?= h($error) ?></div><?php endif; ?>

    <div class="bk-stepper" id="bkStepper">
        <div class="bk-step active" data-step="1"><span class="num">1</span> Service</div>
        <div class="bk-step-line" data-line="1"></div>
        <div class="bk-step" data-step="2"><span class="num">2</span> Date & Time</div>
        <div class="bk-step-line" data-line="2"></div>
        <div class="bk-step" data-step="3"><span class="num">3</span> Details</div>
        <div class="bk-step-line bk-pay-step-el" data-line="3" style="display:none"></div>
        <div class="bk-step bk-pay-step-el" data-step="4" style="display:none"><span class="num">4</span> Payment</div>
    </div>

    <div class="bk-summary" id="bkSummary">Selected: <span id="sumService"></span> · <span id="sumDate"></span> · <span id="sumTime"></span></div>

    <form method="post" action="/booking" id="bookingForm">
        <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
        <input type="hidden" name="service_id" id="fServiceId" value="">
        <input type="hidden" name="staff_id" id="fStaffId" value="">
        <input type="hidden" name="date" id="fDate" value="">
        <input type="hidden" name="time" id="fTime" value="">
        <input type="hidden" name="payment_method" id="fPayMethod" value="">

        <!-- Step 1: Service -->
        <div class="bk-panel active" id="panel1">
            <div class="bk-card">
                <h3 style="margin-bottom:16px">Select a Service</h3>
                <?php if (empty($services)): ?>
                    <p style="color:var(--muted);text-align:center;padding:30px">No services available at this time.</p>
                <?php else: ?>
                <div class="bk-services">
                    <?php foreach ($services as $svc): ?>
                    <div class="bk-service" data-id="<?= (int)$svc['id'] ?>" data-name="<?= h($svc['name']) ?>" data-duration="<?= (int)($svc['duration'] ?? 30) ?>" data-price="<?= (float)($svc['price'] ?? 0) ?>">
                        <div class="bk-service-name"><?= h($svc['name']) ?></div>
                        <div class="bk-service-meta"><?= (int)($svc['duration'] ?? 30) ?> min</div>
                        <?php if ((float)($svc['price'] ?? 0) > 0): ?>
                        <div class="bk-service-price"><?= h($currency) ?> <?= number_format((float)$svc['price'], 2) ?></div>
                        <?php else: ?>
                        <div class="bk-service-price" style="color:var(--success)">Free</div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="bk-actions"><button type="button" class="bk-btn bk-btn-primary" id="btnStep1" disabled>Continue →</button></div>
        </div>

        <!-- Step 2: Date & Time -->
        <div class="bk-panel" id="panel2">
            <div class="bk-card" id="staffCard" style="display:none">
                <h3 style="margin-bottom:12px">Choose a Specialist <small style="color:var(--muted);font-weight:400">(optional)</small></h3>
                <div class="bk-staff-list" id="staffList"></div>
            </div>
            <div class="bk-card">
                <div class="bk-calendar-header">
                    <button type="button" class="bk-nav-btn" id="prevMonth">← Prev</button>
                    <h3 id="calTitle">Loading...</h3>
                    <button type="button" class="bk-nav-btn" id="nextMonth">Next →</button>
                </div>
                <div class="bk-days" id="calGrid"></div>
            </div>
            <div class="bk-card" id="slotsCard" style="display:none">
                <h3 style="margin-bottom:12px">Available Times</h3>
                <div class="bk-slots" id="slotsGrid"></div>
            </div>
            <div class="bk-actions">
                <button type="button" class="bk-btn bk-btn-ghost" id="btnBack2">← Back</button>
                <button type="button" class="bk-btn bk-btn-primary" id="btnStep2" disabled>Continue →</button>
            </div>
        </div>

        <!-- Step 3: Customer Details -->
        <div class="bk-panel" id="panel3">
            <div class="bk-card">
                <h3 style="margin-bottom:16px">Your Details</h3>
                <div class="bk-form-group"><label>Full Name *</label><input type="text" name="customer_name" required placeholder="Jane Doe"></div>
                <div class="bk-form-row">
                    <div class="bk-form-group"><label>Email *</label><input type="email" name="customer_email" required placeholder="jane@example.com"></div>
                    <div class="bk-form-group"><label>Phone</label><input type="tel" name="customer_phone" placeholder="+1 555 123 4567"></div>
                </div>
                <div class="bk-form-group"><label>Notes <small style="color:var(--muted);font-weight:400">(optional)</small></label><textarea name="notes" rows="3" placeholder="Any special requests..."></textarea></div>
            </div>
            <div class="bk-actions">
                <button type="button" class="bk-btn bk-btn-ghost" id="btnBack3">← Back</button>
                <button type="button" class="bk-btn bk-btn-primary" id="btnStep3">Continue →</button>
            </div>
        </div>

        <!-- Step 4: Payment (only for paid services) -->
        <div class="bk-panel" id="panel4">
            <div class="bk-card">
                <h3 style="margin-bottom:16px">Payment</h3>
                <div class="bk-price-summary">
                    <span class="bk-price-label">Total Amount</span>
                    <span class="bk-price-amount" id="payTotal"><?= h($currency) ?> 0.00</span>
                </div>
                <?php if (empty($paymentMethods)): ?>
                <p style="color:var(--muted);text-align:center;padding:20px">No payment methods configured. Please contact us.</p>
                <?php else: ?>
                <div class="bk-payment-methods">
                    <?php foreach ($paymentMethods as $idx => $pm): ?>
                    <label class="bk-pay-method <?= $idx === 0 ? 'selected' : '' ?>" data-method="<?= h($pm['id']) ?>">
                        <input type="radio" name="_pay_method" value="<?= h($pm['id']) ?>" <?= $idx === 0 ? 'checked' : '' ?>>
                        <span class="bk-pay-icon"><?= $pm['icon'] ?></span>
                        <div class="bk-pay-info">
                            <div class="bk-pay-name"><?= h($pm['name']) ?></div>
                            <div class="bk-pay-desc"><?= h($pm['description']) ?></div>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="bk-actions">
                <button type="button" class="bk-btn bk-btn-ghost" id="btnBack4">← Back</button>
                <button type="submit" class="bk-btn bk-btn-primary" id="btnPay">💳 Pay & Book</button>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
(function(){
    'use strict';
    var currency=<?= json_encode($currency) ?>;
    var hasPayMethods=<?= !empty($paymentMethods)?'true':'false' ?>;
    var selectedService=null,selectedStaffId=null,selectedDate=null,selectedTime=null,isPaid=false;
    var today=new Date(),calYear=today.getFullYear(),calMonth=today.getMonth();
    var panels=[document.getElementById('panel1'),document.getElementById('panel2'),document.getElementById('panel3'),document.getElementById('panel4')];
    var summary=document.getElementById('bkSummary');
    var payEls=document.querySelectorAll('.bk-pay-step-el');

    function togglePayStep(){payEls.forEach(function(e){e.style.display=isPaid&&hasPayMethods?'':'none'});}

    function showPanel(idx){
        panels.forEach(function(p,i){if(p)p.classList.toggle('active',i===idx)});
        document.querySelectorAll('#bkStepper .bk-step').forEach(function(s){
            var n=parseInt(s.dataset.step);s.classList.remove('active','done');
            if(n<idx+1)s.classList.add('done');else if(n===idx+1)s.classList.add('active');
        });
        document.querySelectorAll('#bkStepper .bk-step-line').forEach(function(l){
            l.classList.toggle('done',parseInt(l.dataset.line)<idx+1);
        });
        window.scrollTo({top:0,behavior:'smooth'});
    }

    function updateSummary(){
        if(!summary)return;
        summary.style.display=(selectedService||selectedDate||selectedTime)?'block':'none';
        document.getElementById('sumService').textContent=selectedService?selectedService.name:'—';
        document.getElementById('sumDate').textContent=selectedDate||'—';
        document.getElementById('sumTime').textContent=selectedTime||'—';
    }

    // Step 1
    document.querySelectorAll('.bk-service').forEach(function(el){
        el.addEventListener('click',function(){
            document.querySelectorAll('.bk-service').forEach(function(s){s.classList.remove('selected')});
            this.classList.add('selected');
            selectedService={id:this.dataset.id,name:this.dataset.name,duration:parseInt(this.dataset.duration),price:parseFloat(this.dataset.price)};
            document.getElementById('fServiceId').value=selectedService.id;
            document.getElementById('btnStep1').disabled=false;
            isPaid=selectedService.price>0;
            togglePayStep();
            updateSummary();
        });
    });
    document.getElementById('btnStep1')?.addEventListener('click',function(){
        if(!selectedService)return;
        selectedDate=null;selectedTime=null;selectedStaffId=null;
        document.getElementById('fDate').value='';document.getElementById('fTime').value='';document.getElementById('fStaffId').value='';
        loadStaff(selectedService.id);renderCalendar();
        document.getElementById('slotsCard').style.display='none';document.getElementById('btnStep2').disabled=true;
        showPanel(1);
    });

    // Step 2: Staff
    function loadStaff(sid){
        fetch('/api/booking/staff?service_id='+sid).then(function(r){return r.json()}).then(function(d){
            var list=document.getElementById('staffList'),card=document.getElementById('staffCard');
            if(!d.success||!d.data||d.data.length<2){card.style.display='none';return;}
            card.style.display='block';
            list.innerHTML='<div class="bk-staff-chip selected" data-id="">Any available</div>';
            d.data.forEach(function(s){list.innerHTML+='<div class="bk-staff-chip" data-id="'+s.id+'">'+(s.name||'Staff #'+s.id)+'</div>'});
            list.querySelectorAll('.bk-staff-chip').forEach(function(c){
                c.addEventListener('click',function(){
                    list.querySelectorAll('.bk-staff-chip').forEach(function(x){x.classList.remove('selected')});
                    this.classList.add('selected');selectedStaffId=this.dataset.id||null;
                    document.getElementById('fStaffId').value=selectedStaffId||'';
                    if(selectedDate)loadSlots(selectedDate);
                });
            });
        }).catch(function(){document.getElementById('staffCard').style.display='none'});
    }

    // Step 2: Calendar
    function renderCalendar(){
        var M=['January','February','March','April','May','June','July','August','September','October','November','December'];
        document.getElementById('calTitle').textContent=M[calMonth]+' '+calYear;
        var grid=document.getElementById('calGrid'),html=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'].map(function(l){return'<div class="bk-day-label">'+l+'</div>'}).join('');
        var fd=new Date(calYear,calMonth,1).getDay(),dim=new Date(calYear,calMonth+1,0).getDate();
        var ts=today.getFullYear()+'-'+String(today.getMonth()+1).padStart(2,'0')+'-'+String(today.getDate()).padStart(2,'0');
        for(var i=0;i<fd;i++)html+='<div class="bk-day empty"></div>';
        for(var d=1;d<=dim;d++){
            var ds=calYear+'-'+String(calMonth+1).padStart(2,'0')+'-'+String(d).padStart(2,'0');
            var c='bk-day';if(ds<ts)c+=' disabled';if(ds===ts)c+=' today';if(ds===selectedDate)c+=' selected';
            html+='<div class="'+c+'" data-date="'+ds+'">'+d+'</div>';
        }
        grid.innerHTML=html;
        grid.querySelectorAll('.bk-day:not(.disabled):not(.empty)').forEach(function(el){
            el.addEventListener('click',function(){
                grid.querySelectorAll('.bk-day').forEach(function(x){x.classList.remove('selected')});
                this.classList.add('selected');selectedDate=this.dataset.date;selectedTime=null;
                document.getElementById('fDate').value=selectedDate;document.getElementById('fTime').value='';
                document.getElementById('btnStep2').disabled=true;updateSummary();loadSlots(selectedDate);
            });
        });
    }
    document.getElementById('prevMonth')?.addEventListener('click',function(){calMonth--;if(calMonth<0){calMonth=11;calYear--;}renderCalendar()});
    document.getElementById('nextMonth')?.addEventListener('click',function(){calMonth++;if(calMonth>11){calMonth=0;calYear++;}renderCalendar()});

    function loadSlots(date){
        var card=document.getElementById('slotsCard'),grid=document.getElementById('slotsGrid');
        card.style.display='block';grid.innerHTML='<div class="bk-no-slots">Loading...</div>';
        var url='/api/booking/available-slots?date='+date+'&service_id='+selectedService.id;
        if(selectedStaffId)url+='&staff_id='+selectedStaffId;
        fetch(url).then(function(r){return r.json()}).then(function(data){
            if(!data.success||!data.data||!data.data.length){grid.innerHTML='<div class="bk-no-slots">No available times. Try another day.</div>';return;}
            grid.innerHTML='';
            data.data.forEach(function(slot){
                var el=document.createElement('div');el.className='bk-slot';
                el.textContent=slot.display||slot.time||slot;el.dataset.time=slot.time||slot;
                el.addEventListener('click',function(){
                    grid.querySelectorAll('.bk-slot').forEach(function(s){s.classList.remove('selected')});
                    this.classList.add('selected');selectedTime=this.dataset.time;
                    document.getElementById('fTime').value=selectedTime;document.getElementById('btnStep2').disabled=false;updateSummary();
                });
                grid.appendChild(el);
            });
        }).catch(function(){grid.innerHTML='<div class="bk-no-slots">Error loading slots.</div>'});
    }

    document.getElementById('btnBack2')?.addEventListener('click',function(){showPanel(0)});
    document.getElementById('btnStep2')?.addEventListener('click',function(){if(selectedTime)showPanel(2)});

    // Step 3
    document.getElementById('btnBack3')?.addEventListener('click',function(){showPanel(1)});
    document.getElementById('btnStep3')?.addEventListener('click',function(){
        var n=document.querySelector('input[name="customer_name"]'),e=document.querySelector('input[name="customer_email"]');
        if(!n.value.trim()){n.focus();return;}if(!e.value.trim()||!e.validity.valid){e.focus();return;}
        if(isPaid&&hasPayMethods){
            var t=document.getElementById('payTotal');if(t)t.textContent=currency+' '+selectedService.price.toFixed(2);
            var r=document.querySelector('input[name="_pay_method"]:checked');
            if(r)document.getElementById('fPayMethod').value=r.value;
            showPanel(3);
        }else{
            document.getElementById('fPayMethod').value='';
            document.getElementById('bookingForm').submit();
        }
    });

    // Step 4: Payment
    document.querySelectorAll('.bk-pay-method').forEach(function(el){
        el.addEventListener('click',function(){
            document.querySelectorAll('.bk-pay-method').forEach(function(m){m.classList.remove('selected')});
            this.classList.add('selected');this.querySelector('input[type=radio]').checked=true;
            document.getElementById('fPayMethod').value=this.dataset.method;
        });
    });
    document.getElementById('btnBack4')?.addEventListener('click',function(){showPanel(2)});

    document.getElementById('bookingForm')?.addEventListener('submit',function(e){
        if(!selectedService||!selectedDate||!selectedTime){e.preventDefault();alert('Please complete all steps.');return;}
        var btn=document.getElementById('btnPay');if(btn){btn.disabled=true;btn.textContent='⏳ Processing...';}
    });
})();
</script>
</body></html>
