<!-- The file upload form used as target for the file upload widget -->
<?php echo CHtml::beginForm($this -> url, 'post', $this -> htmlOptions);?>
<div class="fileupload-buttonbar">
    
        <!-- The fileinput-button span is used to style the file input field as button -->
        <span class="btn btn-success fileinput-button"> <i class="icon-plus icon-white"></i> <span>Add files...</span>
                <?php 
                    echo CHtml::fileField($name, $this -> value, $htmlOptions) . "\n"; ?>
        </span>
        <button type="submit" class="btn btn-primary start">
                <i class="icon-upload icon-white"></i>
                <span>Start upload</span>
        </button>
        <button type="reset" class="btn btn-warning cancel">
                <i class="icon-ban-circle icon-white"></i>
                <span>Cancel upload</span>
        </button>
        <button type="button" class="btn btn-danger delete">
                <i class="icon-trash icon-white"></i>
                <span>Delete</span>
        </button>
        <input type="checkbox" class="toggle">
        
	<div class="span5">
		<!-- The global progress bar -->
		<div class="progress progress-success progress-striped active fade">
			<div class="bar" style="width:0%;"></div>
		</div>
	</div>
</div>
<!-- The loading indicator is shown during image processing -->
<div class="fileupload-loading"></div>
<br>
<!-- The table listing the files available for upload/download -->
<table class="table table-striped assessment-photos">
	<tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery">
            <?php echo Yii::app()->controller->renderPartial('//assessment/assessment_photos', array('model_id'=>$model->id)); ?>
            
        </tbody>
</table>
<?php echo CHtml::endForm();?>
