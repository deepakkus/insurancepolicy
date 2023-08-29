<?php

if ($this->action->getId() === 'create')
{
    $this->breadcrumbs=array(
        'Users' => array('admin'),
        'Create',
    );

    echo '<h1>Create User</h1>';

    echo $this->renderPartial('_form', array('model' => $model));
}
else // oauth users
{
    $this->breadcrumbs=array(
        'Oauth2 Users' => array('adminOauth'),
        'Create',
    );

    echo '<h1>Create Oauth2 User</h1>';

    echo $this->renderPartial('_form_oauth', array('model' => $model));
}