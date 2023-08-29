<?php

class m161009_162218_parent_client_id_fk extends CDbMigration
{
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        $this->addColumn('client', 'parent_client_id', 'int');
        $this->addForeignKey('FK_parent_client', 'client', 'parent_client_id', 'client', 'id');
	}

	public function safeDown()
	{
        $this->dropForeignKey('FK_parent_client', 'client');
        $this->dropColumn('client', 'parent_client_id');
	}

}