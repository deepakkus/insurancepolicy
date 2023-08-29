<?php
    $this->breadcrumbs=array(
        'Agent Properties'=>array('admin'),
        'Create',
    );
?>

<h1>Create a New Agent Property for <?php echo $agentProperty->agent_first_name . " " . $agentProperty->agent_last_name; ?></h1>

<?php echo $this->renderPartial('_form', array('agentProperty'=>$agentProperty));

