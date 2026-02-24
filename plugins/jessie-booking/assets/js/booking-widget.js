// Jessie Booking Widget JS
(function() {
    var state = { serviceId: null, serviceName: '', date: '', time: '', endTime: '', price: 0, step: 1 };

    // Load services on init
    fetch('/api/booking/services').then(function(r){ return r.json(); }).then(function(data) {
        if (!data.ok) return;
        var el = document.getElementById('bw-services');
        var html = '';
        data.services.forEach(function(s) {
            html += '<div class="bw-service" onclick="selectService(' + s.id + ',\'' + esc(s.name) + '\',' + (s.price||0) + ',\'' + esc(s.color||'#6366f1') + '\')">';
            html += '<div class="dot" style="background:' + esc(s.color||'#6366f1') + '"></div>';
            html += '<div class="info"><div class="name">' + esc(s.name) + '</div>';
            html += '<div class="meta">' + s.duration_minutes + ' min' + (s.category ? ' • ' + esc(s.category) : '') + '</div></div>';
            html += '<div class="price">' + (s.price > 0 ? '$' + parseFloat(s.price).toFixed(2) : 'Free') + '</div>';
            html += '</div>';
        });
        el.innerHTML = html || '<p class="bw-hint">No services available</p>';
    });

    // Set min date to today
    var dateInput = document.getElementById('bw-date');
    dateInput.min = new Date().toISOString().split('T')[0];
    dateInput.addEventListener('change', function() { loadSlots(this.value); });

    window.selectService = function(id, name, price, color) {
        state.serviceId = id;
        state.serviceName = name;
        state.price = price;
        document.querySelectorAll('.bw-service').forEach(function(el){ el.classList.remove('selected'); });
        event.currentTarget.classList.add('selected');
        setTimeout(function(){ goToStep(2); }, 200);
    };

    function loadSlots(date) {
        state.date = date;
        var el = document.getElementById('bw-slots');
        el.innerHTML = '<p class="bw-hint">Loading...</p>';

        fetch('/api/booking/availability?date=' + date + '&service_id=' + state.serviceId)
        .then(function(r){ return r.json(); })
        .then(function(data) {
            if (!data.ok || !data.slots.length) { el.innerHTML = '<p class="bw-hint">No available slots for this date</p>'; return; }
            var html = '';
            data.slots.forEach(function(s) {
                var cls = s.available ? 'bw-slot' : 'bw-slot unavailable';
                html += '<div class="' + cls + '" ' + (s.available ? 'onclick="selectSlot(\'' + s.start + '\',\'' + s.end + '\')"' : '') + '>' + s.start + '</div>';
            });
            el.innerHTML = html;
        });
    }

    window.selectSlot = function(start, end) {
        state.time = start;
        state.endTime = end;
        document.querySelectorAll('.bw-slot').forEach(function(el){ el.classList.remove('selected'); });
        event.currentTarget.classList.add('selected');
        setTimeout(function(){
            updateSummary();
            goToStep(3);
        }, 200);
    };

    function updateSummary() {
        var el = document.getElementById('bw-summary');
        var dateStr = new Date(state.date + 'T12:00:00').toLocaleDateString('en-US', {weekday:'long', month:'long', day:'numeric', year:'numeric'});
        el.innerHTML = '<strong>Booking Summary</strong>'
            + '📋 ' + esc(state.serviceName) + '<br>'
            + '📅 ' + dateStr + '<br>'
            + '🕐 ' + state.time + ' – ' + state.endTime
            + (state.price > 0 ? '<br>💰 $' + state.price.toFixed(2) : '');
    }

    window.submitBooking = function() {
        var name = document.getElementById('bw-name').value.trim();
        var email = document.getElementById('bw-email').value.trim();
        if (!name || !email) { alert('Please fill in your name and email'); return; }

        var btn = document.getElementById('bw-submit');
        btn.disabled = true;
        btn.textContent = '⏳ Booking...';

        fetch('/api/booking/book', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                service_id: state.serviceId,
                date: state.date,
                start_time: state.time,
                customer_name: name,
                customer_email: email,
                customer_phone: document.getElementById('bw-phone').value,
                notes: document.getElementById('bw-notes').value
            })
        })
        .then(function(r){ return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.textContent = '✅ Confirm Booking';
            if (data.ok) {
                document.getElementById('bw-confirm-msg').textContent = 'Your ' + state.serviceName + ' appointment on ' + state.date + ' at ' + state.time + ' has been ' + data.status + '. Check your email for details.';
                goToStep(4);
            } else {
                alert(data.error || 'Booking failed. Please try again.');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.textContent = '✅ Confirm Booking';
            alert('Network error. Please try again.');
        });
    };

    window.resetWidget = function() {
        state = { serviceId: null, serviceName: '', date: '', time: '', endTime: '', price: 0, step: 1 };
        document.querySelectorAll('.bw-service, .bw-slot').forEach(function(el){ el.classList.remove('selected'); });
        document.getElementById('bw-name').value = '';
        document.getElementById('bw-email').value = '';
        document.getElementById('bw-phone').value = '';
        document.getElementById('bw-notes').value = '';
        goToStep(1);
    };

    function goToStep(n) {
        state.step = n;
        document.querySelectorAll('.bw-step').forEach(function(el){ el.classList.remove('active'); });
        document.getElementById('step-' + n).classList.add('active');
    }

    function esc(s) {
        if (!s) return '';
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }
})();
