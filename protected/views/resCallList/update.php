<?php

Yii::app()->clientScript->registerCssFile('/css/resCallList/update.css');
Yii::app()->clientScript->registerScriptFile('/js/resCallList/update.js');

$this->breadcrumbs=array(
    'Response' => array('/resNotice/landing'),
    'Call List' => array('/resCallList/admin'),
    'Update'
);
$form = $this->beginWidget('CActiveForm', array(
	    'id' => 'formResponsePropertyAccess',
	    'enableAjaxValidation' => false,
    ));
?>

<div id="resCallContainer">
    <div class ="row">
        <div class ="span6">
            <h2 class="floatLeft">Response Call &ndash; <?php echo $model->getMemberFullName(); ?></h2>
        </div>
        <div class ="span6" style ="text-align:right;">
            <a class="btn btn-primary" style="margin:10px 0;" href="index.php?r=resCallList/admin">Back To Call List <i class ="glyphicon glyphicon-menu-left"></i></a>
            <?php echo CHtml::link('Properties Page', $this->createUrl('property/view',array('pid'=>$model->property->pid)), array(
                'target'=>'_blank',
                'style'=>'margin:10px 0;',
                'class' => 'btn btn-primary'
            )); ?>
        </div>
    </div>
    <div id="row">
        <div class="span5" style="padding:20px 0;">
           <table class="infoTable">
               <tr>
                   <td>Name:</td>
                   <td><?php echo CHtml::link($model->getMemberFullName(), array('member/view', 'mid'=>$model->property->member_mid)); ?></td>
               </tr>
               <tr>
                   <td><?php echo ucfirst($model->client->policyholder_label); ?> Number:</td>
                   <td><?php echo $model->member_num; ?></td>
               </tr>
               <tr>
                   <td>Address:</td>
                   <td>
                       <?php
                           echo $model->property->address_line_1 . ' ';
                           echo $model->property->address_line_2 . '<br/>';
                           echo $model->property->city . ', ';
                           echo $model->property->state . ' ';
                           echo $model->property->zip;
                           if (!empty($model->property->zip_supp)) 
                               echo '-' . $model->property->zip_supp;
                       ?>
                   </td>
               </tr>
                <tr>
                    <td>Mailing Address:</td>
                    <td>
                        <?php if (!empty($model->property->member->mail_address_line_1)) 
                              {
                                  echo $model->property->member->mail_address_line_1 . ' ';
                                  echo $model->property->member->mail_address_line_2;
                                  echo '<br/>' . $model->property->member->mail_city . ', ';
                                  echo $model->property->member->mail_state . ' ';
                                  echo $model->property->member->mail_zip;
                                  if (!empty($model->property->member->mail_zip_supp)) 
                                      echo '-' . $model->property->member->mail_zip_supp; 
                              }
                              else 
                              {
                                  echo 'N/A';
                              }
                        ?>
                    </td>
                </tr>
           </table>            
        </div>
        <div class="span4" style="padding:20px 0;">
            <table class="infoTable">
                <tr>
                    <td>Home Phone:</td>
                    <td><?php echo Helper::formatPhone($model->property->member->home_phone); ?></td>
                </tr>
                <tr>
                    <td>Work Phone:</td>
                    <td><?php echo Helper::formatPhone($model->property->member->work_phone); ?></td>
                </tr>
                <tr>
                    <td>Cell Phone:</td>
                    <td><?php echo Helper::formatPhone($model->property->member->cell_phone); ?></td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td><?php echo Helper::formatText($model->property->member->email_1); ?></td>
                </tr>
            </table>
        </div>
        <div class="span3" style="padding:20px 0;">
            <table class="infoTable">
               <tr>
                   <td>Client:</td>
                   <td><?php echo $model->client_name; ?></td>
               </tr>
                <tr>
                    <td>Fire:</td>
                    <td><?php echo $model->fire_name; ?></td>
                </tr>
                <tr>
                    <td>Notice Type:</td>
                    <td><?php echo ResNotice::getDispatchedType($model->notice_type); ?></td>
                </tr>
                <tr>
                    <td>Distance:</td>
                    <td><?php echo isset($model->res_triggered_distance) ? number_format($model->res_triggered_distance, 2) : 'N/A'; ?></td>
                </tr>
                <tr>
                    <td>Threat:</td>
                    <td><?php echo Helper::formatText(Helper::getBooleanStringFromInt($model->res_triggered_threat)); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div id="preRisksContainer">
        <div class ="table-responsive">
            <h3>Pre Risk Entries for this Property</h3>
            <?php $this->widget('zii.widgets.grid.CGridView', array(
                'id'=>'pre-risks-grid',
                'dataProvider'=>$pre_risks->search(),
                'columns'=>array(
                    array(
                        'class'=>'CLinkColumn',
                        'label'=>'Edit',
                        'urlExpression'=>'"index.php?r=preRisk/update&type=review&id=".$data->id',
                        'header'=>'Production',
                    ),
                    array(
                        'class'=>'CLinkColumn',
                        'label'=>'Edit',
                        'urlExpression'=>'"index.php?r=preRisk/update&type=resource&id=".$data->id',
                        'header'=>'Scheduling',
                    ),
                    'id',
                    'status',
                    'engine',
                    'ha_time',
                    'ha_date',
                    'call_list_month',
                    'call_list_year'
                )
            )); ?>
        </div>
    </div>

    <div id="responseCallContainer">
        <div class ="row">
            <div class ="span4">
                <div id="propertyAccessContainer">
                    <?php $this->renderPartial('_formPropertyAccess', array('model' => $propertyAccess,'form' => $form)); ?>
                </div>
            </div>
            <div class ="span8">
                <div id="callAttemptsContainer">
                    <?php 
                        $this->renderPartial('_gridCallAttempts', array(
                            'dataProvider' => $callAttemptsDataProvider,
                            'columnsToShow' => $callAttemptsColumnsToShow,
                            'callList' => $model
                        )); ?>
                    <?php 
                        $this->renderPartial('_formCallAttempt', array(
                            'model' => $callAttempt,
                            'callList' => $model,
                            'callerUsers' => $callerUsers,
                            'form' => $form,
                        )); ?>
                </div>
            <div class="buttons saveAllButton">
            <?php echo CHtml::submitButton('Save All', array('class'=>'submit pull-right btn-large','id'=>'save_all_items'));?>
            <?php $this->endWidget(); ?>
            </div> 
          </div>
        </div>
    </div>
</div>
