<?php

if ($page == 'monitor')
{
    $this->breadcrumbs = array(
        'Response' => array('/resNotice/landing'),
        'Monitor Log' => array('/resMonitorLog/admin'),
        'View Map'
    );
}
else
{
    $this->breadcrumbs = array(
        'Response' => array('/resNotice/landing'),
        'Smoke Check' => array('/resMonitorLog/smokeCheck'),
        'View Smoke Check'
    );
}

Assets::registerMapboxPackage();
Assets::registerMapboxLeafletOmnivore();
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/map-fire-style.js', CClientScript::POS_HEAD);
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

    #TriggeredPolicyholder_matched label{
display:inline-block;}
.menu-ui {
	  position: absolute;
	  margin-top: 13px;
	  right: 184px;
	  z-index: 9999;
	  }
');

$i = 0;

?>

<?php if ($model->Smoke_Check): ?>
    <h1 class="center">Smoke Check - <?php echo $fire->Name; ?></h1>
<?php else: ?>
    <h1 class="center"><?php echo $fire->Name; ?></h1>
<?php endif; ?>

<h3 class="center"><?php echo $fire->City . ', ' . $fire->State; ?> | Monitored Date: <?php echo $model->monitored_date; ?></h3>
<h3 class="center">Alert Distance: <?php echo $model->Alert_Distance; ?> miles</h3>

<div class="row" style="padding-top:25px;">
    <div id="map-wrapper">
	    <div id='client_list' class='menu-ui'></div>
        <div id="map" style="height:500px;"> 
			<div id="selectBoxtest">
			</div>
		</div>
    </div>
</div>
<?php if ($nearbyPerimeters): ?>
<p class="center">Past fire perimeters shown on map have been created within the last 24 months, intersect the 5 mile perimeter, and are over 100 acres.</p>
<?php endif; ?>
<p class="center">Note: The policyholders on the map are in real time, everything else is not</p>

<p class="center">
    <a href="<?php echo $this->createUrl('/resMonitorLog/downloadKMZ', array('perimeter_id' => $model->Perimeter_ID, 'fire_name' => $fire->Name, 'miles' => $model->Alert_Distance)); ?>" id="download-kml" class="btn btn-default btn-large">View KML</a>
</p>

<?php

if ($page === 'smokecheck')
{
    echo '<p class="center">';
    if (ResPerimeters::hasThreatForPerimeter($model->Perimeter_ID) === false)
        echo CHtml::link('Add Threat', array('/resPerimeters/createThreat', 'id' => $model->Perimeter_ID), array('id' => 'threat-url', 'class' => 'btn btn-default'));
    else
        echo CHtml::link('Update Threat', array('/resPerimeters/updateThreat', 'id' => $model->Perimeter_ID), array('id' => 'threat-url', 'class' => 'btn btn-default'));
    echo '</p>';
}

?>

<div class="row-fluid" style="padding-top:50px;">
    <div class="span6">
        <h2>Fire Information</h2>
        <ul style='padding:0;'>
            <li>Size: <?php echo  $fireDetails->Size; ?></li>
            <li>Containment: <?php echo $fireDetails->Containment; ?> </li>
            <li>Suppression: <?php echo $fireDetails->Supression; ?> </li>
        </ul>
        <?php if(!empty($model->Comments)): ?>
        <p><?php echo $model->Comments; ?></p>
        <?php endif; ?>
        <?php if(!empty($model->Smoke_Check_Comments)): ?>
        <p><?php echo $model->Smoke_Check_Comments; ?></p>
        <?php endif; ?>
    </div>

    <div class="span6">
        <h2>Weather</h2>
        <table cellpadding="10">
            <tr>
                <td valign="top">
                    <p><strong>Current</strong></p>
                    <ul style='padding:0;'>
                        <li>Wind: <?php echo !empty($fireDetails->Gust) ? $fireDetails->Wind_Speed . " mph " . $fireDetails->Wind_Dir : $fireDetails->Wind_Speed . " mph, gusting " . $fireDetails->Gust . " " . $fireDetails->Wind_Dir; ?></li>
                        <li>Temperature: <?php echo $fireDetails->Temp; ?> </li>
                        <li>Humidity: <?php echo $fireDetails->Humidity; ?> &#37;</li>
                        <li>Red Flags <?php echo ($fireDetails->Red_Flags) ? "YES" : "No"; ?></li>
                    </ul>
                </td>
                <td valign="top">
                    <p><strong>Forecast (<?php echo $fireDetails->Fx_Time; ?>)</strong></p>
                    <ul style='padding:0;'>
                        <li>Wind: <?php echo $fireDetails->Fx_Wind_Speed . " mph "; ?> <?php (!empty($fireDetails->Fx_Gust)) ? " G " . $fireDetails->Fx_Wind_Dir : ""; ?></li>
                        <li>Temperature: <?php echo $fireDetails->Fx_Temp; ?></li>
                    </ul>
                </td>
            </tr>
        </table>
    </div>
