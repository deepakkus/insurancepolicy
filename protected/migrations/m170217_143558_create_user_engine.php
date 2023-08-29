<?php

class m170217_143558_create_user_engine extends CDbMigration
{
	public function safeUp()
	{
        echo "This is a duplicate migration";

        return true;
	}

	public function safeDown()
	{
		return true;
	}
}