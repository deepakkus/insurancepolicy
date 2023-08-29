<?php
class USAAPolicyStatusUpdateCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');
		print "\nStarting USAA Policy Status Update for ".date('Y-m-d')."\n";

        //Look up all properties/policies where Today > policy_expiration AND policy_status = 'active' AND transaction_type = 'non-renew'
        //and set policy_status = 'expired'
        //(NOTE: otherwise we just assume policies are renewing if we didn't get a non-renew...effective dates will come on change list)
        print "Starting expire loop\n";
        $propsToExpire = Property::model()->findAll("'".date('Y-m-d')."' > policy_expiration AND policy_status = 'active' AND transaction_type = 'non-renew'");
        foreach($propsToExpire as $prop)
        {
            $prop->policy_status = 'expired';
            $prop->policy_status_date = date('Y-m-d');
            if(!$prop->save())
            {
                print 'Could not save prop. Details: ' . var_export($prop->getErrors(), true) . "\n";
                $errors++;
            }
        }
        print "Updated ".count($propsToExpire)." Properties to policy_status = 'expired'\n";

        //Look up all properties/policies where Today >= policy_effective AND policy_status = 'pending'
        //and set policy_status = 'active'
        //also set transaction type to renew as we are going to assume this until further notice, this will make it so that it stays active until a non-renew or cancel comes in the add/drops
        print "Starting activate loop\n";
        $propsToActivate = Property::model()->findAll("'".date('Y-m-d')."' >= policy_effective AND policy_status = 'pending'");
        foreach($propsToActivate as $prop)
        {
            $prop->policy_status = 'active';
            $prop->policy_status_date = date('Y-m-d');
            $prop->transaction_type = 'renew';
            if(!$prop->save())
            {
                print 'Could not save prop. Details: ' . var_export($prop->getErrors(), true) . "\n";
                $errors++;
            }
        }
        print "Updated ".count($propsToActivate)." Properties to policy_status = 'active'\n";

        //Look up all properties/policies where Today >= transaction_effective date AND transaction_type = 'cancel'
        //and set policy_status = 'canceled'
        print "Starting cancel loop\n";
        $propsToCancel = Property::model()->findAll("'".date('Y-m-d')."' >= transaction_effective AND policy_status = 'active' AND transaction_type = 'cancel'");
        foreach($propsToCancel as $prop)
        {
            $prop->policy_status = 'canceled';
            $prop->policy_status_date = date('Y-m-d');
            if(!$prop->save())
            {
                print 'Could not save prop. Details: ' . var_export($prop->getErrors(), true) . "\n";
                $errors++;
            }
        }
        print "Updated ".count($propsToCancel)." Properties to policy_status = 'canceled'\n";

        print "Done Updating USAA Policy Statuses\n";
    }
}