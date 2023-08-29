<?php

class m161014_014208_fs_carrier_key_index extends CDbMigration
{
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        $this->createIndex('carrier_key', 'members', 'fs_carrier_key');
	}

	public function safeDown()
	{
        $this->dropIndex('carrier_key', 'members');
	}
}