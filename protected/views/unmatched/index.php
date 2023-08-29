<?php 

$this->breadcrumbs=array(
    'Response' => array('/resNotice/landing'),
    'Unmatched'
);

Yii::app()->clientScript->registerCss('responseLandingCSS','
    .jumbotron {
        margin: 40px 0;
        text-align: center;
    }
    .jumbotron h1 {
        font-size: 40px;
        line-height: 1;
    }
    p.text-large {
        font-size: 120%;
    }

    #map {
        height:600px;
        width:100%;
    }
    #features {
        position: absolute;
        top: 10px;
        right: 10px;
        height: 50px;
        width: 200px;
        overflow: auto;
        background: rgba(255, 255, 255, 0.9);
        font-size: 1.3em;
        padding: 10px;
        z-index: 10;
        display: none;
    }
    /* Overriding yii bootstrap CSS */
    #features table td, table th {
        padding: 0 !important;
    }
    #zips-export {
        position: absolute;
        top: 90px;
        right: 10px;
        height: 200px;
        width: 200px;
        overflow: auto;
        background: rgba(255, 255, 255, 0.9);
        font-size: 1.3em;
        padding: 10px;
        z-index: 10;
        display: none;
    }
');

Assets::registerD3PackageV3();
Assets::registerMapboxGLPackage();
Assets::registerMapboxGLGeocoderPackage();

?>

<div class="container-fluid">

    <div class="jumbotron">
        <h1>Unmatched</h1>
    </div>

    <!-- Update coordiantes and wds_geocode status of existing policy -->

    <?php $this->beginWidget('bootstrap.widgets.TbBox', array(
        'title' => 'Update Unmatched',
        'htmlOptions' => array('class' => 'center')
    )); ?>
    
    <div class="row-fluid center" style="padding:25px 0;">
        <div class="span12">
            <h2>Update Unmatched</h2>
            <p class="text-large">Update coordiantes and matched type properties.</p>
            <p><a href="<?php echo $this->createUrl('/unmatched/unmatched') ?>" class="btn btn-primary btn-large">Update Unmatched</a></p>
        </div>
    </div>

    <?php $this->endWidget(); ?>

    <!-- Find Unmatched by client + Map view -->

    <?php $this->beginWidget('bootstrap.widgets.TbBox', array(
        'title' => '<p class="center">Find Unmatched By Zipcode</p>',
        'htmlOptions' => array('class' => 'center')
    )); ?>

    <div style="text-align: left;" class="marginTop20 marginBottom20">
        <?php
        $clients = Client::model()->findAllByAttributes(array('active' => 1, 'wds_fire' => 1));
        echo '<b class="show marginBottom10">Client</b>';
        echo CHtml::dropDownList('clients', '', CHtml::listData($clients, 'id', 'name'), array('prompt' => ''));
        ?>
        <div class="floatRight" id="export-buttons">
            <a class="btn btn-success" id="list-export" href="javascript:void(0);">Export List</a>
        </div>
    </div>

    <div style="text-align: left;">
        <div id="map">
            <div id="features"></div>
            <div id="zips-export"></div>
        </div>
    </div>

    <?php $this->endWidget(); ?>

    <!-- Update coordiantes and wds_geocode status of existing policy -->

    <?php $this->beginWidget('bootstrap.widgets.TbBox', array(
        'title' => 'Download All Unmatched By Client',
        'htmlOptions' => array('class' => 'center')
    )); ?>
    
    <div class="row-fluid center" style="padding:25px 0;">
        <div class="span12">
            <?php $this->renderPartial('_downloadUnmatchedByClient'); ?>
        </div>
    </div>

    <?php $this->endWidget(); ?>

</div>

