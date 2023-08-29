<?php

class m170201_212622_reissue_to_reinstate extends CDbMigration
{
	public function safeUp()
	{
        //need to find and replace all 're-issue' transactions so they are 'reinstate' instead
        $this->update('properties', array('transaction_type'=>'reinstate'), "transaction_type = 're-issue'");
	}

	public function safeDown()
	{
        $this->update('properties', array('transaction_type'=>'re-issue'), "transaction_type = 'reinstate'");
	}
}