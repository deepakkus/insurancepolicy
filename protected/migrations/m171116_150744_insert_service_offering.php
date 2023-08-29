<?php

class m171116_150744_insert_service_offering extends CDbMigration
{
	
	public function safeUp()
	{
        $this->insert('service_offering', array(
        "service_offering_name" => "WDSed(Internal Agents)",
        "service_offering_code" => "IA"
        ));
        $this->insert('service_offering', array(
        "service_offering_name" => "FireShield",
        "service_offering_code" => "FS"
        ));
        $this->insert('service_offering', array(
        "service_offering_name" => "Second Look",
        "service_offering_code" => "SL"
        ));
        $this->insert('service_offering', array(
        "service_offering_name" => "Public",
        "service_offering_code" => "PU"
        ));
	}

	public function safeDown()
	{
        $this->execute('truncate table service_offering');
	}
	
}