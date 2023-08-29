<?php
    $this->breadcrumbs=array(
        'Users' => array('manageClientUsers'),
        $model->id => array('updateclientuser', 'id' => $model->id),
        'Update'
    );

    echo '<h1>Update Client User ' . $model->username . '</h1>';

    echo $this->renderPartial('_client_user_form', array('model' => $model));
