<?php

class m180906_124324_insert_records_service_offering extends CDbMigration
{
	
	public function safeUp()
	{
        $this->insert('service_offering', array(
        "service_offering_name" => "App2.0",
        "service_offering_code" => "AP"
        ));
	}

	public function safeDown()
	{
        echo "m180906_124324_insert_records_service_offering does not support migration down.\n";
		return false;
	}
	
}