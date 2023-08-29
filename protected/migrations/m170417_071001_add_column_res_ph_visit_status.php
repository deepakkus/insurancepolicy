<?php

class m170417_071001_add_column_res_ph_visit_status extends CDbMigration
{
	public function safeUp()
	{
        $this->addColumn('res_ph_visit', 'review_status', 'varchar(50)');	
        $this->createIndex('review_status', 'res_ph_visit', 'review_status', false);
        $this->update('res_ph_visit', array('review_status' => 'published'), 'publish = 1');
        $this->update('res_ph_visit', array('review_status' => 'not reviewed'), 'publish = 0');        
        $this->execute("DECLARE @DefaultConstraintName varchar(100)
                        SELECT @DefaultConstraintName = dc.name
                        FROM sys.default_constraints dc
                        INNER JOIN sys.columns c ON c.default_object_id = dc.object_id
                        WHERE OBJECT_NAME(parent_object_id) = 'res_ph_visit'
                        AND c.name = 'publish'

                        IF @DefaultConstraintName IS NOT NULL
                        BEGIN
                        EXEC('ALTER TABLE [res_ph_visit] DROP CONSTRAINT ' + @DefaultConstraintName)
                        END"
                      );         
        $this->dropColumn('res_ph_visit', 'publish'); 
               
	}

	public function safeDown()
	{
        $this->dropIndex('review_status', 'res_ph_visit');
        $this->dropColumn('res_ph_visit', 'review_status');
	    $this->addColumn('res_ph_visit', 'publish', 'int');        
	}
}