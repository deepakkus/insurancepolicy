<div class="row-fluid" style="margin-top:20px;">
    <div class="span4">
        <?php $this->widget('zii.widgets.CDetailView', array(
            'data' => ($visitmodel->isNewRecord) ? Property::model()->findByPk($pid) : $visitmodel->property,
            'htmlOptions' => array(
                'class' => 'table table-bordered',
                'style' => 'border-collapse: collapse;'
            ),
            'itemTemplate' => '<tr><td style="font-weight: 600;">{label}</th><td>{value}</td></tr>',
            'nullDisplay' => '<span style="color:red"><i>Not Set</i></span>',
            'attributes' => array(
                array(
                    'label' => 'Name',
                    'value' => function($data) { return isset($data->member) ? $data->member->first_name . ' ' . $data->member->last_name : ''; }
                ),
                array(
                    'name' => 'address_line_1',
                    'label' => 'Address'
                ),
                array(
                    'label' => 'City/State',
                    'value' => function($data) { return $data->city . ', ' . $data->state; }
                ),
                'policy'
            )
        )); ?>
    </div>
</div>

<div class="form-section" style="margin-bottom:300px;">
    <div class="row-fluid">
        <div class="span6">

            <!-- Photo Grid -->

            <div>
                <div>
                    <i class="icon-picture"></i>
                    <span class="heading">Photos</span> 
                    <a href="javascript:void(0);" id="lnkAddNewPhoto" class="paddingLeft10">Add new Photo</a>
                </div>

                <?php

                    $dataProvider = ResPhPhotos::model()->search($visitmodel->id);
                    $this->beginWidget('CActiveForm', array(
                                    'id' => 'existing-res-photos-form',
                                    'method'=>'post',
                                    'htmlOptions' => array('enctype' => 'multipart/form-data')
                                ));
                    $this->widget('bootstrap.widgets.TbExtendedGridView',
                        array (
                            'id' => 'gridPhotos',
                            'cssFile' => Yii::app()->baseUrl . '/css/wdsExtendedGridView.css',
                            'type' => 'striped bordered condensed',
                            'dataProvider' => $dataProvider,
                            'template' => '{items}',
                            'columns' => array(
                                array( // This ID field is used for selecting the active row
                                    'name' => 'id',
                                    'headerHtmlOptions' => array('style' => 'display:none'),
                                    'htmlOptions' => array('style' => 'display:none')
                                ),
                                array( // The delete action for this is handled via ajax in js/resPolicyholderActions/update.js
                                    'class'=>'bootstrap.widgets.TbButtonColumn',
                                    'template' => '{delete}{update}',
                                    'deleteButtonLabel' => 'Delete Photo',
                                    'updateButtonLabel' => 'Edit Photo',
                                    'deleteButtonUrl' => '$this->grid->controller->createUrl("resPhVisit/deletePhoto", array("id" => $data->visit_id, "photoID" => $data->id))',
                                    'updateButtonUrl' => '$this->grid->controller->createUrl("file/edit", array("id" => $data->file_id, "url" => Yii::app()->request->requestUri))',
                                ),
                                array( // The original photo name with notes field
                                    'name' => 'Photo',
                                    'value' => function($data) {
                                            $return = 'Filename ' . CHtml::textField('ExistingResPhPhotos['.$data->id.'][name]',$data->file->name,array('style'=>'width:60%;margin-bottom:3%')) . '<br>';
                                            $return .= 'Notes '.CHtml::textField('ExistingResPhPhotos['.$data->id.'][notes]',$data->notes,array('style'=>'width:60%;margin-left:8%'));
                                            return $return;
                                        },
                                    'type' => 'raw',
                                ),
                                array(
                                    'name' => 'Image',
                                    'type' => 'html',
                                    'value' => function($data) {
                                        return CHtml::image($this->createUrl('file/loadThumbnail', array('id' => $data->file_id)), 'policyholder image', array('height' => '100px'));
                                    }
                                )
                            ),
                            'enableSorting' => false,
                            'emptyText' => 'No photos have been added.',
                            'selectableRows' => 1,
                        )
                    );
                    echo CHtml::submitButton(('Save Updates'), array('class'=>'submit'));
                    $this->endWidget();
                ?>
            </div>

            <!-- Photo Form -->

            <?php echo CHtml::cssFile(Yii::app()->baseUrl . '/css/resPolicyPhotos/form.css'); ?>

            <div id="formPhotos" class="form">
                <h4>New Photo</h4>
                <div class="container">
                    <?php
            
                        $form=$this->beginWidget('CActiveForm', array(
                            'id' => 'visit-add-photo-form',
                            'method'=>'post',
                            'htmlOptions' => array('enctype' => 'multipart/form-data')
                        ));

                    ?>

                    <div>                        
		                <?php echo CHtml::fileField('create_file_id','',array('accept'=>'image/*')); ?>
                        <?php echo CHtml::image('images/photo-medium.png', 'List'); ?>
                    </div>

                    <?php echo CHtml::hiddenField("NewResPhPhoto[visit_id]", $visitmodel->id); ?>

                    <div class="buttons actionButton">
                        <?php echo CHtml::submitButton('Add Photo', array('class'=>'submit')); ?>
                    </div>
                </div>    
                <?php $this->endWidget(); ?>

            </div>
        </div>
    </div>
</div>