<?php
Yii::app()->clientScript->registerCoreScript('jquery.ui');
Yii::app()->clientScript->registerCssFile('/js/fancybox2.1.5/jquery.fancybox.css?v=2.1.5');
Yii::app()->clientScript->registerScriptFile('/js/fancybox2.1.5/jquery.fancybox.pack.js?v=2.1.5');
Yii::app()->clientScript->registerCssFile("/js/fancybox2.1.5/helpers/jquery.fancybox-thumbs.css?v=1.0.7");
Yii::app()->clientScript->registerScriptFile("/js/fancybox2.1.5/helpers/jquery.fancybox-thumbs.js?v=1.0.7");
Yii::app()->clientScript->registerScriptFile('/js/fsReport/update2.js');
Yii::app()->bootstrap->init();
Yii::app()->clientScript->registerCssFile('/css/fsReport/update.css');

$incomingPath = Helper::getDataStorePath().'fs_reports'.DIRECTORY_SEPARATOR.'incoming'.DIRECTORY_SEPARATOR.$fsReport->report_guid.DIRECTORY_SEPARATOR;

$admin = false;
if (in_array('Admin', Yii::app()->user->types))
    $admin = true;
$property = $fsReport->property;
if(!isset($property))
    $property = new Property;

$form = $this->beginWidget('CActiveForm', array(
    'id'=>'fs-report-form',
    'enableAjaxValidation'=>false,
    'htmlOptions'=>array('enctype'=>'multipart/form-data'),
));

