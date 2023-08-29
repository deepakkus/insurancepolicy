<?php

class m170221_165247_dedicated_service_client_tables extends CDbMigration
{
	public function safeUp()
	{
        // Add tables

        $this->execute("
            CREATE TABLE [client_dedicated]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [client_id] [int] NOT NULL,
                [client_dedicated_hours_id] [int] NOT NULL,
                CONSTRAINT [PK_client_dedicated] PRIMARY KEY CLUSTERED ([id] ASC)
            )

            CREATE TABLE [client_dedicated_hours]
            (
                [id] [int] IDENTITY(1,1) NOT NULL,
                [name] [varchar](100) NOT NULL,
                [dedicated_hours] [int] NOT NULL,
                [dedicated_start_date] [datetime] NOT NULL,
                [notes] [varchar](300) NULL,
                CONSTRAINT [PK_client_dedicated_hours] PRIMARY KEY CLUSTERED ([id] ASC)
            )
        ");

        // Add foreign constraints
        $this->execute("
            ALTER TABLE [client_dedicated] ADD CONSTRAINT [FK_client_dedicated_client] FOREIGN KEY ([client_id]) REFERENCES [client] ([id])

            ALTER TABLE [client_dedicated] ADD CONSTRAINT [FK_client_dedicated_dedicated_hours] FOREIGN KEY ([client_dedicated_hours_id]) REFERENCES [client_dedicated_hours] ([id]) ON DELETE CASCADE
        ");

        // Migrate old data into the new system
        $this->execute("
        
            SET NOCOUNT ON

            DECLARE
                @clientID INT,
                @clientName VARCHAR(40),
                @dedicatedHours VARCHAR(8),
                @dedicatedStartDate DATETIME,
                @notes VARCHAR(300)

            DECLARE db_cursor CURSOR FOR
            SELECT
                r.client_id,
                c.name,
                r.dedicated_hours,
                r.dedicated_start_date,
                r.notes
            FROM res_dedicated_hours r
            LEFT OUTER JOIN client c ON r.client_id = c.id

            OPEN db_cursor
            FETCH NEXT FROM db_cursor INTO @clientID,@clientName,@dedicatedHours,@dedicatedStartDate,@notes

            WHILE @@FETCH_STATUS = 0
            BEGIN

                IF @clientID IN (3,7)
                -- Liberty / Safeco
                BEGIN
                    IF NOT EXISTS (SELECT * FROM [client_dedicated_hours] WHERE [name] = 'Liberty/Safeco' AND dedicated_start_date = @dedicatedStartDate)
                    BEGIN
                        -- Not yet entered, create new entry
                        INSERT INTO [client_dedicated_hours] (name,dedicated_hours,dedicated_start_date)
                        VALUES ('Liberty/Safeco',CONVERT(INT, @dedicatedHours), @dedicatedStartDate)

                        INSERT INTO [client_dedicated] (client_id, client_dedicated_hours_id)
                        VALUES (@clientID, (SELECT TOP 1 id FROM [client_dedicated_hours] ORDER BY id DESC))
                    END
                    ELSE
                    BEGIN
                        -- Link up with previous entry
                        INSERT INTO [client_dedicated] (client_id, client_dedicated_hours_id)
                        VALUES (@clientID, (SELECT TOP 1 id FROM [client_dedicated_hours] WHERE [name] = 'Liberty/Safeco' AND dedicated_start_date = @dedicatedStartDate))
                    END
                END
                -- Everyone else
                ELSE
                BEGIN
                    INSERT INTO [client_dedicated_hours] (name,dedicated_hours,dedicated_start_date)
                    VALUES (@clientName,CONVERT(INT, @dedicatedHours), @dedicatedStartDate)

                    INSERT INTO [client_dedicated] (client_id, client_dedicated_hours_id)
                    VALUES (@clientID, (SELECT TOP 1 id FROM [client_dedicated_hours] ORDER BY id DESC))
                END

                FETCH NEXT FROM db_cursor INTO @clientID,@clientName,@dedicatedHours,@dedicatedStartDate,@notes
            END
            CLOSE db_cursor
            DEALLOCATE db_cursor

            SET NOCOUNT OFF

        ");
	}

	public function safeDown()
	{
		// echo "m170221_165247_dedicated_service_client_tables does not support migration down.\n";
		// return false;

        $this->execute('ALTER TABLE [client_dedicated] DROP CONSTRAINT [FK_client_dedicated_client]');
        $this->execute('ALTER TABLE [client_dedicated] DROP CONSTRAINT [FK_client_dedicated_dedicated_hours]');
        $this->execute('DROP TABLE [client_dedicated]');
        $this->execute('DROP TABLE [client_dedicated_hours]');
	}
}