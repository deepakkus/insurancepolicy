<?php
    $this->breadcrumbs=array(
        'Import Files' => array('admin'),
        'Create',
    );
?>

<h1>Create a New Import File</h1>

<?php 
    if ($standard){
        echo $this->renderPartial('_form', array('importFile'=>$importFile));
    }
    else{
        echo $this->renderPartial('_form_one_off', array('importFile'=>$importFile));
    }
?>
