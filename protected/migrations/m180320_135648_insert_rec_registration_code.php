<?php

class m180320_135648_insert_rec_registration_code extends CDbMigration
{
	public function safeUp()
	{
        $this->insert('registration_code', array(
        "code" => "FS85FS0pPBlu",
        "is_active" => "1"
        ));
        $this->insert('registration_code', array(
        "code" => "FS45FSpk3PHlu",
        "is_active" => "1"
        ));
        $this->insert('registration_code', array(
        "code" => "FS20FS2kBUvy",
        "is_active" => "1"
        ));
        $this->insert('registration_code', array(
        "code" => "FS44FSpr7EWks",
        "is_active" => "1"
        ));
	}

	public function safeDown()
	{
        $this->execute("
            DELETE FROM [registration_code]
            WHERE [code] = 'FS85FS0pPBlu'
        ");
        $this->execute("
            DELETE FROM [registration_code]
            WHERE [code] = 'FS45FSpk3PHlu'
        ");
        $this->execute("
            DELETE FROM [registration_code]
            WHERE [code] = 'FS20FS2kBUvy'
        ");
        $this->execute("
            DELETE FROM [registration_code]
            WHERE [code] = 'FS44FSpr7EWks'
        ");
	}
	
}