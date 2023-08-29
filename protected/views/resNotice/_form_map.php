<div id="map"></div>

<script type="text/javascript">

    function perimeterPointStyle(layer) {
        var latlng = new L.LatLng(layer.feature.geometry.coordinates[1], layer.feature.geometry.coordinates[0]);
        var pointLayer = new L.CircleMarker(latlng, {
            radius: 8,
            fillColor: '#FF0000',
            color: '#000000',
            weight: 1,
            opacity: 0.7,
            fillOpacity: 0.5
        });
        fireLayer.removeLayer(layer);
        fireLayer.addLayer(pointLayer);
    }

    function perimeterStyle(layer) {
        layer.setStyle({
            fillColor: '#FF0000',
            weight: 2,
            opacity: 0.7,
            color: '#000000',
            fillOpacity: 0.5
        });
    }

    var bufferColors = {
        'three': 'yellow',
        'one': 'orange',
        'half': 'red'
    };

    function getBufferColor(d) {
        return bufferColors[d];
    }

    function bufferStyle(feature) {
        return {
            fillColor: 'transparent',
            weight: 3,
            opacity: 1,
            color: getBufferColor(feature.properties.distance)
        };
    }

    function threatStyle(feature) {
        return {
            fillColor: 'yellow',
            weight: 0,
            opacity: 0.4,
            weight: 0.7,
            color: 'yellow',
            fillOpacity: 0.4
        };
    }

    L.mapbox.accessToken = '<?php echo Helper::MAPBOX_ACCESS_TOKEN; ?>';

    var map = new L.mapbox.map('map', 'mapbox.streets').setView([40.03, -99.2], 4);

    map.removeControl(map.attributionControl);

    var fireLayer = new L.mapbox.featureLayer();
    $(document).on('map:perimeter', function(event) {
        if (map.hasLayer(fireLayer)) map.removeLayer(fireLayer);
        fireLayer = new L.mapbox.featureLayer().loadURL('<?php echo $this->createUrl('resPerimeters/getPerimeterGeoJson'); ?>&perimeterID=' + event.perimeterID).on('ready', function() {
            if (this.getGeoJSON().features.length) {
                this.eachLayer(function(layer) {
                    if (layer.feature.geometry.type === 'Point') {
                        perimeterPointStyle(layer);
                    } else {
                        perimeterStyle(layer);
                    }
                });
            }
        }).addTo(map);
    });

    var bufferLayer = new L.mapbox.featureLayer();
    $(document).on('map:buffer', function(event) {
        if (map.hasLayer(bufferLayer)) map.removeLayer(bufferLayer);
        bufferLayer = new L.mapbox.featureLayer().loadURL('<?php echo $this->createUrl('resPerimeters/getPerimeterGeoJsonBuffer'); ?>&perimeterID=' + event.perimeterID).on('ready', function() {
            if (this.getGeoJSON().features.length) {
                this.eachLayer(function(layer) {
                    layer.setStyle(bufferStyle(layer.feature));
                });
                map.fitBounds(this.getBounds());
            }
        }).addTo(map);
    });

    var threatLayer = new L.mapbox.featureLayer();
    $(document).on('map:threat', function(event) {
        if (map.hasLayer(threatLayer)) map.removeLayer(threatLayer)
        threatLayer = new L.mapbox.featureLayer().loadURL('<?php echo $this->createUrl('resPerimeters/getThreatGeoJson'); ?>&perimeterID=' + event.perimeterID).on('ready', function() {
            if (this.getGeoJSON().features.length) {
                this.eachLayer(function(layer) {
                    layer.setStyle(threatStyle(layer.feature));
                });
                map.fitBounds(this.getBounds());
            }
        }).addTo(map);
    });

    // Load map automatically if this is an update

    <?php if(!$model->isNewRecord): ?>

    $(document).ready(function () {
        var perimeterID = $('#ResNotice_perimeter_id').val();

        if (perimeterID) {
            mapEventHandler();
        }
    });

    <?php endif; ?>

</script>