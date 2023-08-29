<?php

class m161004_221937_add_property_location extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('properties', 'location', "varchar(10) not null DEFAULT '1'");
	}

	public function safeDown()
	{
        $this->dropColumn('properties', 'location');
	}

}