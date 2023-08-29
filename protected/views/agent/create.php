<?php
    $this->breadcrumbs=array(
        'Agents' => array('admin'),
        'Create',
    );
?>

<h1>Create a New Agent</h1>

<?php echo $this->renderPartial('_form', array('agent'=>$agent)); ?>
