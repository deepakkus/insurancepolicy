<div class="form">

<?php

Assets::registerColorPicker();

Yii::app()->clientScript->registerScript('colorpickerscript','
    var colorPickerOptions = {
	    hideButton: true,
        defaultPalette: "web",
        displayIndicator: true,
        history: false,
        showOn: "focus"
    };

    $("#' . CHtml::activeId($client,'map_enrolled_color') . '").colorpicker(colorPickerOptions);
    $("#' . CHtml::activeId($client,'map_not_enrolled_color') . '").colorpicker(colorPickerOptions);
');

$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id' => 'client-form',
	'enableAjaxValidation' => false,
    'htmlOptions' => array('enctype' => 'multipart/form-data')
));

?>

    <p class="note">
        Fields with
        <span class="required">*</span>
        are required.
    </p>

    <?php echo $form->errorSummary($client); ?>

    <div class="form-section">

        <h2>General Information</h2>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'name'); ?>
            <?php
            if($client->isNewRecord)
                echo $form->textField($client,'name',array('size'=>40,'maxlength'=>40));
            else
                echo $form->textField($client,'name',array('size'=>40,'maxlength'=>40, 'readonly'=>true));
            ?>
            <?php echo $form->error($client,'name'); ?>
        </div>

        <div class="row-fluid">
            <?php
            echo $form->labelEx($client,'parent_client_id');
            echo $form->dropDownList($client,'parent_client_id', Client::model()->getClientNames(), array('empty'=>''));
            echo $form->error($client,'parent_client_id');
            ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'code'); ?>
            <?php echo $form->textField($client,'code',array('size'=>25,'maxlength'=>25)); ?>
            <?php echo $form->error($client,'code'); ?>
        </div>

        <div style="position:relative;">
            <div class="row-fluid">
                <?php echo CHtml::label('Logo Photo',CHtml::activeId($client, 'logo_id')); ?>
                <?php echo CHtml::fileField('create_logo_id','',array('accept'=>'image/*')); ?>
                <?php echo CHtml::image('images/photo-medium.png', 'List'); ?>
            </div>
            <p>
                <i>
                    (Currently: <?php echo $client->logoName; ?>)
                </i>
            </p>
            <span style="position:absolute; display:inline-block; left:450px; top:0;">
                <?php if (!empty($client->logo_id)): ?>
                <?php echo CHtml::image($this->createUrl('/file/loadThumbnail', array('id'=>$client->logo_id)), 'Client Logo'); ?>
                <?php endif; ?>
            </span>
        </div>
        <div class="row-fluid">
            <?php
            echo $form->labelEx($client,'business_entity');
            echo $form->dropDownList($client,'business_entity',array('WDS'=>'WDS','WDIS'=>'WDIS'));
            echo $form->error($client,'business_entity');
            ?>
        </div>

        <h3 style="margin-top:15px;">Services:</h3>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'wds_fire'); ?>
            <?php echo $form->checkbox($client,'wds_fire'); ?>
            <?php echo $form->error($client,'wds_fire'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'wds_risk'); ?>
            <?php echo $form->checkbox($client,'wds_risk'); ?>
            <?php echo $form->error($client,'wds_risk'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'wds_pro'); ?>
            <?php echo $form->checkbox($client,'wds_pro'); ?>
            <?php echo $form->error($client,'wds_pro'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'wds_education'); ?>
            <?php echo $form->checkbox($client,'wds_education'); ?>
            <?php echo $form->error($client,'wds_education'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'api'); ?>
            <?php echo $form->checkBox($client,'api');  ?>
            <?php echo $form->error($client,'api'); ?>
        </div>

        <div class="row-fluid">
            <?php
            echo $form->labelEx($client,'property_access_days');
            echo $form->textField($client,'property_access_days',array('max'=>366, 'min'=>1));
            echo $form->error($client,'property_access_days');
            ?>
        </div>
    </div>

    <div class="form-section">

        <h2>Response Program</h2>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'response_program_name'); ?>
            <?php echo $form->textField($client,'response_program_name',array('size'=>100,'maxlength'=>100, 'style'=>'width:400px')); ?>
            <?php echo $form->error($client,'response_program_name'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'response_disclaimer'); ?>
            <?php echo $form->textArea($client,'response_disclaimer',array('rows'=>3, 'cols'=>50, 'maxlength'=>700, 'style'=>'width:400px')); ?>
            <?php echo $form->error($client,'response_disclaimer'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'analytics'); ?>
            <?php echo $form->textField($client,'analytics'); ?>
            <?php echo $form->error($client,'analytics'); ?>
        </div>


        <div class="row-fluid">
            <?php echo $form->labelEx($client,'policyholder_label'); ?>
            <?php echo $form->textField($client,'policyholder_label',array('size'=>100,'maxlength'=>50, 'style'=>'width:400px')); ?>
            <?php echo $form->error($client,'policyholder_label'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'enrolled_label'); ?>
            <?php echo $form->textField($client,'enrolled_label',array('size'=>100,'maxlength'=>50, 'style'=>'width:400px')); ?>
            <?php echo $form->error($client,'enrolled_label'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'not_enrolled_label'); ?>
            <?php echo $form->textField($client,'not_enrolled_label',array('size'=>100,'maxlength'=>50, 'style'=>'width:400px')); ?>
            <?php echo $form->error($client,'not_enrolled_label'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'mapbox_layer_id'); ?>
            <?php echo $form->textField($client,'mapbox_layer_id',array('size'=>100,'maxlength'=>50, 'style'=>'width:400px')); ?>
            <?php echo $form->error($client,'mapbox_layer_id'); ?>
        </div>

        <div class="row-fluid">
            <?php /*echo $form->colorpickerRow($client, 'map_enrolled_color', array(
                'prepend' => '<div style="background-color: ' . $client->map_enrolled_color . '; width: 100%; height: 100%" id="color-thumbnail"></div>',
                'placeholder' => 'Pick as color',
                'style' => 'margin: auto;',
                'format' => 'hex',
                'events' => array(
                    'changeColor' => new CJavaScriptExpression('
                        function(event) {
                            $("#color-thumbnail").css("backgroundColor", event.color.toHex());
                        }
                    ')
                )
            ));*/ ?>
		    <?php echo $form->labelEx($client,'map_enrolled_color'); ?>
            <?php echo $form->textField($client,'map_enrolled_color'); ?>
		    <?php echo $form->error($client,'map_enrolled_color'); ?>
	    </div>

        <div class="row-fluid">
		    <?php echo $form->labelEx($client,'map_not_enrolled_color'); ?>
            <?php echo $form->textField($client,'map_not_enrolled_color'); ?>
		    <?php echo $form->error($client,'map_not_enrolled_color'); ?>
	    </div>

        <div class="row-fluid">
		    <?php echo $form->labelEx($client,'noteworthy_distance'); ?>
            <?php echo $form->numberField($client,'noteworthy_distance'); ?>
		    <?php echo $form->error($client,'noteworthy_distance'); ?>
	    </div>

        <div class="row-fluid">
		    <?php echo $form->labelEx($client,'call_list'); ?>
            <?php echo $form->checkBox($client,'call_list');  ?>
            <?php echo $form->error($client,'call_list'); ?>
        </div>

        <div class="row-fluid">
		    <?php echo $form->labelEx($client,'client_call_list'); ?>
            <?php echo $form->checkBox($client,'client_call_list');  ?>
		    <?php echo $form->error($client,'client_call_list'); ?>
	    </div>

        <div class="row-fluid">
		    <?php echo $form->labelEx($client,'dedicated'); ?>
            <?php echo $form->checkBox($client,'dedicated');  ?>
            <?php echo $form->error($client,'dedicated'); ?>
        </div>

        <div class="row-fluid">
		    <?php echo $form->labelEx($client,'unmatched'); ?>
            <?php echo $form->checkBox($client,'unmatched');  ?>
		    <?php echo $form->error($client,'unmatched'); ?>
	    </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'enrollment'); ?>
            <?php echo $form->checkBox($client,'enrollment');  ?>
            <?php echo $form->error($client,'enrollment'); ?>
        </div>

        <b>Response States</b>
        <div class="rows-fluid">
            <div class="compactRadioGroup" style="width:600px;">
                <ul style="columns: 4; -webkit-columns: 4; -moz-columns: 4;">
                <?php
                $stateIDs = array_map(function($model) { return $model->state_id; }, $clientStates);
                $data = CHtml::listData(GeogStates::model()->findAll(array('select' => 'id, abbr')), 'id', 'abbr');
                echo CHtml::checkBoxList('ClientStates[state_id]', $stateIDs, $data, array( 'template' => '<li>{input} {label}</li>' ));
                echo CHtml::hiddenField('ClientStates[placeholder]');
                ?>
                </ul>
            </div>
        </div>

    </div>

    <div class="form-section">

        <h2>WDS PRO - Fireshield</h2>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'welcome_screen_url'); ?>
            <?php echo $form->textField($client,'welcome_screen_url',array('size'=>100,'maxlength'=>256, 'style'=>'width:400px')); ?>
            <?php echo $form->error($client,'welcome_screen_url'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'report_type'); ?>
            <?php echo $form->dropDownList($client, 'report_type', FSReport::model()->getTypes(), array('empty'=>'')); ?>
            <?php echo $form->error($client,'report_type'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'fs_default_download_types'); ?>
            <?php echo $form->dropDownList($client, 'fs_default_download_types', FSReport::model()->getDownloadTypes(), array('empty'=>'')); ?>
            <?php echo $form->error($client,'fs_default_download_types'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'fs_default_email_download_types'); ?>
            <?php echo $form->dropDownList($client, 'fs_default_email_download_types', FSReport::model()->getDownloadTypes(), array('empty'=>'')); ?>
            <?php echo $form->error($client,'fs_default_email_download_types'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'report_logo_url'); ?>
            <?php echo $form->textField($client,'report_logo_url',array('size'=>100,'maxlength'=>256, 'style'=>'width:400px')); ?>
            <?php echo $form->error($client,'report_logo_url'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'report_stamp_1'); ?>
            <?php echo $form->textArea($client,'report_stamp_1',array('rows'=>3, 'style'=>'width:100%')); ?>
            <?php echo $form->error($client,'report_stamp_1'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'report_stamp_2'); ?>
            <?php echo $form->textArea($client,'report_stamp_2',array('rows'=>3, 'cols'=>50, 'maxlength'=>256, 'style'=>'width:400px')); ?>
            <?php echo $form->error($client,'report_stamp_2'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'photos_question_num'); ?>
            <?php echo $form->textField($client,'photos_question_num',array('size'=>25,'maxlength'=>25)); ?>
            <?php echo $form->error($client,'photos_question_num'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'fra_report_threshold'); ?>
            <?php echo $form->textField($client,'fra_report_threshold',array('size'=>25,'maxlength'=>25)); ?>
            <?php echo $form->error($client,'fra_report_threshold'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'risk_multiplier'); ?>
            <?php echo $form->textField($client,'risk_multiplier',array('size'=>25,'maxlength'=>25)); ?>
            <?php echo $form->error($client,'risk_multiplier'); ?>
        </div>

        <div class="row-fluid">
            <?php echo $form->labelEx($client,'no_scoring'); ?>
            <?php echo $form->checkBox($client,'no_scoring'); ?>
            <?php echo $form->error($client,'no_scoring'); ?>
        </div>
    </div>

    <div class="row-fluid">
        <?php
		echo $form->labelEx($client,'report_options');
		echo '<b>NOTE:</b> Needs to be a JSON object. You can use the following template variables:<br>';
        echo '~member_name~, ~member_num~, ~property_address~, ~property_city~, ~property_state~, ~property_zip~, ~property_policy_num~, ~property_producer~, ~agent_name~, ~report_date~, ~pre_risk_ha_date~ <br>';
        echo 'For example: <pre>';
		echo htmlspecialchars('
{
  "app2-edu-stamp":"<br><b>Policyholder:</b> ~member_name~<br><br><b>Address:</b><br> ~property_address~<br>~property_city~, ~property_state~ ~property_zip~<br><br><b>Policy #:</b> ~property_policy_num~<br><b>Agent:</b> ~agent_name~<br><b>Date:</b> ~report_date~<br><b>Report Type:</b> Education<br>",
  "app2-uw-stamp":"<br><b>Policyholder:</b> ~member_name~<br><br><b>Address:</b> ~property_address~<br><br>~property_city~, ~property_state~ ~property_zip~<br><br><b>Policy:</b> ~property_policy_num~<br><br><b>Agent:</b> ~agent_name~<br><br><b>Date:</b> ~report_date~",
  "app2-uw-logo":"/images/client_logo.png",
  "app2-edu-logo":"/images/client_edu_logo.png",
  "app2-edu-footer-text":"<b>This is a service made available to you by you Insurance Carrier.</b><br>  For further information, please feel free to contact: <br>Wildfire Defense Systems, Inc.   |   (877) 323-4730 ha@wildfire-defense.com "
  "app2-uw-footer-text":"For further information, please feel free to contact: <br>Wildfire Defense Systems, Inc.   |   (877) 323-4730 ha@wildfire-defense.com "
}');
        echo '</pre>';
		echo $form->textArea($client,'report_options',array('style'=>'width:90%', 'rows'=>10));
		echo $form->error($client,'report_options');
        ?>
    </div>

    <div class="row-fluid">
        <?php
		echo $form->labelEx($client,'report_los_structure');
//        echo '<b>NOTE:</b> Needs to be a JSON object of this format (example): <br>';
//        echo '<pre>
//[
//    {"type":"geo", "label":"Low Geographic Threat", "start_value":0, "end_value":3},
//    {"type":"geo", "label":"Moderate Geographic Threat", "start_value":4, "end_value":6},
//    {"type":"geo", "label":"High Geographic Threat", "start_value":7, "end_value":9},
//    {"type":"site", "label":"Low Structure Threat", "start_value":0, "end_value":6},
//    {"type":"site", "label":"Moderate Structure Threat", "start_value":7, "end_value":18},
//    {"type":"site", "label":"High Structure Threat", "start_value":19, "end_value":90},
//    {"type":"site", "label":"Extreme Structure Threat", "start_value":91, "end_value":134},
//    {"type":"los", "label":"Low Threat", "start_value":0, "end_value":8},
//    {"type":"los", "label":"Moderate Threat","start_value":9, "end_value":25},
//    {"type":"los", "label":"High Threat", "start_value":26, "end_value":66},
//    {"type":"los", "label":"Extreme Threat", "start_value":67, "end_value":100}
//]
//</pre>
//';
		echo $form->textArea($client,'report_los_structure',array('maxlength'=>2000,  'style'=>'width:400px'));
		echo $form->error($client,'report_los_structure');
        ?>
    </div>

    <div class="row-fluid">
        <?php
		echo $form->labelEx($client,'app_contexts');
		echo '<b>NOTE:</b> Needs to be a JSON object array.<br>';
        echo 'For example: <pre>';
		echo htmlspecialchars('
[
    {"name":"wdspro.icloud"},
    {"name":"wdspro.android"}
]');
        echo '</pre>';
		echo $form->textArea($client,'app_contexts',array('maxlength'=>2000, 'style'=>'width:400px'));
		echo $form->error($client,'app_contexts');
        ?>
    </div>

    <div class="row-fluid">
        <?php echo $form->labelEx($client,'active'); ?>
        <?php echo $form->checkbox($client,'active'); ?>
        <?php echo $form->error($client,'active'); ?>
    </div>

	<div class="row-fluid buttons">
		<?php echo CHtml::submitButton($client->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
	</div>

    <?php $this->endWidget(); ?>

</div>
<!-- form -->

