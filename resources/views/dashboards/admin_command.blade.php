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
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            background: #050505;
            color: var(--white);
        }

        /* ── Command Header ── */
        .cmd-header {
            height: 58px;
            background: rgba(8,8,8,0.98);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            padding: 0 20px;
            gap: 16px;
            z-index: 2000;
            flex-shrink: 0;
        }
        .cmd-back {
            display: flex; align-items: center; gap: 7px;
            background: rgba(255,255,255,0.06); border: 1px solid var(--glass-border);
            color: var(--white); text-decoration: none;
            padding: 6px 14px; border-radius: 8px;
            font-size: 0.78rem; font-weight: 700; letter-spacing: 0.5px;
            transition: background 0.2s; white-space: nowrap;
        }
        .cmd-back:hover { background: rgba(255,255,255,0.12); }
        .cmd-divider { width: 1px; height: 28px; background: var(--glass-border); flex-shrink: 0; }
        .cmd-logo { height: 30px; width: auto; object-fit: contain; }
        .cmd-title { font-size: 0.85rem; font-weight: 900; letter-spacing: 3px; color: var(--white); opacity: 0.9; white-space: nowrap; }
        .cmd-spacer { flex: 1; }

        /* Live status */
        .cmd-live { display: flex; align-items: center; gap: 8px; }
        .live-dot { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 8px #22c55e; animation: blink 2s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }
        .live-label { font-size: 0.72rem; font-weight: 800; color: #22c55e; letter-spacing: 1px; }

        /* Header stats */
        .cmd-stats { display: flex; gap: 24px; }
        .cmd-stat { text-align: center; }
        .cmd-stat-val { font-size: 1.35rem; font-weight: 900; display: block; line-height: 1.1; }
        .cmd-stat-lbl { font-size: 0.6rem; color: var(--grey); font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }

        /* ── Content row ── */
        .cmd-body {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        /* ── Incident sidebar ── */
        .cmd-sidebar {
            width: 290px;
            flex-shrink: 0;
            background: rgba(6,6,6,0.98);
            border-right: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .cmd-sidebar-head {
            padding: 14px 16px 10px;
            border-bottom: 1px solid var(--glass-border);
            flex-shrink: 0;
        }
        .cmd-sidebar-head h3 {
            font-size: 0.72rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--grey);
            margin-bottom: 10px;
        }
        .filter-tabs { display: flex; gap: 4px; }
        .ftab {
            flex: 1; padding: 5px 4px;
            background: transparent;
            border: 1px solid var(--glass-border);
            color: var(--grey); border-radius: 6px;
            font-size: 0.64rem; font-weight: 800;
            cursor: pointer; transition: all 0.2s;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .ftab.active { background: var(--red); border-color: var(--red); color: #fff; }
        .ftab:hover:not(.active) { border-color: rgba(255,255,255,0.3); color: var(--white); }

        #incidentList { flex: 1; overflow-y: auto; padding: 8px; }
        #incidentList::-webkit-scrollbar { width: 4px; }
        #incidentList::-webkit-scrollbar-thumb { background: var(--glass-border); border-radius: 2px; }

        .inc-card {
            padding: 11px 13px;
            background: rgba(255,255,255,0.03);
            border: 1px solid var(--glass-border);
            border-radius: 10px;
            margin-bottom: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .inc-card:hover { background: rgba(255,255,255,0.07); border-color: rgba(229,9,20,0.25); }
        .inc-card.selected { border-color: var(--red); background: rgba(229,9,20,0.07); }
        .inc-card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 5px; }
        .inc-patient { font-weight: 700; font-size: 0.83rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .inc-time { font-size: 0.65rem; color: var(--grey); white-space: nowrap; }
        .inc-card-bot { display: flex; align-items: center; gap: 6px; }

        /* Status pills */
        .s-pill { padding: 2px 7px; border-radius: 100px; font-size: 0.62rem; font-weight: 800; text-transform: uppercase; }
        .s-pending    { background: rgba(245,158,11,0.12); color: #f59e0b; }
        .s-dispatched { background: rgba(34,197,94,0.1);   color: #22c55e; }
        .s-enroute    { background: rgba(37,99,235,0.12);  color: #2563eb; }
        .s-arrived    { background: rgba(168,85,247,0.12); color: #a855f7; }
        .s-resolved   { background: rgba(99,102,241,0.12); color: #6366f1; }

        .inc-responder { font-size: 0.65rem; color: #22c55e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* ── Map ── */
        .cmd-map-wrap { flex: 1; position: relative; overflow: hidden; }
        #map { position: absolute; inset: 0; z-index: 1; }

        .map-legend {
            position: absolute; bottom: 80px; left: 16px; z-index: 1000;
            background: rgba(6,6,6,0.88); backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border); border-radius: 10px;
            padding: 10px 14px; display: flex; flex-direction: column; gap: 6px;
        }
        .legend-row { display: flex; align-items: center; gap: 8px; font-size: 0.72rem; font-weight: 600; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .legend-sq  { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

        /* ── Dispatch panel ── */
        #dispatchPanel {
            position: fixed; right: 0; top: 0; bottom: 0; width: 320px;
            background: rgba(6,6,6,0.98); backdrop-filter: blur(24px);
            border-left: 1px solid var(--glass-border); z-index: 3000;
            transform: translateX(100%); transition: transform 0.3s ease;
            overflow-y: auto; display: flex; flex-direction: column;
        }
        #dispatchPanel.open { transform: translateX(0); }

        .panel-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--glass-border);
            display: flex; justify-content: space-between; align-items: flex-start;
            position: sticky; top: 0; background: rgba(6,6,6,0.98); z-index: 1;
        }
        .panel-title { font-size: 0.9rem; font-weight: 800; margin-bottom: 3px; }
        .panel-sub   { font-size: 0.72rem; color: var(--grey); }
        .panel-close { background: none; border: none; color: var(--grey); cursor: pointer; font-size: 1.2rem; line-height: 1; padding: 4px; }
        .panel-close:hover { color: var(--white); }

        .panel-body { padding: 16px; flex: 1; }
        .panel-section { margin-bottom: 18px; }
        .panel-section-title { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: var(--grey); margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }

        .responder-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 12px;
            background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border);
            border-radius: 9px; margin-bottom: 6px;
        }
        .responder-row:hover { background: rgba(255,255,255,0.06); }

        .dispatch-btn {
            background: var(--red); border: none; color: #fff;
            padding: 6px 12px; border-radius: 7px;
            font-weight: 800; font-size: 0.72rem; cursor: pointer;
            transition: opacity 0.2s; white-space: nowrap;
        }
        .dispatch-btn:hover { opacity: 0.8; }
        .dispatch-btn:disabled { background: #333; cursor: default; opacity: 1; }

        /* SOS toast */
        #sosToast {
            position: fixed; bottom: 24px; left: 24px; z-index: 9999;
            background: var(--dark); border: 1px solid var(--red); border-radius: 14px;
            padding: 16px 20px; color: var(--white); min-width: 260px;
            box-shadow: 0 0 40px rgba(229,9,20,0.4); display: none;
            animation: slideUp 0.4s ease;
        }
        @keyframes slideUp { from { transform: translateY(20px); opacity:0; } to { transform: translateY(0); opacity:1; } }

        /* Responsive: hide sidebar on small screens */
        @media (max-width: 768px) {
            .cmd-sidebar { display: none; }
            .cmd-stats { gap: 14px; }
            .cmd-stat-val { font-size: 1.1rem; }
            .cmd-title { display: none; }
        }

        /* Marker styles */
        .emergency-marker {
            background: var(--red); border: 2px solid #fff; border-radius: 50%;
            width: 18px; height: 18px;
            animation: pulse-marker 2s infinite;
        }
        @keyframes pulse-marker {
            0%   { box-shadow: 0 0 0 0 rgba(229,9,20,0.7); }
            70%  { box-shadow: 0 0 0 12px rgba(229,9,20,0); }
            100% { box-shadow: 0 0 0 0 rgba(229,9,20,0); }
        }
        .responder-marker { background: #2563eb; border: 2px solid #fff; border-radius: 50%; width: 14px; height: 14px; }
        .responder-marker.ambulance { background: #22c55e; }
        .responder-marker.fire      { background: #f59e0b; }
        .responder-marker.security  { background: #0ea5e9; }
    </style>
</head>
<body>

<!-- ── COMMAND HEADER ── -->
<header class="cmd-header">
    <a href="{{ route('dashboard') }}" class="cmd-back">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> EXIT
    </a>
    <div class="cmd-divider"></div>
    <img src="{{ asset('images/logo.png') }}" alt="ResQLink" class="cmd-logo">
    <span class="cmd-title">COMMAND CENTER</span>
    <div class="cmd-spacer"></div>
    <div class="cmd-live">
        <div class="live-dot"></div>
        <span class="live-label">LIVE</span>
    </div>
    <div class="cmd-divider"></div>
    <div class="cmd-stats">
        <div class="cmd-stat">
            <span class="cmd-stat-val" id="hdrActive" style="color:var(--red);">{{ count($emergencies) }}</span>
            <span class="cmd-stat-lbl">Active</span>
        </div>
        <div class="cmd-stat">
            <span class="cmd-stat-val" id="hdrPending" style="color:#f59e0b;">0</span>
            <span class="cmd-stat-lbl">Pending</span>
        </div>
        <div class="cmd-stat">
            <span class="cmd-stat-val" id="hdrOnDuty" style="color:#22c55e;">{{ count($responders) }}</span>
            <span class="cmd-stat-lbl">On-Duty</span>
        </div>
    </div>
</header>

<!-- ── BODY: SIDEBAR + MAP ── -->
<div class="cmd-body">

    <!-- Incident Sidebar -->
    <aside class="cmd-sidebar">
        <div class="cmd-sidebar-head">
            <h3>Live Incidents</h3>
            <div class="filter-tabs">
                <button class="ftab active" data-filter="all"       onclick="setFilter('all',       this)">All</button>
                <button class="ftab"        data-filter="pending"   onclick="setFilter('pending',   this)">Pending</button>
                <button class="ftab"        data-filter="active"    onclick="setFilter('active',    this)">Active</button>
                <button class="ftab"        data-filter="arrived"   onclick="setFilter('arrived',   this)">Arrived</button>
            </div>
        </div>
        <div id="incidentList">
            <div style="padding:24px;text-align:center;opacity:0.4;font-size:0.8rem;">Loading…</div>
        </div>
    </aside>

    <!-- Map -->
    <div class="cmd-map-wrap">
        <div id="map"></div>

        <!-- Map legend -->
        <div class="map-legend">
            <div class="legend-row"><div class="legend-dot" style="background:var(--red);box-shadow:0 0 6px var(--red);"></div>SOS Emergency</div>
            <div class="legend-row"><div class="legend-dot" style="background:#22c55e;"></div>Ambulance</div>
            <div class="legend-row"><div class="legend-dot" style="background:#f59e0b;"></div>Fire Unit</div>
            <div class="legend-row"><div class="legend-dot" style="background:#0ea5e9;"></div>Security</div>
        </div>
    </div>
</div>

<!-- ── DISPATCH PANEL ── -->
<div id="dispatchPanel">
    <div class="panel-header">
        <div>
            <div class="panel-title">Dispatch Responder</div>
            <div class="panel-sub" id="panelSub">Select an incident first</div>
        </div>
        <button class="panel-close" onclick="closePanel()">✕</button>
    </div>
    <div class="panel-body">

        <!-- Incident info -->
        <div class="panel-section">
            <div class="panel-section-title"><i data-lucide="alert-triangle" style="width:12px;height:12px;"></i> Incident</div>
            <div id="panelStatus" style="margin-bottom:10px;"></div>
        </div>

        <!-- Evidence -->
        <div id="evidenceSection" style="display:none;" class="panel-section">
            <div class="panel-section-title"><i data-lucide="mic" style="width:12px;height:12px;"></i> Voice Evidence</div>
            <audio id="evidencePlayer" controls style="width:100%;border-radius:8px;"></audio>
        </div>

        <!-- Responders -->
        <div class="panel-section">
            <div class="panel-section-title"><i data-lucide="shield" style="width:12px;height:12px;"></i> On-Duty Responders</div>
            <div id="responderList">
                <p style="color:var(--grey);font-size:0.8rem;">No on-duty responders available.</p>
            </div>
        </div>

        <p id="dispatchMsg" style="margin-top:10px;font-size:0.8rem;color:#22c55e;display:none;"></p>
    </div>
</div>

<!-- ── SOS TOAST ── -->
<div id="sosToast">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
        <i data-lucide="siren" style="color:var(--red);width:16px;height:16px;"></i>
        <span style="font-weight:800;font-size:0.82rem;color:var(--red);">NEW SOS</span>
    </div>
    <p id="toastPatient" style="margin:0 0 4px;font-weight:700;font-size:0.9rem;"></p>
    <p id="toastTime"    style="margin:0 0 10px;font-size:0.7rem;color:var(--grey);"></p>
    <button onclick="document.getElementById('sosToast').style.display='none'"
        style="width:100%;background:var(--red);border:none;color:#fff;padding:7px;border-radius:7px;font-weight:700;cursor:pointer;">
        Dismiss
    </button>
</div>

<script>
    lucide.createIcons();

    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // ── Map ──────────────────────────────────────────────────────────────
    const map = L.map('map', { zoomControl: false, attributionControl: false })
        .setView([6.465422, 3.406448], 13);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(map);
    L.control.zoom({ position: 'bottomright' }).addTo(map);

    const emergencyMarkers = {};
    const responderMarkers = {};
    const knownIds = new Set();
    let latestEmergencies = [];
    let latestResponders  = [];
    let selectedEmergency = null;
    let activeFilter      = 'all';

    // Seed initial data
    const initEmergencies = @json($emergencies);
    const initResponders  = @json($responders);
    initEmergencies.forEach(e => { addEmergencyMarker(e); latestEmergencies.push(e); });
    initResponders.forEach(r  => { addResponderMarker(r);  latestResponders.push(r); });
    renderSidebar();

    // ── Marker helpers ───────────────────────────────────────────────────
    function addEmergencyMarker(item) {
        const lat = item.latitude ?? item.lat;
        const lng = item.longitude ?? item.lng;
        if (!lat || !lng) return;
        const patient  = item.user ? item.user.name : 'Unknown';
        const assigned = item.assigned_responder_name
            ? `<br><small style="color:#22c55e;">↳ ${item.assigned_responder_name}</small>` : '';
        const popup = `<b>${patient}</b><br>
            <span class="s-pill s-${item.status}">${item.status}</span>${assigned}<br>
            <small style="color:#999;">${new Date(item.created_at).toLocaleTimeString()}</small><br>
            <button onclick="openPanel(${item.id})"
                style="margin-top:8px;background:var(--red);border:none;color:#fff;padding:5px 12px;border-radius:6px;font-weight:700;font-size:0.72rem;cursor:pointer;width:100%;">
                Dispatch
            </button>`;

        if (emergencyMarkers[item.id]) {
            emergencyMarkers[item.id].setLatLng([lat, lng]).bindPopup(popup);
        } else {
            emergencyMarkers[item.id] = L.marker([lat, lng], {
                icon: L.divIcon({ className: 'emergency-marker', iconSize: [18,18] })
            }).addTo(map).bindPopup(popup);
        }
        knownIds.add(item.id);
    }

    function addResponderMarker(item) {
        const lat  = item.current_lat ?? item.lat;
        const lng  = item.current_lng ?? item.lng;
        if (!lat || !lng) return;
        const name = item.user ? item.user.name : (item.name ?? 'Unit');
        const type = item.responder_type ?? item.type ?? '';

        if (responderMarkers[item.id]) {
            responderMarkers[item.id].setLatLng([lat, lng]);
        } else {
            responderMarkers[item.id] = L.marker([lat, lng], {
                icon: L.divIcon({ className: `responder-marker ${type}`, iconSize: [14,14] })
            }).addTo(map).bindPopup(`<b>${name}</b><br>${type.toUpperCase()}`);
        }
    }

    // ── Sidebar ──────────────────────────────────────────────────────────
    function setFilter(f, btn) {
        activeFilter = f;
        document.querySelectorAll('.ftab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        renderSidebar();
    }

    function matchFilter(e) {
        if (activeFilter === 'all')     return true;
        if (activeFilter === 'pending') return e.status === 'pending';
        if (activeFilter === 'active')  return ['dispatched','enroute'].includes(e.status);
        if (activeFilter === 'arrived') return e.status === 'arrived';
        return true;
    }

    function statusClass(s) {
        if (s === 'pending')    return 's-pending';
        if (s === 'dispatched') return 's-dispatched';
        if (s === 'enroute')    return 's-enroute';
        if (s === 'arrived')    return 's-arrived';
        if (s === 'resolved')   return 's-resolved';
        return '';
    }

    function renderSidebar() {
        const list = document.getElementById('incidentList');
        const items = latestEmergencies.filter(matchFilter);
        if (items.length === 0) {
            list.innerHTML = '<div style="padding:28px;text-align:center;opacity:0.45;font-size:0.78rem;">No incidents in this filter.</div>';
            return;
        }
        list.innerHTML = items.map(e => {
            const isSelected = selectedEmergency && selectedEmergency.id === e.id;
            const patient = e.user ? e.user.name : 'Unknown';
            const assigned = e.assigned_responder_name
                ? `<span class="inc-responder">↳ ${e.assigned_responder_name}</span>` : '';
            return `<div class="inc-card ${isSelected ? 'selected' : ''}" onclick="selectIncident(${e.id})">
                <div class="inc-card-top">
                    <span class="inc-patient">${patient}</span>
                    <span class="inc-time">${timeAgo(e.created_at)}</span>
                </div>
                <div class="inc-card-bot">
                    <span class="s-pill ${statusClass(e.status)}">${e.status}</span>
                    ${assigned}
                </div>
            </div>`;
        }).join('');
    }

    function timeAgo(iso) {
        const diff = Math.floor((Date.now() - new Date(iso)) / 1000);
        if (diff < 60)  return diff + 's ago';
        if (diff < 3600) return Math.floor(diff/60) + 'm ago';
        return Math.floor(diff/3600) + 'h ago';
    }

    function selectIncident(id) {
        const e = latestEmergencies.find(x => x.id === id);
        if (!e) return;
        selectedEmergency = e;
        renderSidebar();

        // Zoom map to incident
        const lat = e.latitude ?? e.lat;
        const lng = e.longitude ?? e.lng;
        if (lat && lng) {
            map.setView([lat, lng], 16);
            emergencyMarkers[id] && emergencyMarkers[id].openPopup();
        }
        openPanel(id);
    }

    // ── Dispatch panel ───────────────────────────────────────────────────
    function openPanel(emergencyId) {
        const e = latestEmergencies.find(x => x.id === emergencyId)
            || { id: emergencyId, user: null, status: '—', assigned_responder_name: null };
        selectedEmergency = e;

        document.getElementById('panelSub').textContent =
            'Patient: ' + (e.user ? e.user.name : 'Unknown');

        document.getElementById('panelStatus').innerHTML =
            `<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span style="font-size:0.78rem;color:var(--grey);">Status:</span>
                <span class="s-pill ${statusClass(e.status)}">${e.status}</span>
             </div>` +
            (e.assigned_responder_name
                ? `<div style="margin-top:6px;font-size:0.75rem;color:#22c55e;">Currently assigned: ${e.assigned_responder_name}</div>`
                : '');

        const evidenceSection = document.getElementById('evidenceSection');
        const evidencePlayer  = document.getElementById('evidencePlayer');
        if (e.evidence_file) {
            evidencePlayer.src = '/storage/' + e.evidence_file;
            evidenceSection.style.display = 'block';
        } else {
            evidenceSection.style.display = 'none';
            evidencePlayer.src = '';
        }

        renderResponderList();
        document.getElementById('dispatchMsg').style.display = 'none';
        document.getElementById('dispatchPanel').classList.add('open');
        lucide.createIcons();
    }

    function closePanel() {
        document.getElementById('dispatchPanel').classList.remove('open');
        selectedEmergency = null;
        renderSidebar();
    }

    function renderResponderList() {
        const list = document.getElementById('responderList');
        if (latestResponders.length === 0) {
            list.innerHTML = '<p style="color:var(--grey);font-size:0.78rem;">No on-duty responders available.</p>';
            return;
        }
        list.innerHTML = latestResponders.map(r => {
            const name  = r.user ? r.user.name : (r.name ?? 'Unit #' + r.id);
            const type  = (r.responder_type ?? r.type ?? '').toUpperCase();
            const color = r.responder_type === 'ambulance' ? '#22c55e' : (r.responder_type === 'fire' ? '#f59e0b' : '#0ea5e9');
            return `<div class="responder-row">
                <div style="display:flex;align-items:center;gap:9px;">
                    <div style="width:28px;height:28px;border-radius:50%;background:${color}22;display:flex;align-items:center;justify-content:center;font-size:0.7rem;font-weight:800;color:${color};">${name.charAt(0)}</div>
                    <div>
                        <p style="margin:0;font-weight:700;font-size:0.82rem;">${name}</p>
                        <p style="margin:0;font-size:0.66rem;color:${color};">${type}</p>
                    </div>
                </div>
                <button class="dispatch-btn" onclick="dispatchResponder(${r.id}, '${name.replace(/'/g,'\\x27')}')">Assign</button>
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
            selectedEmergency.assigned_responder_name = responderName;
            selectedEmergency.status = 'dispatched';
            document.getElementById('panelStatus').innerHTML =
                `<div style="display:flex;align-items:center;gap:8px;">
                    <span style="font-size:0.78rem;color:var(--grey);">Status:</span>
                    <span class="s-pill s-dispatched">dispatched</span>
                 </div>
                 <div style="margin-top:6px;font-size:0.75rem;color:#22c55e;">Assigned: ${responderName}</div>`;
            renderSidebar();
        })
        .catch(() => { buttons.forEach(b => b.disabled = false); });
    }

    // ── Live polling ─────────────────────────────────────────────────────
    function showToast(e) {
        document.getElementById('toastPatient').textContent = 'Patient: ' + (e.user ? e.user.name : 'Unknown');
        document.getElementById('toastTime').textContent    = new Date(e.created_at).toLocaleTimeString();
        const toast = document.getElementById('sosToast');
        toast.style.display = 'block';
        lucide.createIcons();
        setTimeout(() => { toast.style.display = 'none'; }, 10000);
    }

    if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js').catch(() => {});

    setInterval(() => {
        fetch('/admin/live-data')
            .then(r => r.json())
            .then(data => {
                // Update header stats
                const pendingCount = data.emergencies.filter(e => e.status === 'pending').length;
                document.getElementById('hdrActive').textContent  = data.emergencies.length;
                document.getElementById('hdrPending').textContent = pendingCount;
                document.getElementById('hdrOnDuty').textContent  = data.responders.length;

                // New emergencies toast
                data.emergencies.forEach(e => {
                    if (!knownIds.has(e.id)) showToast(e);
                });

                latestEmergencies = data.emergencies;
                latestResponders  = data.responders;

                // Sync markers
                const activeIds = new Set(data.emergencies.map(e => e.id));
                data.emergencies.forEach(addEmergencyMarker);
                Object.keys(emergencyMarkers).forEach(id => {
                    if (!activeIds.has(parseInt(id))) {
                        map.removeLayer(emergencyMarkers[id]);
                        delete emergencyMarkers[id];
                        knownIds.delete(parseInt(id));
                    }
                });

                const onDutyIds = new Set(data.responders.map(r => r.id));
                data.responders.forEach(addResponderMarker);
                Object.keys(responderMarkers).forEach(id => {
                    if (!onDutyIds.has(parseInt(id))) {
                        map.removeLayer(responderMarkers[id]);
                        delete responderMarkers[id];
                    }
                });

                renderSidebar();
                if (selectedEmergency) renderResponderList();
            })
            .catch(() => {});
    }, 6000);
</script>
</body>
</html>
