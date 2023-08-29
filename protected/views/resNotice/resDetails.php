<?php
$this->renderPartial('//site/indexAnalyticsNav');
echo CHtml::beginForm($this->createUrl($this->route));
?>
<h2 class ="left">Response Details</h2>
<p>Select attributes for statistics over a period of time</p>
<div class="row-fluid" style = "background:#f5f5f5;padding:15px;">
    <div class="span6">
    <label>Clients</label>
<?php
$selectedClients = array();
$selectedOptions = array();
if(isset($_POST['Client']['id']))
{
    $selectedClients = $_POST['Client']['id'];
    foreach($selectedClients as $clients)
    {
        $selectedOptions[$clients] = array('selected'=>"selected");
    }
}

$htmlOptions = array(
    'options' => $selectedOptions,
    'multiple' => 'multiple',
    'size' => '5',
);
echo CHtml::activeDropDownList(Client::model(),'id',
    CHtml::listData(Client::model()->findAll(),'id','name'),
    $htmlOptions
)."Hold down CTRL to select multiple types";
?>
    </div>
    <div class="span6">
        <p>Start Date</p>

<?php
if(isset($_POST['startdate']) && $_POST['startdate']!= '')
{
    $startDate = $_POST['startdate'];
}
if(isset($_POST['enddate']) && $_POST['enddate']!= '')
{
    $endDate = $_POST['enddate'];
}

$this->widget('zii.widgets.jui.CJuiDatePicker', array(
	'name' => 'startdate',
	'options' => array(
		'showAnim' => 'fold',
		'dateFormat' => 'yy-mm-dd',
        'onSelect' => new CJavaScriptExpression('function(selectedDate) {
            var minDate = new Date(selectedDate);
            minDate.setDate(minDate.getDate() + 2);
            $("#enddate").datepicker("option", "minDate", minDate);
        }')
	),
	'value' => $startDate,
        'htmlOptions' => array(
        'readonly' => 'readonly',
        'style' => 'cursor: pointer;'
    )
));

?>

<p>End Date</p>

<?php

$this->widget('zii.widgets.jui.CJuiDatePicker', array(
    'name' => 'enddate',
	'options' => array(
		'showAnim' => 'fold',
		'dateFormat' => 'yy-mm-dd',
        'onSelect' => new CJavaScriptExpression('function(selectedDate) {
            var maxDate = new Date(selectedDate);
            $("#startdate").datepicker("option", "maxDate", maxDate);
        }')
	),
	'value' => $endDate,
    'htmlOptions' => array(
        'readonly' => 'readonly',
        'style' => 'cursor:pointer;',
    )
));

