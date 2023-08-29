<div class="formContainer">
    <h3>Pre Risk Entries for this Property</h3>
    <?php
        $this->widget('zii.widgets.grid.CGridView', array(
            'id'=>'pre_risks-grid',
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
                'id', 'status', 'engine', 'ha_time', 'ha_date', 'call_list_month', 'call_list_year',
            ),
        ));
    ?>
</div>