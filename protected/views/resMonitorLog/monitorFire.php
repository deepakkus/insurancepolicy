<?php

$this->breadcrumbs=array(
    'Response' => array('/resNotice/landing'),
    'New Fire'
);

Assets::registerMapboxPackage();
Assets::registerMapboxLeafletOmnivore();
Assets::registerTurfJs();

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/map-fire-style.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCss('oldPerimetersFlashingCss','
    .animated {
        -webkit-animation-duration: 1.5s;
                animation-duration: 1.5s;
        -webkit-animation-fill-mode: both;
                animation-fill-mode: both;
        -vendor-animation-iteration-count: 3;
                animation-iteration-count: 3;
    }

    @-webkit-keyframes flashing {
        from, 50%, to {
            opacity: 1;
        }
        25%, 75% {
            opacity: 0;
        }
    }

    @keyframes flashing {
        from, 50%, to {
            opacity: 1;
        }
        25%, 75% {
            opacity: 0;
        }
    }

    .flashing {
        -webkit-animation-name: flashing;
                animation-name: flashing;
    }
');

echo $this->renderPartial('_coordinateConversion');

?>

<div class="row-fluid marginTop20 marginBottom20">

    <div class="span4">
        <!-- Form for submitting coordinates to monitor -->
        <form action="<?php echo $this->createUrl($this->route); ?>" method="post">
            <table>
                <tr>
                    <td colspan="2">
                        <h3 style="display: inline-block;">Enter Coordinates</h3>
                        <a class="coordinate-conversion paddingLeft10" href="#">Coordinate Conversion</a>
                    </td>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="smokecheck[lat]" placeholder="Lat" pattern="\d{2,3}\.{1}\d+" <?php if ($dispatchLat) { echo "value = '$dispatchLat'"; } ?> required />
                        <input type="text" name="smokecheck[long]" placeholder="Lon" pattern="-\d{2,3}\.{1}\d+" <?php if ($dispatchLong) { echo "value = '$dispatchLong'"; } ?> required />
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" value="Submit" class="submit" /></td>
                </tr>
            </table>
        </form>
    </div>

    <div class="span4">
        <h1 class="center">OR</h1>
    </div>

    <div class="span4">
        <!-- Form for uploading a file -->
        <form action="<?php echo $this->createUrl($this->route); ?>" method="post" enctype="multipart/form-data" class="pull-right">
            <h3>Upload Perimeter</h3>
            <b>Choose KML: </b><input accept=".kml,.kmz" type="file" value="" name="kml" required /><br />
            <input type="submit" value="Upload" class="submit" />
        </form>
    </div>

</div>

<div class="row-fluid paddingTop20" style="background-color: #CCCCCC;">
    <div class="span4">
        <!-- Form for point to acres -->
        <form action="<?php echo $this->createUrl($this->route); ?>" method="post" enctype="multipart/form-data" style="margin: 0 0 10px 10px;" class="<?php echo ($lat && $long && !$pointtoacresWkt) ? 'visible' : 'hide'; ?>">
            <table>
                <tr>
                    <td colspan="2"><h3>Convert point to acres</h3></td>
                </tr>
                <tr>
                    <td>
                        <input type="number" name="pointtoacres[acres]" placeholder="Acres" required />
                        <input type="hidden" name="pointtoacres[lat]" value="<?php echo $lat; ?>" />
                        <input type="hidden" name="pointtoacres[long]" value="<?php echo $long; ?>" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" value="Convert" class="submit" /></td>
                </tr>
            </table>
        </form>
    </div>
    <div class="span4">
        <?php if ($geoJson): ?>
            <p class="center"><a href="<?php echo $this->createUrl('/resMonitorLog/downloadKMZ', array('miles' => $alertDistance)); ?>" id="download-kml" class="btn btn-default btn-large">View KML</a></p>

        <?php elseif($lat == null && $fileName == null): ?>

        <div class="center marginBottom20">
            <h3 class="center">Enter coordinates or upload a perimeter</h3>
        </div>

        <?php else: ?>
            <!-- Form for running a smokecheck -->
            <div class="center marginBottom20">
                <form id="monitor-fire" enctype="multipart/form-data" action="<?php echo $this->createUrl($this->route); ?>" method="post">
                    <h3 class="center">Check Policyholders</h3>
                    <input type="hidden" value="" name="smokecheck[geoJson]">
                    <input type="hidden" value="" name="smokecheck[centroidLat]">
                    <input type="hidden" value="" name="smokecheck[centroidLong]">
                    <input type="hidden" value="<?php echo (isset($pointToAcres['acres'])) ? $pointToAcres['acres'] : ''; ?>" name="smokecheck[fireSize]">
                    <input type="hidden" value="<?php if ($fileName) echo $fileName; ?>" name="smokecheck[kmlFileName]">
                    <div class="row" style="padding:5px 0;">
                        <?php echo CHTML::label('Alert Distance','smokecheck[alertDistance]'); ?>
                        <?php echo CHTML::dropDownList('smokecheck[alertDistance]', '5', array_combine(range(1,20), range(1,20)), array('style'=>'width:75px;padding-right:10px;')); ?>
                    </div>
                    <div class="row" id="button-row" style="padding:5px 0;">
                        <input type="submit" value="Monitor Fire" class="btn btn-primary" >
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <div class="span4"></div>
</div>

<div id="map-wrapper">
    <div id="map" style="height:500px;"></div>
</div>

<?php if ($nearbyPerimeters): ?>

<p class="center">Past Fire perimeters shown on map have been created within the last month and intersect the 3 mile perimeter.</p>

<?php endif; ?>

<?php if (!is_null($smokecheckResults)): ?>
    <?php $chunks = array_chunk($smokecheckResults, 2, true); ?>
    
    <div class="row-fluid" style="padding-top:25px;">
        <div class="span6">
        <?php foreach ($chunks as $chunk): ?>
            <div class="row-fluid">
            <?php foreach ($chunk as $result): ?>
                <div class="span6">
                    <h2><?php echo $result['client']; ?></h2>
                    <ul>
                        <li><u>Total: <?php echo ($result['enrolled'] + $result['not_enrolled']); ?></u></li>
                        <li>Enrolled: <?php echo $result['enrolled']; ?></li>
                        <li>Not Enrolled: <?php echo $result['not_enrolled']; ?></li>
                        <li>Unmatched: <?php echo $result['unmatched']; ?></li>
                    </ul>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        </div>
        <div class="span6">
            <div class="row-fluid">
                <div class="span6">
                    <?php if(isset(Yii::app()->session['fireSize']) && !empty(Yii::app()->session['fireSize'])): ?>
                    <p>Size based on perimeter: <?php echo number_format(Yii::app()->session['fireSize']); ?> acres</p>
                    <?php endif; ?>
                    <h3>To Proceed With This Fire</h3>
                    <ol>
                        <li>Add Fire into system</li>
                        <li>Add Fire Details</li>
                        <li>Add Monitor Log Entry</li>
                        <li>Proceed with notification if necessary</li>
                    </ol>
                </div>
                <div class="span6">
                    <p class="center" style="padding-top:25px;"><a href ='<?php echo $this->createUrl('/resFireName/create'); ?>' class="btn btn-success btn-large">Add Fire</a></p>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script type="text/javascript">

    // SETUP MAP
    L.mapbox.accessToken = '<?php echo Helper::MAPBOX_ACCESS_TOKEN; ?>';
    L.mapbox.config.FORCE_HTTPS = true;

    var fileName = <?php echo json_encode($fileName); ?>;
    var lat = <?php echo json_encode($lat); ?>;
    var long = <?php echo json_encode($long); ?>;
    var geoJson = <?php echo $geoJson ? $geoJson : json_encode($geoJson); ?>;
    var pointtoacresWkt = <?php echo json_encode($pointtoacresWkt); ?>;

    //Configure Map
    var map = new L.mapbox.Map('map').setView([40.03, -99.2], 4);
    map.scrollWheelZoom.disable();

    var esriTileOptions = {
        detectRetina: true,
        reuseTiles: true,
        subdomains: ['server', 'services']
    };

    new L.LayerGroup([
        new L.tileLayer('https://{s}.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', esriTileOptions),
        new L.tileLayer('https://{s}.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', esriTileOptions)
    ]).addTo(map);

    // If a kml was uploaded
    if (fileName) {

        var kmlLayer = omnivore.kml('/tmp/' + fileName).on('ready', function() {

            //Centroid
            var center = turf.centroid(this.toGeoJSON());

            this.setStyle({
                color: 'black',
                weight:2,
                fillColor: 'red',
                fillOpacity: 0.5
            });

            // Set map view
            map.fitBounds(this.getBounds());

            // Set hidden fields used in next query (add fire)
            // If user proceeds w/ fire, the geojson is later stored in the res_perimeters, and the lat/long are used in the res_fire_name for weather etc.
            document.forms[3].elements['smokecheck[geoJson]'].value = JSON.stringify(this.toGeoJSON());
            document.forms[3].elements['smokecheck[centroidLat]'].value = center.geometry.coordinates[1];
            document.forms[3].elements['smokecheck[centroidLong]'].value = center.geometry.coordinates[0];
            document.forms[3].elements['smokecheck[fireSize]'].value = Math.round(turf.area(kmlLayer.toGeoJSON()) * .000247105);

        }).on('error', function() {
            console.log('Something went wrong while converting the kml. Please make sure the geometry is valid and file is a KML.');
        }).addTo(map);
    }

    // If point to acres was run
    else if (pointtoacresWkt) {

        var fireLayer = omnivore.wkt.parse(pointtoacresWkt, null, new L.geoJson(null, {
            style: {
                color: 'black',
                weight: 2,
                fillColor: 'red',
                fillOpacity: 0.5
            }
        })).addTo(map);

        map.fitBounds(fireLayer.getBounds());

        var geoJson = fireLayer.toGeoJSON();
        var center = turf.centroid(geoJson);

        // Set hidden fields used in next query (add fire)
        // If user proceeds w/ fire, the geojson is later stored in the res_perimeters, and the lat/long are used in the res_fire_name for weather etc.
        document.forms[3].elements['smokecheck[geoJson]'].value = JSON.stringify(geoJson);
        document.forms[3].elements['smokecheck[centroidLat]'].value = center.geometry.coordinates[1];
        document.forms[3].elements['smokecheck[centroidLong]'].value = center.geometry.coordinates[0];
    }

    // If Coordiantes were used
    else if (lat && long) {

        var fireLayer = new L.Marker(new L.latLng(lat, long), {
            icon: new L.Icon({
                iconSize: new L.Point(40, 40),
                iconUrl: 'images/fire-icon.png'
            })
        }).addTo(map);

        var latlng = fireLayer.getLatLng();

        map.setView(latlng, 17);

        var geojson = {
            'type': 'FeatureCollection',
            'features': [{
                'type': 'Feature',
                'geometry': {
                    'type': 'Point',
                    'coordinates': [latlng.lng, latlng.lat]
                }
            }]
        };

        //Set hidden variables used in next query (add fire)
        document.forms[3].elements['smokecheck[geoJson]'].value = JSON.stringify(geojson);
        document.forms[3].elements['smokecheck[centroidLat]'].value = latlng.lat;
        document.forms[3].elements['smokecheck[centroidLong]'].value = latlng.lng;
    }

    // If a result of smokecheck being run
    else if (geoJson) {

        var fireStyle = {
            color: 'black',
            weight:2,
            fillColor: 'red',
            fillOpacity: 0.5
        };

        var fireLayer = new L.mapbox.featureLayer(geoJson, {
            pointToLayer: function(feature, latlng) {
                return new L.Marker(latlng, {
                    icon: new L.Icon({
                        iconSize: [40, 40],
                        iconUrl: 'images/fire-icon.png'
                    })
                });
            }
        }).setStyle(fireStyle).addTo(map);

        map.fitBounds(fireLayer.getBounds());

        // Add buffers
        var bufferLayer = new L.mapbox.featureLayer().loadURL('<?php echo $this->createUrl('/resPerimeters/getPerimeterGeoJsonBuffer', array('perimeterID' => 0, 'fourthRing'=>$alertDistance)); ?>').on('ready', function () {
            this.eachLayer(function(layer) {
                layer.setStyle(Perimeter.bufferStyle(layer.feature));
            }).bringToBack();
            map.fitBounds(bufferLayer.getBounds());
        }).addTo(map);

        // Disabled KML download
        var $downloadKML = $('#download-kml');
        var originalText = $downloadKML.text();
        $downloadKML
            .text('Loading ...')
            .addClass('disabled')
            .bind('click', function() { return false; });

        // Add Policyholders
        $.getJSON('<?php echo $this->createUrl('/property/getGeoJson', array('perimeterID' => 0, 'bufferDistance' => $alertDistance)); ?>', function (featureCollection) {

            // Enabling KML download
            $downloadKML
                .removeClass('disabled')
                .unbind('click')
                .text(originalText);

            if (featureCollection.features.length) {
                var clientControl = new L.Control.Layers(null, null, { collapsed: false, position: 'topright' }).addTo(map);
                var featureLayers = [];
                var featureLayersGeoJson = [];
                // Splitting the feature collection into individual layers by client
                featureCollection.features
                    .map(function(feature) { return feature.properties.client; })
                    .filter(function(elem, pos, arr) { return arr.indexOf(elem) == pos; })
                    .sort()
                    .forEach(function(clientName, index) {
                        // Create individual client feature collection
                        var clientGeoJson = {
                            type: 'FeatureCollection',
                            features: featureCollection.features.filter(function(feature) {
                                return feature.properties.client == clientName;
                            })
                        };
                        // Assuming the layer is populated, create the geojson layer and add to client FeatureGroup
                        if (clientGeoJson.features.length) {
                            var geoJsonLayer = new L.geoJson(clientGeoJson, {
                                onEachFeature: Policyholders.onEachFeature,
                                pointToLayer: Policyholders.pointToLayer
                            });
                            if (index === 0) {
                                geoJsonLayer.addTo(map);
                            }
                            clientControl.addBaseLayer(geoJsonLayer, clientName);
                            featureLayers.push(geoJsonLayer);
                            featureLayersGeoJson.push(clientGeoJson);
                        }
                    });

                    new L.Control.PolicyStatusToggle({
                        featureLayers: featureLayers,
                        featureLayersGeoJson: featureLayersGeoJson,
                        statusPropertyName: 'response_status'
                    }).addTo(map);
            }
        });
    }

    <?php if(!$geoJson): ?>

    // Add progress bar when monitored
    $('#monitor-fire').submit(function() {
        //var submitButton = this.querySelector('input[type="submit"]');
        $('#button-row').empty();
        $('#button-row').html(
            '<div style="width: 100%; margin: 5px 0px; float:left; visibility: visible;" class="monitor-progress progress progress-striped active">' +
                '<div class="bar" style="width: 100%;margin:0;"></div>' +
            '</div>');
    });

    <?php endif; ?>

    // Add past fires if they exist
    var perimetersData = <?php echo json_encode($nearbyPerimeters); ?>;

    if (perimetersData && perimetersData.length) {

        var pastFiresLayerGroup = new L.LayerGroup().addTo(map);

        var pastFiresLayer = new L.geoJson(null, {
            pointToLayer: function(feature, latlng) {
                return new L.Marker(latlng, {
                    icon: new L.Icon({
                        iconSize: [40, 40],
                        iconUrl: 'images/fire-icon.png'
                    })
                });
            },
            style: {
                color: 'black',
                weight: 2,
                fillColor: 'red',
                fillOpacity: 0.5
            }
        });

        var nearbyPerimeterControl = new L.Control.Layers(null, { 'Past Fires': pastFiresLayerGroup }, { collapsed: false, position: 'bottomright' }).addTo(map);
        var container = nearbyPerimeterControl.getContainer();
        container.style.border = '5px solid red';
        container.className += ' animated flashing';

        perimetersData.forEach(function(fire) {

            var popup = '<b>' + fire.name + '</b>' +
                '<table>' +
                    '<tr>' +
                        '<td style"width:1%; white-space: nowrap;">Fire Start Date:</td>' +
                        '<td>' + fire.start_date + '</td>' +
                    '</tr>' +
                    '<tr>' +
                        '<td style"width:1%; white-space: nowrap;">Last Monitored Date:</td>' +
                        '<td>' + fire.monitored_date + '</td>' +
                    '</tr>' +
                '</table>';

            var pastFireLayer = omnivore.wkt.parse(fire.geog, null, $.extend(true, {}, pastFiresLayer))
                .bindLabel(fire.name)
                .bindPopup(popup, { offset: new L.Point(0, -3) });

            pastFiresLayerGroup.addLayer(pastFireLayer);
        });
    }

</script>
