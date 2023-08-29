<?php
$this->breadcrumbs=array(
	'Agents' => array('admin'),
	$agent->id => array('update','id' => $agent->id),
	'Update',
);
?>

<h1>Update an Agent</h1>

<?php 

echo $this->renderPartial('_form', array('agent' => $agent)); 

if(!$agent->isNewRecord)
{    
    $agentProperties = new AgentProperty('search');
	$agentProperties->unsetAttributes();  // clear any default values
	$agentProperties->agent_id = $agent->id;
	$this->renderPartial('_agentProperties',array('agentProperties'=>$agentProperties,'agent'=>$agent));
}

?>