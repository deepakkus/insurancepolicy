<?php
/* @var $this EngEnginesController */
/* @var $model EngEngines */

$this->breadcrumbs=array(
	'Engine Analytics' => array('indexAnalytics'),
    'Crew Analytics'
);

Yii::app()->clientScript->registerCss('crewPictureCSS', '
    .crew-picture img {
        max-width: 100px;
    }
');

$id = $crewmodel->id ? $crewmodel->id : 0;

?>

<h2 class="center marginBottom20" style="font-family: 'Arial Black', Gadget, sans-serif;color:white; background-color:#222;">Engine Analytics by Crew</h2>

<div class="container-fluid">
    <div class="row-fluid">
        <div class="span4">

            <div class="form" style="background-color:#FFFFFF; border: 0;">
                <h3>Select Start and End Dates</h3>

                <?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	                'id'=>'eng-analytics-form'
                )); ?>

                <div>
                    <?php echo CHtml::label('Start Date', ''); ?>
                    <?php $this->widget('bootstrap.widgets.TbDatePicker', array('name' => 'EngCrewManagement[crew_start_date]', 'value' => date('m/d/Y',strtotime($crewstartdate)) )); ?>
                </div>

                <div>
                    <?php echo CHtml::label('End Date', ''); ?>
                    <?php $this->widget('bootstrap.widgets.TbDatePicker', array('name' => 'EngCrewManagement[crew_end_date]', 'value' => date('m/d/Y',strtotime($crewenddate)) )); ?>
                </div>

                <div>
                    <?php echo CHtml::label('Crew Members', ''); ?>
                    <?php echo $form->dropDownList($crewmodel, 'id', $crewmodel->getCrewMembers(), array('empty' => ' ')); ?>
                </div>

                <div class="buttons">
                    <?php echo CHtml::submitButton('Submit', array('class'=>'submit')); ?>
                </div>

                <?php $this->endWidget(); ?>
            </div>

        </div>
        <div class="span8">

            <?php $this->widget('zii.widgets.CDetailView', array(
	            'data' => $crewmodel,
                //'htmlOptions' => array('style' => 'width:50%;'),
	            'attributes'=>array(
		            'id',
                    'fullname',
                    'address',
                    'work_phone',
                    'cell_phone',
                    'email',
                    'crew_type',
                    'alliance_partner',
                    array(
                        'name' => 'photo_id',
                        'type'=>'html',
                        'value'=> CHtml::image($this->createUrl('/file/loadFile', array('id' => $crewmodel->photo_id)), 'crew member image', array('width'=>'100')),
                        'htmlOptions' => array('class' => 'crew-picture')
                    )
	            ),
            )); ?>

        </div>
    </div>
</div>

<?php $this->widget('bootstrap.widgets.TbExtendedGridView',
    array (
        'id'=>'eng-engines-crew-analytics-grid',
        'cssFile' => '../../css/wdsExtendedGridView.css',
        'type' => 'striped hover condensed',
        //'htmlOptions' => array('style' => 'font-size: inherit !important;'),
	    'dataProvider'=>$dataProvider,
	    'filter'=>$employeeModel,
        'columns' => array(
        'engine_city',
        array(
            'name' => 'engine_state',
            'filter' => CHtml::activeDropDownList($employeeModel,'engine_state',CHtml::listData(EngScheduling::model()->findAll(array(
                'with' => 'employees',
                'select' => 'state',
                'distinct' => true,
                'condition' => "employees.crew_id = $id"
            )),'state','state'),array('prompt'=>' '))
        ),
        array(
            'name' => 'engine_assignment',
            'filter' => CHtml::activeDropDownList($employeeModel,'engine_assignment',CHtml::listData(EngScheduling::model()->findAll(array(
                'with' => 'employees',
                'select' => 'assignment',
                'distinct' => true,
                'condition' => "employees.crew_id = $id"
            )),'assignment','assignment'),array('prompt'=>' '))
        ),
        'fire_name',
        'engine_name',
        array(
            'name' => 'scheduled_type',
            'filter' => CHtml::activeDropDownList($employeeModel,'scheduled_type',CHtml::listData(EngSchedulingEmployee::model()->findAll(array(
                'select' => 'scheduled_type',
                'distinct' => true,
                'condition' => "crew_id = $id"
            )),'scheduled_type','scheduled_type'),array('prompt'=>' ')),
            'footer'=>'Day Total'
        ),
        array(
            'name' => 'daycount',
            'filter' => '',
            'class' => 'bootstrap.widgets.TbTotalSumColumn'
        ),
        array('name' => 'start_date','filter' => ''),
        array('name' => 'end_date','filter' => '')
        ),
        'enableSorting' => true,
        'emptyText' => 'No Results, Search for an employee'
    )
); ?>