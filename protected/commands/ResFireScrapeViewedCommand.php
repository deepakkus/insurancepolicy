<?php
class ResFireScrapeViewedCommand extends CConsoleCommand
{
    private $dbCommand;
    private $tableName = 'res_fire_scrape_viewed';
    
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
        
        $this->dbCommand = Yii::app()->db->createCommand();
        $this->dbCommand->setText('DELETE FROM res_fire_scrape_viewed WHERE id IS NOT NULL;');
        $this->dbCommand->execute();
    }
}