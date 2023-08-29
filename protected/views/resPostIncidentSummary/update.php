<?php

/* @var $this ResPostIncidentSummaryController */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Post Incident Summary' => array('/resPostIncidentSummary/admin'),
    'Update'
);

?>

<h1><?php echo ucwords($this->action->id); ?> a Post Incident Summary for <?php echo Client::model()->findByPk($client)->name; ?></h1>

<h2>Publish Prerequisites</h2>

<ol>
    <li>A final notice has been issued</li>
    <li>The <a href ="<?php echo $this->createUrl("/resFireName/update", array("id"=>$model->fire_id)); ?>" target ="_blank">fire summary</a> is complete (cause, location description, etc)</li>
</ol>

<?php  $this->renderPartial('_form', array('model'=>$model, 'client'=>$client)); ?>