<?php

class m161220_145408_shift_ticket_table_refactor extends CDbMigration
{
    public function safeUp()
    {
        // Dynamically remove all foreign keys
        $this->execute("
            DECLARE @foreignkey varchar(100)
            DECLARE @tablename varchar(100)
            DECLARE @command nvarchar(1000)

            DECLARE db_cursor CURSOR FOR
            SELECT fk.name, t.name
            FROM sys.foreign_keys fk
            JOIN sys.tables t ON t.object_id = fk.parent_object_id
            WHERE t.name IN (
                'eng_shift_ticket',
                'eng_shift_ticket_scheduling',
                'eng_shift_ticket_activity',
                'eng_shift_ticket_activity_type',
                'eng_shift_ticket_status_type',
                'eng_shift_ticket_draft'
            )

            OPEN db_cursor
            FETCH NEXT FROM db_cursor INTO @foreignkey, @tablename
            WHILE @@FETCH_STATUS = 0
            BEGIN
                SELECT @command = 'ALTER TABLE ' + @tablename + ' DROP CONSTRAINT ' + @foreignkey
                EXECUTE(@command)
                FETCH NEXT FROM db_cursor INTO @foreignkey, @tablename
            END
            CLOSE db_cursor
            DEALLOCATE db_cursor
        ");

        // Dropping old tables
        $this->execute("
            IF OBJECT_ID('eng_shift_ticket_draft', 'U') IS NOT NULL DROP TABLE eng_shift_ticket_draft
            IF OBJECT_ID('eng_shift_ticket_activity_type', 'U') IS NOT NULL DROP TABLE eng_shift_ticket_activity_type
            IF OBJECT_ID('eng_shift_ticket_status_type', 'U') IS NOT NULL DROP TABLE eng_shift_ticket_status_type
            IF OBJECT_ID('eng_shift_ticket_scheduling', 'U') IS NOT NULL DROP TABLE eng_shift_ticket_scheduling
            IF OBJECT_ID('eng_shift_ticket', 'U') IS NOT NULL DROP TABLE eng_shift_ticket
            IF OBJECT_ID('eng_shift_ticket_activity', 'U') IS NOT NULL DROP TABLE eng_shift_ticket_activity
        ");

        // Creating tables
        $this->execute('
            CREATE TABLE [eng_shift_ticket_activity_type]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [type] [varchar](25) NOT NULL,
                CONSTRAINT [PK_eng_shift_ticket_activity_type] PRIMARY KEY CLUSTERED ([id] ASC)
            )

            CREATE TABLE [eng_shift_ticket_status_type]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [type] [varchar](50) NOT NULL,
                CONSTRAINT [PK_eng_shift_ticket_status_type] PRIMARY KEY CLUSTERED ([id] ASC)
            )

            CREATE TABLE [eng_shift_ticket_scheduling]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [eng_scheduling_id] [int] NOT NULL,
                [eng_shift_ticket_id] [int] NOT NULL,
                CONSTRAINT [PK_eng_shift_ticket_scheduling] PRIMARY KEY CLUSTERED ([id] ASC)
            )

            CREATE TABLE [eng_shift_ticket]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [eng_shift_ticket_status_type_id] [int] NOT NULL,
                [date] datetime NOT NULL,
                [start_miles] [varchar](15) NOT NULL,
                [end_miles] [varchar](15) NOT NULL,
                [safety_meeting] [tinyint] NOT NULL,
                [safety_meeting_comments] [varchar](500) NULL,
                [locked] [tinyint] CONSTRAINT DF__eng_shift_ticket__locked DEFAULT 0 NOT NULL,
                CONSTRAINT [PK_eng_shift_ticket] PRIMARY KEY CLUSTERED ([id] ASC)
            )

