(function( $ ) { 'use strict';
    
    $( document ).ready( function() {

        let coastalynk = {
            init: function() {
                // Initialize the map
                const map = L.map('coastlynkmap').setView([51.505, -0.09], 7);

                // Add tile layer (OpenStreetMap)
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                // Function to create vessel markers with heading
                function createVesselMarkers(vessels) {
                    const markers = [];
                    
                    vessels.forEach(vessel => {
                        console.log(vessel);

                        if (vessel.lastPositionUpdate?.latitude && vessel.lastPositionUpdate?.longitude) {
                            const lat = vessel.lastPositionUpdate.latitude;
                            const lng = vessel.lastPositionUpdate.longitude;
                            const name = vessel.staticData?.name || 'Unknown vessel';
                            const mmsi = vessel.staticData?.mmsi || 'N/A';
                            const imo = vessel.staticData?.imo || 'N/A';
                            const heading = vessel.lastPositionUpdate?.heading || 0;

                            // Create icon (triangle for heading direction)
                            const icon = L.divIcon({
                                className: 'heading-icon',
                                iconSize: [20, 20]
                            });

                            // Create rotated marker
                            const marker = L.marker([lat, lng], { 
                                icon: icon,
                                rotationAngle: heading,
                                rotationOrigin: 'center'
                            }).addTo(map);

                            // Add popup with vessel info
                            marker.bindPopup(`
                                <b>${name}</b><br>
                                MMSI: ${mmsi}<br>
                                IMO: ${imo}<br>
                                Heading: ${heading}°
                            `);

                            markers.push(marker);
                        }
                    });

                    // Fit map to show all markers if we have any
                    if (markers.length > 0) {
                        const group = new L.featureGroup(markers);
                        map.fitBounds(group.getBounds());
                    }
                }

                // Fetch data from your PHP endpoint (replace with your actual endpoint)
                fetch(COSTALUNKVARS.ajaxURL+ '?action=coastlynk_get_vessels')
                        .then(response => response.json())
                                    .then(data => {
                                        console.log(data);
                                        if (data.vessels?.nodes) {
                                            createVesselMarkers(data.vessels.nodes);
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error fetching vessel data:', error);
                                    });

                   
            }
        };
        coastalynk.init();
    });
})( jQuery );
