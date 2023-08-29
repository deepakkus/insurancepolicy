<?php
    $this->breadcrumbs=array(
        'Engine Users' => array('adminEngineUsers'),
        'Create Engine User',
    );

    echo '<h1>Create Engine User</h1>';

    echo $this->renderPartial('_form_engine', array('model' => $model));
