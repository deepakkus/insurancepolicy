<?php

class m161225_001204_new_sh_ticket_status_types extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("EXEC sp_msforeachtable 'ALTER TABLE ? NOCHECK CONSTRAINT all'");
        $this->execute("DELETE FROM eng_shift_ticket_status_type");
        $this->execute("DBCC CHECKIDENT ('eng_shift_ticket_status_type',RESEED, 0)");
        $this->execute("INSERT INTO eng_shift_ticket_status_type (type) VALUES ('new'),('submitted'),('duty officer'),('finance'),('program'),('complete')");
        $this->execute("EXEC sp_msforeachtable 'ALTER TABLE ? CHECK CONSTRAINT all'");
	}

	public function safeDown()
	{
        echo "m161225_001204_new_sh_ticket_status_types does not support migration down.\n";
		return false;
	}
}