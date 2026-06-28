<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Command Center | ResQLink</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="/css/dashboard.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        * { box-sizing: border-box; }
        .command-layout { height: 100vh; overflow: hidden; display: flex; flex-direction: column; }
        #map { flex: 1; width: 100%; z-index: 1; }
        .map-overlay { position: absolute; top: 20px; left: 80px; z-index: 1000; display: flex; gap: 15px; flex-wrap: wrap; }
        .control-panel { background: rgba(10,10,10,0.88); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 16px; padding: 14px 22px; display: flex; align-items: center; gap: 18px; color: white; }
        .status-dot { width: 10px; height: 10px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 10px #22c55e; animation: blink 2s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.35} }

        .emergency-marker { background: var(--red); border: 2px solid white; border-radius: 50%; width: 20px; height: 20px; animation: pulse-marker 2s infinite; cursor: pointer; }
        @keyframes pulse-marker {
            0%   { box-shadow: 0 0 0 0 rgba(229,9,20,0.7); }
            70%  { box-shadow: 0 0 0 15px rgba(229,9,20,0); }
            100% { box-shadow: 0 0 0 0 rgba(229,9,20,0); }
        }
        .responder-marker { background: #2563eb; border: 2px solid white; border-radius: 50%; width: 16px; height: 16px; }

        /* Dispatch side panel */
        #dispatchPanel {
            position: fixed; right: 0; top: 0; bottom: 0; width: 320px;
            background: rgba(8,8,8,0.97); backdrop-filter: blur(24px);
            border-left: 1px solid var(--glass-border); z-index: 3000;
            transform: translateX(100%); transition: transform 0.3s ease;
            overflow-y: auto; display: flex; flex-direction: column;
        }
        #dispatchPanel.open { transform: translateX(0); }
        .panel-header { padding: 20px; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; background: rgba(8,8,8,0.97); }
        .panel-body { padding: 16px; flex: 1; }
        .responder-row { display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 10px; margin-bottom: 8px; }
        .dispatch-btn { background: var(--red); border: none; color: #fff; padding: 7px 14px; border-radius: 8px; font-weight: 700; font-size: 0.75rem; cursor: pointer; transition: opacity 0.2s; }
        .dispatch-btn:hover { opacity: 0.85; }
        .dispatch-btn:disabled { background: #444; cursor: default; }

        /* SOS toast */
        #sosToast {
            position: fixed; bottom: 30px; right: 30px; z-index: 9999;
            background: var(--dark); border: 1px solid var(--red); border-radius: 16px;
            padding: 20px 24px; color: var(--white); min-width: 280px;
            box-shadow: 0 0 40px rgba(229,9,20,0.4); display: none;
            animation: slideIn 0.4s ease;
        }
        @keyframes slideIn { from { transform: translateY(30px); opacity:0; } to { transform: translateY(0); opacity:1; } }

        .status-pill { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; padding: 3px 8px; border-radius: 6px; }
        .s-pending    { background: rgba(245,158,11,0.15); color: #f59e0b; }
        .s-dispatched { background: rgba(34,197,94,0.12); color: #22c55e; }
        .s-enroute    { background: rgba(37,99,235,0.12); color: #2563eb; }
        .s-arrived    { background: rgba(168,85,247,0.12); color: #a855f7; }
    </style>
</head>
<body class="command-layout dark-mode">

<!-- Top bar -->
<div class="map-overlay">
    <a href="{{ route('dashboard') }}" class="control-panel" style="text-decoration:none;">
        <i data-lucide="arrow-left"></i>
        <span style="font-weight:700;">EXIT COMMAND</span>
    </a>
    <div class="control-panel">
        <div style="display:flex;align-items:center;gap:10px;">
            <div class="status-dot"></div>
            <span style="font-weight:700;font-size:0.9rem;">SYSTEM LIVE</span>
        </div>
        <div style="width:1px;height:20px;background:var(--glass-border);"></div>
        <div style="display:flex;gap:15px;font-size:0.8rem;font-weight:600;opacity:0.8;">
            <span>EMERGENCIES: <span id="activeCount" style="color:var(--red);">{{ count($emergencies) }}</span></span>
            <span>ON-DUTY: <span id="responderCount" style="color:#22c55e;">{{ count($responders) }}</span></span>
        </div>
    </div>
</div>

<!-- SOS toast (bottom-right) -->
<div id="sosToast">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
        <i data-lucide="siren" style="color:var(--red);width:18px;height:18px;"></i>
        <span style="font-weight:800;font-size:0.85rem;color:var(--red);">NEW SOS</span>
    </div>
    <p id="toastPatient" style="margin:0 0 4px;font-weight:700;"></p>
    <p id="toastTime" style="margin:0 0 12px;font-size:0.72rem;color:var(--grey);"></p>
    <button onclick="document.getElementById('sosToast').style.display='none'"
        style="width:100%;background:var(--red);border:none;color:#fff;padding:8px;border-radius:8px;font-weight:700;cursor:pointer;">
        Dismiss
    </button>
</div>

<!-- Dispatch panel (right side) -->
<div id="dispatchPanel">
    <div class="panel-header">
        <div>
            <h3 style="margin:0;font-size:1rem;color:#fff;">Dispatch Responder</h3>
            <p id="panelPatient" style="margin:4px 0 0;font-size:0.75rem;color:var(--grey);"></p>
        </div>
        <button onclick="closePanel()" style="background:none;border:none;color:var(--grey);cursor:pointer;font-size:1.3rem;line-height:1;">✕</button>
    </div>
    <div class="panel-body">
        <div id="panelStatus" style="margin-bottom:16px;"></div>
        <!-- Evidence Playback -->
        <div id="evidenceSection" style="display:none; margin-bottom:16px;">
            <h4 style="color:var(--grey);font-size:0.7rem;text-transform:uppercase;letter-spacing:1px;margin:0 0 8px;">
                <i data-lucide="mic" style="width:12px;height:12px;margin-right:4px;"></i>Voice Evidence
            </h4>
            <audio id="evidencePlayer" controls style="width:100%;border-radius:8px;"></audio>
        </div>

        <h4 style="color:var(--grey);font-size:0.7rem;text-transform:uppercase;letter-spacing:1px;margin:0 0 10px;">On-Duty Responders</h4>
        <div id="responderList">
            <p style="color:var(--grey);font-size:0.8rem;">No on-duty responders available.</p>
        </div>
        <p id="dispatchMsg" style="margin-top:12px;font-size:0.8rem;color:#22c55e;display:none;"></p>
    </div>
</div>

<div id="map"></div>

<script>
    lucide.createIcons();

    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // ── Map setup ─────────────────────────────────────────────────────
    const map = L.map('map', { zoomControl: false, attributionControl: false })
        .setView([6.465422, 3.406448], 13);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(map);
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    // ── Marker registries ─────────────────────────────────────────────
    const emergencyMarkers = {};
    const responderMarkers = {};
    const knownIds = new Set();
    let latestResponders = [];
    let selectedEmergency = null;

    function statusClass(s) {
        if (s === 'pending') return 's-pending';
        if (s === 'dispatched') return 's-dispatched';
        if (s === 'enroute') return 's-enroute';
        if (s === 'arrived') return 's-arrived';
        return '';
    }

    function addEmergencyMarker(item) {
        if (!item.latitude && !item.lat) return;
        const lat = item.latitude ?? item.lat;
        const lng = item.longitude ?? item.lng;
        const patient = item.user ? item.user.name : 'Unknown';
        const assigned = item.assigned_responder_name ? `<br><small style="color:#22c55e;">↳ ${item.assigned_responder_name}</small>` : '';
        const popup = `<b>${patient}</b><br>
            <span class="status-pill ${statusClass(item.status)}">${item.status}</span>${assigned}<br>
            <small style="color:#888;">${new Date(item.created_at).toLocaleTimeString()}</small><br>
            <button onclick="openPanel(${item.id})"
                style="margin-top:8px;background:var(--red);border:none;color:#fff;padding:6px 14px;border-radius:6px;font-weight:700;font-size:0.75rem;cursor:pointer;width:100%;">
                Dispatch Responder
            </button>`;

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
        const name = (item.user ? item.user.name : null) ?? item.name ?? 'Unit';
        const type = item.responder_type ?? item.type ?? '';
        const popup = `<b>${name}</b><br>${type}`;

        if (responderMarkers[item.id]) {
            responderMarkers[item.id].setLatLng([lat, lng]);
        } else {
            responderMarkers[item.id] = L.marker([lat, lng], {
                icon: L.divIcon({ className: 'responder-marker' })
            }).addTo(map).bindPopup(popup);
        }
    }

    // Seed from server-rendered initial data
    const initEmergencies = @json($emergencies);
    const initResponders  = @json($responders);
    initEmergencies.forEach(addEmergencyMarker);
    initResponders.forEach(r => { addResponderMarker(r); latestResponders.push(r); });

    // ── Dispatch panel ────────────────────────────────────────────────
    function openPanel(emergencyId) {
        const e = initEmergencies.find(x => x.id === emergencyId)
            || { id: emergencyId, user: null, status: '...', assigned_responder_name: null };
        selectedEmergency = e;

        document.getElementById('panelPatient').textContent =
            'Patient: ' + (e.user ? e.user.name : 'Unknown');

        document.getElementById('panelStatus').innerHTML =
            `Status: <span class="status-pill ${statusClass(e.status)}">${e.status}</span>` +
            (e.assigned_responder_name ? `<br><span style="font-size:0.78rem;color:#22c55e;margin-top:6px;display:block;">Currently assigned: ${e.assigned_responder_name}</span>` : '');

        // Evidence playback
        const evidenceSection = document.getElementById('evidenceSection');
        const evidencePlayer = document.getElementById('evidencePlayer');
        if (e.evidence_file) {
            evidencePlayer.src = '/storage/' + e.evidence_file;
            evidenceSection.style.display = 'block';
            lucide.createIcons();
        } else {
            evidenceSection.style.display = 'none';
            evidencePlayer.src = '';
        }

        renderResponderList();
        document.getElementById('dispatchMsg').style.display = 'none';
        document.getElementById('dispatchPanel').classList.add('open');
    }

    function closePanel() {
        document.getElementById('dispatchPanel').classList.remove('open');
        selectedEmergency = null;
    }

    function renderResponderList() {
        const list = document.getElementById('responderList');
        if (latestResponders.length === 0) {
            list.innerHTML = '<p style="color:var(--grey);font-size:0.8rem;">No on-duty responders available.</p>';
            return;
        }
        list.innerHTML = latestResponders.map(r => {
            const name = r.user ? r.user.name : (r.name ?? 'Unit #' + r.id);
            const type = (r.responder_type ?? r.type ?? '').toUpperCase();
            return `<div class="responder-row">
                <div>
                    <p style="margin:0;font-weight:700;font-size:0.85rem;">${name}</p>
                    <p style="margin:0;font-size:0.7rem;color:var(--grey);">${type}</p>
                </div>
                <button class="dispatch-btn" onclick="dispatchResponder(${r.id}, '${name}')">Assign</button>
            </div>`;
        }).join('');
    }

    function dispatchResponder(responderId, responderName) {
        if (!selectedEmergency) return;
        const buttons = document.querySelectorAll('.dispatch-btn');
        buttons.forEach(b => b.disabled = true);

        fetch('/admin/dispatch', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ emergency_id: selectedEmergency.id, responder_id: responderId })
        })
        .then(r => r.json())
        .then(data => {
            const msg = document.getElementById('dispatchMsg');
            msg.textContent = '✓ ' + (data.message ?? 'Dispatched successfully.');
            msg.style.display = 'block';
            buttons.forEach(b => b.disabled = false);

            // Update the local emergency so the panel reflects immediately
            selectedEmergency.assigned_responder_name = responderName;
            document.getElementById('panelStatus').innerHTML =
                `Status: <span class="status-pill s-dispatched">dispatched</span>` +
                `<br><span style="font-size:0.78rem;color:#22c55e;margin-top:6px;display:block;">Currently assigned: ${responderName}</span>`;
        })
        .catch(() => { buttons.forEach(b => b.disabled = false); });
    }

    // ── Live polling every 8 s ────────────────────────────────────────
    function showToast(emergency) {
        document.getElementById('toastPatient').textContent = 'Patient: ' + (emergency.user ? emergency.user.name : 'Unknown');
        document.getElementById('toastTime').textContent = new Date(emergency.created_at).toLocaleTimeString();
        const toast = document.getElementById('sosToast');
        toast.style.display = 'block';
        lucide.createIcons();
        setTimeout(() => { toast.style.display = 'none'; }, 10000);
    }

    // Register PWA
    if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js').catch(() => {});

    setInterval(() => {
        fetch('/admin/live-data')
            .then(r => r.json())
            .then(data => {
                document.getElementById('activeCount').textContent = data.emergencies.length;
                document.getElementById('responderCount').textContent = data.responders.length;

                // Store latest responders for dispatch panel
                latestResponders = data.responders;
                if (selectedEmergency) renderResponderList();

                // Emergency markers — add new, update existing, remove resolved
                const activeIds = new Set();
                data.emergencies.forEach(e => {
                    activeIds.add(e.id);
                    if (!knownIds.has(e.id)) showToast(e);
                    addEmergencyMarker(e);
                });
                Object.keys(emergencyMarkers).forEach(id => {
                    if (!activeIds.has(parseInt(id))) {
                        map.removeLayer(emergencyMarkers[id]);
                        delete emergencyMarkers[id];
                        knownIds.delete(parseInt(id));
                    }
                });

                // Responder positions
                data.responders.forEach(addResponderMarker);
                // Remove responders who went off duty
                const onDutyIds = new Set(data.responders.map(r => r.id));
                Object.keys(responderMarkers).forEach(id => {
                    if (!onDutyIds.has(parseInt(id))) {
                        map.removeLayer(responderMarkers[id]);
                        delete responderMarkers[id];
                    }
                });
            })
            .catch(() => {});
    }, 8000);
</script>

</body>
</html>
