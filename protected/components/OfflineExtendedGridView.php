<?php

Yii::import('bootstrap.widgets.TbExtendedGridView');

/**
 * Extends the TbExtendedGridView for offline support.
 */
class OfflineExtendedGridView extends TbExtendedGridView
{        
    /*
     * Override renderContent so that the grid's data can be stored in 
     * a hidden div for javascript offline access. This will get rendered 
     * below the grid's pager.
     */
    public function renderContent()
    {
        parent::renderContent();
        
        $pdata = '[';
        foreach ($this->dataProvider->getData() as $record)
        {
            $data = json_encode($record) . json_encode($record->attributes);                                   
            $pdata .= preg_replace('/\}\{/', ',', $data) . ',';
        }
        
        $pdata = rtrim($pdata, ',') . ']';

        echo "<div id='offlineExtendedGridViewData' class='hidden'>$pdata</div>";
    }
}