</div>

<?php if($triggers): ?>
    <div style ="float:left;width:100%">
    <div style ="float:left;width:35%"><h2>Triggered Policyholders:</h2></div>
    
    <div style ="float:left;width:22%;margin-top:12px;">
    <a href = "<?php echo $this->createUrl('resMonitorLog/downloadMatchedList',array("monitor_Id"=>$_GET['id']))?>" class="btn btn-default btn-large"> Download All Matched </a>
    </div>
    <div style ="float:left;width:30%;margin-top:12px;">
    <a href = "<?php echo $this->createUrl('unmatched/downloadAllUnmatchedList',array("zipcodes"=>$model->Zip_Codes,"monitor_Id"=>$_GET['id']))?>" class="btn btn-default btn-large"> Download All Unmatched </a>
    </div>
    </div>
    <?php foreach(array_chunk($triggers, 4, true) as $triggersArray): ?>
        <div class="row-fluid">
            <?php foreach($triggersArray as $trigger): ?>
            <div class="span3">
                <p><strong><?php echo $trigger->client->name; ?></strong></p>
                <ul>
                    <li>Enrolled: <?php echo $trigger->enrolled; ?></li>
                    <li>Not Enrolled: <?php echo $trigger->eligible; ?></li>
                    <li>Closest: <?php echo  $trigger->closest; ?></li>
                    <li>Unmatched: <?php echo ($trigger->unmatched) ? $trigger->unmatched . " - <a href = '" . $this->createUrl('unmatched/downloadUnmatchedList', array("zipcodes"=>$model->Zip_Codes,"client_id"=>$trigger->client_id)) . "'>View Unmatched</a>": $trigger->unmatched; ?></li>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<h2>Fire History:</h2>

<div class ="table-responsive">
    <table class ="table table-hover table-border">
        <tr>
            <td><strong>Date</strong></td>
            <td><strong>Size</strong></td>
            <td><strong>Containtment</strong></td>
            <td><strong>Temperature</strong></td>
            <td><strong>Temperature Fx</strong></td>
            <td><strong>Wind</strong></td>
            <td><strong>Wind Fx</strong></td>
            <td><strong>Humidity</strong></td>
            <td><strong>Red Flags</strong></td>
            <td><strong>Suppression</strong></td>
        </tr>

        <?php foreach($fireDetailsHistory as $entry): ?>
            <tr>
                <td><?php echo $entry->date_updated; ?></td>
                <td><?php echo $entry->Size; ?></td>
                <td><?php echo $entry->Containment; ?></td>
                <td><?php echo $entry->Temp; ?></td>
                <td><?php echo $entry->Fx_Temp; ?></td>
                <td><?php echo $entry->Wind_Speed . ' ' . $entry->Wind_Dir; ?></td>
                <td><?php echo $entry->Fx_Wind_Speed . ' ' . $entry->Fx_Wind_Dir; ?></td>
                <td><?php echo $entry->Humidity; ?></td>
                <td><?php echo ($entry->Red_Flags) ? 'Yes' : 'No'; ?></td>
                <td><?php echo $entry->Supression; ?></td>
            </tr>
        <?php endforeach; ?>

    </table>
</div>

