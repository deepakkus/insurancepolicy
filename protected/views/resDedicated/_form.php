<?php
/* @var $this ResDedicatedController */
/* @var $model ResDedicated */
/* @var $form CActiveForm */

if ($model->isNewRecord)
{
    echo '<h1>Fill Out Dedicated Hours for ' . Client::model()->find('id = :id', array(':id' => $clientid))->name . '</h1>';
}
else
{
    echo '<h1>Update Dedicated Hours for ' . $model->client_name . ' (' . date('F Y', strtotime($model->date)) . ')</h1>';
}

?>

<div class="form paddingTop20 paddingBottom20">
    <div class="container-fluid">

        <?php

        $form = $this->beginWidget('CActiveForm', array('id' => 'res-dedicated-form'));

        echo $form->errorSummary($model);
        
        ?>

        <?php if ($model->isNewRecord): ?>

        <div class="row-fluid">
            <div class="span12">
	            <div>
		            <?php echo $form->labelEx($model,'date'); ?>
		            <?php echo $form->dropDownList($model,'date',$months,array('prompt'=>'')); ?>
		            <?php echo $form->error($model,'date'); ?>
	            </div>
            </div>
        </div>

        <?php endif; ?>

        <?php

        foreach (array_chunk(ResDedicated::getDedicatedStates(), 3, true) as $threestates)
        {
            echo '<div class="row-fluid">';
            foreach ($threestates as $state)
            {
                echo '<div class="span4">';
                echo '<div>';
                echo $form->labelEx($model, $state);
                echo $form->textField($model, $state, array('size' => 5, 'maxlength' => 10));
                echo $form->error($model, $state);
                echo '</div>';
                echo '</div>';
            }
            echo '</div>';
        }
            
        if ($model->isNewRecord)
        {
            echo $form->hiddenField($model,'client_id', array('value' => $clientid));
            echo $form->hiddenField($model,'hours_id', array('value' => $hoursid));
        }
        else
        {
            echo $form->hiddenField($model, 'client_id');
            echo $form->hiddenField($model, 'hours_id');
            echo $form->hiddenField($model, 'date');
        }
    
        ?>

        <div class="row-fluid">
            <div class="span12">
                <div class="buttons marginTop20">
		            <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Update', array('class' => 'submit')); ?>
                    <span class="paddingLeft10">
                        <?php echo CHtml::link('Cancel', array('resDedicated/admin')); ?>
                    </span>
	            </div>
            </div>
        </div>

        <?php $this->endWidget(); ?>

    </div>
</div>