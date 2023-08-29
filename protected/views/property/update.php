<?php

$this->breadcrumbs=array(
	'Properties'=>array('admin'),
	$property->pid=>array('update','pid'=>$property->pid),
	'Update',
);

?>

<h1>Update Property</h1>

<?php

if (in_array('Admin', Yii::app()->user->types))
{
    echo CHtml::link('Create New WDSrisk', array('property/refreshRisk', 'pid' => $property->pid), array('class' => 'marginRight20'));
}

echo CHtml::link('Edit Property Access', array('property/propertyAccess', 'pid'=>$property->pid), array('class' => 'marginRight20'));

$this->renderPartial('_form', array(
    'property' => $property, 
    'member' => $member,
    'responseStatusHistory' => $responseStatusHistory,
    'fireShieldStatusHistory' => $fireShieldStatusHistory,
    'preRiskStatusHistory' => $preRiskStatusHistory,
    'policyStatusHistory' => $policyStatusHistory, 
    'additionalContacts' => $additionalContacts,
    'wdsFireEnrollments' => $wdsFireEnrollments,
    'locationHistory' => $locationHistory,
    'propertyFiles' => $propertyFiles
 )); 

?>