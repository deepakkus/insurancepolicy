<?php

/* @var $this ResPostIncidentSummaryController */

$this->breadcrumbs=array(
	'Response' => array('/resNotice/landing'),
    'Post Incident Summary'
);

Yii::app()->bootstrap->init(); 
Yii::app()->format->dateFormat = 'm/d/Y';
?>

<h1>Post Incident Summary</h1>

<?php
    $minimumDate = ResPostIncidentSummary :: minDate();
    $createMinDate = date('Y-m-d', strtotime($minimumDate['cminDate']));
    $updateMinDate = date('Y-m-d', strtotime($minimumDate['uminDate']));
    $accessGainedMinDate = date('Y-m-d', strtotime($minimumDate['gminDate']));
    $accessDeniedMinDate = date('Y-m-d', strtotime($minimumDate['dminDate']));

    $availibleClientsArray = Client::model()->findAll(array(
        'condition' => 'wds_fire = 1 and id != 999',
        'order' => 'name ASC'
    ));

    $this->widget('bootstrap.widgets.TbButtonGroup', array(
        'size' => 'large',
        'type' => 'success',
        'buttons' => array(
            array(
                'label' => 'Create A Post Incident Summary',
                'items' => array_map(function($data) { 
                    return array(
                        'label' => $data->name,
                        'url' => $this->createUrl('resPostIncidentSummary/create', array('client' => $data->id))
                    );
                }, $availibleClientsArray)
            ),
        ),
    ));
    
    $columnArray = array(
        array(
                'class' => 'CButtonColumn',
                'htmlOptions' => array('style'=>'min-width:15px'),
                'template' => '{update}',
                'header' => 'Actions',
                'headerHtmlOptions' => array('class' => 'grid-column-width-50'),
                'htmlOptions' => array('style' => 'text-align: center'),
                'updateButtonUrl' => '$this->grid->controller->createUrl("/resPostIncidentSummary/update", array("id"=>$data->id,"client"=>$data->client_id))',
        ),
	    array(
		    'name' => 'id',
		    'htmlOptions' => array('width'=>'2'),
	    ),
        array(
            'name'=>'client_name',
            'value' => '(isset($data->client->name)) ? $data->client->name : "";',
            'filter' => CHtml::activeDropDownList($model, 'client_name', CHtml::listData(Client::model()->findAll(array('select'=>array('name','name'))), 'name', 'name'), array('empty' => '')),
        ),
        array(
            'name' => 'fire_name',
            'value' => '(isset($data->fire->Name)) ? $data->fire->Name : "";', 
        ),
        array(
            'name' => 'fire_city',
            'value' => '(isset($data->fire->City)) ? $data->fire->City : "";',
        ),
        array(
            'name' => 'fire_state',
            'value' => '(isset($data->fire->State)) ? $data->fire->State : "";'
        ),
        array(
            'name' => 'date_created',
            'type' => 'date',
            'filter' => CHtml::activeDateField($model,'date_created',array('min'=> $createMinDate, 'onkeydown' => 'return false')),
        ),
        array(
            'name' => 'date_updated',
             'type' => 'date',
            'filter' => CHtml::activeDateField($model,'date_updated',array('min'=> $updateMinDate, 'onkeydown' => 'return false')),
        ),
        array(
            'name' => 'published',
            'type' => 'boolean',
            'filter' => CHtml::activeDropDownList($model,'published', array(0 => '&#x2716;', 1 => '&#x2713;'), array('encode'=>false,'prompt'=>''))
        ),
        //array(
        //    'class' => 'CLinkColumn',
        //    'header' => 'View Summary',
        //    'labelExpression' => function($data) { return ($data->fire->Name) ? $data->fire->Name : 'View Notice'; },
        //    'urlExpression' => function($data) {
        //        return Yii::app()->params['wdsfireBaseUrl'] . '/index.php?r=site/auto-login' . '&u=' . Yii::app()->user->getState('username') .'&t=' . Yii::app()->user->getState('auto_login_token') . '&cid=' . $data->client_id . '&fid=' . $data->fire_id ;
        //    },
        //    'linkHtmlOptions' => array('target'=>'_blank')
        //),
        array(
            'class' => 'CLinkColumn',
            'header' => 'View Summary',
            'labelExpression' => 'isset($data->fire->Name) ? $data->fire->Name : "View Summary"',
            'urlExpression' => 'array("/resNotice/viewNotice","clientid"=>$data->client_id,"fireid"=>$data->fire_id, "status" => $data->published)',
            'linkHtmlOptions' => array('target'=>'_blank')
        ),
        array(
            'name' => 'date_access_gained',
             'type' => 'date',
             'filter' => CHtml::activeDateField($model,'date_access_gained',array('min'=> $accessGainedMinDate, 'onkeydown' => 'return false')),
        ),
        array(
            'name' => 'access_gained_comment'
        ),
        array(
            'name' => 'date_access_denied',
            'type' => 'date',
            'filter' => CHtml::activeDateField($model,'date_access_denied',array('min'=> $accessDeniedMinDate, 'onkeydown' => 'return false')),
        ),
        array(
            'name' => 'access_denied_comment'
        )

    );
    
?>

<div class ="table-responsive">
    
    <?php
        $this->widget('bootstrap.widgets.TbExtendedGridView',
            array (
                'id' => 'pis-grid',
                'cssFile' => '../../css/wdsExtendedGridView.css',
                'type' => 'striped bordered condensed',
                'dataProvider' => $model->search(),
                'filter' => $model,
                'template' => "{summary}{items}{pager}",
                'columns' => $columnArray,
                'ajaxUpdate' => false,

        ));
    ?>

</div>