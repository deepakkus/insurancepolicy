<?php
$this->breadcrumbs=array(
	'Agent Properties' => array('admin'),
	$agentProperty->id => array('update','id' => $agentProperty->id),
	'Update',
);
?>

<h1>Update Agent Property</h1>

<?php 

$this->renderPartial('_form', array('agentProperty' => $agentProperty)); 

// TODO: Add agent reports here.
//if(!$property->isNewRecord)
//{        
//	$agentReports = new FSReport('search');
//	$agentReports->unsetAttributes();  // clear any default values
//	$agentReports->property_pid = $property->pid;
//	$this->renderPartial('_agent_reports',array('agentReports' => $agentReports));
//}
?>