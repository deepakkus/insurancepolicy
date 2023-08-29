<?php
/* @var $this ResTriageZoneController */
/* @var $model ResTriageZone */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Evac Zones'
);

Yii::app()->format->dateFormat = 'Y-m-d H:i';

Yii::app()->getClientScript()->registerCoreScript('jquery.ui');
Yii::app()->clientScript->registerCssFile(Yii::app()->clientScript->getCoreScriptUrl().'/jui/css/base/jquery-ui.css');

?>

<h1>Notification Evac Zones</h1>

<a class="btn btn-success marginTop10" href="<?php echo $this->createUrl('/resEvacZone/create'); ?>">Create Evac Zone(s)</a>

<div class="table-responsive">

    <?php

    $this->widget('bootstrap.widgets.TbExtendedGridView', array(
    'id' => 'res-notice-grid',
    'type' => 'striped bordered condensed',
    'cssFile' => Yii::app()->getBaseUrl() . 'css/wdsExtendedGridView.css',
    'dataProvider' => $notice->search(null, null, true), //ResNotice search($startDate, $endDate, $evacZones)
    'filter' => $notice,
    'columns' => array(
        array(
				'class' => 'bootstrap.widgets.TbButtonColumn',
				'template' => '{update}{delete}',
                'buttons' => array (
                    'update' => array(
                        'url' => 'Yii::app()->createUrl("resEvacZone/update", array("notice_id"=>$data->notice_id))',
                        'options' => array('style' => 'padding: 5px'),
                    ),
                    'delete' => array(
                        'url' => 'Yii::app()->createUrl("resEvacZone/deleteNoticeEvacZones", array("notice_id"=>$data->notice_id))',
                        'options' => array('style' => 'padding: 5px'),
                    ),
                ),
				'header' => 'Actions'
			),
        array(
            'name' => 'client_name',
            'filter' => CHtml::activeDropDownList($notice, 'client_name', CHtml::listData(ResNotice::model()->findAll(array(
                'select' => 'client_id',
                'distinct' => true
            )),'client_name','client_name'),array('prompt'=>''))
        ),
        'fire_name',
        array(
            'name' => 'res_status',
            'filter' => CHtml::activeDropDownList($notice,'res_status', CHtml::listData(ResNotice::model()->findAll(array(
                'select' => 'wds_status',
                'distinct' => true
            )), 'res_status', 'res_status'),array('prompt'=>''))
        ),
        array(
            'name' => 'recommended_action',
            'filter' => CHtml::activeDropDownList($notice,'recommended_action',CHtml::listData($notice->findAll(array(
                'select' => 'recommended_action',
                'distinct' => true
            )),'recommended_action','recommended_action'))
        ),
        array(
				'header' => 'Number of Zones',
				'value' => 'count($data->resEvacZones)',
			),
        array(
            'name' => 'date_updated',
            'type' => 'date',
            'filter' => ''
        ),
        array(
            'class' => 'CLinkColumn',
            'header' => 'Notice',
            'label' => 'View Notice',
            'urlExpression' => 'array("/resNotice/update", "id" => $data->notice_id, "client_id" => $data->client_id)'
        ),
        array(
            'class' => 'CLinkColumn',
            'header' => 'Copy',
            'label' => 'Copy',
            'urlExpression' => 'array("/resEvacZone/copy", "notice_id" => $data->notice_id)'
        )

   )));
    ?>

</div>