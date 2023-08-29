<div id="formCallAttempt" class="form">
    <?php if ($model->isNewRecord): ?>
        <h3>New Call Attempt</h3>
    <?php else: ?>
        <h3>Call Attempt #<?php echo $model->attempt_number; ?></h3>
    <?php endif; ?>
    
    <div class="container">
        <div class="leftColumn">
            <div>
                <?php
                    echo $form->labelEx($model, 'caller_user_id');
                    echo $form->dropDownList($model, 'caller_user_id', CHtml::listData($callerUsers, 'id', 'name'), array('empty' => ''));
                    echo $form->error($model, 'caller_user_id');
                ?>
            </div>
            <div>
                <?php 
                    echo $form->labelEx($model,'date_called');
                    $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                        'model' => $model,
                        'attribute' => 'date_called',
                        'options' => array(
                            'showAnim' => 'fold',
                            'showButtonPanel' => true,
                            'autoSize' => true,
                            'dateFormat' => 'mm/dd/yy',
                            'timeFormat' => 'h:mm tt',
                            'ampm' => true,
                            'separator' => ' ',
                            'maxDate' => 'today',                            
                        ),
                        'htmlOptions'=>array(  
                                'readonly' => true,                              
                                'style'=>'cursor:pointer;'
                            ),
                    ));
                    echo $form->error($model,'date_called');
                ?>
            </div>
             <div>
                <?php
                      echo $form->labelEx($model,'contact_type');
                      echo $form->dropDownList($model,'contact_type', 
                      array_combine($model->getAdminContactTypes(), $model->getAdminContactTypes()),
                      array('empty' => '-- Select a Type --'));
                      echo $form->error($model,'contact_type');
                ?>
            </div>
            <div>
                <?php
                    echo $form->labelEx($model, 'point_of_contact');
                    echo $form->textField($model, 'point_of_contact');
                    echo $form->error($model, 'point_of_contact');
                ?>
            </div>
            <div>
                <label>Point of Contact Description</label>
                <?php            
                    echo $form->textField($model, 'point_of_contact_description');
                    echo $form->error($model, 'point_of_contact_description');
                ?>
            </div>
            <div class="paddingTop10 paddingBottom10">
                <div class="radioButtonGroup">
                    <?php
                        echo $form->labelEx($model, 'in_residence');
                        echo $form->radioButtonList($model, 'in_residence', 
                            array(1 => 'Yes', 0 => 'No'),
                            array('separator' => ''));
                        echo $form->error($model, 'in_residence');
                    ?>
                </div>
                <div class="radioButtonGroup">
                    <?php
                        echo $form->labelEx($model, 'evacuated');
                        echo $form->radioButtonList($model, 'evacuated', 
                            array(1 => 'Yes', 0 => 'No'),
                            array('separator' => ''));
                        echo $form->error($model, 'evacuated');
                    ?>
                </div>
                <div class="radioButtonGroup">
                    <?php
                        echo $form->labelEx($model, 'publish');
                        echo $form->radioButtonList($model, 'publish', 
                            array(1 => 'Yes', 0 => 'No'),
                            array('separator' => ''));
                        echo $form->error($model, 'publish');
                    ?>
                </div>
                <div class="radioButtonGroup">
                    <?php
                        echo $form->labelEx($callList, 'do_not_call');
                        if ($model->isNewRecord)
                        {
                            $callList->do_not_call = 0;
                        }
                        echo $form->checkBox($callList, 'do_not_call', 
                            array(1 => 'Selected', 0 => 'Not-Selected'),
                            array('separator' => ''));
                        echo $form->error($callList, 'do_not_call');
                    ?>
                </div>
            </div>
        </div>
        <div class="rightColumn">
            <div>
                <?php
                    echo $form->labelEx($model, 'dashboard_comments');
                    echo $form->textArea($model, 'dashboard_comments', array('rows'=>'5', 'style'=>'width:100%'));
                    echo $form->error($model, 'dashboard_comments');
                ?>
            </div>
            <div>
                <?php
                    echo $form->labelEx($model, 'general_comments');
                    echo $form->textArea($model, 'general_comments', array('rows'=>'5', 'style'=>'width:100%'));
                    echo $form->error($model, 'general_comments');
                ?>
            </div>
            <?php echo $form->hiddenField($model,'call_list_id');  ?>
        </div>
    </div>    
</div>