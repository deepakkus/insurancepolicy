<div id="map" style="height:500px;"></div>

<script type="text/javascript">

    L.mapbox.accessToken = '<?php echo Helper::MAPBOX_ACCESS_TOKEN; ?>';
    L.mapbox.config.FORCE_HTTPS = true;
    var map = new L.mapbox.Map('map');

    var fullscreen = new L.Control.Fullscreen();
    map.addControl(fullscreen);
    map.scrollWheelZoom.disable();

    // -------------------- Base maps -------------------

    var esriTileOptions = {
        detectRetina: true,
        reuseTiles: true,
        subdomains: ['server', 'services']
    };

    new L.LayerGroup([
        new L.tileLayer('https://{s}.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', esriTileOptions),
        new L.tileLayer('https://{s}.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', esriTileOptions)
    ]).addTo(map);

    //------------------------------------------Get Layers-----------------------------------------

    // Fire
    $.getJSON('<?php echo $this->createUrl('/resPerimeters/getPerimeterGeoJson', array('perimeterID' => $perimeterID)); ?>', function(featureCollection) {
        firesLayer = new L.mapbox.featureLayer(featureCollection, {
            pointToLayer: function(feature, latlng) {
                return new L.Marker(latlng, {
                    icon: new L.Icon({
                        iconSize: [40, 40],
                        iconUrl: 'images/fire-icon.png'
                    })
                })
            }
        }).setStyle(Perimeter.perimeterPolyStyle()).addTo(map);
        map.fitBounds(firesLayer.getBounds());
    });

    // Threat
    new L.mapbox.featureLayer().loadURL('<?php echo $this->createUrl('resPerimeters/getThreatGeoJson', array('perimeterID' => $perimeterID)); ?>').on('ready', function() {
        this.eachLayer(function(layer) {
            layer.setStyle(Threat.threatStyle(layer.feature));
        });
    }).addTo(map);

    // Buffers
    new L.mapbox.featureLayer().loadURL('<?php echo $this->createUrl('/resPerimeters/getPerimeterGeoJsonBuffer', array('perimeterID' => $perimeterID, 'fourthRing' => 6)); ?>').on('ready', function() {
        this.eachLayer(function(layer) {
            layer.setStyle(Perimeter.bufferStyle(layer.feature));
        });
        this.bringToBack();
    }).addTo(map);

    // Policyholders
    $.getJSON('<?php echo $this->createUrl('/resPhVisit/getPolicyGeoJson', array('clientID' => $clientID, 'fireID' => $fireID, 'policyholders' => $policyholders)); ?>', function(data) {
        if (data) {
            new L.geoJson(data, {
                onEachFeature: Policyholders.onEachFeature,
                pointToLayer: Policyholders.pointToLayer
            }).addTo(map);
        }
    });

</script>

<div class = "table-responsive">

<?php echo $this->renderPartial('_viewPolicyActionDataGrid', array('dataProvider' => $dataProvider)); ?>

</div>

<script type="text/javascript">

    if (gridJsRegistered === false) {
        jQuery("#resPolicyAction-grid").yiiGridView({
            "ajaxUpdate":["1","resPolicyAction-grid"],
            "ajaxVar":"ajax",
            "pagerClass":"pager",
            "loadingClass":"grid-view-loading",
            "filterClass":"filters",
            "tableClass":"items",
            "selectableRows":1,
            "enableHistory":false,
            "updateSelector":"{page}, {sort}",
            "filterSelector":"{filter}","pageVar":"page"
        });
        gridJsRegistered = true;
    }

</script>