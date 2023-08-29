<?php
    $this->setTitle('Response Property Status');
    
    // Set the manifest url so that this page may be cached for offline use.
    $this->htmlManifest = 'manifest="index.php?r=site/page&view=offlineCache"';
        
    echo CHtml::cssFile(Yii::app()->baseUrl.'/css/resPropertyStatus/admin.css');
    echo CHtml::scriptFile(Yii::app()->baseUrl.'/js/resPropertyStatus/admin.js');
    
    if (Yii::app()->user->hasFlash('message'))
    {
        echo '<div style="color:red">';
        echo Yii::app()->user->getFlash('message');
        echo '</div>';
    }

    $this->breadcrumbs = array(
        'Response' => array('admin'),
        'Property Status',
    );
?>

<div class="padding20">
    <h1>Response Property Status</h1>

    <div class="paddingTop10">
        <button id="btnColumns" type="button" class="btn marginRight10">Columns</button>
        <button id="btnResetFilters" type="button" class="btn marginRight10">Reset Filters</button>
        <button id="btnPrintChecklist" type="button" class="btn marginRight10">Print Checklist</button>
        <button id="btnSavePendingChanges" type="button" class="btn btn-primary hidden">Save Pending Changes</button>
    </div>  

    <?php    
        echo $this->renderPartial('_adminColumnsToShow', array(
            'columnsToShow' => $columnsToShow, 
            'columnsToShowName' => $columnsToShowName, 
            'pageSize' => $pageSize,
            'pageSizeName' => $pageSizeName,
        ));
    
        $columnArray = array();

        $columnArray[] = array('class' => 'bootstrap.widgets.TbButtonColumn',
            'template' => '{update}', 
            'buttons' => array(
                'update' => array('url'=>'$this->grid->controller->createUrl("/resPropertyStatus/update", array("id"=>$data->id,))',)
            ),
        );

        foreach ($columnsToShow as $columnToShow)
        {
            if (strpos($columnToShow, 'member_') !== FALSE)
            {
                if ($columnToShow == 'member_num')
                    $data_attr = 'property->member->member_num';
                else
                    $data_attr = str_replace('member_', 'property->member->', $columnToShow);

                $columnArray[] = array(
                    'name' => $columnToShow, 
                    'value' => '(isset($data->'.$data_attr.')) ? $data->'.$data_attr.' : "";',
                );
            }
            else if ($columnToShow == 'distance')
            {
                $columnArray[] = array(
                    'name' => $columnToShow,
                    'value' => 'number_format($data->'.$columnToShow.', 2)', 
                );                
            }
            else if (in_array($columnToShow, array('has_photo', 'threat')))
            {
                $columnArray[] = array(
                    'name' => $columnToShow,
                    'type' => 'boolean',
                    'value' => '$data->'.$columnToShow, 
                );
            }
            else if ($columnToShow == 'fire_name')
            {
                $columnArray[] = array(
                    'name' => $columnToShow,
                    'value' => '(isset($data->fire_name)) ? $data->fire_name : "";',
                    'filter' => CHtml::activeDropDownList($model, 'fire_name', CHtml::listData($fireNames, 'name', 'name'), array('empty' => '')),
                );
            }
            else if ($columnToShow == 'engine_name')
            {
                $columnArray[] = array(
                    'name' => $columnToShow, 
                    'value' => '(isset($data->' . $columnToShow . ')) ? $data->' . $columnToShow . ' : "";',
                    'filter' => CHtml::activeDropDownList($model, $columnToShow, CHtml::listData($engines, 'name', 'name'), array('empty' => '')),
                );
            }
            else if ($columnToShow == 'client_name')
            {
                $columnArray[] = array(
                    'name' => $columnToShow,
                    'value' => '(isset($data->' . $columnToShow . ')) ? $data->' . $columnToShow .' : "";', 
                    'filter' => CHtml::activeDropDownList($model, $columnToShow, CHtml::listData($clients, 'name', 'name'), array('empty' => '')), 
                );
            }
            else 
            {
                $columnArray[] = array(
                    'name' => $columnToShow,
                    'value' => '$data->'.$columnToShow, 
                );
            }
        }
        
        $this->widget('OfflineExtendedGridView',
            array (
                'id' => 'gridResponsePropertyStatus',
                'cssFile' => '../../css/wdsExtendedGridView.css',
                'type' => 'striped bordered condensed',
                'dataProvider' => $dataProvider,
                'filter' => $model,
                'template' => "{summary}{items}{pager}",
                'columns' => $columnArray,
                'beforeAjaxUpdate' => 'function(id, options) { WDSResPropertyStatus.onBeforeGridUpdate(); }',
                'afterAjaxUpdate' => 'function(id, data) { WDSResPropertyStatus.onAfterGridUpdate(); }',
            )
        );    
    ?>
</div>