<?php

class m170307_044347_add_submitted_user_id_to_st extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('eng_shift_ticket', 'submitted_by_user_id', 'smallint');
        $this->addForeignKey('FK_submitted_by_user', 'eng_shift_ticket', 'submitted_by_user_id', 'user', 'id');
        $shiftTickets = EngShiftTicket::model()->findAll();
        foreach($shiftTickets as $shiftTicket)
        {
            $origHistoryModel = ModelHistory::model()->find(array(
                'select' => 'data',
                'condition' => "[table] = 'eng_shift_ticket' AND table_pk = ".$shiftTicket->id,
                'order' => 'id ASC'
            ));
            $dataArray = json_decode($origHistoryModel->data, true);
            $this->update('eng_shift_ticket', array('submitted_by_user_id'=>$dataArray['user_id']), 'id = '.$shiftTicket->id);
        }
	}

	public function safeDown()
	{
        $this->dropForeignKey('FK_submitted_by_user', 'eng_shift_ticket');
        $this->dropColumn('eng_shift_ticket', 'submitted_by_user_id');
	}

}