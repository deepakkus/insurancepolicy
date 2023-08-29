<?php

	$categories = ResPhActionCategory::model()->findAll(array('order' => 'category ASC'));
	$currentActions = ResPhAction::model()->findAllByAttributes(array('visit_id' => $model->id), array('select' => 'id, action_type_id, qty'));
	$currentActionIDs = array_map(function($model) { return $model->action_type_id; }, $currentActions);

	// Create $quantities array for quantities with respect to  action type id;
	$quantities = array();
	foreach($currentActions as $action)
	{
		$quantities[$action->action_type_id] = $action->qty;
	}

?>

<?php foreach (array_chunk($categories, 4, true) as $categoryArray): ?>

    <div class="row-fluid">

    <?php foreach ($categoryArray as $category): ?>

        <div class="span3">
			<h3><?php echo $category->category; ?></h3>

			<?php $types = ResPhActionType::model()->with('phActions')->findAllByAttributes(array('category_id' => $category->id, 'active' => true), array('order' => 'name ASC')); ?>

			<div class="control-group">
				<div class="controls" style="margin-left: 0;">
					<span id="ResPhActions_Post_Fire_Services">

						<?php foreach($types as $type): ?>

							<div class="checkbox checkbox-padding">
								<?php echo CHtml::checkBox('ResPhActions[]',in_array($type->id,$currentActionIDs) ? 'checked': '',array('value'=>$type->id,'id'=>$type->id)); ?>
								<?php echo $type->name; ?>
				

								<?php if(!empty($type->definition)): ?>
            
									 <a data-toggle="tooltip" title="<?php echo $type->definition; ?>"><i class="icon icon-info-sign" id="defination-ico"></i></a>

								<?php endif; ?>

								<?php if($type->units != ''): ?>

									&nbsp;<?php echo CHtml::numberField('ResPhActionTypeQty['.$type->id.']', array_key_exists($type->id,$quantities) ? $quantities[$type->id]: '', array(
                                        'style' => 'width:15%',
                                        'max' => '999',
                                        'step' => '0.1'
                                    )); ?>
									<b><?php echo $type->units; ?></b>

								<?php endif; ?>

							</div>

						<?php endforeach; ?>

					</span>
				</div>
			</div>
		</div>

    <?php endforeach; ?>

	</div>

<?php endforeach; ?> 
    
