<?php

/* @var $this RiskModelController */
/* @var $reportForm RiskReportForm */
/* @var $form CActiveForm */

$this->breadcrumbs=array(
	'WDS Risk Query',
);

Assets::registerMapboxPackage();
Assets::registerRiskControl();
Assets::registerOpacityControl();

Yii::app()->clientScript->registerCss('riskcss','
    #map-wrapper {
        position: relative;
        height: 600px;
    }
    #map {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 100%;
    }
');

?>

<h2>Risk Model Query</h2>

<div class="marginBottom20 marginTop20">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id'=>'risk-report-form',
        'action' => $this->createUrl('/riskModel/riskReport'),
        'htmlOptions' => array('target' => '_blank')
    ));
    echo CHtml::submitButton('View Report', array('class' => 'submit'));
    echo $form->hiddenField($reportForm, 'risk_v', array('value' => false));
    echo $form->hiddenField($reportForm, 'risk_whp', array('value' => false));
    echo $form->hiddenField($reportForm, 'risk_wds', array('value' => false));
    echo $form->hiddenField($reportForm, 'geojson', array('value' => false));
    echo $form->hiddenField($reportForm, 'address', array('value' => false));
    echo $form->hiddenField($reportForm, 'lat', array('value' => false));
    echo $form->hiddenField($reportForm, 'lon', array('value' => false));
    $this->endWidget();
    ?>
</div>

<div id="map-wrapper">
    <div id="map"></div>
</div>

<script type="text/javascript">

    var Risk = (function() {

        // PRIVATE VARIABLES

        var featureLayer = new L.mapbox.featureLayer();

        var marker = new L.CircleMarker().setStyle({
            color: 'black',
            weight: 2,
            opacity: 0.6,
            fillColor: 'steelblue',
            fillOpacity: 0.8,
            radius: 6
        });

        var colors = {
            0: '#006837',
            1: '#1A9850',
            2: '#A6D96A',
            3: '#FFFFAF',
            4: '#FFFF00',
            5: '#F0C800',
            6: '#FFA500',
            7: '#FF4500',
            8: '#FF0000',
            9: '#8B0000'
        };

        // PRIVATE FUNCTIONS

        var mapLegend = function() {
            var canvas = document.createElement('canvas');
            var context = canvas.getContext('2d');

            canvas.height = 70;
            canvas.width = 100;
            canvas.style.margin = '10px';

            context.rect(0, 0, canvas.width / 2, canvas.height);

            context.font = 'bold 15px Arial';
            context.fillText('High', canvas.width * 0.6, canvas.height * 0.2);
            context.fillText('Low', canvas.width * 0.6, canvas.height * 0.9);

            var gradient = context.createLinearGradient(0, 0, 0, canvas.height);
            gradient.addColorStop(0, '#a50026');
            gradient.addColorStop(0.5, '#d9ef8b');
            gradient.addColorStop(1, '#006837');
            context.fillStyle = gradient;
            context.fillRect(0, 0, canvas.width / 2, canvas.height);

            return canvas;
        };

        var getRiskStyle = function(feature) {
            return {
                fillColor: colors[feature.properties.risk],
                color: colors[feature.properties.risk],
                weight: 0,
                opacity: 0.4,
                fillOpacity: 0.6
            };
        };

        var loadRisk = function(featureCollection, latlng) {
            featureLayer.clearLayers();
            featureLayer.setGeoJSON(featureCollection);
            // This commented out JS change is needed with postgres
            //featureLayer.setGeoJSON(JSON.parse(featureCollection));
            featureLayer.eachLayer(function(layer) {
                layer.bindPopup('<b style="margin:20px;">Risk:<b/> ' + layer.feature.properties.risk.toString());
                layer.setStyle(getRiskStyle(layer.feature));
            });
            marker.setLatLng([latlng.lat, latlng.lng]);
            map.fitBounds(featureLayer.getBounds());
            riskLayerGroup.clearLayers();
            riskLayerGroup.addLayer(featureLayer);
            riskLayerGroup.addLayer(marker);
        };

        // PUBLIC FUNCTIONS

        return {
            getMapLegend: function() {
                return mapLegend();
            },
            getLoadRisk: function(feature_collection, latlng) {
                return loadRisk(feature_collection, latlng);
            }
        };

    })();

