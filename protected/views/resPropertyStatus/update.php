<?php
    echo CHtml::cssFile(Yii::app()->baseUrl.'/css/resPropertyStatus/update.css');
    echo CHtml::scriptFile(Yii::app()->baseUrl.'/js/resPropertyStatus/update.js');

    // Set the manifest url so that this page may be cached for offline use.
    $this->htmlManifest = 'manifest="index.php?r=site/page&view=offlineCache"';
    
    $this->breadcrumbs=array(
	    'Response Property Status' => array('admin'),
	    $model->id,
	    'Update',
    );
    
    $memberName = $model->member_first_name . ' ' . $model->member_last_name;
?>

<div id="responsePropertyStatusContainer">
    <div class="group">
        <h2 class="floatLeft">
            Response Property Status
        </h2>
    </div>
    <div class="form propertyStatusForm marginTop10 marginBottom10">

        <?php
            $form = $this->beginWidget('CActiveForm', array(
	            'id' => 'formPropertyStatus',
	            'enableAjaxValidation' => false,
            ));
            
            echo $form->errorSummary($model);
        ?>

        <div class="group formContainer">
            <div id="memberContainer" class="group">
                <div class="cellFirst">               
                   <table class="infoTable">
                       <tr>
                           <td><span id="lblMember"></span></td>
                           <td>
                               <span id="lblMemberName">
                                    <?php 
                                        if (isset($model->property))
                                            echo CHtml::link($memberName, array('member/view', 'mid'=>$model->property->member_mid)); 
                                    ?>
                                </span>
                           </td>
                       </tr>
                       <tr>
                           <td>Address:</td>
                           <td>
                               <span id="lblAddress">
                                   <?php
                                       if (isset($model->property))
                                       {
                                           echo $model->property->address_line_1 . ' ';
                                           echo $model->property->address_line_2 . '<br/>';
                                           echo $model->property->city . ', ';
                                           echo $model->property->state . ' ';
                                           echo $model->property->zip;
                                           if (!empty($model->property->zip_supp)) 
                                               echo '-' . $model->property->zip_supp;
                                       }
                                   ?>
                               </span>
                           </td>
                       </tr>
                    </table>
                </div>
                <div class="cellMiddle">
                    <table class="infoTable">
                        <tr>
                            <td>Threat:</td>
                            <td><span id="lblThreat"><?php echo $model->threat; ?></span></td>
                        </tr>
                        <tr>
                            <td>Priority:</td>
                            <td><span id="lblPriority"><?php echo $model->priority; ?></span></td>
                        </tr>
                        <tr>
                            <td>Distance:</td>
                            <td><span id="lblDistance"><?php echo round($model->distance, 2); ?></span></td>
                        </tr>
                    </table>
                </div>
                <div class="cellLast">
                    <table class="infoTable">
                        <tr>
                            <td>Client:</td>
                            <td><span id="lblClient"><?php echo $model->client_name; ?></span></td>
                        </tr>
                        <tr>
                            <td>Response Status:</td>
                            <td><span id="lblResponseStatus"><?php echo $model->property_response_status; ?></span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="group">
                <div class="fluidField">
                    <?php
                        echo $form->labelEx($model, 'status');
                        echo $form->dropDownList($model, 'status', $model->getPropertyStatuses());
                        echo $form->error($model, 'status');
                    ?>
                </div>
                <div class="fluidField">
                    <?php 
                        echo $form->labelEx($model,'date_visited');
                        $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                            'id' => 'dateVisited',
                            'model' => $model,
                            'attribute' => 'date_visited',
                            'options' => array(
                                'showAnim' => 'fold',
                                'showButtonPanel' => true,
                                'autoSize' => true,
                                'dateFormat' => 'yy-mm-dd',
                                'timeFormat' => 'h:mm',
                                'ampm' => false,
                                'separator' => ' ',
                            ),
                        ));
                        echo $form->error($model,'date_visited');
                    ?>
                </div>
                <div class="fluidField">
                    <?php
                        echo $form->labelEx($model, 'division');
                        echo $form->textField($model,'division');
                        echo $form->error($model,'division');
                    ?>
                </div>
                <div class="fluidField">
                    <?php
                        echo $form->labelEx($model, 'engine_id');
                        echo $form->dropDownList($model, 'engine_id', $engines, array('empty' => ''));
                        echo $form->error($model, 'engine_id');
                    ?>
                </div>
                <div class="fluidField">
                    <?php
                        echo $form->labelEx($model, 'has_photo');
                        echo $form->checkBox($model, 'has_photo');
                        echo $form->error($model,'has_photo');
                    ?>
                </div>
            </div>
            <div class="group">
                <div class="halfWidthField">
                    <?php
                        echo $form->labelEx($model, 'actions');
                        echo $form->textArea($model, 'actions', array('rows'=>'5'));
                        echo $form->error($model,'actions');
                    ?>
                </div>
                <div class="halfWidthField">
                    <?php
                        echo $form->labelEx($model, 'other_issues');
                        echo $form->textArea($model, 'other_issues', array('rows'=>'5'));
                        echo $form->error($model,'other_issues');
                    ?>
                </div>
            </div>
        </div>
   
	    <div class="row buttons paddingBottom20">
            <button type="submit" id="btnSubmit" class="btn btn-primary">Save</button>
            <span class="paddingLeft10">
                <button type="button" id="btnCancel" class="btn">Cancel</button>
            </span>
	    </div>

        <?php $this->endWidget(); ?>
    </div>
</div>