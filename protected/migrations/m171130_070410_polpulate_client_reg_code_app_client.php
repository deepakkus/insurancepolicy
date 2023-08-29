<?php

class m171130_070410_polpulate_client_reg_code_app_client extends CDbMigration
{
    //safe up
	public function safeUp()
	{
        $this->execute("UPDATE [client] SET [client_reg_code] = 85, app = 1 WHERE id = 1");
	}

    // safe down
    public function safeDown()
	{
        $this->execute("UPDATE [client] SET [client_reg_code] = NULL, app = NULL WHERE id = 1");
	}
}