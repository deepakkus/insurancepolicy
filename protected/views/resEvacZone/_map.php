
<div id="map" style="height:600px;"></div>

<script type="text/javascript">

    L.mapbox.accessToken = '<?php echo Helper::MAPBOX_ACCESS_TOKEN; ?>';
    L.mapbox.config.FORCE_HTTPS = true;

    var map = new L.mapbox.map('map').setView([40.03, -99.2], 4);

    var layersAdded = {
        buffer: false,
        perimeter: false,
        threat: false,
        draw: false,
        properties: false
    };

    var keys = Object.keys(layersAdded);

    var orderLayers = function() {
        for (var i = 0; i < keys.length; i++) {
            var key = keys[i];
            if (layersAdded[key] !== false) {
                if (key === 'buffer' && map.hasLayer(layersAdded[key])) {
                    layersAdded[key].bringToBack();
                } else if (key === 'perimeter' && map.hasLayer(layersAdded[key])) {
                    layersAdded[key].bringToBack();
                } else if (key === 'threat' && map.hasLayer(layersAdded[key])) {
                    layersAdded[key].bringToBack();
                } else if (key === 'draw' && map.hasLayer(layersAdded[key])) {
                    layersAdded[key].bringToFront();
                } else if (key === 'properties' && map.hasLayer(layersAdded[key])) {
                    layersAdded[key].bringToFront();
                }
            }
        }
    };

    var addedLayer = function(layer) {
        layersAdded[layer.type] = layer;
        orderLayers();
    };

    <?php if (!$notice->isNewRecord): ?>
    // For some reason, the ordering of layers is getting messed up somewhere due to the asynch nature of javascript
    // This is to ensure the ordering is done right by doing one more layer ordering sweep after everything's loaded
    // ..... leaflet 1.0 and it's vector layer ordering reallllyyyyyy needs to be released!
    setTimeout(function() {
        orderLayers();
    }, 5000);
    <?php endif; ?>

    map.on('layeradd', function(e) {
        var layer = e.layer;
        if (layer instanceof L.FeatureGroup) {
            if (layer.hasOwnProperty('type')) {
                addedLayer(layer);
            }
        }
    });

    var evacZones = <?php echo isset($mapEvacZones) ? json_encode($mapEvacZones) : json_encode(array()); ?>;
    var editableLayers = new L.FeatureGroup();
    editableLayers.type = 'draw';
    editableLayers.addTo(map);

    var fullscreenControl = new L.Control.Fullscreen().addTo(map);
    var evacControl = new L.Control.EvacControl(evacZones, editableLayers).addTo(map);

    var esriTileOptions = {
        detectRetina: true,
        reuseTiles: true,
        subdomains: ['server', 'services']
    };

    new L.LayerGroup([
        new L.tileLayer('https://{s}.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', esriTileOptions),
        new L.tileLayer('https://{s}.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', esriTileOptions)
    ]).addTo(map);

    var perimeterLayer = null;
    $(document).on('map:perimeter', function(e) {
        if (map.hasLayer(perimeterLayer)) map.removeLayer(perimeterLayer);
        $.getJSON('<?php echo $this->createUrl('/resPerimeters/getPerimeterGeoJson'); ?>&perimeterID=' + e.perimeterID, function(featureCollection) {
            perimeterLayer = new L.mapbox.featureLayer(featureCollection, {
                pointToLayer: function(feature, latlng) {
                    return new L.Marker(latlng, {
                        icon: new L.Icon({
                            iconSize: [40, 40],
                            iconUrl: '/images/fire-icon.png'
                        })
                    })
                }
            }).setStyle(Perimeter.perimeterPolyStyle());
            perimeterLayer.type = 'perimeter';
            perimeterLayer.addTo(map);
        });
    });

    var bufferLayer = null;
    $(document).on('map:buffer', function(e) {
        if (map.hasLayer(bufferLayer)) map.removeLayer(bufferLayer);
        bufferLayer = new L.mapbox.featureLayer().loadURL('<?php echo $this->createUrl('resPerimeters/getPerimeterGeoJsonBuffer'); ?>&perimeterID=' + e.perimeterID).on('ready', function() {
            this.eachLayer(function(layer) {
                layer.setStyle(Perimeter.bufferStyle(layer.feature));
            });
            map.fitBounds(this.getBounds());
        });
        bufferLayer.type = 'buffer';
        bufferLayer.addTo(map);
    });

    var threatLayer = null;
    $(document).on('map:threat', function(e) {
        if (map.hasLayer(threatLayer)) map.removeLayer(threatLayer);
        if (e.threatID === null) {
            return;
        }
        else {
            threatLayer = new L.mapbox.featureLayer().loadURL('<?php echo $this->createUrl('resPerimeters/getThreatGeoJson'); ?>&perimeterID=' + e.perimeterID).on('ready', function() {
                this.eachLayer(function(layer) {
                    layer.setStyle(Threat.threatStyle(layer.feature));
                });
            });
            threatLayer.type = 'threat';
            threatLayer.addTo(map);
        }
    });

    var geoJsonLayer = null;
    $(document).on('map:policyholders', function(e) {
        if (map.hasLayer(geoJsonLayer)) map.removeLayer(geoJsonLayer);
        $.getJSON('<?php echo $this->createUrl('/property/getGeoJson'); ?>&perimeterID=' + e.perimeterID + '&bufferDistance=3&clientID=' + e.clientID, function(featureCollection) {
            if (featureCollection.features.length) {
                geoJsonLayer = new L.geoJson(featureCollection, {
                    onEachFeature: Policyholders.onEachFeature,
                    pointToLayer: Policyholders.pointToLayer
                });
                geoJsonLayer.type = 'properties';
                geoJsonLayer.addTo(map);
            }
        });
    });

</script>