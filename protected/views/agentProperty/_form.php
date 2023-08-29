<div class="form">
<?php
 
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'agent-property-form',
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($agentProperty); ?>

	<div class="padding10">
        <?php if (!$agentProperty->isNewRecord) echo '<div><b>Agent Property ID:</b> ' . $agentProperty->id . '</div>'; ?>
        <div>
            <b>Agent ID:</b> <?php echo $agentProperty->agent_id; ?>
        </div>
        <div>
            <b>Agent Name:</b> 
            <?php echo CHtml::link($agentProperty->agent_first_name . ' ' . $agentProperty->agent_last_name, array('agent/update', 'id'=>$agentProperty->agent_id)); ?>
        </div>
        <div>
            <b>Agent #:</b> <?php echo $agentProperty->agent_num; ?>
        </div>
        <?php echo $form->hiddenField($agentProperty, 'agent_id'); ?>
        <?php
        if(!empty($agentProperty->property_pid))
        {
            echo '<div>';
            echo '  <b>Related Property PID: </b>'.CHtml::link($agentProperty->property_pid, array('property/view', 'pid'=>$agentProperty->property_pid));
            echo '</div>';
        }
        if(!empty($agentProperty->member_mid))
        {
            echo '<div>';
            echo '  <b>Related Member MID: </b>'.CHtml::link($agentProperty->member_mid, array('member/view', 'mid'=>$agentProperty->member_mid));
            echo '</div>';
        }
        ?>
    </div>
 
    <div class="clearfix formContainer">
        <h3>Address</h3>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($agentProperty,'address_line_1');
                echo $form->textField($agentProperty,'address_line_1');
                echo $form->error($agentProperty,'address_line_1'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($agentProperty,'address_line_2');
                echo $form->textField($agentProperty,'address_line_2');
                echo $form->error($agentProperty,'address_line_2'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($agentProperty,'city');
                echo $form->textField($agentProperty,'city');
                echo $form->error($agentProperty,'city'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($agentProperty,'state');
                echo $form->textField($agentProperty,'state');
                echo $form->error($agentProperty,'state'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($agentProperty,'zip');
                echo $form->textField($agentProperty,'zip');
                echo $form->error($agentProperty,'zip'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($agentProperty,'zip_supp');
                echo $form->textField($agentProperty,'zip_supp');
                echo $form->error($agentProperty,'zip_supp'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($agentProperty,'long');
                echo $form->textField($agentProperty,'long');
                echo $form->error($agentProperty,'long'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($agentProperty,'lat');
                echo $form->textField($agentProperty,'lat');
                echo $form->error($agentProperty,'lat'); 
            ?>
        </div>
    </div>
    
    <div class="clearfix formContainer">
        <h3>Property Values</h3>
        <div class="fluidField">
            <?php
            echo $form->labelEx($agentProperty,'status');
            echo $form->dropDownList($agentProperty,'status',array_combine($agentProperty->getStatuses(), $agentProperty->getStatuses()));
            echo $form->error($agentProperty,'status');
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($agentProperty,'policyholder_name');
            echo $form->textField($agentProperty,'policyholder_name');
            echo $form->error($agentProperty,'policyholder_name');
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($agentProperty,'work_order_num');
                echo $form->textField($agentProperty,'work_order_num');
                echo $form->error($agentProperty,'work_order_num'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($agentProperty,'property_value');
                echo $form->textField($agentProperty,'property_value');
                echo $form->error($agentProperty,'property_value'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($agentProperty,'geo_risk');
                echo $form->textField($agentProperty,'geo_risk');
                echo $form->error($agentProperty,'geo_risk'); 
            ?>
        </div>
    </div>
    
	<div class="buttons">
		<?php echo CHtml::submitButton($agentProperty->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
        <span class="paddingLeft10">
            <?php echo CHtml::link('Cancel', array('agentProperty/admin')); ?>
        </span>
	</div>
       
<?php $this->endWidget(); ?>

</div><!-- form -->