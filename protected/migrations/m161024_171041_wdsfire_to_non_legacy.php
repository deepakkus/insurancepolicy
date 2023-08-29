<?php

class m161024_171041_wdsfire_to_non_legacy extends CDbMigration
{
	public function safeUp()
	{
        $this->update('user', array('type' => 'OAuth2'), 'username = :username', array(
            ':username' => 'wds_dash'
        ));
	}

	public function safeDown()
	{
        $this->update('user', array('type' => 'OAuth2 Legacy'), 'username = :username', array(
            ':username' => 'wds_dash'
        ));
	}
}