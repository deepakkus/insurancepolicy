<div class="search-form ">
    <?php
	$form=$this->beginWidget('CActiveForm', array(
		'action'=>Yii::app()->createUrl($this->route),
		'method'=>'get',
	));
    ?>
	<h3>Advanced Search</h3>
    <div>NOTE: hold ctrl for multi-select</div>

    <style>
        .adv-search h5, h4 {
            margin: 0px;
        }
    </style>
    <div class="adv-search clearfix">

        <div class="floatLeft paddingRight20">
            <h4>Clients:</h4>
            <?php
            $options = array();
            $selectedOptions = array();
            $clients = Client::model()->findAll((array('select' => array('id','name'))));
            foreach($clients as $client)
            {
                $options[$client->id] = $client->name;
                if(isset($advSearch['clients']) && in_array($client->id, $advSearch['clients']))
                {
                    $selectedOptions[$client->id] = array('selected'=>"selected");
                }
            }

            $htmlOptions = array(
                'options' => $selectedOptions,
                'multiple' => 'multiple',
                'size' => '18',
                'name' => 'advSearch[clients]',
                'id' => 'adv-search-clients',
            );

            echo CHtml::activeDropDownList($properties, 'client_id', $options, $htmlOptions);
            ?>
        </div>

        <div class="floatLeft paddingRight20">
            <h4>States:</h4>
            <?php
            $selectedOptions = array();
            $states = Helper::getStates();
            foreach($states as $state)
            {
                if(isset($advSearch['states']) && in_array($state, $advSearch['states']))
                    $selectedOptions[$state] = array('selected'=>"selected");
            }

            $htmlOptions = array(
                'options' => $selectedOptions,
                'multiple' => 'multiple',
                'size' => '18',
                'name' => 'advSearch[states]',
                'id' => 'adv-search-states',
                'style' => 'width:65px',
            );

            echo CHtml::activeDropDownList($properties, 'state', $states, $htmlOptions);
            ?>
        </div>

        <div class="floatLeft">
            <div class="paddingRight20">
                <h5>Policy Statuses</h5>
                <?php
                $options = array();
                $selectedOptions = array();
                foreach($properties->getPolicyStatuses() as $option)
                {
                    $options[$option] = $option;
                    if(isset($advSearch['policy_statuses']) && in_array($option, $advSearch['policy_statuses']))
                    {
                        $selectedOptions[$option] = array('selected'=>"selected");
                    }
                }

                $htmlOptions = array(
                    'options' => $selectedOptions,
                    'multiple' => 'multiple',
                    'size' => '4',
                    'name' => 'advSearch[policy_statuses]',
                    'id' => 'adv-search-policy-statuses',
                );

                echo CHtml::activeDropDownList($properties, 'policy_status', $options, $htmlOptions);
                ?>
            </div>

            <div class="paddingRight20">
                <h5>Response Statuses</h5>
                <?php
                $options = array();
                $selectedOptions = array();
                foreach($properties->getProgramStatuses() as $option)
                {
                    $options[$option] = $option;
                    if(isset($advSearch['response_statuses']) && in_array($option, $advSearch['response_statuses']))
                    {
                        $selectedOptions[$option] = array('selected'=>"selected");
                    }
                }

                $htmlOptions = array(
                    'options' => $selectedOptions,
                    'multiple' => 'multiple',
                    'size' => '5',
                    'name' => 'advSearch[response_statuses]',
                    'id' => 'adv-search-response-statuses',
                );

                echo CHtml::activeDropDownList($properties, 'response_status', $options, $htmlOptions);
                ?>
            </div>

            <div class="paddingRight20">
                <h5>FireShield Statuses</h5>            
                <?php
                $options = array();
                $selectedOptions = array();
                foreach($properties->getProgramStatuses() as $option)
                {
                    $options[$option] = $option;
                    if(isset($advSearch['fs_statuses']) && in_array($option, $advSearch['fs_statuses']))
                    {
                        $selectedOptions[$option] = array('selected'=>"selected");
                    }
                }
            
                $htmlOptions = array(
                    'options' => $selectedOptions,
                    'multiple' => 'multiple',
                    'size' => '5',
                    'name' => 'advSearch[fs_statuses]',
                    'id' => 'adv-search-fs-statuses',                        
                );

                echo CHtml::activeDropDownList($properties, 'fireshield_status', $options, $htmlOptions); 
                ?>
            </div>
        </div>

        <div class="floatLeft paddingTop10">
            <h5>Policy Effective Date Between</h5>
            <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'model'=>$properties,
                'name'=>'advSearch[policyEffDateBegin]',
                'options'=>array(
                    'showAnim'=>'fold',
                    'dateFormat'=>'yy-mm-dd',                    
                ),
                'value'=>$advSearch['policyEffDateBegin'],
                 'htmlOptions'=>array(
                                 'readonly' => true,
                                 'style' => 'cursor:pointer',
                            ),

            ));
            ?>
			    and
            <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'model'=>$properties,
                'name'=>'advSearch[policyEffDateEnd]',
                'options'=>array(
                    'showAnim'=>'fold',
                    'dateFormat'=>'yy-mm-dd',
                ),
                'value'=>$advSearch['policyEffDateEnd'],
                'htmlOptions'=>array(
                                 'readonly' => true,
                                 'style' => 'cursor:pointer',
                            ),
            ));
            ?>

            <h5>Response Enrolled Date Between</h5>
            <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'model'=>$properties,
                'name'=>'advSearch[resEnrolledDateBegin]',
                'options'=>array(
                    'showAnim'=>'fold',
                    'dateFormat'=>'yy-mm-dd',
                ),
                'value'=>$advSearch['resEnrolledDateBegin'],
                 'htmlOptions'=>array(
                                 'readonly' => true,
                                 'style' => 'cursor:pointer',
                            ),
            ));
            ?>
			    and
            <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'model'=>$properties,
                'name'=>'advSearch[resEnrolledDateEnd]',
                'options'=>array(
                    'showAnim'=>'fold',
                    'dateFormat'=>'yy-mm-dd',
                ),
                'value'=>$advSearch['resEnrolledDateEnd'],
                 'htmlOptions'=>array(
                                 'readonly' => true,
                                 'style' => 'cursor:pointer',
                            ),
            ));
            ?>

            <h5>Last Update Date Between</h5>
            <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'model'=>$properties,
                'name'=>'advSearch[lastUpdateDateBegin]',
                'options'=>array(
                    'showAnim'=>'fold',
                    'dateFormat'=>'yy-mm-dd',
                ),
                'value'=>$advSearch['lastUpdateDateBegin'],
                 'htmlOptions'=>array(
                                 'readonly' => true,
                                 'style' => 'cursor:pointer',
                            ),
            ));
            ?>
			    and
            <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'model'=>$properties,
                'name'=>'advSearch[lastUpdateDateEnd]',
                'options'=>array(
                    'showAnim'=>'fold',
                    'dateFormat'=>'yy-mm-dd',
                ),
                'value'=>$advSearch['lastUpdateDateEnd'],
                 'htmlOptions'=>array(
                                 'readonly' => true,
                                 'style' => 'cursor:pointer',
                            ),
            ));
            ?>

            <h5>First Name</h5>            
            <?php echo CHtml::textField('advSearch[member_first_name]',$advSearch['member_first_name'],array());     ?>
            <h5>Last Name</h5>            
            <?php echo CHtml::textField('advSearch[member_last_name]',$advSearch['member_last_name'],array());     ?>
            <h5>Address Line 1</h5>            
            <?php echo CHtml::textField('advSearch[address_line_1]',$advSearch['address_line_1'],array());     ?>
			
        </div>

        <div class="clearfix width100 paddingTop20">
            <div class="floatRight">
                <?php echo CHtml::submitButton('Search', array('name' => 'columnsSubmit', 'class'=>'submitButton')); ?>
                <?php echo CHtml::link('close', '#', array('id' => 'closeAdvancedSearch', 'class' => 'paddingLeft10')); ?>
            </div>
        </div>
    </div>

<?php $this->endWidget(); ?>
</div><!-- search-form -->
