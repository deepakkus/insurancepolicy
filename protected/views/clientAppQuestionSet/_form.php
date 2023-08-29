<div class="form">

    <?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'client-app-question-set-form',
	'enableAjaxValidation'=>false,
    'htmlOptions' => array('enctype' => 'multipart/form-data'),
));

          $client = Client::model()->findByPk($clientAppQuestionSet->client_id);
          if($clientAppQuestionSet->isNewRecord)
              echo '<br><b>Adding ';
          else
              echo '<br><b>Updating ';

          echo 'Client App Question Set for Client '.$client->name.'<br>';
    ?>

    <p class="note">
        Fields with
        <span class="required">*</span>
        are required.
    </p>

    <?php echo $form->errorSummary($clientAppQuestionSet); ?>

    <div class="row-fluid">
        <?php
        echo $form->labelEx($clientAppQuestionSet, 'name');
        echo $form->textField($clientAppQuestionSet,'name', array('size'=>25,'maxlength'=>25));
        echo $form->error($clientAppQuestionSet,'name');
        ?>
    </div>

    <div class="row-fluid">
        <?php
        echo $form->labelEx($clientAppQuestionSet,'client_id');
        echo $form->dropDownList($clientAppQuestionSet,'client_id', Client::model()->getClientNames());
        echo $form->error($clientAppQuestionSet,'client_id');
        ?>
    </div>

    <div class="row-fluid">
        <?php 
        echo $form->labelEx($clientAppQuestionSet,'active');

        if($clientAppQuestionSet->isNewRecord)
            $clientAppQuestionSet->active = 1;
        echo $form->checkBox($clientAppQuestionSet, 'active', array('value' => 1, 'uncheckValue' => 0));
        echo $form->error($clientAppQuestionSet,'active'); 
        ?>
    </div>

    <div class="row-fluid">
        <?php
        echo $form->labelEx($clientAppQuestionSet,'is_default');
        echo $form->checkBox($clientAppQuestionSet, 'is_default', array('value' => 1, 'uncheckValue' => 0));
        echo $form->error($clientAppQuestionSet,'is_default'); 
        ?>
    </div>

    <div class="row-fluid">
        <?php
        echo $form->labelEx($clientAppQuestionSet, 'default_level');
        echo $form->dropDownList($clientAppQuestionSet,'default_level', array(1=>1, 2=>2));
        echo $form->error($clientAppQuestionSet,'default_level');
        ?>
    </div>

    <div class="row-fluid buttons">
        <?php echo CHtml::submitButton($clientAppQuestionSet->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
    </div>


    <?php $this->endWidget(); ?>

</div>
<!-- form -->