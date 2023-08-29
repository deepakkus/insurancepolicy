<?php

/* @var $this ResPhActionTypeController */
/* @var $data ResPhActionCategory */
/* @var $model ResPhActionType */

?>

<div class="row-fluid">
    <div class="span12">
        <div class="category-section">
            <p class="lead">
                <a class="btn btn-primary" title="Update Category" href="<?php echo $this->createUrl('resPhActionCategory/update', array('id' => $data->id)); ?>">
                    <i class="icon-pencil icon-white"></i>
                </a> Category: <?php echo $data->category; ?>
            </p>
            <hr style="border-color: powderblue;" />
            <div class="row-fluid">
                <div class="span12">
                    <div class="marginBottom10">
                        <a title="Update Action Type" href="<?php echo $this->createUrl('resPhActionType/create', array('id' => $data->id)); ?>">Create New Type</a>
                    </div>
                    <?php

                    $this->widget('bootstrap.widgets.TbExtendedGridView', array(
                        'id' => 'res-ph-action-grid-' . $data->id,
                        'dataProvider' => $model->search($data->id),
                        'filter' => $model,
                        'summaryCssClass' => 'hidden',
                        'emptyText' => 'No action types found',
                        'ajaxUrl' => array('resPhActionType/manageSearch', 'id' => $data->id),
                        'columns' => array(
                            array(
                                'class' => 'bootstrap.widgets.TbButtonColumn',
                                'template' => '{update}',
                                'header' => 'Update'
                            ),
                            'name',
                            array(
                                'name' => 'action_type',
                                'filter' => CHtml::activeDropDownList($model, 'action_type', array('Physical' => 'Physical', 'Recon' => 'Recon'), array('empty' => ''))
                            ),
                            array(
                                'name' => 'definition',
                                'filter' => false,
                                'sortable' => false
                            ),
                            array(
                                'name' => 'active',
                                'type' => 'boolean',
                                'filter' => CHtml::activeDropDownList($model, 'active', array('0' => 'No', '1' => 'Yes'), array('empty' => ''))
                            ),
                            'units',
                            array(
                                'name' => 'app_sub_category',
                                'filter' => CHtml::activeDropDownList($model, 'app_sub_category', $model->getSubCategories(), array('empty' => ''))
                            ),
                            'action_item_order'
                        )
                    ));

                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
