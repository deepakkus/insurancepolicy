<?php

class m170412_190417_ph_photos extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('res_ph_photos', 'publish', 'tinyint');
		$this->addColumn('res_ph_photos', 'order', 'tinyint');
	}

	public function safeDown()
	{
		$this->dropColumn('res_ph_photos', 'publish');
		$this->dropColumn('res_ph_photos', 'order');
	}
}