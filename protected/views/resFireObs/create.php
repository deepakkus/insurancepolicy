<?php
/* @var $this ResFireObsController */
/* @var $model ResFireObs */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Fire Details' => array('/resFireObs/admin'),
    'Create'
);

?>

<h1>Create Fire Details</h1>
<h2><?php echo $fire->Name . " - " . $fire->City . ", " . $fire->State; ?></h2>

<?php 

// Launch NOAA weather javascript on document ready
Yii::app()->clientScript->registerScript('weather',"(function() { Weather.getNoaaWeather({$fire->Coord_Lat}, {$fire->Coord_Long}, true); })();", CClientScript::POS_READY);
      
$this->renderPartial('_form', array(
    'model' => $model,
    'fire' => $fire
));

?>