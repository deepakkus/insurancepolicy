<?php
$this->breadcrumbs=array(
	'Import Files' => array('admin'),
	$importFile->id => array('update','id' => $importFile->id),
	'Update',
);
?>

<h1>Update an Import File</h1>


<?php 
if ($standard){
    echo $this->renderPartial('_form', array('importFile'=>$importFile));
}
else{
    echo $this->renderPartial('_form_one_off', array('importFile'=>$importFile));
}
?>