<?php

class m170818_170141_adding_ineligible_to_enrollment_status_table extends CDbMigration
{
    public function safeUp()
    {
        $this->execute("DELETE FROM enrollment_status WHERE status = 'ineligible'");
        $this->execute("INSERT INTO enrollment_status (status) VALUES ('ineligible')");
    }

    public function safeDown()
    {
        $this->execute("DELETE FROM enrollment_status WHERE status = 'ineligible'");
    }
}