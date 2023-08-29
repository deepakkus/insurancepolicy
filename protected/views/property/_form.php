<?php

if (isset($readOnly))
{
	Yii::app()->clientScript->registerScript('member-form-js', "

		if(".$readOnly."){
			$(':input').prop('disabled', true);
			$('input[type=submit]').hide();
		}

	", CClientScript::POS_READY);
}

echo CHtml::cssFile(Yii::app()->baseUrl.'/css/propertyForm.css');
echo CHtml::scriptFile(Yii::app()->baseUrl.'/js/moment.min.js');

CHtml::$renderSpecialAttributesValue = false;

Yii::app()->format->dateFormat = 'Y-m-d H:i';
Yii::app()->clientScript->registerScript('property-form-js', "
$(function() 
{
    var regExp = /[a-z]/i;
    $('#Property_lat, #Property_long, #Property_coverage_a_amt').on('keydown keyup', function(e) 
    {
        var value = String.fromCharCode(e.which) || e.key;
        if (regExp.test(value)) 
        {
            e.preventDefault();
            return false;
        }
    });
});
", CClientScript::POS_READY);

?>

<div class="form">
<?php
 
$form=$this->beginWidget('CActiveForm', array(
	'id'=>'property-form',
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($property); ?>

	<div class="padding10">
        <div>
            <b>Property ID:</b> <?php echo $property->pid; ?>
        </div>
        <?php if(isset($member)) : ?>
        <div>
            <b>Member ID:</b> <?php echo $member->mid; ?>
        </div>
        <div>
            <b>Member Name:</b> 
            <?php
                  if(isset($readOnly) && $readOnly == true)
                      echo CHtml::link($member->first_name . ' ' . $member->last_name, array('member/view', 'mid'=>$member->mid)); 
                  else
                      echo CHtml::link($member->first_name . ' ' . $member->last_name, array('member/update', 'mid'=>$member->mid));
            ?>
        </div>
        <div>
            <b>Client Member #:</b> <?php echo $member->member_num; ?>
        </div>
        <?php echo $form->hiddenField($property, 'member_mid'); ?>
        <?php endif; //end if member is set ?>

        <div>
            <b>Client:</b>
            <?php echo $property->client->name; ?>
        </div>
        <div>
            <b>Property Type:</b>
            <?php echo $property->properties_type->type; ?>
        </div>
    </div>
 
    <div class="clearfix formContainer">
        <h3>Address</h3>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'address_line_1');
                echo $form->textField($property,'address_line_1');
                echo $form->error($property,'address_line_1'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'address_line_2');
                echo $form->textField($property,'address_line_2');
                echo $form->error($property,'address_line_2'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'city');
                echo $form->textField($property,'city');
                echo $form->error($property,'city'); 
            ?>
        </div>

        <?php if (!$property->isNewRecord) : ?>

        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'state');
                echo $form->textField($property,'state');
                echo $form->error($property,'state'); 
            ?>
        </div>

        <?php else: ?>

        <div class="clearfix">
		    <?php echo $form->labelEx($property,'state'); ?>
            <?php echo $form->dropDownList($property,'state', Helper::getStates(), array('style'=>'width:100px;','prompt'=>'')); ?>
		    <?php echo $form->error($property,'state'); ?>
        </div>

        <?php endif; ?>

        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'county');
                echo $form->textField($property,'county');
                echo $form->error($property,'county'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'zip');
                echo $form->textField($property,'zip');
                echo $form->error($property,'zip'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'zip_supp');
                echo $form->textField($property,'zip_supp');
                echo $form->error($property,'zip_supp'); 
            ?>
        </div>
		<div class="fluidField">
            <?php 
                echo $form->labelEx($property,'comments');
                echo $form->textArea($property,'comments', array('rows'=>'4', 'cols'=>'50'));
                echo $form->error($property,'comments'); 
            ?>
        </div> 

        <?php if (!$property->isNewRecord) : ?>
        
        <div class="fluidField">
            <h5>Additional Contacts</h5>
            <?php $this->widget('zii.widgets.grid.CGridView', array(
                'id' => 'gridAdditionalContacts',
                'dataProvider' => $additionalContacts,
                'summaryCssClass' => 'hidden',
                'emptyText' => 'No contacts found',
                'columns' => array('priority', 'name', 'relationship', 'type', 'detail'),
            )); ?>
        </div>

    <?php endif; ?>

    </div>

    <div class="clearfix formContainer">
        <h3>Geocoding</h3>

        <div class="fluidField">
            <?php
            echo $form->labelEx($property,'lat');
            echo $form->textField($property,'lat', array('disabled' => ($property->isNewRecord) ? false : true));
            echo $form->error($property,'lat');
            ?>
        </div>
        <div class="fluidField">
            <?php 
            echo $form->labelEx($property,'long');
            echo $form->textField($property,'long', array('disabled' => ($property->isNewRecord) ? false : true));
            echo $form->error($property,'long'); 
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($property,'geocode_level');
            echo $form->textField($property,'geocode_level', array('disabled' => ($property->isNewRecord) ? false : true));
            echo $form->error($property,'geocode_level');
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($property,'wds_lat');
            echo $form->textField($property,'wds_lat', array('disabled' => true));
            echo $form->error($property,'wds_lat');
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($property,'wds_long');
            echo $form->textField($property,'wds_long', array('disabled' => true));
            echo $form->error($property,'wds_long');
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($property,'wds_geocode_level');
            echo $form->textField($property,'wds_geocode_level', array('disabled' => true));
            echo $form->error($property,'wds_geocode_level');
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($property,'wds_geocoder');
            echo $form->textField($property,'wds_geocoder', array('disabled' => true));
            echo $form->error($property,'wds_geocoder');
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($property,'wds_match_address');
            echo $form->textField($property,'wds_match_address', array('disabled' => true));
            echo $form->error($property,'wds_match_address');
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($property,'wds_match_score');
            echo $form->textField($property,'wds_match_score', array('disabled' => true));
            echo $form->error($property,'wds_match_score');
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($property,'wds_geocode_date');
            echo $form->textField($property,'wds_geocode_date', array('disabled' => true,  'value' => date('m/d/Y h:i A', strtotime($property->wds_geocode_date))));
            echo $form->error($property,'wds_geocode_date');
            ?>
        </div>


    </div>

    <?php if (!$property->isNewRecord) : ?>

    <div class="clearfix formContainer">

        <?php
    	    $this->renderPartial('_risk_score', array(
                'property' => $property
		    ));
        ?>

    </div>

    <?php endif; ?>
    
    <div class="clearfix formContainer">
        <h3>Types and Policy Info</h3>
         <div class="fluidField">
            <?php 
            echo $form->labelEx($property,'policy');
            echo $form->textField($property,'policy', array('style'=>'width:150px'));
            echo $form->error($property,'policy'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
            echo $form->labelEx($property,'lob');
            echo $form->textField($property,'lob');
            echo $form->error($property,'lob'); 
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($property,'wds_lob');
            echo $form->dropDownList($property,'wds_lob',array_combine($property->getLOBTypes(), $property->getLOBTypes()), array());
            echo $form->error($property,'wds_lob');
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'dwelling_type');
                echo $form->textField($property,'dwelling_type');
                echo $form->error($property,'dwelling_type'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
            echo $form->labelEx($property,'roof_type');
            echo $form->textField($property,'roof_type');
            echo $form->error($property,'roof_type'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
            echo $form->labelEx($property,'coverage_a_amt');
            echo $form->textField($property,'coverage_a_amt', array('style'=>'width:150px'));
            echo $form->error($property,'coverage_a_amt'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'geo_risk');
                echo $form->textField($property,'geo_risk', array('style'=>'width:50px'));
                echo $form->error($property,'geo_risk'); 
            ?>
        </div>

        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'fs_assessments_allowed');
                echo $form->textField($property,'fs_assessments_allowed', array('style'=>'width:50px'));
                echo $form->error($property,'fs_assessments_allowed'); 
            ?>
        </div>
         <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'producer');
                echo $form->textField($property,'producer', array('style'=>'width:400px'));
                echo $form->error($property,'producer'); 
            ?>
        </div>
        <div class="fluidField">
            <?php 
                echo $form->labelEx($property,'rated_company');
                echo $form->textField($property,'rated_company', array('style'=>'width:300px'));
                echo $form->error($property,'rated_company'); 
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($property,'agency_name');
            echo $form->textField($property,'agency_name', array('style'=>'width:300px'));
            echo $form->error($property,'agency_name');
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($property,'agency_code');
            echo $form->textField($property,'agency_code', array('style'=>'width:200px'));
            echo $form->error($property,'agency_code');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($property,'response_auto_enrolled');
                echo $form->checkbox($property,'response_auto_enrolled');
                echo $form->error($property,'response_auto_enrolled'); 
            ?>
        </div>
        <div class="fluidField">
            <?php
            echo $form->labelEx($property,'multi_family');
            echo $form->checkbox($property,'multi_family');
            echo $form->error($property,'multi_family');
            ?>
        </div>
    </div>
    
	<?php 
		if ($property->isNewRecord)
		{
			$current_dt = date('m/d/Y h:i A');
			$property->res_status_date = $current_dt;
			$property->fs_status_date = $current_dt;
			$property->pr_status_date = $current_dt;
			$property->policy_status_date = $current_dt;
		}
	?>

    <div class="clearfix formContainer">
        <h3>Status</h3>
        <div class="statusHistoryColumn">    
            <div>
                <?php 
                    echo $form->labelEx($property,'response_status');
                    echo $form->dropDownList($property,'response_status',array_combine($property->getProgramStatuses(), $property->getProgramStatuses()), array('onchange'=>'$("#Property_res_status_date").val(moment().format("MM/D/YYYY h:mm A"));'));
                    echo $form->error($property,'response_status'); 
                ?>
            </div>
            <div class="field">
            <?php
                echo $form->labelEx($property,'res_status_date');
                $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                    'model' => $property,
                    'attribute' => 'res_status_date',
                    'mode' => 'datetime',
                    'options'=>array(
                        'showAnim'=>'fold',
                        'showButtonPanel'=>false,
                        'autoSize'=>true,
                        'dateFormat'=>'mm/dd/yy',
                        'timeFormat'=>'h:mm TT',
                        'ampm'=> true,
                        'separator'=> ' ',
                        ),
                ));
                echo $form->error($property,'res_status_date');
            ?>
            </div>
			<?php

            if (!$property->isNewRecord)
            {
                // Response Status History
                echo '<h5>History</h5>';
                echo '<div class="field">';
                $this->widget('zii.widgets.grid.CGridView', array(
                    'id' => 'gridResponseStatusHistory',
                    'dataProvider' => $responseStatusHistory,
                    'summaryCssClass' => 'hidden',
                    'emptyText' => 'No history found',
                    'columns' => array(
                        array(
                            'name' => 'date_changed', 
                            'header' => 'Date Changed', 
                            'value' => 'Yii::app()->dateFormatter->format("MM/d/y h:mm a", strtotime($data->date_changed))',
                        ),
                        array('name' => 'status', 'header' => 'Status'),
                        array(
                            'name' => 'user_id', 
                            'header' => 'By',
                            'value' => 'isset($data->user) ? $data->user->name : "System"',    
                        ),
                    )
                ));
                echo '</div>';
 
                // WDSfire Enrollment Activity
                echo '<h5>WDSFire Enrollment Activity</h5>';
                echo '<div class="field">';
                $this->widget('zii.widgets.grid.CGridView', array(
                    'id' => 'wdsfire-enrollments-grid',
                    'dataProvider' => $wdsFireEnrollments,
                    'emptyText' => 'No Dashboard Activity Found',
                    'columns' => array(
                        'status_type',
                        array(
                            'name' => 'date',
                            'type' => 'date'
                        ),
                        'user_name',
                    )
                    ));
                echo '</div>';
            }
            
            ?>
        </div>

        <?php if (!$property->isNewRecord) : ?>

        <div class="fluidField">
            <?php 
                  echo $form->labelEx($property,'response_enrolled_date');

                  $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                  'model'=>$property,
                  'attribute' => 'response_enrolled_date',
                  'options'=>array(
                      'showAnim'=>'fold',
                      'showButtonPanel'=>true,
                      'autoSize'=>true,
                      'dateFormat'=>'mm/dd/yy',
                      'timeFormat'=>'h:mm tt',
                      'ampm'=> true,
                      'separator'=> ' ',
                      ),
                  ));
                  
                  echo $form->error($property,'response_enrolled_date');
            ?>
        </div>

        <?php endif; ?>

        <div class="statusHistoryColumn">
            <div>
                <?php 
                    echo $form->labelEx($property,'fireshield_status');
                    echo $form->dropDownList($property,'fireshield_status',array_combine($property->getProgramStatuses(), $property->getProgramStatuses()), array('onchange'=>'$("#Property_fs_status_date").val(moment().format("MM/D/YYYY h:mm A"));'));
                    echo $form->error($property,'fireshield_status'); 
                ?>
            </div>
            <div class="field">
            <?php 
                echo $form->labelEx($property,'fs_status_date');
                $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                'model'=>$property,
                'attribute' => 'fs_status_date',
                'options'=>array(
                    'showAnim'=>'fold',
                    'showButtonPanel'=>true,
                    'autoSize'=>true,
                    'dateFormat'=>'mm/dd/yy',
                    'timeFormat'=>'h:mm tt',
                    'ampm'=> true,
                    'separator'=> ' ',
                    ),
                ));
                echo $form->error($property,'fs_status_date');
            ?>
            </div>
			<?php if (!$property->isNewRecord) : ?>
            <h5>History</h5>
            <div class="field">
                <?php
                $this->widget('zii.widgets.grid.CGridView', array(
                    'id' => 'gridFireShieldStatusHistory',
                    'dataProvider' => $fireShieldStatusHistory,
                    'summaryCssClass' => 'hidden',
                    'emptyText' => 'No history found',
                    'columns' => array(
                        array(
                            'name' => 'date_changed', 
                            'header' => 'Date Changed', 
                            'value' => 'Yii::app()->dateFormatter->format("MM/d/y h:mm a", strtotime($data->date_changed))',
                        ),
                        array('name' => 'status', 'header' => 'Status'),
                        array(
                            'name' => 'user_id', 
                            'header' => 'By',
                            'value' => 'isset($data->user) ? $data->user->name : "System"',    
                        ),
                    )
                ));
                ?>
            </div>
			<?php endif; ?>
        </div>    
        <div class="statusHistoryColumn">
            <div>
                <?php 
                    echo $form->labelEx($property,'pre_risk_status');
                    echo $form->dropDownList($property,'pre_risk_status',array_combine($property->getProgramStatuses(), $property->getProgramStatuses()), array('onchange'=>'$("#Property_pr_status_date").val(moment().format("MM/D/YYYY h:mm A"));'));
                    echo $form->error($property,'pre_risk_status'); 
                ?>
            </div>
            <div class="field">
            <?php 
                echo $form->labelEx($property,'pr_status_date');
                $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                'model'=>$property,
                'attribute' => 'pr_status_date',
                'options'=>array(
                    'showAnim'=>'fold',
                    'showButtonPanel'=>true,
                    'autoSize'=>true,
                    'dateFormat'=>'mm/dd/yy',
                    'timeFormat'=>'h:mm tt',
                    'ampm'=> true,
                    'separator'=> ' ',
                    ),
                ));
                echo $form->error($property,'pr_status_date');
            ?>
            </div>
			<?php if (!$property->isNewRecord) : ?>
            <h5>History</h5>
            <div class="field">
                <?php
                $this->widget('zii.widgets.grid.CGridView', array(
                    'id' => 'gridPreRiskStatusHistory',
                    'dataProvider' => $preRiskStatusHistory,
                    'summaryCssClass' => 'hidden',
                    'emptyText' => 'No history found',
                    'columns' => array(
                        array(
                            'name' => 'date_changed', 
                            'header' => 'Date Changed', 
                            'value' => 'Yii::app()->dateFormatter->format("MM/d/y h:mm a", strtotime($data->date_changed))',
                        ),
                        array('name' => 'status', 'header' => 'Status'),
                        array(
                            'name' => 'user_id', 
                            'header' => 'By',
                            'value' => 'isset($data->user) ? $data->user->name : "System"',    
                        ),
                    )
                ));
                ?>
            </div>
			<?php endif; ?>
        </div>    
        <div class="statusHistoryColumn">
            <div>
                <?php 
                    echo $form->labelEx($property,'policy_status');
                    echo $form->dropDownList($property,'policy_status',array_combine($property->getPolicyStatuses(), $property->getPolicyStatuses()), array('onchange'=>'$("#Property_policy_status_date").val(moment().format("MM/D/YYYY h:mm A"));'));
                    echo $form->error($property,'policy_status'); 
                ?>
            </div>
            <div class="field">
            <?php 
                echo $form->labelEx($property,'policy_status_date');
                $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                'model'=>$property,
                'attribute' => 'policy_status_date',
                'options'=>array(
                    'showAnim'=>'fold',
                    'showButtonPanel'=>true,
                    'autoSize'=>true,
                    'dateFormat'=>'mm/dd/yy',
                    'timeFormat'=>'h:mm tt',
                    'ampm'=> true,
                    'separator'=> ' ',
                    ),
                ));
                echo $form->error($property,'policy_status_date');
            ?>
            </div>
			<?php if (!$property->isNewRecord) : ?>
            <h5>History</h5>
            <div class="field">
            <?php
                $this->widget('zii.widgets.grid.CGridView', array(
                    'id' => 'gridPolicyStatusHistory',
                    'dataProvider' => $policyStatusHistory,
                    'summaryCssClass' => 'hidden',
                    'emptyText' => 'No history found',
                    'columns' => array(
                        array(
                            'name' => 'date_changed', 
                            'header' => 'Date Changed', 
                            'value' => 'Yii::app()->dateFormatter->format("MM/d/y h:mm a", strtotime($data->date_changed))',
                        ),
                        array('name' => 'status', 'header' => 'Status'),
                        array(
                            'name' => 'user_id', 
                            'header' => 'By',
                            'value' => 'isset($data->user) ? $data->user->name : "System"',    
                        ),
                    )
                ));
            ?>
            </div>
			<?php endif; ?>
        </div>
		<div class="statusHistoryColumn">
			 <div class="field">
            <?php 
                echo $form->labelEx($property,'policy_effective');
                $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                'model'=>$property,
                'attribute' => 'policy_effective',
                'options'=>array(
                    'showAnim'=>'fold',
                    'showButtonPanel'=>true,
                    'autoSize'=>true,
                    'dateFormat'=>'mm/dd/yy',
                    'timeFormat'=>'h:mm tt',
                    'ampm'=> true,
                    'separator'=> ' ',
                    ),
                ));
                echo $form->error($property,'policy_effective');
            ?>
            </div>
			 <div class="field">
            <?php 
                echo $form->labelEx($property,'policy_expiration');
                $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                'model'=>$property,
                'attribute' => 'policy_expiration',
                'options'=>array(
                    'showAnim'=>'fold',
                    'showButtonPanel'=>true,
                    'autoSize'=>true,
                    'dateFormat'=>'mm/dd/yy',
                    'timeFormat'=>'h:mm tt',
                    'ampm'=> true,
                    'separator'=> ' ',
                    ),
                ));
                echo $form->error($property,'policy_expiration');
            ?>
            </div>
			 <div class="field">
            <?php 
                echo $form->labelEx($property,'transaction_effective');
                $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                'model'=>$property,
                'attribute' => 'transaction_effective',
                'options'=>array(
                    'showAnim'=>'fold',
                    'showButtonPanel'=>true,
                    'autoSize'=>true,
                    'dateFormat'=>'mm/dd/yy',
                    'timeFormat'=>'h:mm tt',
                    'ampm'=> true,
                    'separator'=> ' ',
                    ),
                ));
                echo $form->error($property,'transaction_effective');
            ?>
            </div>
			<div class="field">
				<?php 
                    echo $form->labelEx($property,'transaction_type');
                    echo $form->dropDownList($property,'transaction_type', array_combine($property->getTransactionTypes(), $property->getTransactionTypes()));
                    echo $form->error($property,'transaction_type'); 
                ?>
			</div>
            <div class="field">
				<?php 
                    echo $form->labelEx($property,'last_update');
                    echo $form->textField($property,'last_update', array('style'=>'width:200px;', 'disabled'=>'disabled'));
                    echo $form->error($property,'last_update'); 
                ?>
			</div>
            <div class="field">
                <?php
                echo $form->labelEx($property,'app_status');
                echo $form->dropDownList($property,'app_status', array_combine($property->getAppStatuses(), $property->getAppStatuses()));
                echo $form->error($property,'app_status');
                ?>
            </div>
        </div>

        <?php if (!$property->isNewRecord) : ?>

        <div class="fluidField">
            <h5>Location History</h5>
            <?php $this->widget('zii.widgets.grid.CGridView', array(
                'id' => 'gridLocationHistory',
                'dataProvider' => $locationHistory,
                'summaryCssClass' => 'hidden',
                'emptyText' => 'No location history found',
                'columns' => array('wds_geocode_level', 'wds_lat', 'wds_long', 'wds_geocoder', 'wds_match_address', 'wds_match_score', 'wds_geocode_date:date'),
            )); ?>
        </div>

        <?php endif; ?>

    </div>

    <?php if (!$property->isNewRecord) : ?>

    <div class="clearfix formContainer">
        <h3 id="property-files">Files</h3>
        <?php

        echo CHtml::link('Attach New File', array('propertiesFile/create', 'pid' => $property->pid), array('style' => 'display: block; margin-bottom: 10px;'));

        $this->widget('zii.widgets.grid.CGridView', array(
            'id' => 'gridPropertiesFile',
            'dataProvider' => $propertyFiles,
            'summaryCssClass' => 'hidden',
            'emptyText' => 'No files uploaded',
            'columns' => array(
                array(
                    'class' => 'bootstrap.widgets.TbButtonColumn',
                    'template' => '{update}{delete}',
                    'header' => 'Actions',
                    'updateButtonUrl' => '$this->grid->controller->createUrl("propertiesFile/update", array("id" => $data->id))',
                    'deleteButtonUrl' => '$this->grid->controller->createUrl("propertiesFile/delete", array("id" => $data->id))'
                ),
                array(
                    'class' => 'CLinkColumn',
                    'header' => 'Download',
                    'labelExpression' => 'isset($data->file->name) ? $data->file->name : ""',
                    'urlExpression' => 'array("/propertiesFile/download","fileID"=>$data->file_id)'
                ),
                array(
                    'name' => 'notes',
                    //'value' => '(strlen($data->notes) > 100) ? substr($data->notes, 0, 100) . "..." : $data->notes'
                )
            )
        ));

        ?>
    </div>

    <?php endif; ?>
    
	<div class="buttons">
		<?php echo CHtml::submitButton($property->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
        <span class="paddingLeft10">
            <?php
			if(!isset($readOnly) || $readOnly == false)
				echo CHtml::link('Cancel', array('property/view', 'pid'=> $property->pid)); 
			?>
        </span>
	</div>
       
    <?php $this->endWidget(); ?>

    <?php
    if(!$property->isNewRecord)
    {        
	    $fs_reports = new FSReport('search');
	    $fs_reports->unsetAttributes();
	    $fs_reports->property_pid = $property->pid;
	    $this->renderPartial('_fs_reports',array('fs_reports'=>$fs_reports));
	
	    $pre_risks = new PreRisk('search');
	    $pre_risks->unsetAttributes();
	    $pre_risks->property_pid = $property->pid;
	    $this->renderPartial('_pre_risks',array('pre_risks'=>$pre_risks));
        
        $call_attempts = new ResCallAttempt('search');
	    $call_attempts->unsetAttributes();
	    $call_attempts->property_id = $property->pid;
        $this->renderPartial('_res_call_attempts',array('call_attempts'=>$call_attempts));
    }
    ?>

    <!-- Property Access -->

    <?php if(isset($readOnly)): ?>

    <div class="clearfix formContainer">
        <div id="formPropertyAccess">
            <h3>Property Access</h3>
            <div class="statusHistoryColumn">
                <div class="fluidField radioButtonGroup paddingTop10 paddingBottom10">
                    <?php
                        echo $form->labelEx($propertyAccess, 'address_verified');
                        echo $form->radioButtonList($propertyAccess, 'address_verified', 
                            array(1 => 'Yes', 0 => 'No'),
                            array('separator' => ''));
                        echo $form->error($propertyAccess, 'address_verified');
                    ?>
                </div>
            </div>
            <div class="statusHistoryColumn">
                <div class="field">
                    <?php
                        echo $form->labelEx($propertyAccess, 'best_contact_number');
                        echo $form->textField($propertyAccess, 'best_contact_number');
                        echo $form->error($propertyAccess, 'best_contact_number');
                    ?>
                </div>
                <div class="field">
                    <?php 
                        echo $form->labelEx($propertyAccess,'date_updated');
                        $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                        'model'=>$propertyAccess,
                        'attribute' => 'date_updated',
                        'options'=>array(
                            'showAnim'=>'fold',
                            'showButtonPanel'=>true,
                            'autoSize'=>true,
                            'dateFormat'=>'mm/dd/yy',
                            'timeFormat'=>'h:mm tt',
                            'ampm'=> true,
                            'separator'=> ' ',
                            ),
                        ));
                        echo $form->error($propertyAccess,'date_updated');
                    ?>
                </div>
            </div>
            <div class="statusHistoryColumn">
                <div class="field">
                    <?php
                        echo $form->labelEx($propertyAccess, 'access_issues');
                        echo $form->textField($propertyAccess, 'access_issues');
                        echo $form->error($propertyAccess, 'access_issues');
                    ?>
                </div>
            </div>
            <div class="statusHistoryColumn">
                <div class="field">
                    <?php
                        echo $form->labelEx($propertyAccess, 'gate_code');
                        echo $form->textField($propertyAccess, 'gate_code');
                        echo $form->error($propertyAccess, 'gate_code');
                    ?>
                </div>
            </div>
            <div class="statusHistoryColumn">
                <div class="field">
                    <?php
                        echo $form->labelEx($propertyAccess, 'suppression_resources');
                        echo $form->textField($propertyAccess, 'suppression_resources');
                        echo $form->error($propertyAccess, 'suppression_resources');
                    ?>
                </div>
            </div>
            <div class="statusHistoryColumn">            
                <div class="field">
                    <label>Other Info <span class="notBold">(pets, unique home features, etc.)</span></label>
                    <?php
                        echo $form->textArea($propertyAccess, 'other_info', array('rows'=>'5'));
                        echo $form->error($propertyAccess, 'other_info');
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php endif; ?>

</div><!-- form -->