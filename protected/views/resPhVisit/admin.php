<?php

/* @var $this ResPhVisitController */
/* @var $model ResPhVisit */
/* @var $fireID integer */
/* @var $clientID integer */
/* @var $fireName string */
/* @var $columnsToShow string[] */

$this->breadcrumbs = array(
    'Response' => array('resNotice/landing'),
    'Notifications' => array('resNotice/admin'),
    'Policyholder Visits'
);

Yii::app()->format->booleanFormat = array('&#x2716;', '&#x2713;');
Yii::app()->format->dateFormat = 'Y-m-d H:i';

Yii::app()->clientScript->registerCss('columnsSelectCss', '

    .comments-popup {
        display: block;
        padding-top: 8px;
        text-decoration: none !important;
    }

    .comments-popup:focus {
        outline:0;
    }

    .container {
        width: 100% !important;
    }
    .table-responsive .table{
         margin-bottom: 0;
    }
    .table-responsive .table thead {
        width: 100%!important;
        float: left;
        display: table-row;
    }
    .table-responsive .table thead th{
        min-width: 150px!important;
    }
    .table-responsive .table thead td{
        min-width: 150px!important;
    }
    .table-responsive .table thead td input{
        width:100%!important;
    }
    .table-responsive .table tbody {
        height: 300px!important;
        width: 100%!important;
        overflow-y: scroll;
        float: left;
        display: table-row;
    }
    .table-responsive .table tbody td {
        min-width: 154px!important;
     }
     #res-ph-visit-grid #res-ph-visit-grid_c3{
     width: 8.4%;

     }
');

