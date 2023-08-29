<?php

// Enum defining available database types.
class WDSDatabaseType
{
    const Normal = 0;
    const Trial = 1;
}

// Class that subclasses CActiveRecord to support dynamic database switching.
// Usage:
// Have models extend WDSActiveRecord. Add config/main.php trialdb connection string.
// Example: 
// $member = Member::model()->setDatabaseType(WDSDatabaseType::Trial)->find(...);
class WDSActiveRecord extends CActiveRecord
{
    private static $trialDatabase = null;
    private static $databaseType = WDSDatabaseType::Normal;
        
    public static function model($className=__CLASS__)
	{
	    return parent::model($className);
	}
            
    public function setDatabaseType($databaseType)
    {
        self::$databaseType = $databaseType;
        return $this; // return this to support chaining calls.
    }
    
    public function getDbConnection() 
    {
        if (self::$databaseType == WDSDatabaseType::Trial)
            return self::getTrialDbConnection();
        else
            return parent::getDbConnection();
    }
    
    protected static function getTrialDbConnection() 
    {
        if (self::$trialDatabase !== null) 
        {
            return self::$trialDatabase;
        }
        else
        {
            self::$trialDatabase = Yii::app()->trialdb; // assumes "trialdb" exists in config/main.php
            if (self::$trialDatabase instanceof CDbConnection) 
            {
                self::$trialDatabase->setActive(true);
                return self::$trialDatabase;
            }
            else
            {
                throw new CDbException(Yii::t('yii', 'WDSActiveRecord requires a "trialdb" CDbConnection application component.'));
            }
        }
    }
}

?>
