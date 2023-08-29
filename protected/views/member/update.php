<?php
$this->breadcrumbs=array(
	'Members'=>array('admin'),
	$member->mid=>array('update','mid'=>$member->mid),
	'Update',
);
?>

<h1>Update a Client Member</h1>

<?php 

echo $this->renderPartial('_form', array('member'=>$member, 'fireShieldStatusHistory' => $fireShieldStatusHistory,)); 

if(!$member->isNewRecord)
{    
    $properties = new Property('search');
	$properties->unsetAttributes();  // clear any default values
	$properties->member_mid = $member->mid;
	$this->renderPartial('_properties',array('properties'=>$properties,'member'=>$member));
}

?>