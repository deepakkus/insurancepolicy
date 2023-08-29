<?php
/* @var $this ResFireObsController */
/* @var $model ResFireObs */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Fire Details' => array('/resFireObs/admin'),
    'Update'
);

?>

<h1>Update Fire Details for the <?php echo $model->resFireName->Name; ?></h1>
<h2><?php echo $model->resFireName->City; ?>, <?php echo $model->resFireName->State; ?></h2>

<?php 

// Launch NOAA weather javascript on document ready
Yii::app()->clientScript->registerScript('weather',"(function() { Weather.getNoaaWeather($fire->Coord_Lat, $fire->Coord_Long, false); })();", CClientScript::POS_READY);

$this->renderPartial('_form', array(
    'model' => $model,
    'fire' => $model->resFireName
));

?>