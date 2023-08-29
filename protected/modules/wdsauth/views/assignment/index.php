<?php

/* @var $this AssignmentController */
/* @var $dataProvider CActiveDataProvider */
/* @var $model AuthItem */

$this->breadcrumbs = array(
    'WDSauth Module' => array('auth/manage'),
    'Assign Auth Items'
);

Assets::registerD3Package();

$authManager = $this->module->authManager;

?>

<p class="lead">Pick an Auth Item to assign</p>

<div class="row-fluid">
    <div class="span6">
        <div class="table-responsive">
            <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
	            'id' => 'assignment-index-grid',
                'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
                'selectableRows' => 0,
                'type' => 'striped bordered condensed',
	            'dataProvider' => $dataProvider,
	            'filter' => $model,
	            'columns' => array(
                    array(
                        'class' => 'bootstrap.widgets.TbButtonColumn',
                        'template' => '{assign}{view-tree}',
                        'header' => 'Actions',
                        'buttons' => array(
                            'assign' => array(
                                'url' => '$this->grid->controller->createUrl("assignment/assign", array("name" => $data->name))',
                                'label' => '<i class="icon-pencil"></i>',
                                'options' => array(
                                    'title' => 'Assign',
                                    'class' => 'auth-item-tooltip'
                                )
                            ),
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
                        'filter' => CHtml::activeDropDownList($model, 'type', array(
                            CAuthItem::TYPE_ROLE => ucfirst($authManager->getAuthItemTypeName(CAuthItem::TYPE_ROLE)),
                            CAuthItem::TYPE_TASK => ucfirst($authManager->getAuthItemTypeName(CAuthItem::TYPE_TASK)),
                            //CAuthItem::TYPE_OPERATION => ucfirst($authManager->getAuthItemTypeName(CAuthItem::TYPE_OPERATION))
                        ), array(
                            'prompt' => ''
                        ))
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

    // Tooktips of grip links

    $('.auth-item-tooltip').uitooltip();

    // Render d3 tree

    $(document).on("click", ".tree-data", function() {
        var name = this.href.substr(this.href.indexOf("#") + 1);
        $("#auth-tree-container").load("{$this->createUrl("assignment/tree")}&name=" + encodeURIComponent(name));
        $("#auth-item-details").load("{$this->createUrl("assignment/details")}&name=" + encodeURIComponent(name));
        return false;
    });

JAVASCRIPT;

$css = <<<CSS

CSS;

Yii::app()->clientScript->registerScript('assignment-index-js', $script);
Yii::app()->clientScript->registerCss('assignment-index-css', $css);







