<?php

class m170901_112346_update_client_report_logo_url extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("UPDATE client SET report_logo_url = '/images/nw-app2-edu-logo.png' where name = 'Nationwide'");
	}

	public function safeDown()
	{
        $this->execute("UPDATE client SET report_logo_url = '/images/nw-app2-logo.jpg.png' where name = 'Nationwide'");
	}
}