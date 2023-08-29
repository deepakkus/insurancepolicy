<?php
/* @var $this PreRiskController */
/* @var $model PreRisk */

$this->breadcrumbs=array(
	'Manage Pre Risk'=>array('admin'),
	'Update',
);
?>

<h1><?php 
if($type == "preRiskfollowUp")
{
    echo "PreRisk Follow Up";
}
elseif($type == "resource")
{
    echo "HA Scheduling";
}
elseif($type == "review")
{
    echo "Production";
}
?></h1>

<?php 
//Use the follow up form template
if($type == "preRiskfollowUp")
{
    $this->renderPartial('_form_preRisk_follow_up', array('model'=>$model)); 
}

//Use the scheduling form template
elseif($type == "resource")
{
    $this->renderPartial('_form_resource', array('model'=>$model)); 
}

//Use the production form template
elseif($type == "review")
{
    $this->renderPartial('_form_ha_review', array('model'=>$model)); 
}

?>
