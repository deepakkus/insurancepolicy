<?php 

$this->breadcrumbs=array(
    'Response' => array('/resNotice/landing'),
    'Unmatched' => array('/unmatched/index'),
    'Find Unmatched' => array('/unmatched/unmatched'),
    'Update Unmatched'
);

CHtml::$renderSpecialAttributesValue = false;
Assets::registerMapboxPackage();
Yii::app()->clientScript->registerScriptFile('/js/unmatched/unmatched.js');

?>

<div class="row-fluid row-grey">
    <div class = "span3">
        <?php $form = $this->beginWidget('CActiveForm', array(
            'id' => 'unmatched-form',
            'enableAjaxValidation' => true,
            'clientOptions' => array(
                'validateOnSubmit' => true
                )
        )); ?>

        <?php echo $form->errorSummary($model); ?>
        <div class ="form-section" style="padding-left:10px;">
                    
            <h3 class="marginBottom20">Enter Coordinates for found property</h3>

            <div>
	            <div class="clearfix">
                    <?php echo $form->labelEx($model, 'lat'); ?>
                    <?php echo $form->numberField($model, 'lat', array('required' => true, 'placeholder' => 'Ex: 40.12421' ,'step' => 'any')); ?>
                    <?php echo $form->error($model, 'lat'); ?>
	            </div>

	            <div class="clearfix">
                    <?php echo $form->labelEx($model, 'lon'); ?>
                    <?php echo $form->numberField($model, 'lon', array('required' => true, 'placeholder' => 'Ex: -122.4512','step' => 'any')); ?>
                    <?php echo $form->error($model, 'lon'); ?>
	            </div>
            </div>

            <div class="clearfix">
                <label for = 'confirm'>Location verfied on map</label>
                <?php echo CHtml::checkbox('confirm'); ?>
            </div>

	        <div class="buttons marginTop20">
                <div class="clearfix">
		            <?php echo CHtml::submitButton('Update', array('class'=>'submit', 'id'=>'submitButton')); ?>
                    <span class="paddingLeft10">
                        <?php echo CHtml::link('Cancel', array('unmatched/unmatched')); ?>
                    </span>
                </div>
	        </div>
                
        </div>
        <?php $this->endWidget(); ?>
    </div>
    <div class="span9">
        <div id="map" style="height:500px;width:100%;"></div>
    </div>
            
</div>
  

<script type="text/javascript">

    // SETUP MAP
    L.mapbox.accessToken = '<?php echo Helper::MAPBOX_ACCESS_TOKEN; ?>';
    L.mapbox.config.FORCE_HTTPS = true;
    var map = new L.mapbox.Map('map').setView([40,-110], 8);
    var markerLocationLayer = L.mapbox.featureLayer().addTo(map);
    var marker = L.marker(null, {draggable: true, icon: L.mapbox.marker.icon({
        "marker-color": "#B404AE",
        "marker-size": "large",
        "marker-symbol": "building"
    })});
    marker.on('drag', function(){ updateForm(); });

    //Controls
    map.scrollWheelZoom.disable();
    var layerControl = new L.Control.Layers(null, null);

    if($(window).width() < 992){
        layerControl.options.collapsed = true;
    }
    else
    {
        layerControl.options.collapsed = false;
    }

    layerControl.addBaseLayer(L.mapbox.tileLayer('mapbox.satellite').addTo(map), 'Imagery');
    layerControl.addBaseLayer(L.mapbox.tileLayer('mapbox.streets'), 'Streets');
    map.addControl(layerControl);
   
</script>