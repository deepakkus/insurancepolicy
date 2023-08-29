<div class="form expandedInputs">
    <?php
        $form = $this->beginWidget('CActiveForm', array(
           'id' => 'fsUser-form',
            'enableAjaxValidation' => false,
        ));

        echo $form->errorSummary($model);
    ?>
    <br/>
    <div>
        <?php
            echo $form->labelEx($model, 'email');
            echo $form->textField($model, 'email');
            echo $form->error($model, 'email');
        ?>
    </div>
    <div>
        <?php
            echo $form->labelEx($model, 'first_name');
            echo $form->textField($model, 'first_name');
            echo $form->error($model, 'first_name');
        ?>
    </div>
    <div>
        <?php
            echo $form->labelEx($model, 'last_name');
            echo $form->textField($model, 'last_name');
            echo $form->error($model, 'last_name');
        ?>
    </div>
    <div>
        <?php
            echo $form->labelEx($model, 'password');
            echo $form->passwordField($model, 'password', array('size'=>32, 'maxlength'=>32));
            if (!$model->isNewRecord) 
            {
                echo "<span class='gray paddingLeft10'>Leave blank to keep the same</span>";
            }
            echo $form->error($model, 'password');
        ?>
    </div>
    <div>
        <?php
            echo $form->labelEx($model, 'login_token');
            echo $form->textField($model, 'login_token', array('readonly' => !$model->isNewRecord ? true : false));
            echo $form->error($model, 'login_token');
        ?>
    </div>
    <div>
        <?php
            echo $form->labelEx($model, 'vendor_id');
            echo $form->textField($model, 'vendor_id', array('readonly' => !$model->isNewRecord ? true : false));
            echo $form->error($model, 'vendor_id');
        ?>
    </div>
    <div>
        <?php
        echo $form->labelEx($model, 'platform');
        echo $form->textField($model, 'platform', array('readonly' => !$model->isNewRecord ? true : false));
        echo $form->error($model, 'platform');
        ?>
    </div>
    <?php
        if ($model->isNewRecord)
        {
            echo '<div>';
            echo $form->labelEx($model, 'member_mid');
            echo $form->textField($model, 'member_mid');
            echo $form->error($model, 'member_mid');
            echo '</div>';
			
			echo '<div>';
            echo $form->labelEx($model, 'agent_id');
            echo $form->textField($model, 'agent_id');
            echo $form->error($model, 'agent_id');
            echo '</div>';
        }
    ?>
    <div class="buttons paddingTop10">
        <?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class' => 'submit')); ?>
        <span class="paddingLeft10">
            <?php echo CHtml::link('Cancel', array('fsUser/admin')); ?>
        </span>
    </div>
    <?php $this->endWidget(); ?>
</div>