<?php

class m161011_174854_remove_sp_get_policyholders_by_fire extends CDbMigration
{
	public function safeUp()
	{
        $this->execute('DROP PROCEDURE sp_get_policyholders_by_fire');
	}

	public function safeDown()
	{
        $this->execute('
            -- =============================================
            -- Author:		Eric Barnes
            -- Create date: 2014-04-21
            -- Description:	Retrieves policyholder information
            -- for a particular fire for a given client.
            -- =============================================
            CREATE PROCEDURE [dbo].[sp_get_policyholders_by_fire]
	            @fire_id int,
	            @client_id int
            AS
            BEGIN
	            -- SET NOCOUNT ON added to prevent extra result sets from
	            -- interfering with SELECT statements.
	            SET NOCOUNT ON;

	            SELECT t.property_pid pid,
		            max(t.priority) priority,
		            max(t.threat) threat,
		            max(m.first_name) fname,
		            max(m.last_name) lname,
		            max(p.address_line_1) address,
		            max(p.city) city,
		            max(p.state) state,
		            max(p.zip) zip,
		            max(p.coverage_a_amt) coverage,
		            max(p.response_status) response_status,
		            max(t.response_status) snapshot_status,
		            max(t.distance) distance
	            -- EJB HACK 2014-05-14 - making this work for the client-specific usaa table for now.
	            -- CC Removed hack and set notice source to generic table and uncommented the client_id clause
	            FROM res_notice n
		            join res_triggered t on t.notice_id = n.notice_id
		            join properties p on p.pid = t.property_pid
		            join members m on m.mid = p.member_mid
	            WHERE n.fire_id = @fire_id AND n.client_id = @client_id
	            GROUP BY t.property_pid
	            ORDER BY lname

            END
        ');
	}
}