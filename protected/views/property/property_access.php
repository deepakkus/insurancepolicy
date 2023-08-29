<?php

$this->breadcrumbs = array(
	'Properties' => array('admin'),
	$propertyAccess->property_id => array('view','pid' => $propertyAccess->property_id),
    'View' => array('view','pid' => $propertyAccess->property_id),
	'Property Access',
);

Yii::app()->clientScript->registerCssFile('/css/propertyForm.css');

?>

<h1>Property Access</h1>

<div class="left-cell">
    <div id="formPropertyAccess" class="form" style="border: 1px solid #ccc;">

    <?php 
        $form = $this->beginWidget('CActiveForm', array(
	        'id' => 'formResponsePropertyAccess',
	        'enableAjaxValidation' => false,
        )); 

        echo $form->errorSummary($propertyAccess);
    ?>

        <h3 class="paddingTop10">Property Details</h3>
        <div class="radioButtonGroup paddingTop10 paddingBottom10">
            <?php
                echo $form->labelEx($propertyAccess, 'address_verified');
                echo $form->radioButtonList($propertyAccess, 'address_verified', 
                    array(1 => 'Yes', 0 => 'No'),
                    array('separator' => ''));
                echo $form->error($propertyAccess, 'address_verified');
            ?>
        </div>
        <div>
            <?php
                echo $form->labelEx($propertyAccess, 'best_contact_number');
                echo $form->textField($propertyAccess, 'best_contact_number');
                echo $form->error($propertyAccess, 'best_contact_number');
            ?>
        </div>
        <div>
            <?php
                echo $form->labelEx($propertyAccess, 'access_issues');
                echo $form->textField($propertyAccess, 'access_issues');
                echo $form->error($propertyAccess, 'access_issues');
            ?>
        </div>                            
        <div>
            <?php
                echo $form->labelEx($propertyAccess, 'gate_code');
                echo $form->textField($propertyAccess, 'gate_code');
                echo $form->error($propertyAccess, 'gate_code');
            ?>
        </div>                            
        <div>
            <?php
                echo $form->labelEx($propertyAccess, 'suppression_resources');
                echo $form->textField($propertyAccess, 'suppression_resources');
                echo $form->error($propertyAccess, 'suppression_resources');
            ?>
        </div>                            
        <div>
            <label>Other Info <span class="notBold">(pets, unique home features, etc.)</span></label>
            <?php
                echo $form->textArea($propertyAccess, 'other_info', array('rows'=>'5'));
                echo $form->error($propertyAccess, 'other_info');
            ?>
        </div>                            
        <div class="buttons actionButton">
            <?php echo CHtml::submitButton('Save Property Details', array('class'=>'submit')); ?>
        </div>

    <?php $this->endWidget(); ?>
    </div>
</div>