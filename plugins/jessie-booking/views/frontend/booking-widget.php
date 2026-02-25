<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Book an Appointment</title>
<link rel="stylesheet" href="/plugins/jessie-booking/assets/css/booking-widget.css">
</head>
<body>
<div class="bw-container">
    <h2 class="bw-title">📅 Book an Appointment</h2>

    <!-- Step 1: Select Service -->
    <div class="bw-step active" id="step-1">
        <h3 class="bw-step-title">1. Choose a Service</h3>
        <div class="bw-services" id="bw-services"></div>
    </div>

    <!-- Step 2: Select Date & Time -->
    <div class="bw-step" id="step-2">
        <h3 class="bw-step-title">2. Pick Date & Time</h3>
        <div class="bw-date-picker">
            <input type="date" id="bw-date" min="" class="bw-input">
        </div>
        <div class="bw-slots" id="bw-slots"><p class="bw-hint">Select a date to see available times</p></div>
    </div>

    <!-- Step 3: Your Info -->
    <div class="bw-step" id="step-3">
        <h3 class="bw-step-title">3. Your Information</h3>
        <div class="bw-form">
            <input type="text" id="bw-name" placeholder="Your Name *" class="bw-input" required>
            <input type="email" id="bw-email" placeholder="Email *" class="bw-input" required>
            <input type="tel" id="bw-phone" placeholder="Phone (optional)" class="bw-input">
            <textarea id="bw-notes" placeholder="Notes (optional)" class="bw-input" rows="3"></textarea>
        </div>
        <div class="bw-summary" id="bw-summary"></div>
        <button type="button" class="bw-btn" onclick="submitBooking()" id="bw-submit">✅ Confirm Booking</button>
    </div>

    <!-- Step 4: Confirmation -->
    <div class="bw-step" id="step-4">
        <div class="bw-success">
            <div class="bw-check">✅</div>
            <h3>Booking Confirmed!</h3>
            <p id="bw-confirm-msg"></p>
            <button type="button" class="bw-btn bw-btn-outline" onclick="resetWidget()">Book Another</button>
        </div>
    </div>
</div>
<script src="/plugins/jessie-booking/assets/js/booking-widget.js"></script>
</body>
</html>
