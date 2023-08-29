<?php
/* @var $this EngSchedulingController */
/* @var $model EngScheduling */

$this->breadcrumbs = array(
	'Engines' => array('engEngines/index'),
	'Engine Scheduling' => array('admin'),
	'Update'
);
?>

<h1>Update Schedule for Engine: <?php echo $model->engine_name; ?></h1>

<?php

$this->widget('bootstrap.widgets.TbTabs', array(
    'type' => 'tabs',
    'id' => 'engine-tabs',
    'tabs' => array(
        array(
            'label' => 'Schedule Engine',
            'content' => $this->renderPartial('_form',array(
                'model' => $model
            ), true),
            'active' => (!isset($_GET['employeeID']) && !isset($_GET['engineclientmodelID'])) ? true : false
        ),
        array(
            'label' => 'Engine Clients',
            'content' => $this->renderPartial('_form_client',array(
                'engineclientmodel' => $engineclientmodel,
                'schedulingmodel' => $model
            ), true),
            'active' => isset($_GET['engineclientmodelID']) ? true : false
        ),
        array(
            'label' => 'Employee Scheduling',
            'content' => $this->renderPartial('_form_employee',array(
                'employeemodel' => $employeemodel,
                'schedulingmodel' => $model
            ), true),
            'active' => isset($_GET['employeeID']) ? true : false
        )
    )
));

// If user is not an engine manager, lock down engine scheduling form with execption of a few key things on the first tab.

if (!in_array('Engine Manager',Yii::app()->user->types))
{
    Yii::app()->clientScript->registerScript('disable-bootstrap-tabs', '
    
    var attrs = [
        $("#' . CHtml::activeId($model, 'engine_id') . '"),
        $("#' . CHtml::activeId($model, 'start_date') . '"),
        $("#' . CHtml::activeId($model, 'start_time') . '"),
        $("#' . CHtml::activeId($model, 'end_date') . '"),
        $("#' . CHtml::activeId($model, 'end_time') . '"),
        $("#' . CHtml::activeId($model, 'arrival_date') . '"),
        $("#' . CHtml::activeId($model, 'arrival_time') . '"),
        $("#' . CHtml::activeId($model, 'specific_instructions') . '"),
        $("#' . CHtml::activeId($model, 'fire_officer_id') . '"),
        $("#' . CHtml::activeId($model, 'assignment') . '"),
        $("#' . CHtml::activeId($model, 'fire_id') . '"),
        
        $("#' . CHtml::activeId($engineclientmodel, 'start_date') . '"),
        $("#' . CHtml::activeId($engineclientmodel, 'start_time') . '"),
        $("#' . CHtml::activeId($engineclientmodel, 'end_date') . '"),
        $("#' . CHtml::activeId($engineclientmodel, 'end_time') . '"),
        $("#' . CHtml::activeId($engineclientmodel, 'client_id') . '"),
        
        $("#' . CHtml::activeId($employeemodel, 'start_date') . '"),
        $("#' . CHtml::activeId($employeemodel, 'start_time') . '"),
        $("#' . CHtml::activeId($employeemodel, 'end_date') . '"),
        $("#' . CHtml::activeId($employeemodel, 'end_time') . '"),
        $("#' . CHtml::activeId($employeemodel, 'crew_id') . '"),
        $("#' . CHtml::activeId($employeemodel, 'scheduled_type') . '")
    ];
    
    $.each(attrs, function(index, value) {
        value.prop("disabled", true);
    });
    
    $(\'input[value="Create Status"]\').each(function(e) { $(this).remove(); });
    $(\'input[value="Update Status"]\').each(function(e) { $(this).remove(); });
    
    // Enabling properties before submit so that Yii doesnt freak out
    
    $("#eng-scheduling-form").submit(function(e) {
        $.each(attrs, function(index, value) {
            value.prop("disabled", false);
        });
    });

    ');
}

?>