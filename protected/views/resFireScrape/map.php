<?php

/* @var $this ResFireScrapeController */
/* @var $model ResFireScrape */

Yii::app()->clientScript->registerCoreScript('jquery');

Assets::registerMapboxPackage();

?>

<!DOCTYPE html>
<html lang="en-US">
    <head>
        <title>Fire Monitor Map</title>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="/images/firescraper/fire-icon.ico" type="image/x-icon">
        <style type="text/css">
            html { 
                height: 100% 
            }
            body { 
                height: 100%; 
                margin: 0; 
                padding: 0 
            }
            #map { 
                height: 100% 
            }
            #updated {
                position: absolute;
                top: 15px;
                left: 50px;
                z-index: 10;
            }
        </style>
    </head>
    <body>

        <div id="map">
            <div id="updated"></div>
        </div>

        <script type="text/javascript">

            L.mapbox.accessToken = '<?php echo Helper::MAPBOX_ACCESS_TOKEN; ?>';

            var map = new L.mapbox.map('map').setView([40.03, -99.2], 5);

            map.removeControl(map.attributionControl);

            var geojson = <?php echo json_encode($feature_collection); ?>;

            new L.mapbox.featureLayer(geojson).eachLayer(function(marker) {
                marker.bindPopup(marker.feature.properties.popup);
                marker.setIcon(L.icon(marker.feature.properties.icon));
            }).addTo(map);

            var baseLayers = {
                'Streets': new L.mapbox.tileLayer('mapbox.streets'),
                'Satellite': new L.mapbox.tileLayer('mapbox.streets-satellite'),
                'Outdoors': new L.mapbox.tileLayer('mapbox.outdoors'),
                'Light': new L.mapbox.tileLayer('mapbox.light'),
                'Dark': new L.mapbox.tileLayer('mapbox.dark')
            };
        
            new L.control.layers(baseLayers, null, { collapsed: false }).addTo(map);

            baseLayers['Dark'].addTo(map);

        </script>
    </body>
</html>