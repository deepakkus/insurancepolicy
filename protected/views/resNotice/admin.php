<?php

/* @var $this ResNoticeController */
/* @var $model ResNotice */

$this->breadcrumbs=array(
    'Response' => array('/resNotice/landing'),
    'Notifications'
);

Yii::app()->format->booleanFormat = array('&#x2716;', '&#x2713;');
Yii::app()->format->dateFormat = 'Y-m-d H:i';

?>

<h1>Manage Notifications</h1>

<h2 style="margin-top:25px;">Make Notice</h2>

<?php $this->widget('bootstrap.widgets.TbButtonGroup',array(
    'size' => 'large',
    'type' => 'success',
    'buttons' => array(
        array(
            'label' => 'Create A Notice',
            'items' => array_map(function($data) { 
                return array(
                    'label' => $data->name,
                    'url' => $this->createUrl('/resNotice/create', array('client_id' => $data->id))
                );
            }, Client::model()->findAll(array(
                'select' => array('id','name'),
                'order'=>'name ASC',
                'condition'=>'(wds_fire = 1 AND active = 1) OR id = 999')
            ))
        )
    )
)); ?>

<?php $this->widget('bootstrap.widgets.TbButtonGroup',array(
    'size' => 'large',
    'type' => 'success',
    'buttons' => array(
        array(
            'label' => 'Dispatched Fires',
            'url' => $this->createUrl('/resNotice/fires')
        )
    )
)); ?>

<?php echo $this->renderPartial('_adminDownload'); ?>

