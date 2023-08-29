<div class="form">
<?php
$form = $this->beginWidget('bootstrap.widgets.TbActiveForm', array(
	'id' => 'verification1-form',
	'action' => Yii::app()->createUrl('site/verification2'),
	'enableAjaxValidation' => false,
    'htmlOptions' => array('enctype' => 'multipart/form-data')
));
?>
<div class="row-fluid">  
	<div class="msgBox">
	<h2>WDS Multi-Factor Authentication</h2>
	<p><?php echo $MFAMessage1;?></p>
	</div>
</div>
	<div class="verification">
        <div class="row-fluid">
			<div class="span3 custFieldName textAlignRight">
				<?php echo $form->labelEx($model,'phoneNo'); ?>
			</div>
			<div class="span3 textAlignleft">
				<select name="countryCode" id="countryCode">
				<option data-countryCode="US" value="1">USA (+1)</option>		
				<option data-countryCode="CA" value="1">Canada (+1)</option>
				<option data-countryCode="IN" value="91">India (+91)</option>
				<option data-countryCode="CZ" value="420">Czech Republic (+420)</option>
				</select>
			</div>
			<div class="span6">
				<?php echo $form->numberField($model,'phoneNo'); ?>
				<?php echo $form->error($model,'phoneNo'); ?>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span3 custFieldName textAlignRight">
				<?php echo $form->labelEx($model,'email'); ?>
			</div>
			<div class="span7 custFieldName textAlignleft">
				<?php echo $form->textField($model,'email'); ?>
				<?php echo $form->error($model,'email'); ?>
			</div>
		</div>
		<p> <input type="checkbox" name="termsConditions" id="termsConditions">  Yes, I understand and agree to the
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
	  echo CHtml::hiddenField('username',$username,array('class' => 'messageun'));
	  echo CHtml::hiddenField('password',$password,array('class' => 'messagepw'));
	  echo CHtml::hiddenField('UserGUID',$UserGUID,array('class' => 'guid'));

 		//Fetch MFAtermscondition from SystemSettings 
		$SystemSettings=SystemSettings::model()->find();  
		$MFAtermscondition = $SystemSettings['MFAtermscondition'];
				 
	?>

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
	<div class="row-fluid buttons verification">
		<?php echo CHtml::submitButton('Save', array('class'=>'btn-large btn-primary', 'id'=>'btnSubmit', 'onclick' =>"return validateCredentials();")); ?>
	</div>
	
    <?php $this->endWidget(); ?>

</div>
<!-- form -->
<style>
.verification{
width: 550px;
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
#countryCode{
	width: 130px
}
.custFieldName label {
    padding-top: 9px;
    font-size: 13px!important;
}
.textAlignRight{
	text-align:right;
}
.textAlignleft{
	text-align:left;
}
.form input[type='text'] {
    width: 347px;
}
</style>
<script>
$(function(){
	$('#btnSubmit').attr("disabled", true);
	
	$(":checkbox").change(function(e) {
    if(this.checked){
		$('#btnSubmit').attr("disabled", false);
    }else{
		$('#btnSubmit').attr("disabled", true);
	}
	}).change();

	$('#termsCon').click(function (){
		$('#termsconditionModal').modal('show')
		.find('#termsCon')
		.load($(this).attr('href'));
	});
});
function validateCredentials() {
   var phoneNo = document.getElementById('LoginForm_phoneNo').value;
   var email = document.getElementById('LoginForm_email');
   var mailFormat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;

     if(phoneNo.length != 10) {
          alert("Phone number must contain 10 digits.");
          $(this).focus();
		  return false;
     }
	else if(email.value == '')
	{
		alert("Please enter an email address!");
		$(this).focus();
		return false;
	}
	else if(email.value.match(mailFormat)== null)
	{
		alert("You have entered an invalid email address!");
		$(this).focus();
		return false;
	}
}
</script>