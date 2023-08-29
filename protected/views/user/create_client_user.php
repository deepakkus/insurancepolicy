<?php
    $this->breadcrumbs=array(
        'Users' => array('manageClientUsers'),
        'Create Client',
    );

    echo '<h1>Create Client User</h1>';

    echo $this->renderPartial('_client_user_form', array('model' => $model));
