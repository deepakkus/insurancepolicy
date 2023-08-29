<?php

/* @var $viewAuthItemForm ViewAuthItemForm */

?>

<div class="row-fluid">
    <div class="span4">
        <p><u>Roles</u> <small>Top tier of hierarchy</small></p>
        <?php

        $form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
            'id' => 'view-role-form',
            'enableAjaxValidation' => true,
            'action' => array('auth/viewAuthItem', 'type' => CAuthItem::TYPE_ROLE),
            'htmlOptions' => array(
                'class' => 'well',
                'style' => 'overflow: hidden;'
            ),
            'clientOptions' => array(
                'validateOnChange' => false,
                'validateOnSubmit' => true,
                'validationUrl' => array('auth/manage'),
                'afterValidate' => new CJavaScriptExpression('function(form, data, hasError) {
                    if (!hasError) {
                        var $loading = $(".auth-item-view-loading");
                        $loading.addClass("active");
                        $.post(form.prop("action"), form.serialize(), function(html) {
                            $loading.removeClass("active");
                            $("#role-container").html(html);
                        }).error(function(jqXHR) {
                            $loading.removeClass("active");
                            console.log(jqXHR.responseText);
                        });
                    }
                }')
            )
        ));

        echo $form->dropDownListRow($viewAuthItemForm, 'role', CHtml::listData($this->module->authManager->getRoles(), 'name', 'name'), array(
            'size' => 20,
            'style' => 'width: 300px; display: block;'
        ));

        echo CHtml::submitButton('View', array('class' => 'submit'));

        echo CHtml::ajaxButton('New Role', array('auth/createAuthItem'), array(
            'type' => 'get',
            'data' => array(
                'type' => CAuthItem::TYPE_ROLE
            ),
            'update' => '#role-container'
        ), array(
            'class' => 'marginLeft10',
            'style' => 'background-color: #5cb85c; color: white;'
        ));

        $this->endWidget();
        unset($form);

        ?>
    </div>
    <div class="span8">
        <p><u>Role Details</u> <span class="auth-item-view-loading"></span></p>
        <div id="role-container"></div>
    </div>
</div>