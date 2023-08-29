<?php
if(isset($readOnly))
{
	Yii::app()->clientScript->registerScript('member-form-js', "

		if(".$readOnly."){
			$(':input').prop('disabled', true);
			$('input[type=submit]').hide();
		}

	", CClientScript::POS_READY);
}
?>

<div class="form marginTop10">

<?php
$form = $this->beginWidget('CActiveForm', array(
	'id'=>'member-form',
	'enableAjaxValidation'=>false,
));
?>

	<?php echo $form->errorSummary($member); ?>

    <div class="paddingTop10 paddingBottom10">
        <?php if (!$member->isNewRecord) : ?>
            <div>
                <b>Member ID:</b> <?php echo $member->mid; ?>
            </div>
            <div>
                <b>Type:</b>
                <?php if(isset($member->type->type)){echo $member->type->type;} ?>
            </div>
        <?php endif; ?>
        <div>
            <?php
                echo $form->checkBox($member,'is_tester') . ' Test Member';
            ?>
        </div>
    </div>

    <div class="clearfix formContainer">
        <h3>Member Info</h3>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'member_num');
                echo $form->textField($member,'member_num');
                echo $form->error($member,'member_num');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'rank');
                echo $form->textField($member,'rank');
                echo $form->error($member,'rank');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'client_id');
                echo $form->dropDownList($member,'client',array_combine($member->getClients(), $member->getClients()), array('empty'=>''));
                echo $form->error($member,'client_id');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'signed_ola');
                echo $form->textField($member,'signed_ola');
                echo $form->error($member,'signed_ola');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'spec_handling_code');
                echo $form->textField($member,'spec_handling_code');
                echo $form->error($member,'spec_handling_code');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'salutation');
                echo $form->textField($member,'salutation');
                echo $form->error($member,'salutation');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'first_name');
                echo $form->textField($member,'first_name');
                echo $form->error($member,'first_name');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'middle_name');
                echo $form->textField($member,'middle_name');
                echo $form->error($member,'middle_name');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'last_name');
                echo $form->textField($member,'last_name');
                echo $form->error($member,'last_name');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'home_phone');
                echo $form->textField($member,'home_phone');
                echo $form->error($member,'home_phone');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'work_phone');
                echo $form->textField($member,'work_phone');
                echo $form->error($member,'work_phone');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'cell_phone');
                echo $form->textField($member,'cell_phone');
                echo $form->error($member,'cell_phone');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'email_1');
                echo $form->textField($member,'email_1');
                echo $form->error($member,'email_1');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'email_2');
                echo $form->textField($member,'email_2');
                echo $form->error($member,'email_2');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'fs_carrier_key');
                echo $form->textField($member,'fs_carrier_key');
                echo $form->error($member,'fs_carrier_key');
            ?>
        </div>
    </div>

    <div class="clearfix formContainer">
        <h3>Mailing Address</h3>

        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'mail_address_line_1');
                echo $form->textField($member,'mail_address_line_1');
                echo $form->error($member,'mail_address_line_1');
            ?>
        </div>

        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'mail_address_line_2');
                echo $form->textField($member,'mail_address_line_2');
                echo $form->error($member,'mail_address_line_2');
            ?>
        </div>

        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'mail_city');
                echo $form->textField($member,'mail_city');
                echo $form->error($member,'mail_city');
            ?>
        </div>

        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'mail_county');
                echo $form->textField($member,'mail_county');
                echo $form->error($member,'mail_county');
            ?>
        </div>

        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'mail_state');
                echo $form->textField($member,'mail_state');
                echo $form->error($member,'mail_state');
            ?>
        </div>

        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'mail_zip');
                echo $form->textField($member,'mail_zip');
                echo $form->error($member,'mail_zip');
            ?>
        </div>

        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'mail_zip_supp');
                echo $form->textField($member,'mail_zip_supp');
                echo $form->error($member,'mail_zip_supp');
            ?>
        </div>
    </div>

    <div class="clearfix formContainer valignTop marginRight10">
        <h3>Member's Spouse</h3>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'spouse_member_num');
                echo $form->textField($member,'spouse_member_num');
                echo $form->error($member,'spouse_member_num');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'spouse_rank');
                echo $form->textField($member,'spouse_rank');
                echo $form->error($member,'spouse_rank');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'spouse_salutation');
                echo $form->textField($member,'spouse_salutation');
                echo $form->error($member,'spouse_salutation');
            ?>
        </div>
        <div></div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'spouse_first_name');
                echo $form->textField($member,'spouse_first_name');
                echo $form->error($member,'spouse_first_name');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'spouse_middle_name');
                echo $form->textField($member,'spouse_middle_name');
                echo $form->error($member,'spouse_middle_name');
            ?>
        </div>
        <div class="fluidField">
            <?php
                echo $form->labelEx($member,'spouse_last_name');
                echo $form->textField($member,'spouse_last_name');
                echo $form->error($member,'spouse_last_name');
            ?>
        </div>
    </div>
    
	<div class="clearfix formContainer marginRight10">
		<div class="statusHistoryColumn">
            <div>
                <?php 
                    echo $form->labelEx($member,'mem_fireshield_status');
                    echo $form->dropDownList($member,'mem_fireshield_status',array_combine(Property::model()->getProgramStatuses(), Property::model()->getProgramStatuses()), array('onchange'=>'$("#Member_mem_fs_status_date").val(moment().format("MM/D/YYYY h:mm A"));'));
                    echo $form->error($member,'mem_fireshield_status'); 
                ?>
            </div>
            <div class="field">
            <?php 
                echo $form->labelEx($member,'mem_fs_status_date');
                $this->widget('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker', array(
                'model'=>$member,
                'attribute' => 'mem_fs_status_date',
                'options'=>array(
                    'showAnim'=>'fold',
                    'showButtonPanel'=>true,
                    'autoSize'=>true,
                    'dateFormat'=>'mm/dd/yy',
                    'timeFormat'=>'h:mm tt',
                    'ampm'=> true,
                    'separator'=> ' ',
                    ),
                'htmlOptions'=>array(
                    'readonly' => true,
                    'style' => 'cursor:pointer',
                ),
                ));
                echo $form->error($member,'mem_fs_status_date');
            ?>
            </div>
			<?php if (!$member->isNewRecord) : ?>
            <h5>History</h5>
            <div class="paddingRight10">
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
                    )
                ));
                ?>
            </div>
			<?php endif; ?>
        </div>
	</div>

    <div class="clearfix formContainer valignTop" style="width: 200px">
        <p>
            <b>Status Override</b>
        </p>
        <p>
            Check this box to bypass all automatic member and property status logic for this member.
        </p>
        <div>
            <?php echo $form->checkBox($member, 'status_override', array('value' => 1, 'uncheckValue' => 0)) . ' Status Override'; ?>
        </div>
    </div>

	<div class="buttons">
		<?php echo CHtml::submitButton($member->isNewRecord ? 'Create' : 'Save', array('class'=>'submit')); ?>
        <span class="paddingLeft10">
            <?php 
			if(!isset($readOnly) || $readOnly == false)
				echo CHtml::link('Cancel', array('member/admin', 'mid'=>$member->mid)); 
			?>
        </span>

        <?php
            if(!$member->isNewRecord)
            {
                echo '<div class="paddingTop10" style="float: right">';
                echo CHtml::link('Print Response Program Enrollment Form', array('member/enrollPrint', 'mid'=>$member->mid));
                echo '</div>';
            }
        ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->