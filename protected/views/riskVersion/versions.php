<?php

/* @var $this RiskVersionController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs = array(
    'Risk Versions'
);

Yii::app()->clientScript->registerCss('risk-version-view-css', '
    .version-section {
        border: 1px solid black;
        padding: 20px;
        background-color: #E8E8E8;
        font-size: 1.1em;
        border-radius: 4px;
        box-shadow: 3px 3px 5px 1px #CCCCCC;
        box-sizing: border-box;
        margin-top: 20px;
        overflow: hidden;
    }
    a:hover {
        text-decoration: none;
    }
');

?>

<div class="row-fluid">
    <div class="span12">
        <h1>Risk Versions</h1>
        <div class="marginBottom10">
            <a title="New Risk Version" href="<?php echo $this->createUrl('riskVersion/create'); ?>">Create New Risk Version</a>
        </div>
    </div>
</div>

<?php

$this->widget('zii.widgets.CListView', array(
    'dataProvider' => $version->search(),
    'enablePagination' => false,
    'summaryText' => false,
    'itemView' => '_version',
    'htmlOptions' => array(
        'style' => 'padding-top: 0;'
    )
));
