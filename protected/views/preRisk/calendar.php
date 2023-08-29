<?php

$this->breadcrumbs=array(
	'Pre Risk' => array('admin'),
	'Calendar',
);

Assets::registerFullCalendarPackage();

Yii::app()->bootstrap->init();
Yii::app()->clientScript->registerCss('calendarCSS','
#calendar-wrapper {
    position: relative;
    float: inline-block;
    width: 75%;
    overflow: hidden;
}
#calendar {
    position: relative;
    font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
    font-size: 14px;
    max-width: 900px;
    min-width: 450px;
    margin: 0 auto;
}
.table th {
    text-align: right;
}');

?>

<h1 style="text-align: center;">PreRisk - Calendar</h1>

<?php $engines = $this->calendarEventEngines(); ?>

<?php $this->renderPartial('_calendar_body', array('engines'=>$engines)); ?>
<?php $this->renderPartial('_calendar_script', array('engines'=>$engines)); ?>