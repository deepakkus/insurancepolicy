<?php

$this->breadcrumbs=array(
	'Properties'=>array('admin'),
	$property->pid=>array('view','pid'=>$property->pid),
	'View',
);

?>

<h1>View Property / Policy</h1>

<?php

if (in_array('Admin', Yii::app()->user->types) || in_array('Manager', Yii::app()->user->types))
{
    //echo CHtml::link('Edit Property', array('property/update', 'pid'=>$property->pid), array('class' => 'marginRight20'));
    echo CHtml::link('Create New WDSrisk', array('property/refreshRisk', 'pid' => $property->pid), array('class' => 'marginRight20'));
}

//echo CHtml::link('Edit Property Access', array('property/propertyAccess', 'pid'=>$property->pid), array('class' => 'marginRight20'));

$this->renderPartial('_form', array(
    'property' => $property,
    'member' => $member,
    'responseStatusHistory' => $responseStatusHistory,
    'fireShieldStatusHistory' => $fireShieldStatusHistory,
    'preRiskStatusHistory' => $preRiskStatusHistory,
    'policyStatusHistory' => $policyStatusHistory,
    'additionalContacts' => $additionalContacts,
    'locationHistory' => $locationHistory,
    'propertyFiles' => $propertyFiles,
    'propertyAccess' => $propertyAccess,
	'readOnly' => true,
    'wdsFireEnrollments' => $wdsFireEnrollments,
 ));

?>