</script>

<script type="text/javascript">

    // SETUP MAP

    L.mapbox.accessToken = '<?php echo Helper::MAPBOX_ACCESS_TOKEN; ?>';
    L.mapbox.config.FORCE_HTTPS = true;

    var map = new L.mapbox.Map('map').setView([40.03, -99.2], 4);

    var riskLayerGroup = new L.LayerGroup().addTo(map);

    var esriTileOptions = {
        detectRetina: true,
        reuseTiles: true,
        subdomains: ['server', 'services']
    };

    var imagery = new L.LayerGroup([
        new L.tileLayer('https://{s}.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', esriTileOptions),
        new L.tileLayer('https://{s}.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', esriTileOptions)
    ]).addTo(map);

    var riskRaster = new L.mapbox.tileLayer('wdsresponse.8flq54i7');
    var riskServiceLayer = new L.tileLayer('https://api.mapbox.com/styles/v1/wdsresponse/cinrrnc340089bwma2tzziqrx/tiles/{z}/{x}/{y}?access_token=' + L.mapbox.accessToken).addTo(map);

    // CONTROLS

    var layerControl = new L.Control.Layers(null, {
        'WDS Risk': riskLayerGroup,
        'WDS Risk Raster': riskRaster
    }, { collapsed: false }).addTo(map);

    var fullscreen = new L.Control.Fullscreen().addTo(map);
    var geocoderControl = new L.mapbox.GeocoderControl('https://api.mapbox.com/geocoding/v5/mapbox.places/{query}.json?access_token=' + L.mapbox.accessToken).addTo(map);
    var riskControl = new L.Control.RiskControl(geocoderControl).addTo(map);
    var opacityControl = new L.Control.OpacityToggle(riskRaster, { position : 'topright' }).addTo(map);

    //map.legendControl.getContainer().appendChild(Risk.getMapLegend());
    //map.legendControl.getContainer().style.display = 'block';
    map.attributionControl.removeFrom(map);

    // LISTENERS

    var score_v;
    var score_whp;
    var score_wds;

    riskControl.on('risk', function(e) {
        score_v = e.score_v;
        score_whp = e.score_whp;
        score_wds = e.score_wds;
        Risk.getLoadRisk(e.geojson, e.latlng);
    });

    // On Report Submit, pass risk value and geojson into form and controller method for the report.

    $('#risk-report-form').submit(function(e) {
        if (!riskLayerGroup.getLayers().length) {
            alert('There must be a risk map displayed first!');
            return false;
        }

        var riskLayer = riskLayerGroup.getLayers()[0];
        var geojson = JSON.stringify(riskLayer.getGeoJSON());
        var address = document.getElementsByClassName('leaflet-control-mapbox-geocoder-form')[0].firstChild.value;
        var center = riskLayer.getBounds().getCenter();
        var lat = center.lat;
        var lon = center.lng;

        document.forms[0].elements['<?php echo CHtml::activeName($reportForm, 'risk_v'); ?>'].value = score_v;
        document.forms[0].elements['<?php echo CHtml::activeName($reportForm, 'risk_whp'); ?>'].value = score_whp;
        document.forms[0].elements['<?php echo CHtml::activeName($reportForm, 'risk_wds'); ?>'].value = score_wds;
        document.forms[0].elements['<?php echo CHtml::activeName($reportForm, 'geojson'); ?>'].value = geojson;
        document.forms[0].elements['<?php echo CHtml::activeName($reportForm, 'address'); ?>'].value = address;
        document.forms[0].elements['<?php echo CHtml::activeName($reportForm, 'lat'); ?>'].value = lat;
        document.forms[0].elements['<?php echo CHtml::activeName($reportForm, 'lon'); ?>'].value = lon;
    });

</script>