<style>
#formAddToCallList .span12 .button_FindProperty 
{
    float: right;
    margin-top: 25px;
    position: relative;
    left: -192px;
}
</style>
<div id="formAddToCallList" class="form">

    <div class="row-fluid">
        <div class="span6">

            <?php $form=$this->beginWidget('CActiveForm', array(
	            'id'=>'formAddToCallList',
	            'enableAjaxValidation'=>false,
            )); ?>

            <div class="form-section">

                <div class="row-fluid marginTop20">
                    <div class="span12">
                        <div class="clearfix">
                            <?php echo $form->labelEx($model, 'client_id'); ?>
                            <?php echo $form->dropDownList($model, 'client_id', $clientslist, array('empty'=>' ')); ?>
                            <?php echo $form->error($model, 'client_id'); ?>
                        </div>
                        <div class="clearfix marginLeft20">
                            <?php echo $form->labelEx($model, 'res_fire_id'); ?>
                            <?php echo $form->dropDownList($model, 'res_fire_id', $fireslist, array('empty'=>' ')); ?>
                            <?php echo $form->error($model, 'res_fire_id'); ?>
                        </div>
                    </div>
                </div>
                
                <div class="row-fluid marginTop20">
                    <div class="span12">
                        <h3>Enter PID here</h3>
                        <div class="clearfix">
                            <?php echo $form->labelEx($model, 'property_id'); ?>
                            <?php echo $form->numberField($model,'property_id',array('size'=>20,'maxlength'=>20)); ?>
                            <?php echo $form->error($model,'property_id'); ?>
                        </div>
                        <div class="clearfix marginLeft20 button_FindProperty">
                            <?php echo CHtml::button('Find Policy', array('class' => 'success', 'id' => 'btnFindProperty')); ?>
                        </div>
                    </div>
                </div>

            </div> <!-- form-selction -->

            <div class="buttons">
                <?php echo CHtml::submitButton('Save', array('class'=>'submit')); ?>
                <span class="paddingLeft10">
                    <?php echo CHtml::link('Cancel', array('admin')); ?>
                </span>
            </div>

            <?php $this->endWidget(); ?>

        </div>

        <div class="span6">

            <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array (
                'id' => 'gridNewCallListGrid',
                'cssFile' => '../../css/wdsExtendedGridView.css',
                'type' => 'striped bordered condensed',
                'dataProvider' => $dataProvider,
                'columns' => $columnsArray,
                'enableSorting' => false,
                'emptyText' => 'Search for PID to populate grid with a property',
                'summaryText' => '',
            )); ?>

        </div>
    </div>

</div>
