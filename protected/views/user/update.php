<?php

if ($this->action->getId() === 'update')
{
    $this->breadcrumbs=array(
        'Users' => array('admin'),
        $model->id => array('update', 'id' => $model->id),
        'Update'
    );

    echo '<h1>Update User ' . $model->username . '</h1>';

    echo $this->renderPartial('_form', array('model' => $model));
}
else // oauth users
{
    $this->breadcrumbs=array(
        'Oauth2 Users' => array('adminOauth'),
        $model->id => array('updateOauth', 'id' => $model->id),
        'Update'
    );

    echo '<h1>Update Oauth2 User ' . $model->username . '</h1>';

    echo $this->renderPartial('_form_oauth', array('model' => $model));
}