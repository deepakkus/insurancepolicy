<?php
/**
* WDSCActiveDataProvider class file.
* This class overwride fetchData() function of CActiveDataProvider class.
* This fetchData() function fix the pagination Limit issue which we are having after upgrade.
*/
class WDSGrid 
{
    
   public function getGridDataItems($grid_column,$columnsToShow,$dataProvider,$updateid)
   {
        $html = '<table class="items table table-striped table-bordered table-condensed" id="res_call_list">';
        $html .= '<thead>';
        $html .= '<tr class="hdr_anch">
          <th class="checkbox-column" id="gridResponseCallList_c0"><input type="checkbox" value="1" name="gridResponseCallList_c0_all" id="gridResponseCallList_c0_all"></th>
          <th class="button-column" id="gridResponseCallList_c1">Update</th>';
        
        $html .= $this->getGridItems($grid_column,$columnsToShow,'H');
        $html .= '</tr>';
        $html .= '<tr class="filters">
          <td>&nbsp;</td>
          <td>&nbsp;</td>';
        //$html .= $this->getFilterData($grid_column,$columnsToShow);
        $html .= $this->getGridItems($grid_column,$columnsToShow,'F');
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tfoot>
        <tr>
          <td colspan="26"><div id="egw0" style="position:relative" class="pull-left">&nbsp;
              <button class="disabled bulk-actions-btn btn btn-primary btn-small" id="btnLaunchAssignCallerDialog1" name="yt0" type="button">Assign Caller</button>
              &nbsp;&nbsp;
              <button class="disabled bulk-actions-btn btn btn-primary btn-small" id="btnPublishCaller" name="yt1" type="button">Pubish Calls</button>
              &nbsp;
              <div style="position:absolute;top:0;left:0;height:100%;width:100%;display:block;" class="bulk-actions-blocker"></div>
            </div></td>
        </tr>
      </tfoot>';
      $html .= '<tbody>';
      $html .= $this->getGridData($grid_column,$columnsToShow,$dataProvider,$updateid);
      $html .='</tbody>';
      $html .= '</table>';
	  return $html;
	}
    public function getData($grid_column,$columnsToShow,$data)
    {
        $row = 0;
        $html = '';
        foreach($columnsToShow as $columnToShow)
        {
            $html .= '<td>'.$data[$grid_column[$row++]['column']].'</td>';
        }

        return $html;
    }
    public function getGridData($grid_column,$columnArray,$dataProvider,$updateid)
    {
        
        $row = 0;
        $c = 1;
        $html = '';
        foreach($dataProvider as $data)
        {
            if($c%2==0)
            {
                $rowClass = 'even';
            }
            else
            {
                $rowClass = 'odd';
            }
            
            $html .= '<tr class="'. $rowClass.'">
          <td style="text-align: center;"><input style="width: 30px;" value="'.$data[$updateid].'" id="gridResponseCallList_c0_0" type="checkbox" name="gridResponseCallList_c0[]"></td>
          <td class="button-column"><a class="update" title="Update" rel="tooltip" href="'. Yii::app()->createUrl('resCallList/update',array('id'=>$data[$updateid])).'"><i class="icon-pencil"></i></a></td>';
            $row = 0;
            $gridrow = 0;
            foreach($columnArray as $column)
            {
                for($gridrow=0;$gridrow<count($grid_column);$gridrow++)
                {
                    if($column['name']==$grid_column[$gridrow]['gridcolumn'])
                    { 
                        if($column['format']=='number_format')
                        {
                            $html .= '<td>'.number_format($data[$grid_column[$gridrow]['column']],2).'</td>';
                        }
                        else if($column['format']=='boolean')
                        {
                            $html .= '<td>'.($data[$grid_column[$gridrow]['column']] == 1 ? "DNC": "").'</td>';
                        }
                        else if($column['type']=='boolean_number')
                        {
                            $html .= '<td>'.($data[$grid_column[$gridrow]['column']] == 1 ? "Yes": "No").'</td>';
                        }
                        else if($column['value']!='')
                        {
                            $html .= '<td>'.ResNotice::getDispatchedType($data[$grid_column[$gridrow]['column']] ).'</td>';
                        }
                        else if($column['class']!='')
                        {
                           $html .= '<td class="'.$column['class'].$data[$updateid].'">'.$data[$grid_column[$gridrow]['column']].'</td>'; 
                        }
                        else
                        {
                            $val = (isset($data[$grid_column[$gridrow]['column']]))?$data[$grid_column[$gridrow]['column']]:'';
                            $html .= '<td>'.$val.'</td>';
                        }
                    }
                }
                $row++;
            }
            $html .='</tr>';$c++;
        }
        return $html;
    }
    public function getFilterData($grid_column,$columnsToShow)
    {
        $gridrow = 0;
        $html = '';
        foreach($columnsToShow as $columnToShow)
        {
            for($gridrow=0;$gridrow<count($grid_column);$gridrow++)
            {
                if($columnToShow==$grid_column[$gridrow]['gridcolumn'])
                {
                    $html .= '<td><div class="filter-container">'.
                    CHtml::textField($grid_column[$gridrow]['gridcolumn'], '', array('style' => 'width:90px')).'
                    </div></td>';
                }
            }
        }
        return $html;
    }
    public function getGridItems($grid_column,$columnsToShow,$gridparam)
    {
        $gridrow = 0;
        $html = '';
        foreach($columnsToShow as $columnToShow)
        {
            for($gridrow=0;$gridrow<count($grid_column);$gridrow++)
            {
                if($columnToShow['name']==$grid_column[$gridrow]['gridcolumn'])
                {
                    if($gridparam=='H')
                    {
                        $html .= '<th id="gridResponseCallList_c2"><a id ="'. $grid_column[$gridrow]["id"].'" href = "">'. $grid_column[$gridrow]['label'].'</a></th>';
                    }
                    if($gridparam=='F')
                    {
                        if($columnToShow['filter']!='')
                        {
                            $field = $columnToShow['filter'];
                        }
                        else
                        {
                            $field = CHtml::textField($grid_column[$gridrow]['filterid'], '', array('style' => 'width:90px'));
                        }
                        $html .= '<td><div class="filter-container">'.$field.'
                    </div></td>';
                    }
                }
            }
        }
        return $html;
    }
}
?>
