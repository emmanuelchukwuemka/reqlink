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
        .map-overlay { position: absolute; top: 20px; left: 80px; z-index: 1000; display: flex; gap: 15px; }
        .control-panel { background: rgba(10, 10, 10, 0.85); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 16px; padding: 15px 25px; display: flex; align-items: center; gap: 20px; color: white; }
        .status-dot { width: 10px; height: 10px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 10px #22c55e; }
        .emergency-marker { background: var(--red); border: 2px solid white; border-radius: 50%; width: 20px; height: 20px; box-shadow: 0 0 0 0 rgba(229, 9, 20, 0.4); animation: pulse-marker 2s infinite; }
        @keyframes pulse-marker { 
            0% { box-shadow: 0 0 0 0 rgba(229, 9, 20, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(229, 9, 20, 0); }
            100% { box-shadow: 0 0 0 0 rgba(229, 9, 20, 0); }
        }
        .responder-marker { background: #2563eb; border: 2px solid white; border-radius: 50%; width: 16px; height: 16px; }
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
            <span>ACTIVE EMERGENCIES: <span style="color: var(--red);">{{ count($emergencies) }}</span></span>
            <span>RESPONDERS ON-DUTY: <span style="color: #22c55e;">{{ count($responders) }}</span></span>
        </div>
    </div>
</div>

<div id="map"></div>

<script>
    lucide.createIcons();

    const map = L.map('map', {
        zoomControl: false,
        attributionControl: false
    }).setView([6.465422, 3.406448], 13);

    // Dark tiles
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        maxZoom: 19
    }).addTo(map);

    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // Add Emergencies
    const emergencies = @json($emergencies);
    emergencies.forEach(item => {
        const icon = L.divIcon({ className: 'emergency-marker' });
        L.marker([item.latitude, item.longitude], { icon: icon })
            .addTo(map)
            .bindPopup(`<b>Emergency: ${item.uuid}</b><br>Status: ${item.status}`);
    });

    // Add Responders
    const responders = @json($responders);
    responders.forEach(item => {
        if (item.current_lat && item.current_lng) {
            const icon = L.divIcon({ className: 'responder-marker' });
            L.marker([item.current_lat, item.current_lng], { icon: icon })
                .addTo(map)
                .bindPopup(`<b>Unit: ${item.user.name}</b><br>Type: ${item.responder_type}`);
        }
    });

    // Auto-refresh logic (Simple polling)
    setInterval(() => {
        window.location.reload();
    }, 30000); // Reload every 30 seconds for live updates
</script>

</body>
</html>
