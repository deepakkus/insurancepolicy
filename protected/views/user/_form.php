<?php
/* @var $this UserController */
/* @var $model User */
/* @var $form CActiveForm */

Yii::app()->clientScript->registerScriptFile('/js/user/form.js',CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile('/js/user/formAffiliationToggle.js',CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile('/css/user/form.css');

?>

<div class="form">

    <?php
    
    $form=$this->beginWidget('CActiveForm', array(
        'id' => 'user-form',
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true
        )
    ));

    CHtml::$renderSpecialAttributesValue = false;

    ?>

    <br>
    <h2>General Account Info</h2>
    
	<?php echo $form->errorSummary($model); ?>
        <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'name'); ?>
		<?php echo $form->textField($model,'name',array('size'=>50,'maxlength'=>50)); ?>
		<?php echo $form->error($model,'name'); ?>
	</div>
        
    <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'username'); ?>
		<?php echo $form->textField($model,'username',array('size'=>50,'maxlength'=>50)); ?>
		<?php echo $form->error($model,'username'); ?>
	</div>

    <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'password'); ?>
		<?php echo $form->passwordField($model,'password',array('size'=>32,'maxlength'=>32))." Leave Blank to keep the same"; ?>
		<?php echo $form->error($model,'password'); ?>
   </div>
	
    <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'email'); ?>
		<?php echo $form->textField($model,'email',array('size'=>50,'maxlength'=>50)); ?>
		<?php echo $form->error($model,'email'); ?>
	</div>	

    <?php if (!$model->isNewRecord){ ?>    
    <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'MFACountryCode'); 
			  echo $form->dropDownList($model,'country_id',
			  CHtml::listData(CountryCode::model()->findAll(array("condition"=>"sms_enabled =  1", "order" => "country_name")),'id','countrywithcode'),
			  array('empty'=>'Select Country Code'));    
		?>        
		<?php echo $form->error($model,'MFACountryCode'); ?>
    </div> 
    
    <div class="row-fluid" style ="padding-top:10px;">
        <?php echo $form->labelEx($model,'MFAPhoneNumber'); ?>
        <?php echo $form->textField($model,'MFAPhoneNumber',array('size'=>20,'maxlength'=>12,'placeholder'=>'xxxxxxxxxx','pattern'=>'\d{10}', 'oninvalid'=>'this.setCustomValidity("Please Enter A Valid Phone Number")', 'oninput'=>'this.setCustomValidity("")', 'onkeyup'=>'countrycodevalidate(this)')); ?>
        <?php echo $form->error($model,'MFAPhoneNumber'); ?>
    </div>   
    
    <div class="row-fluid" style ="padding-top:10px;">
        <?php echo $form->labelEx($model,'MFAEmail'); ?>
        <?php echo $form->textField($model,'MFAEmail',array('size'=>100,'maxlength'=>100, 'pattern'=>'[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$', 'oninvalid'=>'this.setCustomValidity("Please Enter A Valid Email Address")', 'oninput'=>'this.setCustomValidity("")')); ?>
        <?php echo $form->error($model,'MFAEmail'); ?>
    </div>

    <div class="row-fluid" style ="padding-top:10px;">
        <?php echo $form->labelEx($model,'MFAMethodDefault'); ?>
        <?php echo $form->dropDownList($model,'MFAMethodDefault', array('1'=>'SMS', '0'=>'Email')); ?>
        <?php echo $form->error($model,'MFAMethodDefault'); ?>
    </div>   
    <?php } ?>
    
    <br>

    <h2>User Affiliation</h2>
    <label for="Affiliation">Affiliation <span class="required">*</span></label>
    <div class="row-fluid" style ="padding-top:10px;">
		<?php  echo CHtml::radioButtonList('affiliation', '', array('wds'=> 'WDS Staff' ,'alliance' => 'Alliance', 'client' => 'Client'), array(
            'labelOptions' => array('style'=>'display:inline; padding-right:25px;'),
            'separator'=>' ',
            'required' => true
        )); ?>
	</div>

    <div class="row-fluid" style ="padding-top:10px;">
        <div class="fluidField">
		    <?php 
            echo $form->labelEx($model,'client_id');
            echo CHtml::activeDropDownList($model, 'client_id', CHtml::listData(Client::model()->findAll(array(
                'select' => array('id', 'name'),
                'order' => 'name ASC'
            )), 'id', 'name'), array('prompt'=>''));
            echo $form->error($model,'client_id'); 
            ?>
        </div>
        <div class="fluidField">
            <label for="User_Clients">User Clients</label>
            <?php
            $options = array();
            $selectedOptions = array();
            
            if(isset($model->client_id))
                $clients = Client::model()->findAll((array('condition' => 'parent_client_id = '.$model->client_id. ' OR id = '.$model->client_id, 'select' => array('id','name'))));
            else
                $clients = Client::model()->findAll((array('select' => array('id','name'))));
            
            $selectedClientIDs = $model->getClientIDs();
            foreach($clients as $client)
            {
                $options[$client->id] = $client->name;
                if(in_array($client->id, $selectedClientIDs))
                {
                    $selectedOptions[$client->id] = array('selected'=>"selected");
                }
            }
            
            //some data for use by javascript to update the user_clients options based on selected parent
            $parentChildClientJSONData = json_encode(Client::model()->getFullParentChildClientArray());
            
            $htmlOptions = array(
                'options' => $selectedOptions,
                'multiple' => 'multiple',
                'size' => '5',
                'data-parent-child-json' => $parentChildClientJSONData,
            );

            echo CHtml::activeDropDownList($model, 'user_clients', $options, $htmlOptions);
            echo $form->error($model,'user_clients');
            ?>
        </div>
	</div>

    <div class="row-fluid" style ="padding-top:10px;">
		<?php 
            echo $form->labelEx($model,'alliance_id');
            echo CHtml::activeDropDownList($model, 'alliance_id', CHtml::listData(Alliance::model()->findAll(), 'id', 'name'), array('prompt'=>''));
            echo $form->error($model,'alliance_id'); 
        ?>
	</div>

    <h2>User Permissions</h2>

    <div class="row-fluid" style ="padding-top:10px;">
		<?php 
            echo $form->labelEx($model,'type');
            $selected = array();
            foreach($model->getSelectedTypes() as $type)
                $selected[$type] = array('selected'=>'selected');
            echo CHtml::activeDropDownList($model, 'type', User::getTypes(), array('id'=>'type-select', 'multiple'=>'multiple', 'size'=>'15', 'options'=>$selected,'required' => true))." Hold down CTRL to select multiple types";
            echo $form->error($model,'type'); 
        ?>
	</div>

    <div class="row-fluid" style="padding-top:10px;">
        <?php

        $userRoles = Yii::app()->authManager->getRoles($model->id);
        $selectedRoles = array_keys($userRoles);
        $roles = Yii::app()->authManager->getRoles();
        echo '<label>Roles:</label>';
        echo CHtml::checkBoxList('user_roles', $selectedRoles, CHtml::listData($roles, 'name', 'name'), array('container'=>'div'));
        ?>
    </div>
    
    <div class="row-fluid" style ="padding-top:10px;">
        <label for="User_Geo">User Geo Location (optional - used to filter email notifications for response dashboards)</label>
        <?php 
            $selected = array();
            foreach($model->user_geo as $geo)
                $selected[$geo->geo_location] = array('selected'=>'selected');
            
            echo CHtml::activeDropDownList($model, 'user_geo', User::model()->getGeoLocations(), array('id'=>'user_geo-select', 'multiple'=>'multiple', 'size'=>'15', 'options'=>$selected))." Hold down CTRL to select multiple types";
            echo $form->error($model,'user_geo');
        ?>
	</div>
    
    <br>
    <h2>Account Expiration and Locking</h2>

    <div class="row-fluid">
		<?php echo $form->labelEx($model,'active'); ?>
        <?php echo $form->checkbox($model,'active'); ?>
		<?php echo $form->error($model,'active'); ?>
	</div>
    <div class="row-fluid">
        <?php echo $form->labelEx($model,'removed'); ?>
        <?php echo $form->checkbox($model,'removed'); ?>
        <?php echo $form->error($model,'removed'); ?>
    </div>
    <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'pw_exp'); ?>
		<?php
            if(!isset($model->pw_exp))
                $model->pw_exp = date('Y-m-d', strtotime('+ 90 days'));
            $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                'model' => $model,
                'attribute' => 'pw_exp',
                'options' => array(
                    'showAnim' => 'fold',
                    'showButtonPanel' => true,
                    'autoSize' => true,
                    'dateFormat' => 'mm/dd/yy',
                    'timeFormat' => 'h:mm TT',
                    'ampm' => true,
                    'separator' => ' ',
                    'defaultValue' => null,
                ),
            ));
		?>
		<?php echo $form->error($model,'pw_exp'); ?>
	 </div>
	
    <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'locked_until'); ?>
		<?php
				$this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
					'model' => $model,
					'attribute' => 'locked_until',
					'options' => array(
						'showAnim' => 'fold',
						'showButtonPanel' => true,
						'autoSize' => true,
						'dateFormat' => 'mm/dd/yy',
						'timeFormat' => 'h:mm TT',
						'ampm' => true,
						'separator' => ' ',
						'defaultValue' => null,
					),
				));
		?>
		<?php echo $form->error($model,'locked_until'); ?>
	 </div>
	
    <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'login_attempts'); ?>
		<?php echo $form->textField($model,'login_attempts',array('size'=>10,'maxlength'=>5)); ?>
		<?php echo $form->error($model,'login_attempts'); ?>
	</div>
    
    <div class="row-fluid" style ="padding-top:10px;">
		<?php echo $form->labelEx($model,'user_expire'); ?>
		<?php
            $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                'model' => $model,
                'attribute' => 'user_expire',
                'options' => array(
                    'showAnim' => 'fold',
                    'showButtonPanel' => true,
                    'autoSize' => true,
                    'dateFormat' => 'mm/dd/yy',
                    'timeFormat' => 'h:mm TT',
                    'ampm' => true,
                    'separator' => ' ',
                    'defaultValue' => null,
                ),
            ));
		?>
		<?php echo $form->error($model,'user_expire'); ?>
	 </div>

	<div class="row-fluid buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
	</div>


<?php $this->endWidget(); ?>

</div><!-- form -->

<script type="text/javascript">

    $('document').ready(function () {

        <?php if (!$model->isNewRecord): ?>

        selectAffiliate();
        checkForDeactivate();

        <?php else: ?>

        $('#User_client_id').prop("disabled", true);
        $('#User_alliance_id').prop("disabled", true);

        <?php endif; ?>

        <?php if (!$model->isNewRecord){ if($model->MFAPhoneNumber != ''){?>

        document.getElementById("User_MFACountryCode").setAttribute("required",true);

        <?php } }?>
    });

    window.countrycodevalidate = function(numele){
        var ele=document.getElementById("User_MFACountryCode"); 
        if(numele.value=="")
            ele.removeAttribute("required"); 
        else
            ele.setAttribute("required",true);
    }

</script>
