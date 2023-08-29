<div>
    <div>
        <span class="heading">Call Attempts</span> 
        <a id="lnkAddNewCallAttempt" class="paddingLeft10">Add new call attempt</a>
    </div>

    <?php
        $columnArray = array();

        foreach ($columnsToShow as $column)
        {
            $item = array(
               'name' => $column,
               'value' => '$data->' . $column, 
           );
            
            if ($column == 'evacuated' || $column == 'in_residence' || $column == 'publish')
            {
                $item['type'] = 'boolean';
            }
            else if ($column == 'id')
            {
                $item['headerHtmlOptions'] = array('style' => 'display: none');
                $item['htmlOptions'] = array('style' => 'display: none');
            }
            else if ($column == 'date_called')
            {
                $item['value'] = '(isset($data->' . $column .')) ? date("m/d/Y g:i A", strtotime($data->' . $column . ')) : "";';
            }
            
            $columnArray[] = $item;
        }
    
        $this->widget('bootstrap.widgets.TbExtendedGridView',
            array (
                'id' => 'gridCallAttempts',
                'cssFile' => '../../css/wdsExtendedGridView.css',
                'type' => 'striped bordered condensed',
                'dataProvider' => $dataProvider,
                'template' => "{summary}{items}{pager}",
                'columns' => $columnArray,
                'enableSorting' => false,
                'emptyText' => 'No call attempts have been made.',
                'selectableRows' => 1,
                'selectionChanged' => 'function(id) { WDSResCallUpdate.onGridRowSelected(id); }',
                'summaryText' => '',
            )
        );
    ?>
</div>