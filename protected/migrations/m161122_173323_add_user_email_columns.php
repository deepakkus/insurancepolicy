<?php

class m161122_173323_add_user_email_columns extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [user] ADD [dash_email_non_dispatch] TINYINT CONSTRAINT [DF__user__dash_email_non_dispatch] DEFAULT 0 NOT NULL');
        $this->execute('ALTER TABLE [user] ADD [dash_email_dispatch] TINYINT CONSTRAINT [DF__user__dash_email_dispatch] DEFAULT 0 NOT NULL');
        $this->execute('ALTER TABLE [user] ADD [dash_email_noteworthy] TINYINT CONSTRAINT [DF__user__dash_email_noteworthy] DEFAULT 0 NOT NULL');
	}

	public function safeDown()
	{
        $this->execute('ALTER TABLE [user] DROP CONSTRAINT [DF__user__dash_email_non_dispatch], COLUMN [dash_email_non_dispatch]');
        $this->execute('ALTER TABLE [user] DROP CONSTRAINT [DF__user__dash_email_dispatch], COLUMN [dash_email_dispatch]');
        $this->execute('ALTER TABLE [user] DROP CONSTRAINT [DF__user__dash_email_noteworthy], COLUMN [dash_email_noteworthy]');
	}
}