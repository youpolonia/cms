/**
 * Jessie CMS — Shared Map Component (Leaflet.js)
 * Usage:
 *   JessieMap.init('#map', { center: [51.5, -0.12], zoom: 13 });
 *   JessieMap.addMarker([51.5, -0.12], { title: 'London', popup: '<b>Hello</b>' });
 *   JessieMap.addMarkers([{lat, lng, title, popup, icon}]);
 *   JessieMap.fitMarkers();
 * 
 * Requires Leaflet CSS+JS loaded before this file:
 *   <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9/dist/leaflet.css">
 *   <script src="https://unpkg.com/leaflet@1.9/dist/leaflet.js"></script>
 */
(function(window) {
    'use strict';

    var JM = window.JessieMap = {};
    var map = null;
    var markers = [];
    var markerGroup = null;

    // Custom icon colors
    var ICONS = {};
    function getIcon(color) {
        color = color || 'blue';
        if (ICONS[color]) return ICONS[color];
        var colors = {
            blue: '#6366f1', red: '#ef4444', green: '#22c55e', orange: '#f59e0b',
            purple: '#8b5cf6', pink: '#ec4899', teal: '#14b8a6', gray: '#64748b'
        };
        var hex = colors[color] || color;
        var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="25" height="41" viewBox="0 0 25 41">'
            + '<path d="M12.5 0C5.6 0 0 5.6 0 12.5C0 21.9 12.5 41 12.5 41S25 21.9 25 12.5C25 5.6 19.4 0 12.5 0z" fill="' + hex + '"/>'
            + '<circle cx="12.5" cy="12.5" r="5" fill="#fff"/></svg>';
        ICONS[color] = L.icon({
            iconUrl: 'data:image/svg+xml;base64,' + btoa(svg),
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [0, -35]
        });
        return ICONS[color];
    }

    /**
     * Initialize map
     * @param {string|HTMLElement} container - CSS selector or DOM element
     * @param {Object} opts - { center: [lat,lng], zoom: 13, style: 'default'|'dark' }
     */
    JM.init = function(container, opts) {
        opts = opts || {};
        var el = typeof container === 'string' ? document.querySelector(container) : container;
        if (!el || typeof L === 'undefined') {
            console.error('JessieMap: container not found or Leaflet not loaded');
            return null;
        }

        // Set minimum height if needed
        if (!el.style.height && el.offsetHeight < 100) {
            el.style.height = '400px';
        }

        var center = opts.center || [51.505, -0.09];
        var zoom = opts.zoom || 13;

        map = L.map(el, {
            scrollWheelZoom: opts.scrollZoom !== false,
            zoomControl: opts.zoomControl !== false
        }).setView(center, zoom);

        // Tile layer
        var tileUrl, tileAttr;
        if (opts.style === 'dark') {
            tileUrl = 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
            tileAttr = '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>';
        } else {
            tileUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            tileAttr = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>';
        }
        L.tileLayer(tileUrl, { attribution: tileAttr, maxZoom: 19 }).addTo(map);

        markerGroup = L.featureGroup().addTo(map);
        markers = [];

        return map;
    };

    /**
     * Add single marker
     */
    JM.addMarker = function(latlng, opts) {
        if (!map) return null;
        opts = opts || {};
        var marker = L.marker(latlng, {
            icon: getIcon(opts.color),
            title: opts.title || ''
        }).addTo(markerGroup);

        if (opts.popup) {
            marker.bindPopup(opts.popup, { maxWidth: 280 });
        }
        if (opts.onClick) {
            marker.on('click', opts.onClick);
        }
        markers.push(marker);
        return marker;
    };

    /**
     * Add multiple markers from array
     * @param {Array} items - [{lat, lng, title, popup, color, url, onClick}]
     */
    JM.addMarkers = function(items) {
        if (!map || !items) return;
        items.forEach(function(item) {
            if (!item.lat || !item.lng) return;
            var popup = item.popup || '';
            if (!popup && item.title) {
                popup = '<div style="min-width:150px">';
                if (item.image) popup += '<img src="' + item.image + '" style="width:100%;max-height:120px;object-fit:cover;border-radius:6px;margin-bottom:8px">';
                popup += '<strong>' + item.title + '</strong>';
                if (item.address) popup += '<br><small style="color:#64748b">' + item.address + '</small>';
                if (item.price) popup += '<br><span style="color:#6366f1;font-weight:700">' + item.price + '</span>';
                if (item.url) popup += '<br><a href="' + item.url + '" style="color:#6366f1;font-size:.85rem">View details →</a>';
                popup += '</div>';
            }
            JM.addMarker([item.lat, item.lng], {
                title: item.title || '',
                popup: popup,
                color: item.color || 'blue',
                onClick: item.onClick
            });
        });
    };

    /**
     * Fit map to show all markers
     */
    JM.fitMarkers = function(padding) {
        if (!map || !markerGroup || markers.length === 0) return;
        try {
            map.fitBounds(markerGroup.getBounds(), {
                padding: [padding || 40, padding || 40],
                maxZoom: 15
            });
        } catch (e) { /* single marker fallback */ }
    };

    /**
     * Clear all markers
     */
    JM.clearMarkers = function() {
        if (markerGroup) markerGroup.clearLayers();
        markers = [];
    };

    /**
     * Get underlying Leaflet map instance
     */
    JM.getMap = function() { return map; };

    /**
     * Geocode address using Nominatim (free, no API key)
     * @param {string} address
     * @returns {Promise<{lat, lng, display_name}>}
     */
    JM.geocode = function(address) {
        return fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(address) + '&limit=1')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && data.length > 0) {
                    return { lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon), display_name: data[0].display_name };
                }
                return null;
            });
    };

    /**
     * Reverse geocode lat/lng to address
     */
    JM.reverseGeocode = function(lat, lng) {
        return fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                return data ? { address: data.display_name, details: data.address } : null;
            });
    };

    /**
     * Add "locate me" button
     */
    JM.addLocateButton = function() {
        if (!map) return;
        var btn = L.control({ position: 'topleft' });
        btn.onAdd = function() {
            var div = L.DomUtil.create('div', 'leaflet-bar');
            div.innerHTML = '<a href="#" title="My Location" style="display:flex;align-items:center;justify-content:center;width:30px;height:30px;font-size:16px;text-decoration:none">📍</a>';
            div.querySelector('a').addEventListener('click', function(e) {
                e.preventDefault();
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(pos) {
                        var latlng = [pos.coords.latitude, pos.coords.longitude];
                        map.setView(latlng, 14);
                        JM.addMarker(latlng, { color: 'green', popup: '<strong>You are here</strong>' });
                    });
                }
            });
            return div;
        };
        btn.addTo(map);
    };

})(window);
