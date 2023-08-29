<div class="form">

<?php
    $form = $this->beginWidget('CActiveForm', array(
       'id' => 'contact-us-form',
        'enableAjaxValidation' => false,
    ));

    echo $form->errorSummary($model);
    
    // existingMemberID will be sent from the controller if a Member was found with the email address given.
    if ($existingMemberID > 0)
    {
        echo "<div class='note padding10'>A member with the email address $model->email exists in the system. ";
        echo CHtml::link('View Member', array('member/update', 'mid' => $existingMemberID));
        echo "</div>";
    }
?>
    <br/>
    <?php if (!$model->isNewRecord) : ?>
    <div>
        <label>Date/Time</label>
        <div><?php echo $model->timestamp; ?></div>
    </div>    
    <br/>
    <?php endif; ?>
    <div>
        <?php
            echo $form->labelEx($model, 'email');
            echo $form->textField($model, 'email');
            echo $form->error($model, 'email');
        ?>
    </div>
    <div>
        <?php
            echo $form->labelEx($model, 'from');
            echo $form->textField($model, 'from');
            echo $form->error($model, 'from');
        ?>
    </div>
    <div>
        <?php
            echo $form->labelEx($model, 'status');
            echo $form->dropDownList($model, 'status', array_combine($model->getStatuses(), $model->getStatuses()), array('empty' => ''));
            echo $form->error($model, 'status');
        ?>
    </div>
    <div>
        <?php
            echo $form->labelEx($model, 'provider');
            echo $form->textField($model, 'provider');
            echo $form->error($model, 'provider');
        ?>
    </div>
    <div class="buttons">
        <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
        <span class="paddingLeft10">
            <?php echo CHtml::link('Cancel', array('fsContactUs/admin')); ?>
        </span>
    </div>

<?php $this->endWidget(); ?>

</div><!-- form -->