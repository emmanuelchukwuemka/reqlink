<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Command Center | ResQLink</title>
    <link rel="stylesheet" href="/css/dashboard.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .command-layout { height: 100vh; overflow: hidden; display: flex; flex-direction: column; }
        #map { flex: 1; width: 100%; z-index: 1; }
        .map-overlay { position: absolute; top: 20px; left: 80px; z-index: 1000; display: flex; gap: 15px; flex-wrap: wrap; }
        .control-panel { background: rgba(10, 10, 10, 0.85); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 16px; padding: 15px 25px; display: flex; align-items: center; gap: 20px; color: white; }
        .status-dot { width: 10px; height: 10px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 10px #22c55e; animation: blink 2s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.4} }
        .emergency-marker { background: var(--red); border: 2px solid white; border-radius: 50%; width: 20px; height: 20px; box-shadow: 0 0 0 0 rgba(229, 9, 20, 0.4); animation: pulse-marker 2s infinite; }
        @keyframes pulse-marker {
            0%   { box-shadow: 0 0 0 0 rgba(229, 9, 20, 0.7); }
            70%  { box-shadow: 0 0 0 15px rgba(229, 9, 20, 0); }
            100% { box-shadow: 0 0 0 0 rgba(229, 9, 20, 0); }
        }
        .responder-marker { background: #2563eb; border: 2px solid white; border-radius: 50%; width: 16px; height: 16px; }

        /* SOS toast */
        #sosToast {
            position: fixed; bottom: 30px; right: 30px; z-index: 9999;
            background: #0a0a0a; border: 1px solid var(--red); border-radius: 16px;
            padding: 20px 24px; color: #fff; min-width: 280px; max-width: 340px;
            box-shadow: 0 0 40px rgba(229,9,20,0.4);
            display: none; animation: slideIn 0.4s ease;
        }
        @keyframes slideIn { from { transform: translateY(30px); opacity:0; } to { transform: translateY(0); opacity:1; } }
    </style>
</head>
<body class="command-layout dark-mode">

<div class="map-overlay">
    <a href="{{ route('dashboard') }}" class="control-panel" style="text-decoration: none;">
        <i data-lucide="arrow-left"></i>
        <span style="font-weight: 700;">EXIT COMMAND</span>
    </a>

    <div class="control-panel">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div class="status-dot"></div>
            <span style="font-weight: 700; font-size: 0.9rem;">SYSTEM LIVE</span>
        </div>
        <div style="width: 1px; height: 20px; background: var(--glass-border);"></div>
        <div style="display: flex; gap: 15px; font-size: 0.8rem; font-weight: 600; opacity: 0.8;">
            <span>ACTIVE EMERGENCIES: <span id="activeCount" style="color: var(--red);">{{ count($emergencies) }}</span></span>
            <span>RESPONDERS ON-DUTY: <span id="responderCount" style="color: #22c55e;">{{ count($responders) }}</span></span>
        </div>
    </div>
</div>

<!-- SOS notification toast -->
<div id="sosToast">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
        <i data-lucide="siren" style="color:var(--red);width:20px;height:20px;"></i>
        <span style="font-weight:800;font-size:0.9rem;color:var(--red);">NEW SOS ALERT</span>
    </div>
    <p id="toastPatient" style="margin:0 0 4px;font-weight:700;"></p>
    <p id="toastTime" style="margin:0;font-size:0.75rem;color:var(--grey);"></p>
    <button onclick="document.getElementById('sosToast').style.display='none'"
        style="margin-top:12px;background:var(--red);border:none;color:#fff;padding:8px 16px;border-radius:8px;font-weight:700;cursor:pointer;width:100%;">
        Dismiss
    </button>
</div>

<div id="map"></div>

<script>
    lucide.createIcons();

    const map = L.map('map', { zoomControl: false, attributionControl: false })
        .setView([6.465422, 3.406448], 13);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(map);
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // ── Marker registries ─────────────────────────────────────────────
    const emergencyMarkers = {};
    const responderMarkers = {};
    const knownIds = new Set();

    function addEmergencyMarker(item) {
        if (!item.latitude && !item.lat) return;
        const lat = item.latitude ?? item.lat;
        const lng = item.longitude ?? item.lng;
        const name = item.user ? item.user.name : 'Unknown';
        const popup = `<b>${name}</b><br>Status: ${item.status}<br><small>${new Date(item.created_at).toLocaleTimeString()}</small>`;

        if (emergencyMarkers[item.id]) {
            emergencyMarkers[item.id].setLatLng([lat, lng]).bindPopup(popup);
        } else {
            emergencyMarkers[item.id] = L.marker([lat, lng], {
                icon: L.divIcon({ className: 'emergency-marker' })
            }).addTo(map).bindPopup(popup);
        }
        knownIds.add(item.id);
    }

    function addResponderMarker(item) {
        const lat = item.current_lat ?? item.lat;
        const lng = item.current_lng ?? item.lng;
        if (!lat || !lng) return;
        const name = item.user ? item.user.name : (item.name ?? 'Unit');
        const type = item.responder_type ?? item.type;
        const popup = `<b>${name}</b><br>Type: ${type}`;

        if (responderMarkers[item.id]) {
            responderMarkers[item.id].setLatLng([lat, lng]);
        } else {
            responderMarkers[item.id] = L.marker([lat, lng], {
                icon: L.divIcon({ className: 'responder-marker' })
            }).addTo(map).bindPopup(popup);
        }
    }

    // Seed from initial server data
    @json($emergencies).forEach(addEmergencyMarker);
    @json($responders).forEach(addResponderMarker);

    // ── Live polling every 8 s ────────────────────────────────────────
    function showToast(emergency) {
        document.getElementById('toastPatient').textContent = 'Patient: ' + (emergency.user ? emergency.user.name : 'Unknown');
        document.getElementById('toastTime').textContent = new Date(emergency.created_at).toLocaleTimeString();
        const toast = document.getElementById('sosToast');
        toast.style.display = 'block';
        lucide.createIcons();
        setTimeout(() => { toast.style.display = 'none'; }, 10000);
    }

    setInterval(() => {
        fetch('/admin/live-data')
            .then(r => r.json())
            .then(data => {
                // Update header counts
                document.getElementById('activeCount').textContent = data.emergencies.length;
                document.getElementById('responderCount').textContent = data.responders.length;

                // Add new / update existing emergency markers; toast for new ones
                const activeIds = new Set();
                data.emergencies.forEach(e => {
                    activeIds.add(e.id);
                    if (!knownIds.has(e.id)) showToast(e);
                    addEmergencyMarker(e);
                });

                // Remove resolved emergencies from map
                Object.keys(emergencyMarkers).forEach(id => {
                    if (!activeIds.has(parseInt(id))) {
                        map.removeLayer(emergencyMarkers[id]);
                        delete emergencyMarkers[id];
                        knownIds.delete(parseInt(id));
                    }
                });

                // Update responder positions
                data.responders.forEach(addResponderMarker);
            })
            .catch(() => {});
    }, 8000);
</script>

</body>
</html>