            CREATE TABLE [eng_shift_ticket_activity]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [eng_shift_ticket_id] [int] NOT NULL,
                [eng_scheduling_id] [int] NOT NULL,
                [eng_shift_ticket_activity_type_id] [int] NOT NULL,
                [start_time] [time] NOT NULL,
                [end_time] [time] NOT NULL,
                [comment] [varchar](200) NULL,
                CONSTRAINT [PK_eng_shift_ticket_activity] PRIMARY KEY CLUSTERED ([id] ASC)
            )
        ');

        // Some new content
        $this->execute("
            INSERT INTO eng_shift_ticket_activity_type (type) VALUES ('Equipment Check'),('Safety Meeting'),('IMT Coordination'),('Policyholder'),('MOB/DEMOB'),('Dedicated'),('Assessment')
            INSERT INTO eng_shift_ticket_status_type (type) VALUES ('new'),('submitted'),('duty officer'),('finance'),('MOB/DEMOB'),('complete')
        ");

        // Creating foreign keys
        $this->execute('
            ALTER TABLE [eng_shift_ticket_scheduling] ADD CONSTRAINT FK_eng_shift_ticket_scheduling_scheduling FOREIGN KEY ([eng_scheduling_id]) REFERENCES [eng_scheduling] ([id])
            ALTER TABLE [eng_shift_ticket_scheduling] ADD CONSTRAINT FK_eng_shift_ticket_scheduling_shift_ticket FOREIGN KEY ([eng_shift_ticket_id]) REFERENCES [eng_shift_ticket] ([id])
            ALTER TABLE [eng_shift_ticket] ADD CONSTRAINT FK_eng_shift_ticket_status FOREIGN KEY ([eng_shift_ticket_status_type_id]) REFERENCES [eng_shift_ticket_status_type] ([id])
            ALTER TABLE [eng_shift_ticket_activity] ADD CONSTRAINT FK_eng_shift_ticket_activity_shift_ticket FOREIGN KEY ([eng_shift_ticket_id]) REFERENCES [eng_shift_ticket] ([id])
            ALTER TABLE [eng_shift_ticket_activity] ADD CONSTRAINT FK_eng_shift_ticket_activity_scheduling FOREIGN KEY ([eng_scheduling_id]) REFERENCES [eng_scheduling] ([id])
            ALTER TABLE [eng_shift_ticket_activity] ADD CONSTRAINT FK_eng_shift_ticket_activity_type FOREIGN KEY ([eng_shift_ticket_activity_type_id]) REFERENCES [eng_shift_ticket_activity_type] ([id])
        ');
    }

    public function safeDown()
    {
        $this->execute("
            DECLARE @foreignkey varchar(100)
            DECLARE @tablename varchar(100)
            DECLARE @command nvarchar(1000)

            DECLARE db_cursor CURSOR FOR
            SELECT fk.name, t.name
            FROM sys.foreign_keys fk
            JOIN sys.tables t ON t.object_id = fk.parent_object_id
            WHERE t.name IN (
                'eng_shift_ticket',
                'eng_shift_ticket_scheduling',
                'eng_shift_ticket_activity',
                'eng_shift_ticket_activity_type',
                'eng_shift_ticket_status_type',
                'eng_shift_ticket_draft'
            )

            OPEN db_cursor
            FETCH NEXT FROM db_cursor INTO @foreignkey, @tablename
            WHILE @@FETCH_STATUS = 0
            BEGIN
                SELECT @command = 'ALTER TABLE ' + @tablename + ' DROP CONSTRAINT ' + @foreignkey
                EXECUTE(@command)
                FETCH NEXT FROM db_cursor INTO @foreignkey, @tablename
            END
            CLOSE db_cursor
            DEALLOCATE db_cursor
        ");


        $this->execute("
            IF OBJECT_ID('eng_shift_ticket_activity_type', 'U') IS NOT NULL DROP TABLE eng_shift_ticket_activity_type
            IF OBJECT_ID('eng_shift_ticket_status_type', 'U') IS NOT NULL DROP TABLE eng_shift_ticket_status_type
            IF OBJECT_ID('eng_shift_ticket_scheduling', 'U') IS NOT NULL DROP TABLE eng_shift_ticket_scheduling
            IF OBJECT_ID('eng_shift_ticket', 'U') IS NOT NULL DROP TABLE eng_shift_ticket
            IF OBJECT_ID('eng_shift_ticket_activity', 'U') IS NOT NULL DROP TABLE eng_shift_ticket_activity
            IF OBJECT_ID('eng_shift_ticket_history', 'U') IS NOT NULL DROP TABLE eng_shift_ticket_history
        ");
    }
}