<div class="table-responsive">
    <?php $this->widget('bootstrap.widgets.TbExtendedGridView', array(
    	'id'=>'eng-resource-order-grid-modal',
        'cssFile' => '../../css/wdsExtendedGridView.css',
        'type' => 'striped hover condensed',
    	'dataProvider'=>$dataProvider,
    	'columns'=>array(
            'id',
            array(
                'header' => 'Engine',
                'name' => 'engineName',
                'type' => 'html',
                'filter' => ''
            ),
            array(
                'header' => 'Assignment',
                'name' => 'engineAssignment',
                'type' => 'html',
                'filter' => ''
            ),
            array(
                'header' => 'City',
                'name' => 'engineCity',
                'type' => 'html',
                'filter' => ''
            ),
            array(
                'header' => 'State',
                'name' => 'engineState',
                'type' => 'html',
                'filter' => ''
            ),
            array(
                'header' => 'Start Date',
                'name' => 'dateStart',
                'type' => 'html',
                'filter' => ''
            ),
           /* array(
                'class' => 'CLinkColumn',
                'header' => 'Use RO #',
                'label' => 'Use',
                'htmlOptions' => array('class' => 'ro-modal-link'),
            ),*/
    	),
        'enableSorting' => false,
        'enablePagination' => false,
        'emptyText' => 'No resource orders founds.'
    )); ?>
</div>