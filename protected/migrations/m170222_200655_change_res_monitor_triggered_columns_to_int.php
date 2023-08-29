<?php

class m170222_200655_change_res_monitor_triggered_columns_to_int extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [res_monitor_triggered] ALTER COLUMN [unmatched] INT');
        $this->execute('ALTER TABLE [res_monitor_triggered] ALTER COLUMN [unmatched_enrolled] INT');
        $this->execute('ALTER TABLE [res_monitor_triggered] ALTER COLUMN [unmatched_not_enrolled] INT');
	}

	public function safeDown()
	{
		echo "m170222_200655_change_res_monitor_triggered_columns_to_int does not support migration down.\n";
        echo "Any int values cannot be downsized to tinyints.\n";
		return false;
	}
    
}