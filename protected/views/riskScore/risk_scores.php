<?php

/* @var $this RiskBatchController */
/* @var $model RiskScore */

$this->breadcrumbs = array(
    'Risk Scores'
);

Yii::app()->format->datetimeFormat = 'Y-m-d H:i';
Yii::app()->clientScript->registerScriptFile('/js/riskScore/risk_scores.js');

?>

<h1>Risk Scores</h1>

<div class="marginTop20">
    <a class="column-toggle paddingRight20" href="#">Columns</a>
    <a class="search-toggle paddingRight20" href="#">Search</a>
</div>

<?php echo $this->renderPartial('_columnsToShow', array('columnsToShow' => $columnsToShow)); ?>
<?php echo $this->renderPartial('_riskScoresSearch', array('riskScore' => $riskScore )); ?>

<div class="table-responsive">

    <?php

    $columns = array();

    foreach ($columnsToShow as $columnToShow)
    {
        switch ($columnToShow)
        {
            case 'geocoded':
            case 'processed':
                $columns[] = $columnToShow . ':boolean';
                break;
            case 'date_created':
                $columns[] = $columnToShow . ':datetime';
                break;
            default:
                $columns[] = $columnToShow;
                break;
        }
    }

    $this->widget('bootstrap.widgets.TbExtendedGridView', array(
		'id'=>'risk-score-grid',
		'cssFile' => '../../css/wdsExtendedGridView.css',
		'type' => 'striped bordered condensed',
		'dataProvider' => $riskScore->search(),
		'columns' => $columns
	));
    
    ?>
	
</div>