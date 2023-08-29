<?php

/* @var $this SystemSettingsController */
/* @var $model SystemSettings */
/* @var $form CActiveForm */

$this->breadcrumbs = array(
    'Anncoucements'
);

Yii::app()->clientScript->registerScript(1, "
    // Add progress bar when submitted
    $('#system-settings-form').submit(function () {
        $('.buttons').empty();
        $('.buttons').html('{$this->widget('bootstrap.widgets.TbProgress', array(
            'percent' => 100,
            'striped' => true,
            'animated' => true,
            'htmlOptions' => array(
                'style' => 'width: 300px;'
            )
        ), true)}');
    });
");

// http://docs.ckeditor.com/#!/guide/dev_unsupported_environments  - Android compatability
// https://github.com/ckeditor/ckeditor-dev/commit/ff0947a         - Adding ios scrollbar
Yii::app()->clientScript->registerScript('ckeditor-mobile-js-compatibility', '

	// Android compatability
    CKEDITOR.env.isCompatible = true;

	// Adding ios scrollbar
    CKEDITOR.on("instanceReady", function() {
        if (CKEDITOR.env.iOS) {
            $(".cke_contents").each(function(index, element) {
                $(element).addClass("ckcontainer");
            });
        }
    });
    
');

Yii::app()->clientScript->registerCss('ckeditor-mobile-css-compatibility', '

	/* Adding ios scrollbar */
    .ckcontainer {
        overflow-y: scroll !important;
        -webkit-overflow-scrolling: touch !important;
    }
    
');

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'system-settings-form',
    'type' => 'horizontal',
    'htmlOptions' => array('class' => 'well')
));

echo $form->errorSummary($model);

echo $form->ckEditorRow($model, 'announcements', array(
    'options' => array(
        // To check availible plugins: CKEDITOR.config.plugins;
        'plugins' => implode(',', array(
            'dialogui','dialog','a11yhelp','basicstyles','bidi','blockquote','button','panelbutton',
            'panel','floatpanel','colorbutton','colordialog','menu','contextmenu','div','elementspath',
            'list','indent','enterkey','entities','popup','floatingspace','listblock','richcombo','font',
            'format','htmlwriter','justify','link','liststyle','maximize','removeformat','menubutton',
            'showborders','sourcearea','stylescombo','tab','toolbar','wysiwygarea'
        ))
    )
));

echo $form->ckEditorRow($model, 'support', array(
    'options' => array(
        // To check availible plugins: CKEDITOR.config.plugins;
        'plugins' => implode(',', array(
            'dialogui','dialog','a11yhelp','basicstyles','bidi','blockquote','button','panelbutton',
            'panel','floatpanel','colorbutton','colordialog','menu','contextmenu','div','elementspath',
            'list','indent','enterkey','entities','popup','floatingspace','listblock','richcombo','font',
            'format','htmlwriter','justify','link','liststyle','maximize','removeformat','menubutton',
            'showborders','sourcearea','stylescombo','tab','toolbar','wysiwygarea'
        ))
    )
));

echo CHtml::tag('div', array('class' => 'marginTop20 marginRight10 clearfix '), CHtml::submitButton('Save', array(
    'class' => 'submit'
)));

echo CHtml::link('Cancel', Yii::app()->request->urlReferrer);

$this->endWidget();

unset($form);