<?php

/* @var $this ApiDocumentationController */
/* @var $model ApiDocumentation */

CTextHighlighter::registerCssFile();

$this->breadcrumbs=array(
	'Api Documentations' => array('admin'),
	'Manage',
);

echo '<h1>Manage Api Documentations</h1>';

echo CHtml::link('Create', array('apiDocumentation/create'), array('class'=>'btn btn-success'));

$this->widget('bootstrap.widgets.TbExtendedGridView', array(
	'id' => 'api-documentation-grid',
    'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
	'dataProvider' => $model->search(),
	'filter' => $model,
	'columns' =>array(
        array(
            'class' => 'bootstrap.widgets.TbButtonColumn',
            'template' => '{update}{delete}',
            'header' => 'Actions'
        ),
		'name',
		'active:boolean',
        array(
            'class' => 'CLinkColumn',
            'header' => 'Docs',
            'label' => 'Preview',
            'urlExpression' => '"#" . $data->id',
            'linkHtmlOptions' => array('class' => 'preview-md')
        )
	)
));

Yii::app()->clientScript->registerScript(1, '

    // Preview Markdown
    $(document).on("click", ".preview-md", function() {
        var id = this.href.substring(this.href.indexOf("#") + 1);
        $.post("' . $this->createUrl('apiDocumentation/renderMarkdown') . '", { id: id }, function(data) {
            $("#modal-container").find("#modal-content").html(data);
            $("#modal-container").dialog("open");
        });
        return false;
    });

');

$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id' => 'modal-container',
    'options' => array(
        'title' => 'Markdown Preview',
        'autoOpen' => false,
        'modal' => true,
        'buttons' => array(
            'OK' => new CJavaScriptExpression('function() { $(this).dialog("close"); }')
        ),
        'show' => array(
            'effect' => 'drop',
            'duration' => 300,
            'direction' => 'up'
        ),
        'hide' => array(
            'effect' => 'fadeOut',
            'duration' => 300
        ),
        'width' => 800,
        'height' => 600,
        'resizable' => true,
        'draggable' => true
    )
));

echo '<div id="modal-content"></div>';

$this->endWidget('zii.widgets.jui.CJuiDialog');