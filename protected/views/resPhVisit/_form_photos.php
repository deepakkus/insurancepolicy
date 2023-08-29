<?php echo CHtml::cssFile(Yii::app()->baseUrl . '/css/resPolicyPhotos/form.css'); ?>
<?php
Yii::app()->clientScript->registerCoreScript('jquery.ui');
Yii::app()->clientScript->registerCssFile('/js/fancybox2.1.5/jquery.fancybox.css?v=2.1.5');
Yii::app()->clientScript->registerScriptFile('/js/fancybox2.1.5/jquery.fancybox.pack.js?v=2.1.5');
Yii::app()->clientScript->registerCssFile("/js/fancybox2.1.5/helpers/jquery.fancybox-thumbs.css?v=1.0.7");
Yii::app()->clientScript->registerScriptFile("/js/fancybox2.1.5/helpers/jquery.fancybox-thumbs.js?v=1.0.7");

 Yii::app()->clientScript->registerScript('fancyBox','              
                $(".fancybox-thumb").fancybox({                    
		            "width"		: "450",
		            "height"		: "350",
                    "fitToView" : false,
		            "autoSize"	: false,                   		            
                    "type" : "image",
                    "helpers"		: {
			            "title"	: { "type" : "inside" },
                        "thumbs" : {
                           "width" : "50",
                           "height" : "50"
                          }			            
		            },
                    
                                   
                });  

                $("#ResPhPhotos_selectAll").attr("checked", false); 
                if ($(".resPhotos_visit_publish:checked").length == $(".resPhotos_visit_publish").length )
                {
                    $("#ResPhPhotos_selectAll")[0].checked = true
                }
                $("#ResPhPhotos_selectAll").change(function(){  //"select all" change 
                var status = this.checked;
                $(".resPhotos_visit_publish").each(function(){ //iterate all listed checkbox items
                this.checked = status; //change ".checkbox" checked status
                    });
                });
                $(".resPhotos_visit_publish").change(function(){ //".checkbox" change 
                //uncheck "select all", if one of the listed checkbox item is unchecked
                if(this.checked == false){ //if this item is unchecked
                    $("#ResPhPhotos_selectAll")[0].checked = false; //change "select all" checked status to false
                }
    
                //check "select all" if all checkbox items are checked
                if ($(".resPhotos_visit_publish:checked").length == $(".resPhotos_visit_publish").length ){ 
                    $("#ResPhPhotos_selectAll")[0].checked = true; //change "select all" checked status to true
                }
            });
            
    ');
    Yii::app()->bootstrap->init();
?>

	<div class="row-fluid">
		<div class="span12">
			<h4>Update Photos</h4>
			<div>
				<table class="table table-striped">
					<tr>
						<th>Order &#35; (Drag rows)&nbsp;&nbsp;&nbsp;&nbsp;Select All&nbsp;<span style="padding-left:4px;padding-top:0px;"><?php echo CHtml::checkBox('ResPhPhotos[selectAll]',true,array()); ?></span></th>
						<th>Edit Image</th>
						<th>Edit Attributes</th>
						<th></th>
					</tr>
					<tbody class="sort list-group gallery">
						<?php foreach($photos as $photo): ?>

						<tr class="order ui-default-state <?php if($photo->publish == false){ echo "error"; } ?>" data-id="<?php echo $photo->id; ?>">
							<td>
								<?php echo CHtml::textField('ExistingResPhPhotos[' . $photo->id . '][order]' ,$photo->order,array('readonly'=>true)); ?>
							</td>
							<td>
								<a class="deleteButton" href="<?php echo Yii::app()->createUrl("resPhPhotos/delete", array("id" => $photo->visit_id, "photoID" => $photo->id)); ?>">
									<i class="icon-trash"></i>
								</a>
								<a href="<?php echo Yii::app()->createUrl("file/edit", array("id" => $photo->file_id, "url" => Yii::app()->request->requestUri)); ?>">
									<i class="icon-pencil"></i>
								</a>
							</td>
							<td>
								<?php echo CHtml::label('File Name:', 'ExistingResPhPhotos[' . $photo->id . '][name]'); ?><?php echo CHtml::textField('ExistingResPhPhotos[' . $photo->id . '][name]', $photo->file->name); ?>
								<br /><?php echo CHtml::label('Notes:', 'ExistingResPhPhotos[' . $photo->id . '][notes]'); ?><?php echo CHtml::textField('ExistingResPhPhotos[' . $photo->id . '][notes]', $photo->notes); ?>
								<br /><?php echo CHtml::label('Publish:', 'ExistingResPhPhotos[' . $photo->id . '][publish]'); ?><?php echo CHtml::checkBox('ExistingResPhPhotos[' . $photo->id . '][publish]', $photo->publish, array('class' => 'resPhotos_visit_publish')); ?>

							</td>
							<td>                             
								<?php $image = CHtml::image($this->createUrl('file/loadThumbnail', array('id' => $photo->file_id)), 'policyholder image', array('height' => '100px')); ?>
                                <?php echo CHtml::link($image, $this->createUrl('file/LoadFile', array('id' => $photo->file_id)),array('alt'=>'policyholder image','rel'=>'phgallery','class'=>'fancybox-thumb','title'=>$photo->file->name)); ?>
							</td>
						</tr><?php endforeach;  ?>

					</tbody>
				</table>
				
			</div>
		</div>
	</div>
	<div class="row-fluid">
        <h4>New Photo</h4>
        <div>
			<?php echo CHtml::fileField('create_file_id[]','',array('accept'=>'image/*','multiple'=>true)); ?>
        </div>
	</div>


