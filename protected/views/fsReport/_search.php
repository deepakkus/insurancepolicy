<?php

$form=$this->beginWidget('CActiveForm', array(
		'action'=>Yii::app()->createUrl($this->route),
		'method'=>'get',
));
?>
	
	<h3>Advanced Search</h3>
	<?php echo CHtml::link('Clear Advanced','#',array('class'=>'clear-advanced')); ?>
	<div class="advSearch">
		<table>
			<tr>
				<td>
				Phone Number:
				</td>
				<td>
					<?php echo CHTML::textField('advSearch[phone]', (isset($advSearch['phone']) ? $advSearch['phone'] : ''));?>
				</td>
			</tr>
			<tr>
				<td>
				Received Date Between:
				</td>
				<td>
				<?php 
				$this->widget('zii.widgets.jui.CJuiDatePicker', array(
					'model'=>$model,
					'name'=>'advSearch[recDate1]',
					'options'=>array(
						'showAnim'=>'fold',
						'dateFormat'=>'yy-mm-dd',
					),
				//	'value'=>$advSearch['recDate1'],
				));  
				?>
				and 
				<?php 
				$this->widget('zii.widgets.jui.CJuiDatePicker', array(
					'model'=>$model,
					'name'=>'advSearch[recDate2]',
					'options'=>array(
						'showAnim'=>'fold',
						'dateFormat'=>'yy-mm-dd',
					),
				//	'value'=>$advSearch['recDate2'],
				));  
				?>
				</td>
			</tr>
		</table>
	</div>

	<div align="center">
    <?php 
        echo CHtml::submitButton('Submit Search', array('name'=>'searchSubmit')); 
    ?>
    </div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->
