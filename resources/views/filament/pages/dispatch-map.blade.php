<x-filament-panels::page>
    <div class="dispatch-map-container" style="height: 75vh; width: 100%; border-radius: 15px; overflow: hidden; position: relative; border: 1px solid rgba(0,0,0,0.1); background: #f8fafc;">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <div id="map" style="height: 100%; width: 100%; z-index: 1;"></div>

        <div class="map-legend" style="position: absolute; bottom: 20px; left: 20px; z-index: 1000; background: white; padding: 15px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); font-size: 0.85rem; border: 1px solid #e2e8f0;">
            <h4 style="margin: 0 0 10px 0; font-weight: 600; color: #1e293b;">Dispatch Legend</h4>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="width: 14px; height: 14px; border-radius: 50%; background: #ef4444; border: 2px solid white; box-shadow: 0 0 0 2px #ef4444; display: inline-block;"></span>
                    <span style="color: #475569;">Active Emergency</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="width: 14px; height: 14px; border-radius: 50%; background: #10b981; border: 2px solid white; display: inline-block;"></span>
                    <span style="color: #475569;">Available Responder</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="width: 14px; height: 14px; background: #3b82f6; border: 2px solid white; display: inline-block;"></span>
                    <span style="color: #475569;">Medical Center / Hospital</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let map;
            let layers = {
                emergencies: L.layerGroup(),
                responders: L.layerGroup(),
                hospitals: L.layerGroup()
            };

            function initMap() {
                if (map) return;
                map = L.map('map').setView([6.5244, 3.3792], 12);
                L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);
                
                layers.emergencies.addTo(map);
                layers.responders.addTo(map);
                layers.hospitals.addTo(map);
            }

            function renderData(data) {
                // Clear existing layers
                layers.emergencies.clearLayers();
                layers.responders.clearLayers();
                layers.hospitals.clearLayers();

                // Render Emergencies
                data.emergencies.forEach(e => {
                    const color = e.priority >= 4 ? "#ef4444" : "#f59e0b";
                    const marker = L.circleMarker([e.lat, e.lng], {
                        radius: 10,
                        fillColor: color,
                        color: "#fff",
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.8
                    });

                    marker.bindPopup(`
                        <div style="padding: 10px; min-width: 150px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: ${color};">${e.type}</span>
                                <span style="font-size: 0.65rem; background: #fee2e2; color: #ef4444; padding: 2px 6px; border-radius: 4px;">P${e.priority}</span>
                            </div>
                            <h4 style="margin: 0 0 5px 0; font-size: 1rem; color: #1e293b;">${e.caller}</h4>
                            <p style="margin: 0; font-size: 0.85rem; color: #64748b;">${e.phone}</p>
                            <hr style="margin: 10px 0; border: 0; border-top: 1px solid #e2e8f0;">
                            <a href="/admin/emergencies/${e.id}/edit" style="display: block; width: 100%; text-align: center; background: #3b82f6; color: white; padding: 6px; border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600;">Dispatch Unit</a>
                        </div>
                    `);
                    
                    layers.emergencies.addLayer(marker);
                });

                // Render Responders
                data.responders.forEach(r => {
                    const marker = L.circleMarker([r.lat, r.lng], {
                        radius: 7,
                        fillColor: "#10b981",
                        color: "#fff",
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.9
                    });
                    marker.bindPopup(`<strong>${r.name}</strong><br><span style="text-transform: capitalize;">${r.type}</span>`);
                    layers.responders.addLayer(marker);
                });

                // Render Hospitals
                data.hospitals.forEach(h => {
                    const hospitalIcon = L.divIcon({
                        className: 'custom-div-icon',
                        html: `<div style="background-color:#3b82f6; width:14px; height:14px; border:2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></div>`,
                        iconSize: [14, 14],
                        iconAnchor: [7, 7]
                    });
                    const marker = L.marker([h.lat, h.lng], { icon: hospitalIcon });
                    marker.bindPopup(`<strong>${h.name}</strong><br>${h.beds} Beds Available`);
                    layers.hospitals.addLayer(marker);
                });
            }

            initMap();
            renderData({
                emergencies: @json($emergencies),
                responders: @json($responders),
                hospitals: @json($hospitals)
            });

            // Listen for Livewire updates
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('map-data-updated', (event) => {
                    renderData(event[0]);
                });
            });
        });
    </script>
</x-filament-panels::page>
