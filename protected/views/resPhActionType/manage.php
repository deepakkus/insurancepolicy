<?php

/* @var $this ResPhActionTypeController */
/* @var $category ResPhActionCategory */
/* @var $type ResPhActionType */

$this->breadcrumbs = array(
    'Response' => array('/resNotice/landing'),
    'Manage Policyholder Action Types'
);

Yii::app()->clientScript->registerCss('manage-view-css', '
    .category-section {
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
    .category-section a, a:hover {
        text-decoration: none;
    }
    .list-view .sorter {
        text-align: left !important;
    }
    .grid-view table.items th a {
        color: white !important;
    }
    .grid-view table td {
        white-space: normal !important;
    }
    .grid-view table th.button-column {
        text-align: center !important;
    }
');

?>

<h2>Manage Policyholder Action Types</h2>

<a class="btn marginTop20" href="<?php echo $this->createUrl('resPhActionCategory/create') ?>">New Action Category</a>

<div class="row-fluid">
    <?php $this->widget('zii.widgets.CListView', array(
        'dataProvider' => $category->search(),
        'enablePagination' => false,
        'summaryText' => false,
        'itemView' => '_category',
        'htmlOptions' => array(
            'class' => 'list-view span12'
        ),
        'viewData' => array(
            'model' => $type
        ),
        'sortableAttributes'=>array(
            'category'
        )
    )); ?>
</div>