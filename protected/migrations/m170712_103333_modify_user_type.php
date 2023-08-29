<?php

class m170712_103333_modify_user_type extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [user] ALTER COLUMN [type] VARCHAR(MAX) NULL');
	}

	public function safeDown()
	{
         $this->execute('ALTER TABLE [user] ALTER COLUMN [type] VARCHAR(500)');
	}
}