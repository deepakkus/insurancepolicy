<?php

class m170515_071405_add_column_eng_shift_ticket_activity_tracking_location extends CDbMigration
{	
	public function safeUp()
	{
        $this->execute('ALTER TABLE [eng_shift_ticket_activity] ADD [tracking_location] VARCHAR(50) NULL');
	}
	public function safeDown()
	{
        $this->execute("DECLARE @DefaultConstraintName varchar(100)
                        SELECT @DefaultConstraintName = dc.name
                        FROM sys.default_constraints dc
                        INNER JOIN sys.columns c ON c.default_object_id = dc.object_id
                        WHERE OBJECT_NAME(parent_object_id) = 'eng_shift_ticket_activity'
                        AND c.name = 'tracking_location'

                        IF @DefaultConstraintName IS NOT NULL
                        BEGIN
                        EXEC('ALTER TABLE [eng_shift_ticket_activity] DROP CONSTRAINT ' + @DefaultConstraintName)
                        END"
                      );
        $this->execute('ALTER TABLE [eng_shift_ticket_activity] DROP COLUMN [tracking_location]');
	}
}