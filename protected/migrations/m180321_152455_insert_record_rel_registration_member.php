<?php
class m180321_152455_insert_record_rel_registration_member extends CDbMigration
{
	public function safeUp()
	{
        $this->insert('rel_registration_member', array(
        "registration_code_id" => 3,
        "member_id" => 318
        ));
        $this->insert('rel_registration_member', array(
        "registration_code_id" => 4,
        "member_id" => 318
        ));
        $this->insert('rel_registration_member', array(
        "registration_code_id" => 5,
        "member_id" => 1343
        ));
        $this->insert('rel_registration_member', array(
        "registration_code_id" => 6,
        "member_id" => 1351
        ));
	}

	public function safeDown()
	{
        $this->execute("
             TRUNCATE TABLE rel_registration_member
        ");
	}
}