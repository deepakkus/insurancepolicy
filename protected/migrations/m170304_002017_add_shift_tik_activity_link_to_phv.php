<?php

class m170304_002017_add_shift_tik_activity_link_to_phv extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('eng_shift_ticket_activity', 'res_ph_visit_id', 'int');
        $this->addForeignKey('FK_phv', 'eng_shift_ticket_activity', 'res_ph_visit_id', 'res_ph_visit', 'id');
	}

	public function safeDown()
	{
        $this->dropForeignKey('FK_phv', 'eng_shift_ticket_activity');
        $this->dropColumn('eng_shift_ticket_activity', 'res_ph_visit_id');
	}

}