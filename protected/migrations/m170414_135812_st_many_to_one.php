<?php

class m170414_135812_st_many_to_one extends CDbMigration
{
	public function safeUp()
	{
        //add new reference direct reference between shift tickets and schedules
        $this->addColumn('eng_shift_ticket', 'eng_scheduling_id', 'int');
        $this->addForeignKey(
            'FK_eng_shift_ticket_scheduling',
            'eng_shift_ticket',
            'eng_scheduling_id',
            'eng_scheduling',
            'id'
        );

        //fill in any existing shift tickets based on first reference found in the current many to many relation
        //table eng_shift_ticket_scheduling. Because there is no production data yet this is simply to make any
        //previously entered test data usable in the dev/local/training/sqa environments.
        $shiftTickets = EngShiftTicket::model()->findAll();
        foreach($shiftTickets as $shiftTicket)
        {
            //find first relation to scheduling entry
            //Can't use this because I removed the engShiftTicketScheduling model already
            //$scheduleRelation = EngShiftTicketScheduling::model()->find('eng_shift_ticket_id = '.$shiftTicket->id);
            //Use query builder instead to get data
            $scheduleRelation = Yii::app()->db->createCommand()->select('eng_scheduling_id')->from('eng_shift_ticket_scheduling')->where('eng_shift_ticket_id = '.$shiftTicket->id)->queryRow();
            if(isset($scheduleRelation))
            {
                //if found then save save schedule id to new relation field
                $shiftTicket->eng_scheduling_id = $scheduleRelation['eng_scheduling_id'];
                $shiftTicket->save();
            }
        }

        //get rid of old many to many relation table (this has yet to be used in production so safe to just straight up drop)
        $this->dropForeignKey('FK_eng_shift_ticket_scheduling_scheduling', 'eng_shift_ticket_scheduling');
        $this->dropForeignKey('FK_eng_shift_ticket_scheduling_shift_ticket', 'eng_shift_ticket_scheduling');
        $this->dropTable('eng_shift_ticket_scheduling');
	}

	public function safeDown()
	{
	}
}