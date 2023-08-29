<?php

class m161031_154205_remove_sys_settings_apimd extends CDbMigration
{
	public function safeUp()
	{
        $this->dropColumn('system_settings', 'api_md');
	}

	public function safeDown()
	{
		$this->addColumn('system_settings', 'api_md', '[varchar](max) NULL');
	}
}