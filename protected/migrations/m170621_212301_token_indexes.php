<?php

class m170621_212301_token_indexes extends CDbMigration
{

	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        $this->createIndex("index_type_token", "oa2_tokens", "type, oauth_token");
	}

	public function safeDown()
	{
        $this->dropIndex("index_type_token");
	}

}