<script type="text/javascript">

    mapboxgl.accessToken = '<?php echo Helper::MAPBOX_ACCESS_TOKEN; ?>';

    var colorArray = ['#ffffb2', '#fed976', '#feb24c', '#fd8d3c', '#f03b20', '#bd0026'];

    var styles = {
        'version': 8,
        'name': 'zipcodes',
        'strite': 'mapbox://sprites/mapbox/streets-v8',
        'glyphs': 'mapbox://fonts/mapbox/{fontstack}/{range}.pbf',
        'transition': {
            'duration': 300,
            'delay': 0
        },
        'sources': {
            'mapbox-streets': {
                'type': 'raster',
                'url': 'mapbox://mapbox.streets',
                'tileSize': 256
            },
            'zipcodes': {
                'type': 'vector',
                'url': 'mapbox://wdsresponse.c2ffe39b'
            }
        },
        'layers': [{
            'id': 'mapbox-streets',
            'type': 'raster',
            'source': 'mapbox-streets',
            'minzoom': 0,
            'maxzoom': 22
        }]
    };

    // Push color filters unto styles array
    colorArray.forEach(function(color) {
        styles.layers.push({
            'id': 'zipcodes-fill-' + color,
            'source': 'zipcodes',
            'source-layer': 'Zipcode',
            'type': 'fill',
            'layout': {
                'visibility': 'visible'
            },
            'paint': {
                'fill-color': color,
                'fill-opacity': 0.8
            },
            'filter': ['all',
                ['==', 'ZIP_CODE', 'NONE']
            ]
        });
    });

    // Push rest of zipcode styles
    styles.layers.push({
        'id': 'zipcodes',
        'interactive': true,
        'source': 'zipcodes',
        'source-layer': 'Zipcode',
        'type': 'line',
        'paint': {
            'line-color': '#4d4d4d'
        }
    }, {
        'id': 'zipcodes-selected',
        'source': 'zipcodes',
        'source-layer': 'Zipcode',
        'type': 'line',
        'paint': {
            'line-color': 'blue',
            'line-width': 3
        },
        'filter': ['all',
            ['==', 'ZIP_CODE', 'NONE']
        ]
    }, {
        'id': 'zipcodes-label',
        'source': 'zipcodes',
        'source-layer': 'Zipcode_Label',
        'type': 'symbol',
        'minzoom': 7,
        'layout': {
            'text-line-height': 1.2,
            'text-size': 14,
            'text-allow-overlap': false,
            'text-font': ['Source Sans Pro Semibold'],
            'symbol-placement': 'point',
            'text-padding': 2,
            'text-field': '{ZIP_CODE}',
            'text-letter-spacing': 0.02,
            'text-max-width': 8
        },
        'paint': {
            'text-color': '#444444',
            'text-halo-width': 2,
            'text-halo-blur': 1,
            'text-halo-color': 'rgba(237,233,217, 0.8)'
        }
    });

    var colorScale = d3.scale.quantize();
    var unmatchedCounts = {};
    var featuresDiv = document.getElementById('features');

    // Client change listener
    $('#clients').change(function() {
        var clientID = this.options[this.selectedIndex].value;
        var clientName = this.options[this.selectedIndex].innerText;

        /**
         * Getting data from database with format:
         * {
         *      zipcode: "unmatched count",
         *      22222: "1",
         *      33333: "13",
         *      77777: "7"
         * }
         */
        $.getJSON('<?php echo $this->createUrl('/unmatched/findUnmatched'); ?>', { clientID: clientID }, function(data) {

            unmatchedCounts = data;
            var counts = Object.keys(data).map(function(key) { return data[key]; });
            var unique = counts.filter(function(item, i, array) { return array.indexOf(item) === i; });

            // If all counts returned from the database are the same number, they color scale can't function (there'd be no range)
            // Go ahead and add another different number to the end so the scale can work properly
            if (unique.length === 1) {
                counts.push((parseInt(unique[0]) + 1).toString());
            }

            var colorFilters = {
                '#ffffb2': [],
                '#fed976': [],
                '#feb24c': [],
                '#fd8d3c': [],
                '#f03b20': [],
                '#bd0026': []
            };

            // Setting color scale object
            colorScale
                .range(colorArray)
                .domain([
                    d3.min(counts),
                    d3.max(counts)
                ]);

            // Populating color filters
            var keys = Object.keys(data);
            for (var i = 0; i < keys.length; i++) {
                var zip = keys[i];
                var zipColor = colorScale(data[zip]);
                if (colorFilters[zipColor].indexOf(zip) === -1) {
                    colorFilters[zipColor].push(zip);
                }
            }

            // Setting map color filters
            var colors = Object.keys(colorFilters);
            for (var i = 0; i < colors.length; i++) {
                var color = colors[i];
                map.setFilter('zipcodes-fill-' + color, ['all', ['==', 'ZIP_CODE', 'NONE']]);
                if (colorFilters[color].length) {
                    var zipCodes = colorFilters[color];
                    map.setFilter('zipcodes-fill-' + color, ['all', ['in', 'ZIP_CODE'].concat(zipCodes)]);
                }
            }

        });
    });

    if (!mapboxgl.supported()) {
        alert('Your browser does not support Mapbox GL');
    } else {
        var map = new mapboxgl.Map({
            container: 'map',
            style: styles,
            center: [-95, 40],
            zoom: 4
        });

        map.addControl(new mapboxgl.Geocoder({ position: 'top-left' }));
        map.addControl(new mapboxgl.Navigation({ position: 'top-left' }));

        function isEmpty(value) {
            return Boolean(value && typeof value == 'object') && !Object.keys(value).length;
        }

        map.on('style.load', function() {
            $('#features').fadeIn();
        });

        map.on('mousemove', function(e) {
            if (map.getZoom() > 5 && !isEmpty(unmatchedCounts)) {
                map.featuresAt(e.point, { layer: 'zipcodes', radius: 5 }, function(err, features) {
                    if (err) throw err;
                    // Only display features when inside a zipcode ( no displaying 2-3 zips when on a border)
                    if (features.length === 1) {
                        var zip = features[0].properties.ZIP_CODE;
                        var unmatchedCount = unmatchedCounts[zip];
                        if (typeof unmatchedCount === 'undefined') {
                            featuresDiv.innerHTML = '';
                        }
                        else {
                            featuresDiv.innerHTML = '<table>' +
                                '<tr><td><b>Unmatched: </b></td><td>' + unmatchedCount + '</td></tr>' +
                                '<tr><td><b>Zipcode: </b></td><td>' + zip + '</td></tr>' +
                                '</table>';
                        }
                    }
                });
            }
        });

        // List Exporter
        $('#list-export').click(function() {
            listExportClick();
        });

        function listExportClick() {

            $('.mapboxgl-canvas-container').css('cursor', 'pointer');

            var exportButtonsDiv = $('#export-buttons');
            var zipsExportDiv = $('#zips-export');
            var listExportButton = $('#list-export');
            var zipsToExport = [];

            listExportButton.css('display', 'none');
            zipsExportDiv.fadeIn();

            var zipDisplayDiv = $('<div></div>');

            var clearLink = $('<a></a>', {
                href: '#',
                class: 'floatRight',
                text: 'Clear Zips',
                on: {
                    click: function(e) {
                        e.preventDefault();
                        zipsToExport.length = 0;
                        zipDisplayDiv.html('');
                        map.setFilter('zipcodes-selected', ['all', ['==', 'ZIP_CODE', 'NONE']]);
                        console.log('Clear Zips Clicked');
                    }
                }
            });

            var doneLink = $('<a></a>', {
                href: '#',
                class: 'btn btn-info',
                text: 'Done',
                on: {
                    click: function(e) {
                        e.preventDefault();

                        $('.mapboxgl-canvas-container').css('cursor', '');

                        // Export List
                        var zipcodes = zipsToExport.join(',');
                        var clientID = $('#clients option:selected').val();
                        console.log(clientID);
                        if (clientID) {
                            window.location.href = '/index.php?r=unmatched/downloadUnmatchedList&zipcodes=' + zipcodes + '&client_id=' + clientID;
                        }
                        else {
                            alert('A client must be selected first.');
                        }

                        // Clean Up

                        listExportButton.css('display', 'block');
                        zipsExportDiv.html('');
                        zipsExportDiv.fadeOut();
                        $(this).remove();
                        zipsToExport.length = 0;
                        map.setFilter('zipcodes-selected', ['all', ['==', 'ZIP_CODE', 'NONE']]);
                        map.off('click');
                    }
                }
            });

            zipsExportDiv.append(clearLink);
            zipsExportDiv.append(zipDisplayDiv);
            exportButtonsDiv.append(doneLink);

            map.on('click', function(e) {
                if (map.getZoom() > 5) {
                    map.featuresAt(e.point, { layer: 'zipcodes', radius: 5 }, function(err, features) {
                        if (err) throw err;
                        if (features.length === 1) {
                            var zip = features[0].properties.ZIP_CODE;
                            zipsToExport.push(zip);
                            zipDisplayDiv.html(zipsToExport.join(', <br />'));
                            map.setFilter('zipcodes-selected', ['all', ['in', 'ZIP_CODE'].concat(zipsToExport)]);
                        }
                    });
                }
            });
        }

        /* Zipcodes Toggle Control */

        // Adding zipcodes toggle
        $('.mapboxgl-ctrl-top-left').append(
            $('<div></div>', {
                class: 'mapboxgl-ctrl-group mapboxgl-ctrl',
                html: $('<button></button>', {
                    class: 'mapboxgl-ctrl-icon',
                    css: {
                        width: '100px'
                    },
                    html: $('<label></label>').append(
                        $('<input></input>', {
                            type: 'checkbox',
                            class: 'checkbox',
                            checked: true,
                            on: {
                                click: function() {
                                    zipcodesVisible(this.checked);
                                }
                            }
                        })
                    ).append('&nbsp;<span><b>Zipcodes<b/></span>')
                })
            })
        );

        // Function toggles all Zipcode layers visibility
        function zipcodesVisible(visible) {
            if (typeof visible !== 'boolean') {
                throw 'argument must be boolean';
            }
            var layers = map.style._layers;
            var layerIDs = Object.keys(layers);
            for (var i = 0; i < layerIDs.length; i++) {
                var layer = layers[layerIDs[i]];
                if (layer.source === 'zipcodes') {
                    var visiblity = visible ? 'visible' : 'none';
                    map.setLayoutProperty(layer.id, 'visibility', visiblity);
                }
            }
        }
    }

</script>