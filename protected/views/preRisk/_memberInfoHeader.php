<?php
/* @var $this PreRiskController */
/* @var $model PreRisk */
/* @var $form CActiveForm */

//This is used as the member info header on each of the pre risk forms, and is rendered in each of the form parts.
if(!isset($model->property, $model->property->member))
{
	echo '<div id="member-container"><h1>Error, Pre-Risk Entry is not properly tied to a Member/Property entry. Please contact IT.</h1></div>';
}
else
{
?>
    <div id ="member-container">
        <h1 class="paddingLeft10">Member Information - <?php echo $model->property->member->first_name.' '.$model->property->member->middle_name.' '.$model->property->member->last_name; ?> <?php if(!empty($model->property)){ echo "(" . $model->property->member->salutation . ")"; } ?></h1>
        <div class="cell-first">               
           <div>
               <p><strong>Member Number:</strong> <?php echo $model->property->member->member_num.CHtml::link(' - Full Member Details', array('member/view', 'mid'=>$model->property->member_mid), array('target'=>'_blank')); ?></p>
               <p><strong>Company:</strong> <?php echo $model->property->rated_company; ?> </p>
           </div>
           <div>
               <p><strong>Member Name:</strong> <?php echo $model->property->member->first_name.' '.$model->property->member->middle_name.' '.$model->property->member->last_name; ?> <?php if(!empty($model->property)){ echo "(" . $model->property->member->salutation . ")"; } ?></p>
               <p><strong>Spouse:</strong> <?php if(!empty($model->property)) { echo $model->property->member->spouse_salutation; } ?></p> 
           </div>
           <div>
               <p><strong>Property Address:</strong> <?php echo $model->property->address_line_1.' '.$model->property->address_line_2; ?>, <?php echo $model->property->city; ?>, <?php echo $model->property->state; ?> <?php echo $model->property->zip; ?> <?php echo $model->property->zip_supp; ?></p>
			   <p><strong>Property Policy#:</strong> <?php echo $model->property->policy.CHtml::link(' - Full Property Details', array('property/view', 'pid'=>$model->property_pid), array('target'=>'_blank')); ?></p>
           </div>
           <div>
               <p><strong>Week to Schedule:</strong> <?php echo $model->week_to_schedule; ?></p>
               <p><strong>Call List Year:</strong> <?php echo $model->call_list_month; ?>, <?php echo $model->call_list_year; ?> </p>
           </div>
        </div>
        <div class="cell-middle">                 
           <div>
               <p><strong>Home Phone:</strong> <?php echo preg_replace("/^(\d{3})(\d{3})(\d{4})$/", "($1) $2-$3", $model->property->member->home_phone); ?></p>
               <p><strong>Work Phone:</strong> <?php echo preg_replace("/^(\d{3})(\d{3})(\d{4})$/", "($1) $2-$3", $model->property->member->work_phone); ?></p>
               <p><strong>Cell Phone:</strong> <?php echo preg_replace("/^(\d{3})(\d{3})(\d{4})$/", "($1) $2-$3", $model->property->member->cell_phone); ?></p>
               <p><strong>Email:</strong> <?php if(!empty($model->property)){ echo $model->property->member->email_1; } ?></p>
               <p><strong>Mailing Address:</strong> <?php if(!empty($model->property)){ echo $model->property->member->mail_address_line_1 . " " . $model->property->member->mail_address_line_2 . ", " . $model->property->member->mail_city . ", " . $model->property->member->mail_state . " " . $model->property->member->mail_zip; } ?></p>
           </div>               
        </div>
        <div class="cell-last">
            <div>
                <?php if (isset($model->property)) : ?>
                <div class="statusBox">
                    <?php
                        $fsStatus = $model->property->fireshield_status;
                        if($fsStatus == 'enrolled')
                            $fsStatus = "<span class = 'fs-enrolled'>Enrolled";
                        elseif($fsStatus == 'offered')
                            $fsStatus = "<span class = 'fs-offered'>Offered";
                        elseif($fsStatus == 'ineligible')
                            $fsStatus = "<span class = 'fs-ineligible'>Ineligible";
                    ?>
                    
                    <p><strong>Program Statuses</strong></p>
                    <p><strong>FS Status:</strong> <?php echo $fsStatus . ' - ' . $model->property->fs_status_date; ?> <strong>Key:</strong> <?php echo $model->property->member->fs_carrier_key; ?></span></p>
                    <p><strong>PR Status:</strong> <?php echo $model->property->pre_risk_status . ' - ' . $model->property->pr_status_date; ?> </p>
                    <p><strong>R Status:</strong> <?php echo $model->property->response_status . ' - ' . $model->property->res_status_date; ?> </p>
					<p><Strong>Policy Status:</strong> <?php echo $model->property->policy_status . ' - ' . $model->property->policy_status_date; ?> </p>
                </div>
                <?php endif; ?>
            </div>
        </div>
		<br>
		<div>
            <?php 
			if(isset($model->property))
				$this->renderPartial('_properties',array('pre_risk'=>$model));
			?>
        </div>
        <div>
            <?php 
			if(isset($model->fs_reports))
				$this->renderPartial('_agent_reports',array('pre_risk'=>$model));
			?>
        </div>
    </div> <!-- member-container -->
<?php } //end check mem/prop exists else ?>