Yii::app()->clientScript->registerScript('columnsSelectJs', '

    $("#columns-select").click(function() {
        $("#columns-select-form").slideToggle();
        return false;
    });

    $("#close-columns-select").click(function() {
        $("#columns-select-form").slideUp();
        return false;
    });

    $("#columns-select-form form").submit(function () {
        $("#columns-select-form").slideToggle();
        $.fn.yiiGridView.update("res-ph-visit-grid", {
            data: $(this).serialize()
        });
        return false;
    });

');

?>

<h1>Policyholder Visits</h1>
<p class="lead"><?php echo $fireName . ', ' . $clientName;  ?></p>

<div>
    <a class="btn btn-success" href="<?php echo $this->createUrl('resPhVisit/policyAdd', array('fid' => $fireID, 'cid' => $clientID, 'fn' => $fireName, 'cn' => $clientName)); ?>">Add Policyholder Visits</a>
    <a class="btn btn-success" href="<?php echo $this->createUrl('resPhVisit/downloadvisitlist', array('fn' => $fireName, 'cn' => $clientName, 'fid' => $fireID, 'cid' => $clientID)); ?>">Download List</a>
</div>

<div class="marginTop10">
    <?php echo CHtml::link('Columns', '#', array('id' => 'columns-select')); ?>
    <div class="search-form" id="columns-select-form" style="display: none;"><?php echo $this->renderPartial('_adminColumnsToShow', array('columnsToShow' => $columnsToShow)); ?></div>
    <?php echo ' | ' . CHtml::link('Reset', array('resPhVisit/admin', 'reset' => 1)); ?>
</div>

<div class="table-responsive">
    <?php
        $this->widget('bootstrap.widgets.TbExtendedGridView', array(
        'id' => 'res-ph-visit-grid',
        'type' => 'striped bordered',
        'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
        'dataProvider' => $model->search_actions($sort, $fireID, $clientID),
        'filter' => $model,
        'columns' => array(
            array(
                'class' => 'bootstrap.widgets.TbButtonColumn',
                'template' => '{update}',
                'header' => 'Edit Visits',
                'buttons' => array(
                    'update' => array(
                        'url' => function($data) use ($fireID, $clientID, $fireName, $clientName) {
                            return $this->createUrl('resPhVisit/update', array('id' => $data->id, 'fid' => $fireID, 'cid' => $clientID, 'fn' => $fireName, 'cn' => $clientName));
                        },
                        'label' => 'Edit Visits'
                    )
                )
            ),
            array(
                'name' => 'memberFirstName',
                'visible' => in_array('memberFirstName', $columnsToShow) ? true : false
            ),
            array(
                'name' => 'memberLastName',
                'visible' => in_array('memberLastName', $columnsToShow) ? true : false
            ),
             array(
                'name' => 'propertyAddress',
                'visible' => in_array('propertyAddress', $columnsToShow) ? true : false
            ),
            array(
                'name' => 'propertyPolicy',
                'visible' => in_array('propertyPolicy', $columnsToShow) ? true : false
            ),
            array(
                'name' => 'response_status',
                'filter' => CHtml::activeDropDownList($model,'response_status', $model->ResponseStatus, array('encode'=>false,'prompt'=>'')),
                'visible' => in_array('response_status', $columnsToShow) ? true : false,
            ),
            array(
                'name' => 'status',
                'filter' => CHtml::activeDropDownList($model,'status', $model->statusTypes, array('encode'=>false,'prompt'=>'')),
                'visible' => in_array('status', $columnsToShow) ? true : false
            ),
            array(
                'name' => 'date_action',
                'filter' => CHtml::activeDateField($model,'date_action',array('onkeydown' => 'return false')),
                'type' => 'date',
                'visible' => in_array('date_action', $columnsToShow) ? true : false
            ),
             array(
                'name' => 'date_created',
                'filter' => CHtml::activeDateField($model,'date_created',array('onkeydown' => 'return false')),
                'type' => 'date',
                'visible' => in_array('date_created', $columnsToShow) ? true : false
            ),
            array(
                'name' => 'date_updated',
                'filter' => CHtml::activeDateField($model,'date_updated',array('onkeydown' => 'return false')),
                'type' => 'date',
                'visible' => in_array('date_updated', $columnsToShow) ? true : false
            ),
            array(
                'name' => 'userName',
                'visible' => in_array('userName', $columnsToShow) ? true : false
            ),
            array(
                'name' => 'lastUpdateUserName',
                'value' => '$data->getLastUpdateUserName()',
                'filter' => false,
                'visible' => in_array('lastUpdateUserName', $columnsToShow) ? true : false,
            ),
            array(
                'name' => 'approvalUserName',
                'visible' => in_array('approvalUserName', $columnsToShow) ? true : false
            ),
            array(
                'name' => 'review_status',
                'value' => 'ucwords($data->review_status)',
                'filter' => CHtml::activeDropDownList($model,'review_status', $model->reviewstatusType, array('prompt'=>'')),
                'visible' => in_array('review_status', $columnsToShow) ? true : false
            ),
            array(
                'name' => 'comments',
                'value' => '$data->truncateComments("comments")',
                'htmlOptions' => array('style' => 'white-space: normal !important;'),
                'type' => 'raw',
                'visible' => in_array('comments', $columnsToShow) ? true : false
            ),
            array(
                'name' => 'publish_comments' ,
                'value' => '$data->truncateComments("publish_comments")',
                'htmlOptions' => array('style' => 'white-space: normal !important;'),
                'type' => 'raw',
                'visible' => in_array('publish_comments', $columnsToShow) ? true : false
            ),
        )
    )); ?>
</div>

<?php

Yii::app()->clientScript->registerScript('commentPopupJs', '
    $(document).on("click", ".comments-popup", function() {
        $("#modal-container").find("#modal-content").html(this.dataset.comment);
        $("#modal-container").dialog("option", "title", this.dataset.commentType);
        $("#modal-container").dialog("open");
        return false;
    });
');

$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id' => 'modal-container',
    'options' => array(
        'title' => 'Comment',
        'autoOpen' => false,
        'closeText' => false,
        'modal' => true,
        'buttons' => array(
            array(
                'text' => 'Close',
                'click' => new CJavaScriptExpression('function() { $(this).dialog("close"); }'),
            )
        ),
        'show' => array(
            'effect' => 'fadeIn',
            'duration' => 300,
            'direction' => 'up'
        ),
        'hide' => array(
            'effect' => 'fadeOut',
            'duration' => 300
        ),
        'width' => 400,
        'draggable' => true
    )
));

echo CHtml::tag('div', array('id' => 'modal-content'), true);

$this->endWidget('zii.widgets.jui.CJuiDialog');