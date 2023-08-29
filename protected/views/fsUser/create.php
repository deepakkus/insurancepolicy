<?php
    $this->breadcrumbs=array(
        'Fire Shield Users' => array('admin'),
        'Create',
    );
?>

<h1>Create New Fire Shield or Agent App User</h1>

<?php 
    echo $this->renderPartial('_form', array(
        'model' => $model,
    )); 
?>
