<?php

class m170615_181026_add_desc_fields_to_ST_Activity_Types extends CDbMigration
{
    public function safeUp()
	{
        $this->execute('ALTER TABLE [eng_shift_ticket_activity_type] ADD [description] VARCHAR(500) NULL');
        $this->execute('ALTER TABLE [eng_shift_ticket_activity_type] ADD [active] [tinyint] NOT NULL DEFAULT(1)');
        $this->execute("UPDATE [eng_shift_ticket_activity_type] SET [type] = 'Incident' WHERE [type] = 'IMT Coordination'");
        $this->execute("UPDATE [eng_shift_ticket_activity_type] SET [active] = 0 WHERE [type] NOT IN ('Incident', 'Policyholder', 'MOB/DEMOB', 'Break')");
        $this->execute("UPDATE [eng_shift_ticket_activity_type] SET [description] = 'The time needed to coordinate access, attend the incident briefing, drive time to and from the incident briefing, tying in with local resources, structure protection unit, division, etc., equipment check and safety meeting.' WHERE [type] = 'Incident'");
        $this->execute("UPDATE [eng_shift_ticket_activity_type] SET [description] = 'The time spent on the policyholder property or patrolling in the area near our policies. Policyholder time includes travel to each policyholder property, travel to the hotel at the end of the day, filling out shift tickets, uploading photos, and the morning Ops conference call. Use policyholder time as the default activity on dedicated service.' WHERE [type] = 'Policyholder'");
        $this->execute("UPDATE [eng_shift_ticket_activity_type] SET [description] = 'The travel time to and from a dispatched incident only. Do not include daily travel time to incident briefings, policyholder visits, etc.' WHERE [type] = 'MOB/DEMOB'");
	}

	public function safeDown()
	{
        $this->execute('ALTER TABLE [eng_shift_ticket_activity_type] DROP COLUMN [descrpiption]');
        $this->execute('ALTER TABLE [eng_shift_ticket_activity_type] DROP COLUMN [active]');
        $this->execute("UPDATE [eng_shift_ticket_activity_type] SET [type] = 'IMT Coordination' WHERE [type] = 'Incident'");
	}
}