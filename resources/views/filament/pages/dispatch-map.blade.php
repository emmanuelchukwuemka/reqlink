<x-filament-panels::page>
    <div class="command-center-root" wire:poll.10s="refreshData" style="display: flex; height: 82vh; gap: 15px; background: #0f172a; border-radius: 16px; padding: 15px; color: #f8fafc; font-family: 'Inter', sans-serif; overflow: hidden;">
        
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://unpkg.com/lucide@latest"></script>

        <!-- LEFT PANEL: INCIDENT FEED -->
        <div class="side-panel" style="width: 320px; display: flex; flex-direction: column; gap: 15px; overflow-y: auto; padding-right: 5px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="radio" style="width: 18px; color: #ef4444;"></i> LIVE INCIDENTS
                </h3>
                <span style="background: rgba(239, 68, 68, 0.2); color: #ef4444; font-size: 0.75rem; padding: 2px 8px; border-radius: 12px; font-weight: 600;">{{ $stats['pending'] }} NEW</span>
            </div>

            <div id="incident-list" style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($emergencies as $e)
                <div class="incident-card" onclick="centerOn({{ $e['lat'] }}, {{ $e['lng'] }}, 'emergency')" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 12px; cursor: pointer; transition: 0.2s; position: relative; overflow: hidden;">
                    <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: {{ $e['priority'] >= 4 ? '#ef4444' : '#f59e0b' }};"></div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px;">
                        <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; color: {{ $e['priority'] >= 4 ? '#ef4444' : '#f59e0b' }};">{{ $e['type'] }}</span>
                        <span style="font-size: 0.65rem; opacity: 0.6;">{{ $e['time_ago'] }}</span>
                    </div>
                    <h4 style="margin: 0; font-size: 0.95rem;">{{ $e['caller'] }}</h4>
                    <p style="margin: 3px 0 0 0; font-size: 0.75rem; opacity: 0.7; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $e['address'] }}</p>
                    
                    @if($e['evidence'])
                    <div style="margin-top: 10px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); padding: 8px; border-radius: 8px; display: flex; align-items: center; gap: 8px;">
                        <i data-lucide="video" style="width: 14px; color: #ef4444;"></i>
                        <a href="{{ $e['evidence'] }}" target="_blank" style="font-size: 0.7rem; color: #ef4444; font-weight: 800; text-decoration: none;">PLAY PANIC CAPTURE</a>
                    </div>
                    @endif

                    @if($e['triage'])
                    <div style="margin-top: 10px; background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); padding: 10px; border-radius: 8px;">
                        <span style="font-size: 0.6rem; color: #22c55e; font-weight: 800; display: flex; align-items: center; gap: 5px;">
                            <i data-lucide="bot" style="width: 12px;"></i> AI DIAGNOSTICS: {{ strtoupper($e['triage']['protocol'] ?? 'General') }}
                        </span>
                        <div style="margin-top: 5px; font-size: 0.7rem; color: #bef264; display: flex; flex-direction: column; gap: 3px;">
                            @foreach($e['triage']['diagnostics'] as $diag)
                                <span>• {{ $diag['question'] }}: <strong>{{ strtoupper($diag['answer']) }}</strong></span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if($e['status'] === 'pending')
                    <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.1);">
                        <span style="font-size: 0.6rem; color: #3b82f6; font-weight: 700; letter-spacing: 0.5px;">RECOMMENDED UNITS:</span>
                        <div style="display: flex; flex-direction: column; gap: 5px; margin-top: 5px;">
                            @foreach($e['recommendations'] as $rec)
                            <button wire:click.stop="assignResponder({{ $e['id'] }}, {{ $rec['id'] }})" style="text-align: left; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); padding: 5px 8px; border-radius: 6px; font-size: 0.7rem; display: flex; justify-content: space-between; color: #93c5fd; transition: 0.2s;" onmouseover="this.style.background='rgba(59, 130, 246, 0.2)'" onmouseout="this.style.background='rgba(59, 130, 246, 0.1)'">
                                <span>{{ $rec['name'] }}</span>
                                <span style="opacity: 0.8;">{{ $rec['distance'] }}km</span>
                            </button>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div style="margin-top: 8px; display: flex; gap: 5px;">
                         <span style="font-size: 0.65rem; background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px;">{{ strtoupper($e['status']) }}</span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <!-- CENTER PANEL: MAP -->
        <div style="flex: 1; position: relative; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
            <div id="map" style="height: 100%; width: 100%; z-index: 1;"></div>
            
            <!-- Map Overlay: System Stats -->
            <div style="position: absolute; top: 15px; left: 15px; z-index: 1000; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); padding: 10px 20px; border-radius: 12px; display: flex; gap: 20px; align-items: center;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 8px; height: 8px; background: #22c55e; border-radius: 50%; box-shadow: 0 0 8px #22c55e;"></div>
                    <span style="font-size: 0.8rem; font-weight: 700; letter-spacing: 0.5px;">SYSTEM LIVE</span>
                </div>
                <div style="display: flex; flex-direction: column;">
                    <span style="font-size: 0.6rem; opacity: 0.6;">ACTIVE INCIDENTS</span>
                    <span style="font-size: 0.9rem; font-weight: 800; color: #ef4444;">{{ $stats['active'] + $stats['pending'] }}</span>
                </div>
                <div style="display: flex; flex-direction: column;">
                    <span style="font-size: 0.6rem; opacity: 0.6;">UNITS ON DUTY</span>
                    <span style="font-size: 0.9rem; font-weight: 800; color: #3b82f6;">{{ count($responders) }}</span>
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: LOGISTICS -->
        <div class="side-panel" style="width: 300px; display: flex; flex-direction: column; gap: 15px; overflow-y: auto; padding-left: 5px;">
            <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="truck" style="width: 18px; color: #3b82f6;"></i> UNIT LOGISTICS
            </h3>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($responders as $r)
                <div class="responder-card" onclick="centerOn({{ $r['lat'] }}, {{ $r['lng'] }}, 'responder')" style="background: rgba(30, 41, 59, 0.7); border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 12px; cursor: pointer; transition: 0.2s;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="margin: 0; font-size: 0.9rem;">{{ $r['name'] }}</h4>
                        <span style="font-size: 0.65rem; color: {{ $r['status'] === 'Available' ? '#22c55e' : '#f59e0b' }}; font-weight: 700;">{{ strtoupper($r['status']) }}</span>
                    </div>
                    <div style="margin-top: 5px; display: flex; gap: 10px; font-size: 0.7rem; opacity: 0.6;">
                        <span style="display: flex; align-items: center; gap: 4px;"><i data-lucide="tag" style="width: 12px;"></i> {{ strtoupper($r['type']) }}</span>
                        <span style="display: flex; align-items: center; gap: 4px;"><i data-lucide="navigation" style="width: 12px;"></i> {{ $r['vehicle'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            <h3 style="margin: 15px 0 0 0; font-size: 1.1rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="hospital" style="width: 18px; color: #10b981;"></i> HOSPITALS
            </h3>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                @foreach($hospitals as $h)
                <div style="background: rgba(30, 41, 59, 0.4); padding: 10px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.8rem;">{{ $h['name'] }}</span>
                    <span style="background: rgba(16, 185, 129, 0.2); color: #10b981; padding: 2px 6px; border-radius: 6px; font-size: 0.7rem; font-weight: 700;">{{ $h['beds'] }} BEDS</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <style>
        .incident-card:hover, .responder-card:hover { background: rgba(51, 65, 85, 0.9) !important; border-color: rgba(255,255,255,0.3) !important; transform: translateY(-2px); }
        .side-panel::-webkit-scrollbar { width: 4px; }
        .side-panel::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        
        .emergency-marker { background: #ef4444; border: 2px solid white; border-radius: 50%; box-shadow: 0 0 15px rgba(239, 68, 68, 0.6); animation: pulse-red 2s infinite; }
        .responder-marker { background: #3b82f6; border: 2px solid white; border-radius: 4px; box-shadow: 0 0 10px rgba(59, 130, 246, 0.5); }
        
        @keyframes pulse-red {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();
            
            let map;
            let markers = { emergencies: {}, responders: {}, hospitals: {} };
            let lastEmergencyIds = new Set();

            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

            function playEmergencyAlert() {
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();
                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, audioCtx.currentTime); // A5
                gainNode.gain.setValueAtTime(0, audioCtx.currentTime);
                gainNode.gain.linearRampToValueAtTime(0.1, audioCtx.currentTime + 0.1);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.8);
                oscillator.start();
                oscillator.stop(audioCtx.currentTime + 0.8);
            }

            function initMap() {
                map = L.map('map', { zoomControl: false, attributionControl: false }).setView([6.5244, 3.3792], 12);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);
                L.control.zoom({ position: 'bottomright' }).addTo(map);
            }

            window.centerOn = function(lat, lng, type) {
                map.setView([lat, lng], 15, { animate: true, duration: 1 });
            }

            function renderData(data) {
                // Check for new emergencies
                let newIds = data.emergencies.map(e => e.id);
                let hasNew = newIds.some(id => !lastEmergencyIds.has(id));
                if (hasNew && lastEmergencyIds.size > 0) {
                    playEmergencyAlert();
                }
                lastEmergencyIds = new Set(newIds);

                // Clear and Redraw
                Object.values(markers.emergencies).forEach(m => m.remove());
                Object.values(markers.responders).forEach(m => m.remove());
                
                data.emergencies.forEach(e => {
                    const icon = L.divIcon({ className: 'emergency-marker', iconSize: [14, 14] });
                    markers.emergencies[e.id] = L.marker([e.lat, e.lng], { icon: icon })
                        .addTo(map)
                        .bindPopup(`<div style="color: #000"><b>${e.type}</b><br>${e.caller}<br><a href="/admin/emergencies/${e.id}/edit">Dispatch</a></div>`);
                });

                data.responders.forEach(r => {
                    const icon = L.divIcon({ className: 'responder-marker', iconSize: [10, 10] });
                    markers.responders[r.id] = L.marker([r.lat, r.lng], { icon: icon })
                        .addTo(map)
                        .bindPopup(`<div style="color: #000"><b>${r.name}</b><br>${r.type}<br>${r.status}</div>`);
                });

                data.hospitals.forEach(h => {
                    if (!markers.hospitals[h.id]) {
                        const icon = L.divIcon({ 
                            html: '<i data-lucide="plus-square" style="color: #10b981; width: 16px;"></i>',
                            className: 'hospital-marker',
                            iconSize: [16, 16]
                        });
                        markers.hospitals[h.id] = L.marker([h.lat, h.lng], { icon: icon }).addTo(map);
                    }
                });
            }

            initMap();
            renderData({
                emergencies: @json($emergencies),
                responders: @json($responders),
                hospitals: @json($hospitals)
            });

            // Livewire polling integration
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('map-data-updated', (event) => {
                    renderData(event[0]);
                });
                
                Livewire.on('notify', (event) => {
                    // Could implement a toast here
                    console.log(event[0].message);
                });
            });
        });
    </script>
</x-filament-panels::page>
</x-filament-panels::page>
