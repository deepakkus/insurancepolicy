<?php

/* @var $this ApiDocumentationController */
/* @var $model ApiDocumentation */
/* @var $form CActiveForm */

CTextHighlighter::registerCssFile();

Yii::app()->clientScript->registerScript(1, "
    // Add progress bar when submitted
    $('#api-documentation-form').submit(function() {
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

Yii::app()->clientScript->registerCss(1, '
    #' . CHtml::activeId($model, 'docs') . ' {
        font-family: "Courier New", Courier, monospace;
    }
');

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id' => 'api-documentation-form',
    'type' => 'horizontal',
    'htmlOptions' => array('class' => 'well')
));

echo $form->errorSummary($model);

echo $form->textFieldRow($model, 'name', array(
    'placeholder' => $model->getAttributeLabel('name')
));

?>

<div class="control-group">
    <?php echo CHtml::label('API Docs', CHtml::activeId($model, 'docs'), array('class' => 'control-label')); ?>
    <div class="controls">
        <div class="row-fluid" style="border: 1px lightgray solid; padding: 10px; box-sizing: border-box; border-radius: 4px;">
            <div class="span12">
                <?php echo CHtml::activeTextArea($model, 'docs', array('class' => 'span12', 'rows' => '30', 'style' => 'resize: none;')); ?>
            </div>
            <?php echo CHtml::ajaxButton('Preview', array('apiDocumentation/renderMarkdown'), array(
                'type' => 'post',
                'data' =>  new CJavaScriptExpression('{ text: $("#' . CHtml::activeId($model, 'docs') . '").val() }'),
                'success' => 'function(data) {
                    $("#modal-container").find("#modal-content").html(data);
                    $("#modal-container").dialog("open");
                }'
            ), array(
                'class' => 'submit',
                'style' => 'float: right; margin: 8px;',
                'title' => 'Preview Formatted Text'
            )); ?>
        </div>
    </div>
</div>

<?php

echo $form->checkboxRow($model, 'active');

echo '<div class="buttons">';
echo CHtml::submitButton('Save', array('class' => 'submit'));
echo CHtml::link('Cancel', array('admin'), array('class' => 'paddingLeft10'));
echo '</div>';

$this->endWidget('bootstrap.widgets.TbActiveForm');
unset($form);

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

?>