?>
    </div>
    <?php
    Yii::app()->clientScript->registerCss('style1',
    "
    .fixed_headers {
      width: 750px;
      table-layout: fixed;
      border-collapse: collapse;
    }
    .fixed_headers th {
      text-decoration: underline;
    }
    .fixed_headers th,
    .fixed_headers td {
      padding: 5px;
      text-align: left;
    }
    .fixed_headers td:nth-child(1),
    .fixed_headers th:nth-child(1) {
      min-width: 180px;
    }
    .fixed_headers td:nth-child(2),
    .fixed_headers th:nth-child(2) {
      min-width: 180px;
    }
    .fixed_headers td:nth-child(3),
    .fixed_headers th:nth-child(3) {
      width: 50px;
    }
    .fixed_headers td:nth-child(4),
    .fixed_headers th:nth-child(4) {
      width: 70px;
    }
    .fixed_headers td:nth-child(7),
    .fixed_headers th:nth-child(7) {
      width: 230px;
    }
    .fixed_headers td:nth-child(8),
    .fixed_headers th:nth-child(8) {
      width: 300px;
    }
    .fixed_headers td:nth-child(5),
    .fixed_headers th:nth-child(5) {
      width: 80px;
    }
    .fixed_headers td:nth-child(6),
    .fixed_headers th:nth-child(6) {
      width: 180px;
    }
    .fixed_headers thead {
      background-color: #666;
      color: #FDFDFD;
    }
    .fixed_headers thead tr {
      display: block;
      position: relative;
    }
    .fixed_headers tbody {
      display: block;
      overflow: auto;
      width: 100%;
      height: 300px;
    }
    .fixed_headers tbody tr:nth-child(even) {
      background-color: #DDD;
    }
    .old_ie_wrapper {
      height: 300px;
      width: 750px;
      overflow-x: hidden;
      overflow-y: auto;
    }
    .old_ie_wrapper tbody {
      height: auto;
    }
");
?>
<div class="row-fluid">
    <div class="span10">
    <?php echo CHtml::submitButton('Search', array('class' => 'submit')); ?>
    </div>
</div>

<?php

echo CHtml::endForm();

?>
</div>
<h3 class="center" >Results</h3>
<div class="span12 fixed_headers" >
        <?php if ($fireData): ?>
        <table class="">
            <thead class="header">
            <tr>
                <th><strong>Fire Name</strong></th>
                <th><strong>City</strong></th>
                <th><strong>State</strong></th>
                <th><strong>Acreage</strong></th>
                <th><strong>Containment</strong></th>
                <th><strong>Clients</strong></th>
                <th style="white-space: nowrap;"><strong>Total Number Threatened</strong></th>
                <th style="white-space: nowrap;"><strong>Total Coverage Amount of Threatened</strong></th>
                <th style="white-space: nowrap;"><strong>Total number of engines assigned</strong></th>
            </tr>
            </thead>
            <?php 
                $threatened = 0;
                $threatenedExp = 0;
                $enginesAssigned = 0;
                $totalFireNo = count($fireData);
                $containment = '';
                $totalThreatenedNo = 0;
                $totalCoverageAmount = 0;
            ?>

            <?php foreach ($fireData as $data):?>
            <?php  
                $enginesAssigned += $data['engines'];
                $containment = ($data['Containment']!= -1) ? $data['Containment'] .'%' : '';
                $totalThreatenedNo = $data['threatened_enrolled'] + $data['threatened_eligible'];
                $totalCoverageAmount = $data['threatened_enrolled_exp'] + $data['threatened_eligible_exp'];
                $threatened += $totalThreatenedNo;
                $threatenedExp += $totalCoverageAmount;
                $clientNamestr = $data['client_names'];
                /*
                * Show selected clients in the list
                */
                if(!empty($selectedClients))
                {
                    $clientIds = explode(",",$data['client_ids']);
                    $clientNames = explode(",",$data['client_names']);
                    $i = 0;
                    $clientNamestr = '';
                    foreach($clientIds as $clientId)
                    {
                        if(in_array($clientId,$selectedClients))
                        {
                           $clientNamestr .= $clientNames[$i].",";
                        }
                        $i++;
                    }
                    $clientNamestr = substr($clientNamestr,0,-1);
                }
            ?>
            <tr>
                <td><?= ($data['fire_name']);?></td>
                <td><?= ($data['city']);?></td>
                <td><?= ($data['state']);?></td>
                <td><?= ($data['Size']);?></td>   
                <td><?= ($containment);?></td>
                <td><?= implode('<br />', explode(',', $clientNamestr));?></td>
                <td><?= ($totalThreatenedNo);?></td>
                <td> $<?= Yii::app()->format->number($totalCoverageAmount);?></td>
                <td><?= ($data['engines']);?></td>
                
            </tr>

            <?php endforeach;?>
            <tr>    
                <td><b>Totals: </b><?= ($totalFireNo) ;?> Fire(s)</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><?= ($threatened);?></td>
                <td> $<?= Yii::app()->format->number(($threatenedExp));?></td>
                <td><?= ($enginesAssigned);?></td>
             </tr>
        </table>
        <?php else: ?>

        <p class="lead center">
            <i>No dispatched fires</i>
        </p>

        <?php endif; ?>

    </div>