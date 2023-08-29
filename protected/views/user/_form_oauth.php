<?php

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'user-form-oauth',
    'type' => 'horizontal',
    'htmlOptions' => array(
        'class' => 'well'
    )
));

echo $form->textFieldRow($model, 'username', array(
    'size' => 50,
    'maxlength' => 50,
    'placeholder' => 'clientname_live | clientname_test'
));

echo $form->dropDownListRow($model, 'client_id', CHtml::listData(Client::model()->findAll(array(
    'select' => array('id', 'name'),
    'order' => 'name ASC'
)), 'id', 'name'), array(
    'prompt' => ''
));

foreach ($model->getSelectedTypes() as $type)
    $selected[$type] = array('selected'=>'selected');

echo $form->dropDownListRow($model, 'type', User::getTypes(), array(
    'multiple' => true,
    'options' => $selected,
    'size' => 15,
    'hint' => "The OAuth2 Legacy usertype don't have expiring access tokens"
));

echo $form->textFieldRow($model, 'client_secret', array(
    'size' => 40,
    'maxlength' => 40,
    'placeholder' => 'Create a ' . $model->getAttributeLabel('client_secret'),
    'style' => 'display: inline-block; visible: hidden;',
    'append' => CHtml::ajaxLink('Generate Client Secret', array('user/getGeneratedClientSecret'), array(
        'type' => 'get',
        'data' =>  new CJavaScriptExpression('{}'),
        'success' => 'function(clientSecret) { $("#' . CHtml::activeId($model, 'client_secret') . '").val(clientSecret); }'
    ))
));

echo $form->textFieldRow($model, 'redirect_uri', array(
    'size' => 50,
    'maxlength' => 200,
    'placeholder' => 'Create a ' . $model->getAttributeLabel('redirect_uri'),
    'hint' => 'Optional - However, if not filled out, client must include in auth code api'
));

echo $form->select2Row($model, 'scope', array(
    'asDropDownList' => false,
    'options' => array(
        'tags' => array(
            WDSAPI::SCOPE_DASH,
            WDSAPI::SCOPE_FIRESHIELD,
            WDSAPI::SCOPE_RISK,
            WDSAPI::SCOPE_USAAENROLLMENT,
            WDSAPI::SCOPE_ENGINE,
            WDSAPI::WDS_PRO
        ),
        'tokenSeparators' => array(',')
    ),
    'placeholder' => 'Choose a ' . $model->getAttributeLabel('scope')
));

echo $form->dropDownListRow($model, 'api_mode', User::getApiModeTypes(), array(

));

if (!$model->isNewRecord)
{
    echo $form->checkBoxRow($model, 'active', array(
        'hint' => 'Deactivating OAuth2 User will set all access and refresh tokens to now (expiring them).  Creation of future tokens will be not allowed.<br />' .
            'Reactivating OAuth2 User will set tokens back to NULL if user is Oauth2 Legacy and will allow creation of new tokens for both users going forward.'
    ));
}

echo CHtml::tag('div', array('class' => 'marginTop20'), CHtml::submitButton($model->isNewRecord ? 'Create' : 'Update', array(
    'class' => 'submit'
)));

$this->endWidget();