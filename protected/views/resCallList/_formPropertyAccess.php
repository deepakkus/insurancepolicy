<div id="formPropertyAccess" class="form">

<?php 
    echo $form->errorSummary($model);
?>        
    <h3 class="paddingTop10">Property Details</h3>
    <div class="radioButtonGroup paddingTop10 paddingBottom10">
        <?php
            echo $form->labelEx($model, 'address_verified');
            echo $form->radioButtonList($model, 'address_verified', 
                array(1 => 'Yes', 0 => 'No'),
                array('separator' => ''));
            echo $form->error($model, 'address_verified');
        ?>
    </div>
    <div>
        <?php
            echo $form->labelEx($model, 'best_contact_number');
            echo $form->textField($model, 'best_contact_number',array('size'=>20,'maxlength'=>12,'placeholder'=>'xxx-xxx-xxxx','pattern'=>'\d{3}-\d{3}-\d{4}'));
            echo $form->error($model, 'best_contact_number');
        ?>
    </div>
    <div>
        <?php
            echo $form->labelEx($model, 'access_issues');
            echo $form->textField($model, 'access_issues');
            echo $form->error($model, 'access_issues');
        ?>
    </div>                            
    <div>
        <?php
            echo $form->labelEx($model, 'gate_code');
            echo $form->textField($model, 'gate_code');
            echo $form->error($model, 'gate_code');
        ?>
    </div>                            
    <div>
        <?php
            echo $form->labelEx($model, 'suppression_resources');
            echo $form->textField($model, 'suppression_resources');
            echo $form->error($model, 'suppression_resources');
        ?>
    </div>                            
    <div>
        <label>Other Info <span class="notBold">(pets, unique home features, etc.)</span></label>
        <?php
            echo $form->textArea($model, 'other_info', array('rows'=>'5'));
            echo $form->error($model, 'other_info');
        ?>
    </div>                            
</div>