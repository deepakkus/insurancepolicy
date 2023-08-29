<?php

/* @var $this ResThreatController */
/* @var $model ResThreat */
/* @var $fireID integer */

$this->breadcrumbs = array(
    'Response' => array('/resNotice/landing'),
    'Manage Fires' => array('/resFireName/admin'),
    'Perimeters' => array('/resPerimeters/admin'),
    'New Threat'
);

Assets::registerMapboxPackage();

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/map-fire-style.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCss('new-threat-css','
    .section {
        border: 1px solid black;
        padding: 20px;
        background-color: #E8E8E8;
        font-size: 1.1em;
        border-radius: 4px;
        box-shadow: 3px 3px 5px 1px #CCCCCC;
        box-sizing: border-box;
        margin-top: 20px;
        overflow: hidden;
    }
');

$perimeters = ResPerimeters::getPerimetersForFire($fireID);
$threats = ResThreat::getThreatsForFireID($fireID);

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'res-new-threat-form',
    'type' => 'horizontal',
    'htmlOptions' => array(
        'class' => 'well',
        'enctype' => 'multipart/form-data'
    )
));

echo $form->errorSummary($model);

?>

<div class="row-fluid">
    <div class="span6">

        <h2>New Threat</h2>

        <?php

        echo $form->dropDownListRow($model, 'perimeter_id', CHtml::listData($perimeters, 'id', function($data) {
            return $data->resFireName->Name . ' - ' . date('Y-m-d H:i', strtotime($data->date_updated));
        }), array(
            'prompt' => ''
        ));

        ?>

        <p class="lead"><u>Create new or choose from existing threats</u></p>

        <div class="section">

            <div class="control-group">
                <div class="control-label">
                    <?php echo CHtml::image('images/kmz-medium.png', 'KML'); ?>
                </div>
            </div>

            <?php

            echo $form->fileFieldRow($model, 'kmlFileUpload', array(
                'accept'=>'.kml,.kmz',
                'hint' => 'Any file upload will be choosen over an existing threat selection'
            ));

            if ($threats)
            {
                echo $form->radioButtonListRow($model, 'threatIDToCopy', CHtml::listData($threats, 'id', function($data) {
                    return $data->resFireName->Name . ' - ' . date('Y-m-d H:i', strtotime($data->date_updated));
                }));
            }
            else
            {
                echo '<div class="control-group">
                    <label class="control-label">' . CHtml::activeLabel($model, 'threatIDToCopy') . '</label>
                    <div class="controls">
                        <b>There are no existing threats for this fire</b>
                    </div>
                </div>';

                echo $form->hiddenField($model, 'threatIDToCopy');
            }

            ?>

        </div>

    </div>
    <div class="span6">
        <h3>&nbsp;</h3>
        <div id="map-wrapper">
            <div id="map" style="height:500px;"></div>
        </div>
    </div>
</div>
<div class="row-fluid">
    <div class="span12">

        <div class="marginTop20">
            <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class' => 'submit')); ?>
            <span class="paddingLeft10">
                <?php echo CHtml::link('Cancel', array('resFireName/admin')); ?>
            </span>
        </div>
    </div>
</div>

<?php

$this->endWidget();

?>

<script type="text/javascript">

    // Map Init

    var perimeters = <?php echo json_encode(CHtml::listData($perimeters, 'id', 'geog')); ?>;
    var threats = <?php echo json_encode(CHtml::listData($threats, 'id', 'geog')); ?>;

    L.mapbox.accessToken = '<?php echo Helper::MAPBOX_ACCESS_TOKEN; ?>';
    L.mapbox.config.FORCE_HTTPS = true;

    var map = new L.mapbox.Map("map").setView([40.03, -99.2], 4);
    map.scrollWheelZoom.disable();

    var esriTileOptions = {
        detectRetina: true,
        reuseTiles: true,
        subdomains: ["server", "services"]
    };

    new L.LayerGroup([
        new L.tileLayer("https://{s}.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}", esriTileOptions),
        new L.tileLayer("https://{s}.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}", esriTileOptions)
    ]).addTo(map);

    var fireLayer = new L.mapbox.featureLayer().addTo(map);
    var threatLayer = new L.mapbox.featureLayer().addTo(map);

    // DOM Events

    $("#<?php echo CHtml::activeId($model, 'perimeter_id'); ?>").change(function() {
        map.fire("new-perimeter", {
            perimeterID: this.value
        });
    });

    $("input[type=radio][name='<?php echo CHtml::activeName($model, 'threatIDToCopy') ?>']").change(function() {
        map.fire("new-threat", {
            threatID: this.value,
            perimeterID: $("#<?php echo CHtml::activeId($model, 'perimeter_id'); ?>").val()
        });
    });

    // Map Events

    map.on("new-perimeter", function(event) {
        if (event.perimeterID !== "") {
            $.getJSON('<?php echo $this->createUrl('resPerimeters/getGeoJson'); ?>&perimeterID=' + event.perimeterID, function(featureCollection) {
                if (map.hasLayer(fireLayer)) map.removeLayer(fireLayer);
                fireLayer = new L.mapbox.featureLayer(featureCollection, {
                    pointToLayer: function(feature, latlng) {
                        return new L.Marker(latlng, {
                            icon: new L.Icon({
                                iconSize: [40, 40],
                                iconUrl: 'images/fire-icon.png'
                            })
                        })
                    }
                });
                fireLayer.setStyle(Perimeter.perimeterPolyStyle()).addTo(map);
                map.fitBounds(fireLayer.getBounds());
            });
        }
    });

    map.on("new-threat", function(event) {
        if (event.perimeterID !== "" && event.threatID !== "") {
            if (map.hasLayer(threatLayer)) map.removeLayer(threatLayer);
            threatLayer = new L.mapbox.featureLayer().loadURL('<?php echo $this->createUrl('resThreat/getGeoJson'); ?>&threatID=' + event.threatID + '&perimeterID=' + event.perimeterID).on('ready', function() {
                this.eachLayer(function(layer) {
                    layer.setStyle(Threat.threatStyle(layer.feature));
                });
                map.fitBounds(this.getBounds());
            }).addTo(map);
        } else {
            $("input[type=radio][name='<?php echo CHtml::activeName($model, 'threatIDToCopy') ?>']").removeAttr("checked");
            alert("Select a perimeter first!");
        }
    });

</script>