echo '<h2>App Report ' . $fsReport->id . '</h2>';
echo CHtml::link('Return to All Reports List', array('fsReport/allReports'));
?>
<br />
<br />
<div class="fs-report-update-view">
    <?php echo CHtml::submitButton('Save', array('class'=>'submit', 'id'=>'saveButton')); ?>

    <div id="generalInfo">
        <h4>
            <a id="lnkGeneralInfo">General Info -</a>
        </h4>
        <div id="collapsableGeneralInfo">
            <div class="row-fluid" id="generalInfo" style="margin:0;">

                <div class="span4">
                    <table class="table">
                        <th colspan="2">Report Info</th>
                        <tr>
                            <td>Report Type:</td>
                            <td>
                                <?php
                                echo $form->dropDownList($fsReport,'type',$fsReport->getTypes(), array('empty'=>'', 'style'=>'width:150px;'));
                                echo $form->error($fsReport,'type');
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Report Status:
                                <br />
                                <?php echo '<a href="javascript:void(0);" id="lnkViewStatusHistory">View Status History</a>'; ?>
                            </td>
                            <td>
                                <?php
                                echo $form->dropDownList($fsReport,'status',$fsReport->getStatuses(), array('empty'=>'', 'style'=>'width:150px'));
                                echo $form->error($fsReport,'status');
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Status Date:</td>
                            <td>
                                <?php echo $fsReport->status_date; ?>
                            </td>
                        </tr>

                        <tr>
                            <td>Assigned User:</td>
                            <td>
                                <?php
                                echo User::model()->userDropDownList($fsReport, 'assigned_user_id', $fsReport->assigned_user_id, array('style'=>'width:150px'));
                                echo $form->error($fsReport, 'assigned_user_id');
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Submit Date: </td>
                            <td>
                                <?php echo $fsReport->submit_date; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Due Date: </td>
                            <td>
                                <?php echo $fsReport->due_date; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Send Notification: </td>
                            <td>
                                <?php
                                    echo CHtml::checkBox('send_notification');
                                    echo ' Message: '.CHtml::textField('notification_message', 'Your WDSpro report is ready to download.',array('style'=>'width:260px;'));
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $fsReport->getAttributeLabel('download_types'); ?>: </td>
                            <td>
                                <?php
                                echo $form->dropDownList($fsReport, 'download_types', FSReport::model()->getDownloadTypes(), array('empty'=>''));
                                echo $form->error($fsReport,'download_types');
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="span4">
                    <table class="table wy">
                        <th colspan="2">Property Info</th>
                        <tr>
                            <td>Client:</td>
                            <td>
                                <?php
                                if($fsReport->type == 'sl' || $fsReport->type == 'fso')
                                {
                                    if($admin)
                                    {
                                        echo CHtml::link($fsReport->user->client->name, array('client/update', 'id'=>$fsReport->user->client->id), array('target'=>'_blank'));
                                    }
                                    else
                                    {
                                        echo $fsReport->user->client->name;
                                    }
                                }
                                else
                                {
                                    if(isset($fsReport->client))
                                    {
                                        if($admin)
                                            echo CHtml::link($fsReport->client->name, array('client/update', 'id'=>$fsReport->client->id), array('target'=>'_blank'));
                                        else
                                            echo $fsReport->client->name;
                                    }
                                    else
                                        echo 'n/a';
                               }

                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>App User:</td>
                            <td>
                                <?php
                                if($fsReport->type == '2.0')
                                {
                                echo CHtml::link($fsReport->fs_user->name, array('fsUser/update', 'id'=>$fsReport->fs_user_id), array('target'=>'_blank'));
                                }
                                if($fsReport->type == 'sl' || $fsReport->type == 'fso')
                                {
                                    echo CHtml::link($fsReport->user->name, array('user/update', 'id'=>$fsReport->user->id), array('target'=>'_blank'));
                                }

                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>App User Type:</td>
                            <td>
                                <?php
                                if($fsReport->type == '2.0')
                                {
                                echo $fsReport->fs_user->type;
                                if($fsReport->fs_user->type == 'Agent')
                                    echo ' (Agent ID:'.CHtml::link($fsReport->fs_user->agent_id, array('agent/update', 'id'=>$fsReport->fs_user->agent_id), array('target'=>'_blank')).')';
                                elseif($fsReport->fs_user->type == 'PolicyHolder')
                                    echo ' (PolicyHolder MID: '.CHtml::link($fsReport->fs_user->member_mid, array('member/view', 'mid'=>$fsReport->fs_user->member_mid), array('target'=>'_blank')).')';
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Agent Property ID<?php if(isset($fsReport->agent_property)) { echo CHtml::link(' (View)', array('agentProperty/update', 'id'=>$fsReport->agent_property_id), array('target'=>'_blank')); } ?>:
                            </td>
                            <td>
                                <?php
                                echo $form->textField($fsReport,'agent_property_id',array('style'=>'width:50px;margin:0px;height:10px;','size'=>10,'maxlength'=>10,'height'=>'5'));
                                echo $form->error($fsReport,'agent_property_id');
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Property ID<?php if(isset($fsReport->property)) { echo CHtml::link(' (View)', array('property/view', 'pid'=>$fsReport->property_pid), array('target'=>'_blank')); } ?>:
                            </td>

                            <td>
                                <?php
                                echo $form->textField($fsReport,'property_pid',array('style'=>'width:50px;margin:0px;height:10px;','size'=>10,'maxlength'=>10,'height'=>'5'));
                                echo $form->error($fsReport,'property_pid');
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Policyholder Name: </td>
                            <td>
                                <?php
                                if(!empty($property->member_mid))
                                    echo CHtml::link($property->member->first_name.' '.$property->member->last_name, array('member/view', 'mid'=>$property->member_mid),array('target'=>'_blank'));
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Address: </td>
                            <td>
                                <?php
                                echo $property->address_line_1;
                                if(!empty($property->address_line_2))
                                    echo '<br>'.$property->address_line_2;

                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>City, ST Zip: </td>
                            <td>
                                <?php echo $property->city.', '.$property->state.' '.$property->zip; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Lat / Long: </td>
                            <td>
                                <?php echo $property->lat.' / '.$property->long; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>KML: </td>
                            <td>
                                <?php echo CHtml::link('Download File', array('fsReport/getKML', 'guid'=>$fsReport->report_guid), array()); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>PDF Reports: </td>
                            <td>
                                <?php
                                echo CHtml::link(TbHtml::icon(TbHtml::ICON_FILE).'EDU PDF', array('fsReport/getPDFReport', 'guid'=>$fsReport->report_guid, 'type'=>'edu'), array('target'=>'_blank')).' | ';
                                echo CHtml::link(TbHtml::icon(TbHtml::ICON_FILE).'UW PDF', array('fsReport/getPDFReport', 'guid'=>$fsReport->report_guid, 'type'=>'uw'), array('target'=>'_blank'));
                                echo ' | Refresh PDFs: '.CHtml::checkBox('refresh_pdfs');
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php echo $fsReport->getAttributeLabel('pdf_pass'); ?>
                            </td>
                            <td>
                                <?php
                                echo $form->textField($fsReport, 'pdf_pass');
                                echo $form->error($fsReport,'pdf_pass');
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Send Email: </td>
                            <td>
                                <?php
                                if($fsReport->type == '2.0'){
                                echo CHtml::checkBox('send_emails');
                                echo ' To: '.CHtml::textField('to_emails', $fsReport->fs_user->email, array('style'=>'width:260px;'));
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $fsReport->getAttributeLabel('email_download_types'); ?></td>
                            <td>
                                <?php
                                echo $form->dropDownList($fsReport, 'email_download_types', FSReport::model()->getDownloadTypes(), array('empty'=>''));
                                echo $form->error($fsReport,'email_download_types');
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="span4">
                    <table class="table">
                        <th colspan="2">Scoring</th>
                        <tr>
                            <td>Pro Score:</td>
                            <td>
                                <?php echo $fsReport->calcConditionRisk(); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Pro Score Override:</td>
                            <td>
                                <?php
                                echo $form->textField($fsReport,'condition_risk',array('style'=>'width:50px','size'=>10,'maxlength'=>10,'value'=>($fsReport->condition_risk == $fsReport->calcConditionRisk() ? '': $fsReport->condition_risk)));
                                echo $form->error($fsReport,'condition_risk');
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Risk Score (vuln_score * 100): </td>
                            <td>
                                <?php echo $fsReport->getPropertyRiskScore(); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Risk Score Override: </td>
                            <td>
                                <?php
                                echo $form->textField($fsReport,'geo_risk',array('style'=>'width:50px','size'=>10,'maxlength'=>10,'value'=>(empty($fsReport->geo_risk) ? '': $fsReport->geo_risk)));
                                echo $form->error($fsReport,'geo_risk');
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Integrated Score Calc: </td>
                            <td>(.6*Pro S)+(.4*Risk S)</td>
                        </tr>
                        <tr>
                            <td>Integrated Score: </td>
                            <td>
                                <?php echo $fsReport->risk_level; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Level of Service: </td>
                            <td>
                                <?php
                                echo $form->dropDownList($fsReport,'level', array(1=>1, 2=>2));
                                echo $form->error($fsReport,'geo_risk');
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div id="riskInfo">
        <h4>
            <a id="lnkRiskInfo">Risk Info +</a>
        </h4>
        <div id="collapsableRiskInfo">
            <div class="row-fluid">
                <div class="form" style ="padding:25px 0 25px 0;">
                    <?php
                        $this->renderPartial('//property/_risk_score', array(
                            'property' => Property::model()->findByPk($fsReport->property->pid)
                        ));
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div id="additionalInfo">
        <h4>
            <a id="lnkAdditionalInfo">Additional Info +</a>
        </h4>
        <div id="collapsableAdditionalInfo">
            <div class="row-fluid">
                <div class="span6">
                    Report GUID: <?php echo $fsReport->report_guid; ?>
                    <?php
                    if($fsReport->type == '2.0')
                    {
                    if($fsReport->fs_user->type == 'Agent' && isset($fsReport->agent_property))
                        echo '<br>Work Order #: '.$fsReport->agent_property->work_order_num;          
                        }
                    ?>
                   </div>
            </div>
        </div>
    </div>
    <div id="summaryAndNotes">
        <h4>
            <a id="lnkSummaryAndNotes">Summary and Notes -</a>
        </h4>
        <div id="collapsableSummaryAndNotes">
            <?php
                $summaryAndNotesTabs = array();
                $tabContent = $this->widget(
                    'bootstrap.widgets.TbCKEditor',
                    array(
                        'model' => $fsReport,
                        'attribute'=> 'summary',
                        'editorOptions' => array(
                            'plugins' => 'scayt,selectall,find,colorbutton,contextmenu,horizontalrule,justify,colordialog,basicstyles,resize,popup,font,htmlwriter,format,toolbar,enterkey,entities,floatingspace,wysiwygarea,link,list,liststyle,pastetext,removeformat,richcombo,dialog,dialogui,button,indent,fakeobjects,pastefromword'
                        ),
                        'htmlOptions'=> array(
                            'height'=> '300px',
                            'width'=> '300px',
                        )
                    ), true
                );
                $tabContent .= $form->error($fsReport,'summary');
                $summaryAndNotesTabs[] = array('label'=>'Surrounding Area Risks Summary', 'content'=>$tabContent, 'active'=>true);
                $tabContent = $this->widget(
                    'bootstrap.widgets.TbCKEditor',
                    array(
                        'model' => $fsReport,
                        'attribute'=> 'risk_summary',
                        'editorOptions' => array(
                            'plugins' => 'scayt,selectall,find,colorbutton,contextmenu,horizontalrule,justify,colordialog,basicstyles,resize,popup,font,htmlwriter,format,toolbar,enterkey,entities,floatingspace,wysiwygarea,link,list,liststyle,pastetext,removeformat,richcombo,dialog,dialogui,button,indent,fakeobjects,pastefromword'
                        ),
                        'htmlOptions'=> array(
                            'height'=> '300px',
                            'width'=> '300px',
                        )
                    ), true
                );
                $tabContent .= $form->error($fsReport,'risk_summary');
                $summaryAndNotesTabs[] = array('label'=>'Property Risks Summary', 'content'=>$tabContent);

                $tabContent = $this->widget(
                    'bootstrap.widgets.TbCKEditor',
                    array(
                        'model' => $fsReport,
                        'attribute'=> 'risk_detail',
                        'editorOptions' => array(
                            'plugins' => 'scayt,selectall,find,colorbutton,contextmenu,horizontalrule,justify,colordialog,basicstyles,resize,popup,font,htmlwriter,format,toolbar,enterkey,entities,floatingspace,wysiwygarea,link,list,liststyle,pastetext,removeformat,richcombo,dialog,dialogui,button,indent,fakeobjects,pastefromword'
                        ),
                        'htmlOptions'=> array(
                            'height'=> '300px',
                            'width'=> '300px',
                        )
                    ), true
                );
                $tabContent .= $form->error($fsReport,'risk_detail');
                $summaryAndNotesTabs[] = array('label'=>'Risk Detail', 'content'=>$tabContent);

                $tabContent = $form->textArea($fsReport, 'notes', array('rows'=>'12', 'class'=>'span9',));
                $summaryAndNotesTabs[] = array('label'=>'Internal Notes', 'content'=>$tabContent);
                $this->widget(
                    'bootstrap.widgets.TbTabs',
                    array(
                        'type' => 'tabs',
                        'tabs' => $summaryAndNotesTabs,
                    )
                );
            ?>
        </div>
    </div>
    <div id="allFieldData">
        <h3>Field Data</h3>
        <?php
        $i=0;
        $fieldDataTabs = array();
       // if($fsReport->type == '2.0'){
        foreach($fsReport->getOrderedConditionsAndQuestions('field') as $conditionQuestion)
	    {
            $i++;
            $responses = '';
            foreach(json_decode($conditionQuestion['condition']->selected_choices) as $selected_choice)
                $responses .= $selected_choice->label.', ';
            $responses = rtrim($responses, ', ');
            $photos = '';
            if(!empty($conditionQuestion['condition']->submitted_photo_path))
            {
                foreach($conditionQuestion['condition']->getSubmittedPhotosArray() as $photo_name)
                {
                    //$imgURL = $this->createUrl('site/getImage', array('filepath' => $incomingPath.'images'.DIRECTORY_SEPARATOR.$photo_name)); // Don't auto-rotate the image
                    $imgURL = $this->createUrl('site/getJpegImageRotate', array('token' => 'A9er5726rTqncRNC', 'filepath' => $incomingPath.'images'.DIRECTORY_SEPARATOR.$photo_name));
                    $editImgURL = $this->createUrl('file/edit', array('filepath' => $incomingPath.'images'.DIRECTORY_SEPARATOR.$photo_name, 'url' => Yii::app()->request->requestUri));
                    $photos .= '<li class="ui-widget-content ui-corner-tr fieldPhoto" style="position: relative;" id="this" data-photo-name="'.$photo_name.'" data-photo-url="'.$imgURL.'" data-condition-id="'.$conditionQuestion['condition']->id.'">';
                    $photos .= '<a class="photo-edit" title="Edit Photo" href="'. $editImgURL . '"><i class="icon-pencil"></i></a>';
                    $photos .= '<a class="fancybox-thumb" rel="condition'.$conditionQuestion['condition']->id.'photos" href="'.$imgURL.'">';
                    $photos .=  '<img src="'.$imgURL.'" style="height: 90px" />';
                    $photos .= '</a></li>';
                }
            }
            else
            {
                $photos = '<i>No photos were submitted</i>';
            }
            $tabContent = '';
            $tabContent .= '<div class="row-fluid fieldData">';
            $tabContent .= '    <table class="table fieldDataTable span4">';
            $tabContent .= '        <tr>';
            $tabContent .= '            <td class="span2">Question #:</td>';
            $tabContent .= '            <td>'.$conditionQuestion['question']->question_num.'</td>';
            $tabContent .= '        </tr>';
            $tabContent .= '        <tr>';
            $tabContent .= '            <td>Question Set:</td>';
            $tabContent .= '            <td>'.$conditionQuestion['question']->set_id.'</td>';
            $tabContent .= '        </tr>';
            $tabContent .= '        <tr>';
            $tabContent .= '            <td>Description:</td>';
            $tabContent .= '            <td>'.$conditionQuestion['question']->description.'</td>';
            $tabContent .= '        </tr>';
            $tabContent .= '        <tr>';
            $tabContent .= '            <td>Question Text:</td>';
            $tabContent .= '            <td>'.$conditionQuestion['condition']->question_text.'</td>';
            $tabContent .= '        </tr>';
            $tabContent .= '        <tr>';
            $tabContent .= '            <td>Responses:</td>';
            $tabContent .= '            <td>'.$responses.'</td>';
            $tabContent .= '        </tr>';
            $tabContent .= '        <tr>';
            $tabContent .= '            <td>Notes:</td>';
            $tabContent .= '            <td>'.$conditionQuestion['condition']->notes.'</td>';
            $tabContent .= '        </tr>';
            $tabContent .= '        <tr>';
            $tabContent .= '            <td colspan="2"><strong>Drag photos from the right to copy to Conditions below:</strong></td>';
            $tabContent .= '        </tr>';
            $tabContent .= '    </table>';
            $tabContent .= '    <ul class="fieldPhotos ui-helper-reset ui-helper-clearfix span7">'.$photos.'</ul>';
            $tabContent .= '</div>';
            $photoCount = count($conditionQuestion['condition']->getSubmittedPhotosArray());
            if($i == 1)
                $fieldDataTabs[] = array('label'=>$conditionQuestion['question']->title.'<span class="photo-count" id="photo_count_'.$conditionQuestion['condition']->id.'">'.$photoCount.'</span>', 'content'=>$tabContent, 'active'=>true);
            else
                $fieldDataTabs[] = array('label'=>$conditionQuestion['question']->title.'<span class="photo-count" id="photo_count_'.$conditionQuestion['condition']->id.'">'.$photoCount.'</span>', 'content'=>$tabContent);
        }
        $this->widget(
            'bootstrap.widgets.TbTabs',
            array(
                'type' => 'tabs',
                'encodeLabel'=>false,
                'tabs' => $fieldDataTabs,
            )
        );
       // }
        ?>
    </div>

    <div id="allConditionData">
        <h3>Condition Data</h3>
        <?php
        $i=0;
        $conditionTabs = array();
        //if($fsReport->type == 2.0)
        //{

        foreach($fsReport->getOrderedConditionsAndQuestions('condition') as $conditionQuestion)
	    {
            $i++;
            
            $conditionTab = '<div class="conditionData">';
            $conditionTab .= '<div class="row-fluid conditionData">';
            $conditionTab .='   <div class = "span3">';
            $conditionTab .= '    <table class="table conditionDataTable">';
            $conditionTab .= '        <tr>';
            $conditionTab .= '            <td>Question #:</td>';
            $conditionTab .= '            <td>'.$conditionQuestion['question']->question_num.'</td>';
            $conditionTab .=              CHtml::hiddenField('FSConditions['.$conditionQuestion['condition']->id.'][condition_num]', $conditionQuestion['condition']->condition_num);
            $conditionTab .= '        </tr>';
            $conditionTab .= '        <tr>';
            $conditionTab .= '            <td>Question Set:</td>';
            $conditionTab .= '            <td>'.$conditionQuestion['question']->set_id.'</td>';
            $conditionTab .= '        </tr>';
            $conditionTab .= '        <tr>';
            $conditionTab .= '            <td>Response:</td>';
            $conditionTab .= '            <td>';
            $conditionTab .=                 $form->dropDownList($conditionQuestion['condition'], 'response',
                                                array(
                                                    '0' => 'Yes',
                                                    '1' => 'No',
                                                    'Not Sure',
                                                ),
                                                array(
                                                    'name' => 'FSConditions['.$conditionQuestion['condition']->id.'][response]',
                                                    'class' => 'fsReportConditionResponseDropDown',
                                                    'data-condition-id' => $conditionQuestion['condition']->id,
                                                    'data-condition-default-yes-score' => $conditionQuestion['question']->yes_points,
                                                )
                                            );
            $conditionTab .= '           </td>';
            $conditionTab .= '        </tr>';
            $conditionTab .= '        <tr>';
            $conditionTab .= '            <td>Score:</td>';
            $conditionTab .= '            <td>';
            $conditionTab .=                 CHtml::textField('FSConditions['.$conditionQuestion['condition']->id.'][score]',$conditionQuestion['condition']->score,array('style'=>'width:50px','size'=>10,'maxlength'=>10));
            $conditionTab .= '            </td>';
            $conditionTab .= '        </tr>';
            $conditionTab .= '    </table>';
            $conditionTab .= '    </div>';
            $conditionDataTabs = array();
            $photosTab = '<div class="span2">';
            $photosTab .= '  <div class="conditionPhotosCopyDrop ui-widget-content ui-state-default"><h4 class="ui-widget-header">Drop Photo Here to Copy to Condition</h4></div>';
            $photosTab .= '  <br><div id="addPhotoRow_'.$conditionQuestion['condition']->id.'">';
            $photosTab .=    CHtml::fileField('new_photo_'.$conditionQuestion['condition']->id, '', array('id'=>'new_photo_'.$conditionQuestion['condition']->id));
            $photosTab .=    CHtml::submitButton('Upload Photo', array('class'=>'submit'));
            $photosTab .=    '</div>'; //end addPhotoRow
            $photosTab .= '</div>'; //end copydrop/addphto area div

            $photos = '';
            if(!empty($conditionQuestion['condition']->submitted_photo_path))
            {
                foreach($conditionQuestion['condition']->getSubmittedPhotosArray() as $photo_name)
                {
                    $imgURL = Yii::app()->request->baseUrl . '/index.php?r=site/getJpegImageRotate&token=A9er5726rTqncRNC&filepath='.urlencode($incomingPath.'images'.DIRECTORY_SEPARATOR.$photo_name);
                    $photos .= '<div class="conditionPhoto tile well span2" data-photo-name="'.$photo_name.'" id="condition_'.$conditionQuestion['condition']->id.'_photo_'.$photo_name.'">';
                    $photos .= '<input class="deleteImageButton" type="button" data-condition-id="'.$conditionQuestion['condition']->id.'" data-photo-name="'.$photo_name.'">';
                    $photos .= '<a class="fancybox-thumb" rel="condition'.$conditionQuestion['condition']->id.'photos" href="'.$imgURL.'">';
                    $photos .= '  <img src="'.$imgURL.'" style="height: 90px" />';
                    $photos .= '</a></div>';
                }
            }

            $photosTab .= '<div class="conditionPhotos span8" data-condition-id="'.$conditionQuestion['condition']->id.'" id="condition_photos_'.$conditionQuestion['condition']->id.'">'.$photos.'</div>';
            $photosTab .= CHtml::hiddenField('FSConditions['.$conditionQuestion['condition']->id.'][submitted_photo_path]', $conditionQuestion['condition']->submitted_photo_path, array('class'=>'conditionPhotoPath', 'data-condition-id'=>$conditionQuestion['condition']->id));
            $conditionDataTabs[] = array('label'=>'Photos', 'content'=>$photosTab, 'active'=>true);
            
            $notesTab = CHtml::textArea('FSConditions['.$conditionQuestion['condition']->id.'][notes]', $conditionQuestion['condition']->notes, array('rows'=>'6', 'class'=>'span6',));
            $conditionDataTabs[] = array('label'=>'Notes', 'content'=>$notesTab);

            //if risk_text is blank set risk text to client question default (if its set)
            if(empty($conditionQuestion['condition']->risk_text) && isset($conditionQuestion['question']->risk_text))
                $conditionQuestion['condition']->risk_text = $conditionQuestion['question']->risk_text;
            $riskTextTab = CHtml::textArea('FSConditions['.$conditionQuestion['condition']->id.'][risk_text]', $conditionQuestion['condition']->risk_text, array('rows'=>'6', 'class'=>'span6',));
            $conditionDataTabs[] = array('label'=>'Risk Text', 'content'=>$riskTextTab);

            if(empty($conditionQuestion['condition']->recommendation_text) && isset($conditionQuestion['question']->rec_text))
                $conditionQuestion['condition']->recommendation_text = $conditionQuestion['question']->rec_text;
            $actionTextTab = CHtml::textArea('FSConditions['.$conditionQuestion['condition']->id.'][recommendation_text]', $conditionQuestion['condition']->recommendation_text, array('rows'=>'6', 'class'=>'span6',));
            $conditionDataTabs[] = array('label'=>'Action Text', 'content'=>$actionTextTab);

            if(empty($conditionQuestion['condition']->example_text) && isset($conditionQuestion['question']->example_text))
                $conditionQuestion['condition']->example_text = $conditionQuestion['question']->example_text;
            $exampleTextTab = CHtml::textArea('FSConditions['.$conditionQuestion['condition']->id.'][example_text]', $conditionQuestion['condition']->example_text, array('rows'=>'6', 'class'=>'span6',));
            $ex_photo_file_id = $conditionQuestion['question']->example_image_file_id;

            if(empty($conditionQuestion['condition']->example_image_file_id) && isset($conditionQuestion['question']->example_image_file_id))
                $ex_photo_file_id = $conditionQuestion['question']->example_image_file_id;
            $exampleTextTab .= '<br><br>Example Image: <br>';
            $exampleTextTab .= '<img class="ex_img" src="'.Yii::app()->request->baseUrl.'/index.php?r=file/loadFile&id='.$ex_photo_file_id.'" />';
            $exampleTextTab .= CHtml::fileField('example_image_'.$conditionQuestion['condition']->id, '', array('id'=>'example_image_'.$conditionQuestion['condition']->id, 'class'=>'ex_img'));
            $exampleTextTab .= CHtml::submitButton('Upload Custom Example Image', array('class'=>'submit ex_img'));
            $conditionDataTabs[] = array('label'=>'Example Text', 'content'=>$exampleTextTab);
           
            $conditionTab .= '<div class="span9">';
            $conditionTab .= $this->widget(
                'bootstrap.widgets.TbTabs',
                array(
                    'type' => 'tabs',
                    'tabs' => $conditionDataTabs,
                ),
                true
            );
            $conditionTab .= '</div>'; //conditionDataTabs
            $conditionTab .= '</div>'; //conditionData
            $conditionTab .= '</div>'; //row-fluid
            
            $activeTab = false;
            if($i == 1)
                $activeTab = true;

            $tabLabelStyle = '';
            if($conditionQuestion['condition']->response == 0)
                $tabLabelStyle = 'color:#b30000;font-weight:bold';

            $photoCount = count($conditionQuestion['condition']->getSubmittedPhotosArray());

            $conditionTabs[] = array('label'=>'<span id="TabLabel_'.$conditionQuestion['condition']->id.'" style="'.$tabLabelStyle.'">'.$conditionQuestion['question']->title.'</span><span class="photo-count" id="photo_count_'.$conditionQuestion['condition']->id.'">'.$photoCount.'</span>', 'content'=>$conditionTab, 'active'=>$activeTab);
        }
        $this->widget(
                'bootstrap.widgets.TbTabs',
                array(
                    'type' => 'pills',
                    'placement'=> 'bottom',
                    'encodeLabel'=>false,
                    'tabs' => $conditionTabs,
                )
            );//}
        ?>
    </div>
    <?php $this->endWidget(); ?>

</div><!--END fs-report-update-view-->

<div id="hiddenReportStatusHistory" style="display:none;">
    <?php if (!$fsReport->isNewRecord) : ?>
    <div>
        <h3>Report Status History</h3>
        <div class="paddingRight10">
            <?php
              $this->widget('zii.widgets.grid.CGridView', array(
                  'id' => 'gridReportStatusHistory',
                  'dataProvider' => $reportStatusHistory,
                  'summaryCssClass' => 'hidden',
                  'emptyText' => 'No history found',
                  'enableSorting' => false,
                  'columns' => array(
                      array(
                          'name' => 'date_changed',
                          'header' => 'Date Changed',
                          'value' => 'Yii::app()->dateFormatter->format("MM/d/y h:mm a", strtotime($data->date_changed))',
                      ),
                      array('name' => 'status', 'header' => 'Status'),
                      array(
                          'name' => 'user_id',
                          'header' => 'By',
                          'value' => 'isset($data->user) ? $data->user->name : "System"',
                      ),
                  )
              ));
            ?>
        </div>
        <a href="javascript:void(0)" id="lnkReportStatusHistoryClose" class="floatRight">Close</a>
    </div>
    <?php endif; ?>
</div>
