<?php

/* @var $attachAuthItemForm AttachItemForm */
/* @var $this AssignmentController */
/* @var $dataProvider CActiveDataProvider */
/* @var $model AuthItem */
/* @var $name string */

$this->breadcrumbs = array(
    'WDSauth Module' => array('auth/manage'),
    'Assign Auth Items' => array('assignment/index'),
    ucfirst($name)
);

Assets::registerD3Package();

$authManager = $this->module->authManager;

?>

<p class="lead">Assign auth items to item: <em><?php echo $name; ?></em></p>

<div class="row-fluid">
    <div class="span6">
        <div class="table-responsive">
            <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
	            'id' => 'assignment-assign-grid',
                'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
                'type' => 'striped bordered condensed',
                'selectableRows' => 0,
	            'dataProvider' => $dataProvider,
	            'filter' => $model,
	            'columns' => array(
                    array(
                        'class' => 'bootstrap.widgets.TbButtonColumn',
                        'template' => '{view-tree}',
                        'header' => 'Actions',
                        'buttons' => array(
                            'view-tree' => array(
                                'url' => '"#" . $data->name',
                                'label' => '<i class="icon-eye-open"></i>',
                                'options' => array(
                                    'title' => 'View Details',
                                    'class' => 'tree-data auth-item-tooltip'
                                )
                            )
                        )
                    ),
                    'name',
                    array(
                        'name' => 'type',
                        'value' => '$this->grid->controller->module->authManager->getAuthItemTypeName($data->type)',
                        'filter' => false
                    )
                ),
                'bulkActions' => array(
                    'align' => 'left',
                    'actionButtons' => array (
                        array (
                            'id' => 'assignAuthItems',
                            'buttonType' => 'button',
                            'type' => TbHtml::BUTTON_COLOR_PRIMARY,
                            'size' => TbHtml::INPUT_SIZE_SMALL,
                            'label' => 'Assign',
                            'click' => new CJavaScriptExpression('function(values) {
                                var names = [];
                                for (var i = 0; i < values.length; i++) {
                                    names.push(values[i].value);
                                }
                                var form = document.getElementById("assign-auth-items-form");
                                form.elements[1].value = JSON.stringify(names);
                                form.submit();
                            }'),
                        )
                    ),
                    'checkBoxColumnConfig' => array(
                        'class' => 'CCheckBoxColumn',
                        'name' => 'name',
                        'htmlOptions' => array('style' => 'text-align: center;'),
                        'checkBoxHtmlOptions' => array('style' => 'width: initial;'),
                        'checked' => 'in_array($data->name, array("' . implode('","', json_decode($attachAuthItemForm->children)) . '"))',
                    )
                )
            )); ?>
        </div>
    </div>
    <div class="span6">
        <h5>Auth Item Details</h5>
        <div id="auth-item-details"></div>
    </div>
</div>

<div id="auth-tree-container"></div>

<?php

$script = <<<JAVASCRIPT

    // Tooltips of grid links

    $('.auth-item-tooltip').uitooltip();

    // Render d3 tree + item details

    $(document).on("click", ".tree-data", function() {
        var name = this.href.substr(this.href.indexOf("#") + 1);
        $("#auth-tree-container").load("{$this->createUrl("assignment/tree")}&name=" + encodeURIComponent(name));
        $("#auth-item-details").load("{$this->createUrl("assignment/details")}&name=" + encodeURIComponent(name));
        return false;
    });

JAVASCRIPT;

$css = <<<CSS

CSS;

Yii::app()->clientScript->registerScript('assignment-assign-js', $script);
Yii::app()->clientScript->registerCss('assignment-assign-css', $css);

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
    'id' => 'assign-auth-items-form',
    'action' => array('assignment/assignAuthItems'),
));

echo $form->hiddenField($attachAuthItemForm, 'itemname');
echo $form->hiddenField($attachAuthItemForm, 'children');

$this->endWidget();
