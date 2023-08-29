<?php

class m170414_144811_populate_order_publish extends CDbMigration
{
	public function safeUp()
    {
		//Order Number
        $this->execute('update p set p.[order] = t.[order]
			from res_ph_photos p
			inner join (select id, row_number() over (partition by visit_id order by visit_id) as [order] from res_ph_photos) t on t.id = p.id');
		//Publish
		$this->execute('update res_ph_photos set publish = 1');
    }

    public function safeDown()
    {
		//Order
        $this->execute('update res_ph_photos set publish = null');
		//Publish
        $this->execute('update res_ph_photos set [order] = null');
    }
}