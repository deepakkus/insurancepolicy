<?php
    $this->breadcrumbs = array(
        'Contact Us' => array('admin'),
        $model->id => array('update', 'id' => $model->id),
        'Update',
    );
?>

<h1>Update Contact Us (id: <?php echo $model->id; ?>)</h1>

<?php

    echo $this->renderPartial('_form', array(
        'model' => $model,
        'existingMemberID' => $existingMemberID,
    ));
?>