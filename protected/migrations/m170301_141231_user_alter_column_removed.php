<?php

class m170301_141231_user_alter_column_removed extends CDbMigration
{
	public function safeUp()
	{
      
        $this->execute('ALTER TABLE [user] DROP COLUMN [removed]');
        $this->execute('ALTER TABLE [user] ADD [removed] tinyint DEFAULT 0 WITH VALUES');

	}

	public function safeDown()
	{
        echo "m170301_141231_user_alter_column_removed does not support migration.\n";
        echo " ";
		return false;
	}
	
}