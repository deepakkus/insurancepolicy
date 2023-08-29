<?php
/* @var $this EngEnginesController */
/* @var $model EngEngines */

$this->breadcrumbs=array(
	'Engine Analytics' => array('indexAnalytics'),
    'Client Analytics'
);

$clientid = isset($_SESSION[$this::CLIENT_ANALYTICS_ID]) ? $_SESSION[$this::CLIENT_ANALYTICS_ID] : '';

?>

<h2 class="center marginBottom20" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">
    Engine Analytics by Client <?php if (!empty($clientid)) echo '- <i>' . Client::model()->find(array('condition'=>"id = $clientid"))->name . '</i>' ?>
</h2>

<div class ="row-fluid">
    <div class ="span12">

        <div class="form" style="background-color:#FFFFFF; border: 0;">
            <?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	            'id'=>'eng-analytics-form'
            )); ?>

            <div class="col-md-12">
                <?php echo CHtml::label('Start Date', ''); ?>
                <?php $this->widget('bootstrap.widgets.TbDatePicker', array('name' => 'EngAnalyticsClient[client_start_date]', 'value' => date('m/d/Y',strtotime($clientstartdate)), 
                'htmlOptions' => array( 'readonly'=>true,'style' => 'cursor:pointer;') )); ?>
            </div>

            <div class="col-md-12">
                <?php echo CHtml::label('End Date', ''); ?>
                <?php $this->widget('bootstrap.widgets.TbDatePicker', array('name' => 'EngAnalyticsClient[client_end_date]', 'value' => date('m/d/Y',strtotime($clientenddate)),
                'htmlOptions' => array( 'readonly'=>true,'style' => 'cursor:pointer;') )); ?>
            </div>

            <div class="col-md-12">
                <?php echo CHtml::label('Clients', ''); ?>
                <?php echo CHtml::dropDownList('EngAnalyticsClient[client_id]', '', CHtml::listData(Client::model()->findAll(array(
                      'select' => 'id, name',
                      'order'=>'name ASC',
                      'condition'=>'wds_fire = 1'
                )), 'id', 'name'), array(
                    'prompt' => '',
                    'options' => array( $clientid => array('selected'=>true))
                )); ?>
            </div>

            <div class="col-md-12 buttons">
                <?php echo CHtml::submitButton('Submit', array('class'=>'submit')); ?>
            </div>

            <?php $this->endWidget(); ?>
        </div>

    </div>
</div>

<?php $this->widget('bootstrap.widgets.TbExtendedGridView',
    array (
        'id'=>'eng-scheduling-analytics-grid',
        'cssFile' => '../../css/wdsExtendedGridView.css',
        'type' => 'striped hover condensed',
        'dataProvider' => $dataProvider,
        'filter' => $model,
        'columns' => array(
            array(
                'name' => 'state',
                'filter' => CHtml::activeDropDownList($model,'state',CHtml::listData($model->getAvailibleStates(),'state','state'),array('prompt'=>' '))
            ),
            array(
                'name' => 'assignment',
                'filter' => CHtml::activeDropDownList($model,'assignment',CHtml::listData($model->getAvailibleAssignments(),'assignment','assignment'),array('prompt'=>' ')),
                'footer'=>'Totals'
            ),
            array(
                'name' => 'enginecount',
                'class' => 'bootstrap.widgets.TbTotalSumColumn'
            ),
            array(
                'name' => 'daycount',
                'class' => 'bootstrap.widgets.TbTotalSumColumn'
            ),
        ),
        'enableSorting' => true,
        'emptyText' => 'No Statuses have been created.'
    )
); ?>