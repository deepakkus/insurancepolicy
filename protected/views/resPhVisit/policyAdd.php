<?php

/* @var $this ResPhVisitController */
/* @var $model ResPhVisit */

$this->breadcrumbs = array(
	'Response' => array('resNotice/landing'),
    'Notifications' => array('resNotice/admin'),
    'Policyholder Visits' => array('resPhVisit/admin', 'fid' => $fireID, 'cid' => $clientID, 'fn' => $fireName, 'cn' => $clientName),
    'Add Policyholder Visits'
);

Yii::app()->clientScript->registerScriptFile('/js/resPolicyholderActions/policyAdd.js');

?>

<h1>Add Policyholder Visits</h1>
<p class="lead"><?php echo $fireName . ', ' . $clientName;  ?></p>

<?php

$existing_pids = ResPhVisit::model()->getDistinctPidsforFireID($fireID, $clientID);

$this->widget('zii.widgets.grid.CGridView', array(
	'id' => 'res-ph-visit-add-grid',
	'dataProvider' => $dataProvider,
    'filter' => $model,
    'enableSorting' => true,
    'htmlOptions' => array('style' => 'font-size: inherit;'),
	'columns' => array(
        array(
            'class' => 'CButtonColumn', 
            'template' => '{create}',
            'header' => 'Add Policyholder Visits',
            'headerHtmlOptions' => array('class' => 'grid-column-width-50'),
            'buttons' => array(
                'create' => array(
                    'url' => function($data) use ($fireID, $clientID, $fireName, $clientName) { 
                        return $this->createUrl('resPhVisit/create', array('fid' => $fireID, 'cid' => $clientID, 'fn' => $fireName, 'cn' => $clientName, 'pid' => $data['pid']));
                    },
                    'label' => 'Add Policyholder Visit',
                    'imageUrl' => '/images/create.png',
                )
            )
        ),
        'pid',
        array(
            'name' => 'threat',
            'value'=> function($data) { return CHtml::encode(($data['threat'] === '1') ?  'Threatened' : 'Triggered'); },
            'filter' => CHtml::activeDropDownList($model, 'threat', array('1'=>'Threatened','0'=>'Triggered'), array('prompt'=>' ')),
        ),
        'fname',
        'lname',
        'address',
        'city',
        'state',
        'zip',
        array(
            'name' => 'coverage',
            'value' => function($data) { return ($data['coverage'] ? CHtml::encode('$ ' . number_format($data['coverage'], 0)) : null); },
            'filter' => CHtml::activeNumberField($model,'coverage',array('min'=>0))
             

        ),
        'response_status',
        array(
            'name' => 'distance',
            'header' => 'distance (miles)',
            'value' => function($data) { return CHtml::encode(number_format($data['distance'], 2)); }
        )
	)
));
?>

<!-- Adding Policyholder Action by PID -->

<div class="form">

    <div class="cell-left" style="width:35%">
        <div class="form-section">
            <?php $form = $this->beginWidget('CActiveForm', array(
	            'id' => 'formAddToPolicyholderActions',
	            'enableAjaxValidation' => false,
                'htmlOptions' => array('name' => 'NewForm')
            )); ?>
            <div>
                <div class="clearfix">
                    <?php echo CHtml::label('Add New Action by PID', 'add-new-action-pid'); ?>
                    <?php echo CHtml::numberField('pid', '', array('id' => 'add-new-action-pid')); ?>
                </div>
                <div class="clearfix marginLeft20">
                    <?php echo CHtml::submitButton('Find Policy', array('class' => 'success', 'id' => 'btnFindProperty')); ?>
                </div>
            </div>
            <?php $this->endWidget(); ?>
        </div>
    </div>

    <div class="cell-right" style="width:65%">
        <div class="form-section">
            <?php

            $propertiesDataProvider = new CActiveDataProvider('Property', array(
                'sort'=>array(
                    'attributes'=>array('*')
                ),
                'criteria'=> array(
                    'with' => array('member'),
                    'condition' => isset($_GET['pid']) ? 'pid = ' . $_GET['pid']  : 'pid is null',
                )
            ));

            // Determine if returned data belongs to current client
            $models = $propertiesDataProvider->getData();
            $isClient = true;
            if (count($models))
            {
                if (current($models)->client_id != $clientID)
                {
                    $propertiesDataProvider->setData(array());
                    $isClient = false;
                }
            }

            $this->widget('bootstrap.widgets.TbExtendedGridView',
                array (
                    'id' => 'gridAddToPolicyholderActions',
                    'cssFile' => Yii::app()->baseUrl .  '/css/wdsExtendedGridView.css',
                    'type' => 'striped bordered condensed',
                    'dataProvider' => $propertiesDataProvider,
                    'columns' => array(
                        array(
                            'class' => 'CButtonColumn',
                            'template' => '{create}',
                            'header' => 'Add Policyholder Actions',
                            'headerHtmlOptions' => array('class' => 'grid-column-width-50'),
                            'buttons' => array(
                                'create' => array(
                                    'url' => function($data) use ($fireID, $clientID, $fireName, $clientName) {
                                        return $this->createUrl('resPhVisit/create', array('fid' => $fireID, 'cid' => $clientID, 'fn' => $fireName, 'cn' => $clientName, 'pid' => $data->pid));
                                    },
                                    'label' => 'Add Policyholder Action',
                                    'imageUrl' => '/images/create.png'
                                )
                            )
                        ),
                        'pid',
                        'address_line_1',
                        'city',
                        'state',
                        'zip',
                        'member.last_name',
                        'member.first_name'
                    ),
                    'enableSorting' => false,
                    'emptyText' => $isClient ? 'Search for PID to populate grid with a property' : 'The entered PID is for a different client than ' . $clientName,
                    'summaryText' => '',
                )
            ); ?>
        </div>
    </div>

</div>
