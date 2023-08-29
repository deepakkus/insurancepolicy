<div class="form marginTop10 marginBottom10">

<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'agent-form',
	'enableAjaxValidation'=>false,
));
?>

	<?php echo $form->errorSummary($agent); ?>

    <div class="paddingTop10 paddingBottom10">
        <?php if (!$agent->isNewRecord) : ?>
            <div>
                <b>Agent ID:</b> <?php echo $agent->id; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="clearfix formContainer">
        <h3>Agent Information</h3>
        <div class="fluidField">
            <?php
                echo $form->labelEx($agent,'first_name');
                echo $form->textField($agent,'first_name');
                echo $form->error($agent,'first_name');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($agent,'last_name');
                echo $form->textField($agent,'last_name');
                echo $form->error($agent,'last_name');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($agent,'agent_num');
                echo $form->textField($agent,'agent_num');
                echo $form->error($agent,'agent_num');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($agent,'client');
                echo $form->dropDownList($agent,'client', Client::model()->getClientNames(), array('empty'=>''));
                echo $form->error($agent,'client');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($agent,'fs_carrier_key');
                echo $form->textField($agent,'fs_carrier_key');
                echo $form->error($agent,'fs_carrier_key');
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($agent,'agent_type');
            echo $form->dropDownList($agent,'agent_type',Agent::agentTypes(),array('prompt' => ''));
            echo $form->error($agent,'agent_type');
            ?>
        </div>
    </div>
   
	<div class="buttons">
		<?php echo CHtml::submitButton($agent->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
        <span class="paddingLeft10">
            <?php echo CHtml::link('Cancel', array('agent/admin')); ?>
        </span>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->