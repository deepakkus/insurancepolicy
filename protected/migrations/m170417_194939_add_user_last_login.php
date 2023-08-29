<?php

class m170417_194939_add_user_last_login extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [user] ADD [last_login] [datetime] NULL');
        $this->execute('UPDATE [user] SET [last_login] = [pw_exp]');
	}

	public function safeDown()
	{
        $this->execute('ALTER TABLE [user] DROP COLUMN [last_login]');
	}
}