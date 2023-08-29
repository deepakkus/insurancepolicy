<?php

class m170504_100756_engshift_ticket_alter_column_safty_meeting extends CDbMigration
{
	public function safeUp()
	{
        $this->execute("DECLARE @DefaultConstraintName varchar(100)
                        SELECT @DefaultConstraintName = dc.name
                        FROM sys.default_constraints dc
                        INNER JOIN sys.columns c ON c.default_object_id = dc.object_id
                        WHERE OBJECT_NAME(parent_object_id) = 'eng_shift_ticket'
                        AND c.name = 'safety_meeting'

                        IF @DefaultConstraintName IS NOT NULL
                        BEGIN
                        EXEC('ALTER TABLE [eng_shift_ticket] DROP CONSTRAINT ' + @DefaultConstraintName)
                        END"
                      );
        $this->execute('ALTER TABLE [eng_shift_ticket] DROP COLUMN [safety_meeting]');
	}

	public function safeDown()
	{
		$this->execute('ALTER TABLE [eng_shift_ticket] ADD [safety_meeting] tinyint NOT NULL DEFAULT 0 WITH VALUES');
	}
}