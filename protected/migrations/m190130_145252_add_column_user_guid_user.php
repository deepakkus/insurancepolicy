<?php

class m190130_145252_add_column_user_guid_user extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [user] ADD [user_guid] VARCHAR(500) NULL');
	}

	public function safeDown()
	{
        $this->execute('ALTER TABLE [user] DROP COLUMN [user_guid]');
	}
	
}