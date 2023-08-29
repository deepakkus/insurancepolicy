<?php
    $this->breadcrumbs=array(
        'Engine Users' => array('adminEngineUsers'),
        'Update Engine User',
    );

    echo '<h1>Update Engine User</h1>';

    echo $this->renderPartial('_form_engine', array('model' => $model));
