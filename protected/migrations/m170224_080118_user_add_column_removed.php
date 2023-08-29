<?php

class m170224_080118_user_add_column_removed extends CDbMigration
{
	
	public function safeUp()
	{
        $this->execute('ALTER TABLE [user] DROP COLUMN [removed]');
        $this->execute('ALTER TABLE [user] ALTER COLUMN [removed] tinyint NOT NULL DEFAULT 0');
	}

	public function safeDown()
	{
        echo "m170224_080118_user_add_column_removed does not support migration down.\n";
		return false;
	}
	
}