<?php
/* @var $this EngSchedulingController */
/* @var $model EngScheduling */
/* @var $employeemodel EngSchedulingEmployee */
/* @var $form CActiveForm */

?>

<div class="form"  style="margin-bottom: 300px;">
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span6">
                <div class="row-fluid">
                    
                    <!-- Employee Grid -->

                    <div class="marginTop20">
                        <i class="icon-user"></i>
                        <span class="heading">Scheduled Employees</span> 
                        <a href="javascript:void(0);" id="lnkAddNewEmployee" class="paddingLeft10">Add New Employee</a>
                    </div>

                    <div class="table-responsive">

                        <?php
                            
                        $dataProvider = EngSchedulingEmployee::model()->search($schedulingmodel->id);
                            
                        $this->widget('bootstrap.widgets.TbExtendedGridView', array(
                            'id' => 'gridEmployees',
                            'cssFile' => '../../css/wdsExtendedGridView.css',
                            'type' => 'striped bordered condensed',
                            'dataProvider' => $dataProvider,
                            'columns' => array(
                                array( // This ID field is used for selecting the active row
                                    'name' => 'id',
                                ),
                                array(
                                    'header' => 'Crew Name',
                                    'value' => '$data->crew_last_name . ", " .$data->crew_first_name'
                                ),
                                'engine_boss:boolean',
                                'on_engine:boolean',
                                'start_date',
                                'end_date',
    		                    array(
    			                    'class' => 'bootstrap.widgets.TbButtonColumn',
                                    'template' => '{delete}',
                                    'header' => 'Delete',
                                    'buttons' => array(
                                        'delete' => array(
                                            'visible' => 'in_array("Engine Manager", Yii::app()->user->types)',
                                            'url' => '$this->grid->controller->createUrl("/engSchedulingEmployee/delete", array("id"=>$data->id))',
                                            'options' => array(
                                                'method' => 'post'
                                            ),
                                            // Overriding ajax reloading with a manual redirect ... fixes problems with url when a record in edit mode is deleted
                                            'click' => new CJavaScriptExpression('function() {
                                                if (!confirm("Are you sure you want to delete this item?")) return false;
                                                // Delete record with AJAX request
                                                jQuery("#gridEmployees").yiiGridView("update", {
                                                    type: "POST",
                                                    url: jQuery(this).attr("href"),
                                                    success: function(data) {
                                                        // Delete successfull, redirect to update with no selection
                                                        window.location.href = "' . $this->createUrl('engScheduling/update', array('id' => $schedulingmodel->id, 'employeeID' => '')) . '";
                                                    },
                                                    error: function(XHR) {
                                                        console.log(XHR);
                                                    }
                                                });
                                                return false;
                                            }')
                                        )
                                    )
    		                    )
                            ),
                            'enableSorting' => false,
                            'emptyText' => 'No Employees have been scheduled.',
                            'selectableRows' => 1,
                            'selectionChanged' => 'function(id) { EngineEmployee.onGridRowSelected(id); }'
                        ));
                            
                        ?>

                    </div>
                </div>

                <?php
                    
                // If no scheduled employees, provide option to copy from last time this engine was scheduled
                    
                if (!count($dataProvider->getData()))
                {
                    $this->renderPartial('_form_employee_copy',array(
                        'schedulingmodel'=>$schedulingmodel,
                    ));
                }
                    
                ?>

            </div>
            <div class="span6">

                <!-- Status Form -->

                <div class="form" style="border: none;">
                    <?php if ($employeemodel->isNewRecord): ?>
                        <h4 class="marginBottom10">New Employee</h4>
                    <?php else: ?>
                        <h4 class="marginBottom10">Employee for Engine: <?php echo $employeemodel->engine_name; ?></h4>
                    <?php endif; ?>
                    

                    <?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
                        'id' => 'eng-scheduling-status-form',
                        'method'=>'post'
                    ));

                    echo $form->errorSummary($employeemodel); ?>

                    <div class="row-fluid">
                        <div class="span6">
                            <div>
                                <?php echo $form->datepickerRow($employeemodel,'start_date'); ?>
                            </div>
                            <div>
                                <?php echo $form->timepickerRow($employeemodel,'start_time', array(
                                    'class' => 'input-small',
                                    'options' => array(
                                        'showMeridian' => false,
                                        'defaultTime' => false
                                    )
                                )); ?>
                            </div>
                        </div>
                        <div class="span6">
                            <div>
                                <?php echo $form->datepickerRow($employeemodel,'end_date'); ?>
                            </div>
                            <div>
                                <?php echo $form->timepickerRow($employeemodel,'end_time', array(
                                    'class' => 'input-small',
                                    'options' => array(
                                        'showMeridian' => false,
                                        'defaultTime' => false
                                    )
                                )); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row-fluid">
                        <div class="span6">
		                    <?php echo CHtml::label('Qualifications / ' . $employeemodel->getAttributeLabel('crew_id').'<span class="required"> * </span>',CHtml::activeId($employeemodel,'crew_id')); ?>
                            <?php echo $form->dropDownList($employeemodel,'crew_id',$employeemodel->getEngineCrewMembers(),array('empty'=>' ')); ?>
		                    <?php echo $form->error($employeemodel,'crew_id'); ?>
                        </div>
                        <div class="span6">
		                    <?php echo $form->labelEx($employeemodel,'scheduled_type'); ?>
                            <?php echo $form->dropDownList($employeemodel,'scheduled_type',$employeemodel->getScheduledCrewTypes(),array('empty'=>' ')); ?>
		                    <?php echo $form->error($employeemodel,'scheduled_type'); ?>
                        </div>
                    </div>
                </div>

                <?php if ($employeemodel->isNewRecord) { echo $form->hiddenField($employeemodel,'engine_scheduling_id',array('value'=>$schedulingmodel->id)); }  ?>
                <?php if (!$employeemodel->isNewRecord) { echo $form->hiddenField($employeemodel,'engine_scheduling_id',array('value'=>$employeemodel->engine_scheduling_id)); }  ?>

                <div class="buttons actionButton">
                    <?php echo CHtml::submitButton(($employeemodel->isNewRecord ? 'Create Status' : 'Update Status'), array('class'=>'submit')); ?>
                </div>

                <?php $this->endWidget(); ?>

            </div>
        </div>
    </div>
</div>