<?php
$this->breadcrumbs=array(
	'Members'=>array('admin'),
	$member->mid=>array('update','mid'=>$member->mid),
	'Update',
);
?>

<h1>View Member / Policyholder</h1>

<?php 

if (in_array('Admin', Yii::app()->user->types) || in_array('Manager', Yii::app()->user->types))
{
    echo CHtml::link('Edit Member', array('member/update', 'mid'=>$member->mid), array('class'=>'btn btn-info'));
}
	
echo $this->renderPartial('_form', array('member'=>$member, 'fireShieldStatusHistory' => $fireShieldStatusHistory, 'readOnly'=>true)); 

if(!$member->isNewRecord)
{    
    $properties = new Property('search');
	$properties->unsetAttributes();  // clear any default values
	$properties->member_mid = $member->mid;
	$this->renderPartial('_properties',array('properties'=>$properties,'member'=>$member, 'readOnly'=>true));
}

?>