<div class ="table-responsive">

    <?php
    $this->widget('bootstrap.widgets.TbExtendedGridView', array(
    'id' => 'res-notice-grid',
    'type' => 'striped bordered condensed',
    'cssFile' => Yii::app()->getBaseUrl() . 'css/wdsExtendedGridView.css',
    'dataProvider' => $model->search('2015-01-01'),
    'filter' => $model,
    'columns' => array(
        array(
            'class' => 'bootstrap.widgets.TbButtonColumn',
            'template' => '{create}{update}{phone}{delete}{visits}',
            'header' => 'Actions',
            'htmlOptions' => array(
                'style' => 'width: 100px; min-width: 125px; padding: 5px;'
            ),
            'buttons' => array(
                'update' => array(
                    'url' => '$this->grid->controller->createUrl("/resNotice/update", array("id" => $data->notice_id, "client_id" => $data->client_id))',
                    'label' => 'Update Notice',
                    'options' => array('style' => 'padding: 5px')
                ),
                'create' => array(
                    'url' => '$this->grid->controller->createUrl("/resFireObs/create", array("fireid" => $data->fire_id))',
                    'label' => 'Create Details',
                    'imageUrl' => '/images/create.png',
                    'options' => array('style' => 'padding: 5px')
                ),
                'phone' => array(
                    'url' => '$this->grid->controller->createUrl("/resNotice/viewCallList", array("fireid" => $data->fire_id, "clientid" => $data->client_id))',
                    'label' => 'Call List',
                    'imageUrl' => 'images/phone.png',
                    'visible' => '$data->client_id !== "999" && $data->client->call_list === "1"',
                    'options' => array('style' => 'padding: 5px')
                ),
				'delete'=>array(
					'visible' => 'in_array("Admin", Yii::app()->user->types) || in_array("Manager", Yii::app()->user->types)',
                    'options' => array('style' => 'padding: 5px')
				),
                'visits'=>array(
                    'label' => '',
                    'url'=>function($data)
                    {
                        $criteria = new CDbCriteria;
                        $criteria->select = 't.*';            
                        $criteria->addCondition("fire_id = :fid");
                        $criteria->addCondition("client_id = :cid");
                        $criteria->params = array(':fid'=>$data->fire_id,':cid'=>$data->client_id);
                        $criteria->order = 't.date_created DESC';
                        $criteria->limit = 1; 
                        $notice = ResNotice::model()->find($criteria);
                        if($notice->notice_id == $data->notice_id)
                        {
                            // Need to find a set based way to accomplish this in the model's search method.  This will produce a LOT of queries!!!
                            if (ResPhVisit::model()->count('fire_id=:fid AND client_id=:cid AND review_status=:pb',array('fid'=>$data->fire_id,'cid'=>$data->client_id,'pb'=>'not reviewed')))
                            {
                                $image = CHtml::image( 'images/unpublish_read_details.png', 'Policyholder Visits', array('Policyholder Visits'=>'Policyholder Visits')); // If ANY res_ph_visits for that fire / client have status = 'not reviewed'
                            }
                            elseif(ResPhVisit::model()->count('fire_id=:fid AND client_id=:cid AND review_status=:pb',array('fid'=>$data->fire_id,'cid'=>$data->client_id,'pb'=>'not reviewed')) == 0 && ResPhVisit::model()->count('fire_id=:fid AND client_id=:cid AND review_status=:pb',array('fid'=>$data->fire_id,'cid'=>$data->client_id,'pb'=>'reviewed')) > 0)
                            {
                                $image = CHtml::image( 'images/yellow_read_details.png', 'Policyholder Visits', array('title'=>'Policyholder Visits')); // If NO res_ph_visits for that fire / client have status = 'not reviewed' AND ANY status = 'reviewed'
                            }
                            elseif(ResPhVisit::model()->count('fire_id=:fid AND client_id=:cid AND review_status=:rereview', array('fid'=>$data->fire_id, 'cid'=>$data->client_id, 'rereview'=>'re-review')))
                            {
                                $image = CHtml::image('images/purple_read_details.png', 'Policyholder Visits', array('title'=>'Policyholder Visits')); // If there are any 're-review's then purple icon
                            }
                            elseif((ResPhVisit::model()->count('fire_id=:fid AND client_id=:cid',array('fid'=>$data->fire_id,'cid'=>$data->client_id))>0)&&ResPhVisit::model()->count('fire_id=:fid AND client_id=:cid',array('fid'=>$data->fire_id,'cid'=>$data->client_id)) == ResPhVisit::model()->count('fire_id=:fid AND client_id=:cid AND review_status=:pb',array('fid'=>$data->fire_id,'cid'=>$data->client_id,'pb'=>'removed')) )
                            {
                                $image = CHtml::image( 'images/read_details.png', 'Policyholder Visits', array('title'=>'Policyholder Visits')); // If there are res_ph_visits for that fire && all ph_visits have status = 'removed'
                            }
                            else
                            {
                                $image = CHtml::image( 'images/read_details.png', 'Policyholder Visits', array('title'=>'Policyholder Visits'));
                            }
                        }
                        else
                        {
                            $image = CHtml::image( 'images/read_details.png', 'Policyholder Visits', array('title'=>'Policyholder Visits'));
                        }
                    echo CHtml::link($image, array('resPhVisit/admin','fid'=>$data->fire_id,'cid'=>$data->client_id));
                 }
                    
                )
            )
        ),
        array(
        'name' => 'fire_name',
        'type' => 'raw',
        'value' => '$this->grid->controller->getFireName($data)',
        ),
        array(
            'name' => 'client_name',
            'filter' => CHtml::activeDropDownList($model, 'client_name', CHtml::listData(ResNotice::model()->findAll(array(
                'select' => 'client_id',
                'distinct' => true
            )),'client_name','client_name'),array('prompt'=>''))
        ),
        array(
            'name' => 'res_status',
            'filter' => CHtml::activeDropDownList($model,'res_status', CHtml::listData(ResNotice::model()->findAll(array(
                'select' => 'wds_status',
                'distinct' => true
            )), 'res_status', 'res_status'),array('prompt'=>''))
        ),
        array(
            'name' => 'recommended_action',
            'filter' => CHtml::activeDropDownList($model,'recommended_action',CHtml::listData($model->findAll(array(
                'select' => 'recommended_action',
                'distinct' => true
            )),'recommended_action','recommended_action'))
        ),
        array(
            'name' => 'publish',
            'type' => 'boolean',
            'filter' => CHtml::activeDropDownList($model,'publish', array(0 => '&#x2716;', 1 => '&#x2713;'), array('encode'=>false,'prompt'=>''))
        ),
        array(
            'name' => 'date_published',
            'type' => 'date',
            'filter' => CHtml::activeDateField($model,'date_published')
        ),
        array(
            'name' => 'date_updated',
            'type' => 'date',
            'filter' => CHtml::activeDateField($model,'date_updated')
        ),
        array(
            'class' => 'CButtonColumn',
            'template' => '{kml}{unmatched}',
            'header' => 'Downloads',
            'buttons' => array(
                'kml' => array(
                    'url' => '$this->grid->controller->createUrl("/resNotice/downloadKMZ", array("id"=>$data->notice_id))',
                    'label' => 'Download KMZ',
                    'imageUrl' => 'images/kmz-small.png',
                ),
                'unmatched' => array(
                    'url' => '$this->grid->controller->createUrl("/unmatched/downloadUnmatchedList", array("zipcodes"=>$data->zip_codes,"client_id"=>$data->client_id))',
                    'label' => 'Download Unmatched List',
                    'imageUrl' => 'images/excel-small.png'
                )
            )
        ),
        array(
            'class' => 'CLinkColumn',
            'header' => 'View History',
            'label' => 'View History',
            'urlExpression' => 'array("/resNotice/viewFireHistory","clientID"=>$data->client_id,"fireID"=>$data->fire_id)',
            'linkHtmlOptions' => array('target'=>'_blank')
        ),
        
    )
));


    ?>

</div>