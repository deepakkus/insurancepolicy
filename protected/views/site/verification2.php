<div class="form">
<?php
$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id' => 'verification2-form',
	'action' => Yii::app()->createUrl('site/verification3'),
	'enableAjaxValidation' => false,
    'htmlOptions' => array('enctype' => 'multipart/form-data')
));
?>
<div class="row-fluid">
	<div class="msgBox">
		<h2>WDS Multi-Factor Authentication</h2>
		<p><?php
		if($MFAMessage2 != '')
		{
			echo $MFAMessage2;
		}
		?></p>
		<p><?php
		if($MFAMessage3 != '')
		{
			echo $MFAMessage3;
		}
		?></p>
	</div>
</div>

<?php
	echo CHtml::hiddenField('username',$username,array('class' => 'messageun'));
	echo CHtml::hiddenField('password',$password,array('class' => 'messagepw'));
	echo CHtml::hiddenField('UserGUID',$UserGUID,array('class' => 'guid'));
	//Fetch MFAtermscondition from SystemSettings 
	$SystemSettings=SystemSettings::model()->find();  
	$MFAtermscondition = $SystemSettings['MFAtermscondition'];
?>
<?php
if($MFAMessage2 != '')
{
?>
	<div class="row-fluid verification custFieldName">
		<div class="span12">
			<?php echo $form->labelEx($model,'code'); ?>
			<?php echo $form->numberField($model,'code',array('maxlength'=>10,'class' => 'in_wd_100')); ?>
		</div>
	
	</div>
	<div class="row-fluid buttons verification">
		<?php echo CHtml::submitButton('Accept', array('class'=>'zbtn-large btn-primary wid_100','onclick' =>"return validateCode();")); ?>
		<p class="or_btn"> OR </p>
		<?php echo CHtml::submitButton('No Code Received', array('class'=>'zbtn-large cdr_recbtn-info wid_100', 'name' => 'noCode')); ?>
		<p>
		<?php 
		echo CHtml::link('Terms & Conditions.',"#",
				array(
					"id"=>"termsCon"
				)
			); 
		?>
		</p>
	</div>
		<?php 
			//Terms & Conditions Modal
			$this->beginWidget('bootstrap.widgets.TbModal',
				array(
					'id' => 'termsconditionModal',
					'htmlOptions' => array('class' => 'modalSmall')
				)
			); 
		?> 
		<div class="modal-header">
			<a class="close" data-dismiss="modal">&times;</a>
			<h3>Terms & Conditions</h3>
		</div>
		<div class="modal-body">
		<div class =""><?php echo $MFAtermscondition;?></div>
        
		</div>   
		<?php $this->endWidget(); ?>
<?php
}
?>
<?php
if($MFAMessage3 != '')
{
?>
	<div class="row-fluid buttons verification">
	<?php echo CHtml::submitButton('Send Verification Code by Text Message', array('class'=>'zbtn-small cdr_recbtn-info ', 'name' => 'resendSMS')); ?>
	<p class="or_btn"> OR </p>
	<?php echo CHtml::submitButton('Send Verification Code by Email', array('class'=>'zbtn-small cdr_recbtn-info flr_wid_100', 'name' => 'resendEMAIL')); ?>
	<p>
		<?php 
		echo CHtml::link('Terms & Conditions.',"#",
				array(
					"id"=>"termsCon"
				)
			); 
		?>
		</p>
	</div>
		<?php 
			//Terms & Conditions Modal
			$this->beginWidget('bootstrap.widgets.TbModal',
				array(
					'id' => 'termsconditionModal',
					'htmlOptions' => array('class' => 'modalSmall')
				)
			); 
		?> 
		<div class="modal-header">
			<a class="close" data-dismiss="modal">&times;</a>
			<h3>Terms & Conditions</h3>
		</div>
		<div class="modal-body">
		<div class =""><?php echo $MFAtermscondition;?></div>
        
		</div>   
		<?php $this->endWidget(); ?>
<?php
}
?>
<?php $this->endWidget(); ?>

</div>
<!-- form -->
<style>
.verification{
width: 400px;
margin: 0 auto;
text-align:center;
}
.msgBox {
    width: 900px;
    margin: 0 auto;
    text-align: center;
    padding: 20px;
    border: 1px solid #bfbfbf;
    background: #fff;
    border-radius: 15px;
    margin-top: 20px;
    margin-bottom: 15px;
	-webkit-box-shadow: 5px 5px 10px -3px rgba(0,0,0,0.75);
	-moz-box-shadow: 5px 5px 10px -3px rgba(0,0,0,0.75);
	box-shadow: 5px 5px 10px -3px rgba(0,0,0,0.75);
}
.custFieldName label {
    padding-top: 9px;
    font-size: 14px!important;
}
.custFieldName input {
   height : 32px;
   float: left;
}
.or_btn{
line-height: 33px;
    background: #d49b23;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    margin: 10px auto;
    color: #fff;
    font-weight: 600;
	margin-bottom: 15px;
	}
.in_wd_100{
  width: calc(100% - 15px) !important;
}
.wid_100{
width:40% !important;
font-weight: 600;
display:inline-block;
}
.cdr_recbtn-info{
    width: 240px !important;
    font-size: 11px;
    color: #e37f14;
    border: 1px solid #e37f14;
}
</style>
<script>
$(function(){
	$('#termsCon').click(function (){
		$('#termsconditionModal').modal('show')
		.find('#termsCon')
		.load($(this).attr('href'));
	});
});
function validateCode() {
   var value = document.getElementById('LoginForm_code').value;
  
     if(value.length != 6) {
          alert("Access Code Must contain 6 Numbers.");
          $(this).focus();
		  return false;
     }
}
</script>