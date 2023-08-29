<?php
	if(isset($model->member_mid))
	{
		$this->breadcrumbs = array(
			'Fire Shield Users' => array('admin'),
			$model->id => array('update', 'id' => $model->id),
			'Update',
		);
		echo '<h1>Update Fire Shield User</h1>';
	}
	elseif(isset($model->agent_id))
	{
		$this->breadcrumbs = array(
			'Agent App Users' => array('admin'),
			$model->id => array('update', 'id' => $model->id),
			'Update',
		);
		echo '<h1>Update Agent App User</h1>';
	}
?>



<div class="paddingTop10">
    <div>
        <b>User ID:</b> <?php echo $model->id; ?>
    </div>
    <div>
        <b>User Created Date:</b> <?php echo $model->user_created_date; ?>
    </div>
    <div>
        <?php
		if(isset($model->member_mid))
		{
			echo CHtml::link('View Member', array('member/update', 'mid'=>$model->member_mid)); 
		}
		elseif(isset($model->agent_id))
		{
			echo CHtml::link('View Agent', array('agent/update', 'id'=>$model->agent_id)); 
		}
		?>
    </div>
</div>

<?php
    echo $this->renderPartial('_form', array('model' => $model));
?>
