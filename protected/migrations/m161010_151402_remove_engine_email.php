<?php

class m161010_151402_remove_engine_email extends CDbMigration
{
	public function safeUp()
	{
        $this->dropColumn('client', 'engine_email');
	}

	public function safeDown()
	{
        $this->addColumn('client', 'engine_email', '[varchar](50) NULL');
	}
}