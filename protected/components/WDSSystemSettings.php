<?php

/**
 * WDS System Settings application component
 *
 * You can access that instance via `Yii::app()->systemSettings`
 *
 * Example of usage:
 * Yii::app()->systemSettings->maxLoginAttempts;
 * 
 * @property integer $maxLoginAttempts
 * @property string $announcements
 * @property string $support
 * 
 *  @author Matt Eiben <meiben@wildfire-defense.com>
 */
class WDSSystemSettings extends CApplicationComponent
{
    private $_model = null;

    public function init()
    {
        $this->setModel();

        return parent::init();
    }

    /**
     * Retrieves the maximum allow login attempts
     * @return int
     */
    public function getMaxLoginAttempts()
    {
        return (int) $this->model->max_login_attempts;
    }

    /**
     * Retrieves the html formatted announcements text
     * @return string
     */
    public function getAnnouncements()
    {
        $announcements = $this->model->announcements;

        if (empty($announcements))
            return 'No announcements';

        return $announcements;
    }

    /**
     * Retrieves the html formatted support text
     * @return string
     */
    public function getSupport()
    {
        $support = $this->model->support;

        if (empty($support))
            return 'No Support Content';

        return $support;
    }
    
    /**
     * Setter
     */
    public function setModel()
    {
        $this->_model = SystemSettings::model()->find();
    }

    /**
     * Getter
     * @return SystemSettings
     */
    public function getModel()
    {
        return $this->_model;
    }
}