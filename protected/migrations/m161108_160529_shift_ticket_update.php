<?php

class m161108_160529_shift_ticket_update extends CDbMigration
{
	public function safeUp()
	{
        $this->alterColumn('eng_shift_ticket_draft', 'start_miles', 'varchar(15) null');
        $this->alterColumn('eng_shift_ticket_draft', 'end_miles', 'varchar(15) null');
	}

	public function safeDown()
	{
        $this->alterColumn('eng_shift_ticket_draft', 'start_miles', 'varchar(15) not null');
        $this->alterColumn('eng_shift_ticket_draft', 'end_miles', 'varchar(15) not null');
	}
}