<script type="text/javascript">

    // SETUP MAP
    L.mapbox.accessToken = '<?php echo Helper::MAPBOX_ACCESS_TOKEN; ?>';
    L.mapbox.config.FORCE_HTTPS = true;
    var map = new L.mapbox.Map('map');

    //Controls
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

    // Add fire
    $.getJSON('<?php echo $this->createUrl('/resPerimeters/getPerimeterGeoJson', array('perimeterID' => $model->Perimeter_ID)); ?>', function(featureCollection) {
        new L.mapbox.featureLayer(featureCollection, {
            pointToLayer: function(feature, latlng) {
                return new L.Marker(latlng, {
                    icon: new L.Icon({
                        iconSize: [40, 40],
                        iconUrl: 'images/fire-icon.png'
                    })
                })
            }
        }).setStyle(Perimeter.perimeterPolyStyle()).addTo(map);
    });

    // Add buffers
    var bufferLayer = new L.mapbox.featureLayer().loadURL('<?php echo $this->createUrl('/resPerimeters/getPerimeterGeoJsonBuffer', array('perimeterID' => $model->Perimeter_ID, 'fourthRing'=>$model->Alert_Distance)); ?>').on('ready', function() {
        this.eachLayer(function(layer) {
            layer.setStyle(Perimeter.bufferStyle(layer.feature));
        });
        map.fitBounds(bufferLayer.getBounds());
    }).addTo(map);

    // Add Threat
    var threatLayer = new L.mapbox.featureLayer().loadURL('<?php echo $this->createUrl('/resPerimeters/getThreatGeoJson', array('perimeterID' => $model->Perimeter_ID)); ?>').on('ready', function() {
        this.eachLayer(function(layer) {
            layer.setStyle(Threat.threatStyle(layer.feature));
        });
    }).addTo(map);

    // Add zipcodes
    var zipcodesLayer = new L.mapbox.featureLayer().loadURL('<?php echo $this->createUrl('/resPerimeters/getZipGeoJson', array('perimeterID' => $model->Perimeter_ID)); ?>').on('ready', function() {
        this.eachLayer(function(layer) {
            Zipcodes.onEachFeature(layer);
            layer.setStyle(Zipcodes.zipcodesStyle(layer.feature));
            layer.bindLabel(layer.feature.properties.zipcode);
        });
       
    });

    // Disabled KML download
    var $downloadKML = $('#download-kml');
    var originalText = $downloadKML.text();
    $downloadKML
        .text('Loading ...')
        .addClass('disabled')
        .bind('click', function() { return false; });

    var $addThreat = $('#threat-url');
    var addThreatText = $addThreat.text();
    $addThreat
        .text('Loading ...')
        .addClass('disabled')
        .bind('click', function() { return false; });

    // Add Policyholders
    $.getJSON('<?php echo $this->createUrl('/property/getGeoJson', array('perimeterID' => $model->Perimeter_ID, 'bufferDistance' => $model->Alert_Distance)); ?>', function(featureCollection) {
	
        // Enabling KML download
        $downloadKML.removeClass('disabled').unbind('click').text(originalText);
        $addThreat.removeClass('disabled').unbind('click').text(addThreatText);

        if (featureCollection.features.length) {

		var $select = $('<select></select>')
		.appendTo($('#client_list'))

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
					$('<option></option>')
						.text(clientName)
						.attr('value', clientName)
						.appendTo($select)

                    // Assuming the layer is populated, create the geojson layer and add to client FeatureGroup
                    if (clientGeoJson.features.length) {
                        var geoJsonLayer = new L.geoJson(clientGeoJson, {
                            onEachFeature: Policyholders.onEachFeature,
                            pointToLayer: Policyholders.pointToLayer
                        });
                        if (index === 0) {
                            geoJsonLayer.addTo(map);
                        }
                        featureLayers.push(geoJsonLayer);
                        featureLayersGeoJson.push(clientGeoJson);
                    }
                });

                new L.Control.PolicyStatusToggle({
                    featureLayers: featureLayers,
                    featureLayersGeoJson: featureLayersGeoJson,
                    statusPropertyName: 'response_status'
                }).addTo(map);

                new L.Control.Layers(null, { 'Zipcodes': zipcodesLayer }, {
                    collapsed: false,
                    position: 'topright'
                }).addTo(map);
        }
    });

    // Add past fires if they exist
    var perimetersData = <?php echo json_encode($nearbyPerimeters); ?>;

    if (perimetersData.length) {

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
$('#client_list').on('change', function(event, params) {
	$(".propertyStatus").prop("checked", true);
	$( ".marker" ).remove();
        var pid = event.target.selectedIndex;
		$.getJSON('<?php echo $this->createUrl('/property/getGeoJson', array('perimeterID' => $model->Perimeter_ID, 'bufferDistance' => $model->Alert_Distance)); ?>', function(featureCollection) {
		
		 if (featureCollection.features.length) {
		  var featureLayers = [];
            var featureLayersGeoJson = [];
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
					if (clientGeoJson.features.length) 
					{
						var geoJsonLayer = new L.geoJson(clientGeoJson, {
								onEachFeature: Policyholders.onEachFeature,
								pointToLayer: Policyholders.pointToLayer
							});
						if (index === pid  ) 
						{
							map.addLayer(geoJsonLayer);
                        }
					}
				});
		 }
		 });//--end json
	});
</script>
