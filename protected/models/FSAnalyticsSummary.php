<?php
/**
 * Custom model to hold summary data for FS Analytics/Metrics.
 */
class FSAnalyticsSummary extends CModel
{
    protected $memberReleased;
    protected $memberCompleted;
    protected $memberTakeUp;
    
    protected $propertyReleased;
    protected $propertyCompleted;
    protected $propertyTakeUp;
    
    protected $additionalCompleted;
    protected $totalCompleted;    
    
    protected $completedWithinSLO;
    protected $avgCycleTime;
    protected $avgTakeUpTime;
    
    public $serviceLevelSummary;
    public $serviceLevelSummaryPercentages;
    
    public $LOSBreakdown;
    public $LOSBreakdownPercentages;
    public $LOSMove;
    public $LOSMovePercentages;
    
    public $nonTakeUpTotals;
    public $nonTakeUpPercentages;
    
    protected $totalMembersOffered;
    protected $totalMembersEnrolled;
    
    public function FSAnalyticsSummary($sqlResultSet = NULL) 
    {
        $this->setDefaults();
        
        // Populate values if provided from a SQL resultset row.
        if (isset($sqlResultSet) && count($sqlResultSet) == 1)
        {
            $row = $sqlResultSet[0];
            $this->memberReleased = $row['member_released'];
            $this->memberCompleted = $row['member_completed'];
            $this->memberTakeUp = $row['member_take_up'];
            $this->propertyReleased = $row['property_released'];
            $this->propertyCompleted = $row['property_completed'];
            $this->propertyTakeUp = $row['property_take_up'];
            $this->totalCompleted = $row['total_completed'];
            $this->nonTakeUpTotals[0] = $row['non_take_up_geo_risk_1'];
            $this->nonTakeUpTotals[1] = $row['non_take_up_geo_risk_2'];
            $this->nonTakeUpTotals[2] = $row['non_take_up_geo_risk_3'];
        }
    }
    
    private function setDefaults()
    {
        $this->memberReleased = 0;
        $this->memberCompleted = 0;
        $this->memberTakeUp = 0;
        
        $this->propertyReleased = 0;
        $this->propertyCompleted = 0;
        $this->propertyTakeUp = 0;
        
        $this->additionalCompleted = 0;
        $this->totalCompleted = 0;
        
        $this->completedWithinSLO = 0;
        $this->avgCycleTime = 0;        
        $this->avgTakeUpTime = 0;
        
        $this->serviceLevelSummary = array(0, 0, 0);
        $this->serviceLevelSummaryPercentages = array(0, 0, 0);
        
        $this->LOSBreakdown = array(
            array(0, 0, 0),
            array(0, 0, 0),
            array(0, 0, 0),
        );
        $this->LOSBreakdownPercentages = array(
            array(0, 0, 0),
            array(0, 0, 0),
            array(0, 0, 0),
        );
        $this->LOSMove = array(0, 0, 0, 0, 0, 0, 0);
        $this->LOSMovePercentages = array(0, 0, 0, 0, 0, 0, 0);
        
        $this->nonTakeUpTotals = array(0, 0, 0);
        $this->nonTakeUpPercentages = array(0, 0, 0);
        
        $this->totalMembersEnrolled = 0;
        $this->totalMembersOffered = 0;        
    }
    
    public function attributeNames()
    {        
        return array(
            'memberReleased',
            'memberCompleted',
            'memberTakeUp',
            'propertyReleased',
            'propertyCompleted',
            'propertyTakeUp',
            'additionalCompleted',
            'totalCompleted',            
            'completedWithinSLO',
            'avgCycleTime',
            'avgTakeUpTime',
            'totalMembersEnrolled',
            'totalMembersOffered',
        );
    }    
    
    public function __get($name) 
    {
        if (property_exists($this, $name)) 
        {
            return $this->$name;
        }
        else
        {
            return parent::__get($name);
        }
    }
    
    public function __set($name, $value)
    {
        if (property_exists($this, $name))
        {
            $this->$name = $value;
        }
        else
        {
            parent::__set($name, $value);
        }
    }
}

?>
