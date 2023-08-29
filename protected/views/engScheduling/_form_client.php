<?php
/* @var $this EngSchedulingClientController */
/* @var $model EngScheduling */
/* @var $engineclientmodel EngSchedulingClient */
/* @var $form CActiveForm */

?>

<div class="form"  style="margin-bottom:300px;">
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span6">
                    
                <!-- Client Grid -->

                <div class="marginTop20">
                    <i class="icon-user"></i>
                    <span class="heading">Scheduled Clients</span> 
                    <a href="javascript:void(0);" id="lnkAddNewClient" class="paddingLeft10">Add New Client</a>
                </div>

                <div class="table-responsive">

                    <?php
                            
                    $dataProvider = EngSchedulingClient::model()->search($schedulingmodel->id);
                            
                    $this->widget('bootstrap.widgets.TbExtendedGridView', array (
                        'id' => 'gridClients',
                        'cssFile' => '../../css/wdsExtendedGridView.css',
                        'type' => 'striped bordered condensed',
                        'dataProvider' => $dataProvider,
                        'columns' => array(
                            array( // This ID field is used for selecting the active row
                                'name'=>'id',
                            ),
                            'client_name',
                            'client_scheduled:boolean',
                            'start_date',
                            'end_date',
    		                array(
    			                'class'=>'bootstrap.widgets.TbButtonColumn',
                                'template'=>'{delete}',
                                'header' => 'Delete',
                                'buttons' => array(
                                    'delete' => array(
                                        'visible' => 'in_array("Engine Manager",Yii::app()->user->types)',
                                        'url' => '$this->grid->controller->createUrl("/engSchedulingClient/delete", array("id"=>$data->id))',
                                        'options' => array(
                                            'method'=>'post'
                                        ),
                                        // Overriding ajax reloading with a manual redirect ... fixes problems with url when a record in edit mode is deleted
                                        'click' => new CJavaScriptExpression('function() {
                                            if (!confirm("Are you sure you want to delete this item?")) return false;
                                            // Delete record with AJAX request
                                            jQuery("#gridClients").yiiGridView("update", {
                                                type: "POST",
                                                url: jQuery(this).attr("href"),
                                                success: function(data) {
                                                    // Delete successfull, redirect to update with no selection
                                                    window.location.href = "' . $this->createUrl('engScheduling/update', array('id' => $schedulingmodel->id, 'engineclientmodelID' => '')) . '";
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
                        'emptyText' => 'No Clients have been scheduled.',
                        'selectableRows' => 1,
                        'selectionChanged' => 'function(id) { EngineClient.onGridRowSelected(id); }',
                    ));
                            
                    ?>

                </div>
            </div>
            <div class="span6">

                <!-- Status Form -->

                <div class="form"  style="border: none;">
                    <?php if ($engineclientmodel->isNewRecord): ?>
                        <h4 class="marginBottom10">New Client</h4>
                    <?php else: ?>
                        <h4 class="marginBottom10">Client Scheduled for Engine: <?php echo $engineclientmodel->client_name; ?></h4>
                    <?php endif; ?>
                    

                    <?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm', array(
                        'id' => 'eng-scheduling-client-form',
                        'method'=>'post'
                    ));

                    echo $form->errorSummary($engineclientmodel); ?>

                    <div class="row-fluid">
                        <div class="span6">
                            <div>
                                <?php echo $form->datepickerRow($engineclientmodel,'start_date'); ?>
                            </div>
                            <div>
                                <?php echo $form->timepickerRow($engineclientmodel,'start_time', array(
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
                                <?php echo $form->datepickerRow($engineclientmodel,'end_date'); ?>
                            </div>
                            <div>
                                <?php echo $form->timepickerRow($engineclientmodel,'end_time', array(
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
		                <?php echo $form->labelEx($engineclientmodel,'client_id'); ?>
                        <?php echo $form->dropDownList($engineclientmodel,'client_id',CHtml::listData($engineclientmodel->getAvailibleClients($schedulingmodel->fire_id,$schedulingmodel->assignment),'id','name'),array('multiple' => 'multiple','size' => '9')); ?>
		                <?php echo $form->error($engineclientmodel,'client_id'); ?>
                    </div>
                </div>

                <?php if ($engineclientmodel->isNewRecord) { echo $form->hiddenField($engineclientmodel,'engine_scheduling_id',array('value'=>$schedulingmodel->id)); }  ?>
                <?php if (!$engineclientmodel->isNewRecord) { echo $form->hiddenField($engineclientmodel,'engine_scheduling_id',array('value'=>$engineclientmodel->engine_scheduling_id)); }  ?>

                <div class="buttons actionButton">
                    <?php echo CHtml::submitButton(($engineclientmodel->isNewRecord ? 'Create Status' : 'Update Status'), array('class'=>'submit')); ?>
                </div>

                <?php $this->endWidget(); ?>

            </div>
        </div>
    </div>
</div>