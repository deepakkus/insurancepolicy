<?php

class m161121_185606_adding_client_api extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('ALTER TABLE [client] ADD [api] TINYINT CONSTRAINT DF__client__api DEFAULT 0 NOT NULL');
	}

	public function safeDown()
	{
        $this->execute('ALTER TABLE [client] DROP CONSTRAINT [DF__client__api], COLUMN [client]');
	}
}