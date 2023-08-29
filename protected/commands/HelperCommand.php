<?php
class HelperCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        print "-----STARTING HELPER COMMAND--------\n";

        if(isset($args[0]))
        {
            if($args[0] == "fix_states")
                $this->fix_states();
            elseif($args[0] == "cancel_chubb_props")
                $this->cancel_chubb_props();
            elseif($args[0] == 'usaa_ok_auto_enroll_fix')
                $this->usaa_ok_auto_enroll_fix();
            elseif($args[0] == 'test_sftp')
                $this->test_sftp();
            else
                print "Error: First argument must be a recognized function\n";
        }
        else
            print "Error: Must pass in function to run as first argument\n";

        print "-----DONE WITH HELPER COMMAND-------\n";
    }

    private function fix_states()
    {
        $statesToChange = array_keys(Helper::$statesToAbbrDict);
        foreach($statesToChange as $stateToChange)
        {
            $props = Property::model()->findAllByAttributes(array('state'=>$stateToChange));
            print "--Starting State update for: $stateToChange. Found ".count($props)." to update; \n";
            foreach($props as $prop)
            {
                print "Changing PID: ".$prop->pid." with State: ".$prop->state;
                $prop->state = Helper::$statesToAbbrDict[$stateToChange];
                $prop->save() or exit("Could not save prop. Details: ".var_export($property->getErrors(), true));
                print " to State: ".$prop->state."\n";
            }
        }
    }

    private function cancel_chubb_props()
    {
        //setup import_results file
		$results_fh = fopen('C:\\wds_pro\\protected\\imports\\chubb_cancel_import_results.csv', 'w');
		fputcsv($results_fh, array('wds_pid', 'Policy #', 'Sequence #', 'Import Action', 'Address Line 1', 'Address Line 2', 'City', 'State', 'Zip'));

        $props_to_cancel = Property::model()->with('member')->findAll("flag = 0 AND member.client = 'Chubb' AND policy_status != 'canceled'");
        //loop through all Chubb properties in the database that did not get their flags set to 1 and set their policy_status to cancelled
        $canceled_props = count($props_to_cancel);
        //$this->printUpdateStatus($fileToImport, "Processing", "Done Importing, Running Cancel Routine (Updated Mems: $updated_members, New Mems: $new_members,  New Props: $new_properties, Updated Props: $updated_properties, Canceled Props: $canceled_props, Errors: $errors)");
        foreach($props_to_cancel as $prop_to_cancel)
        {
            $results_action = 'Cancel';
            $other_props = Property::model()->findAllByAttributes(array('member_mid'=>$prop_to_cancel->member_mid, 'policy_status'=>'active'));
            foreach($other_props as $other_prop)
            {
                if($other_prop->address_line_1 == $prop_to_cancel->address_line_1 && $prop_to_cancel->pid != $other_prop->pid)
                    $results_action = 'Cancel-ReWrite';
            }

            $prop_to_cancel->policy_status = 'canceled';
            $prop_to_cancel->policy_status_date = date('Y-m-d H:i:s');
            if(!$prop_to_cancel->save())
            {
                print 'Error on saveing property being cancelled. pid: '.$prop_to_cancel->pid."\n";
            }
            else
            {
                //add to results file with the action that was done
                //columns are pid, chubb policy #, chubb sequence #, and action
                print $results_action.' PID '.$prop_to_cancel->pid."\n";
                fputcsv($results_fh, array($prop_to_cancel->pid, $prop_to_cancel->policy, $prop_to_cancel->seq_num, $results_action, $prop_to_cancel->address_line_1, $prop_to_cancel->address_line_2, $prop_to_cancel->city, $prop_to_cancel->state, $prop_to_cancel->zip));

            }
        }
    }

    private function usaa_ok_auto_enroll_fix()
    {
        print "STARTING USAA OK AUTO ENROLL FIX\n\n";

        //setup results file
        $results_fh = fopen(Helper::getDataStorePath().'import_files'.DIRECTORY_SEPARATOR.'usaa_ok_auto_enroll_fix_results.csv', 'w');
		fputcsv($results_fh, array('wds_pid', 'result'));

        $ok_props_that_need_auto_enrolling = Property::model()->findAllBySql("
            SELECT pid FROM properties WHERE
            client_id = 1 AND
            [state] = 'OK' AND
            response_status != 'enrolled' AND response_status != 'declined' AND
            (policy_status = 'active' OR policy_status = 'pending') AND
            (transaction_type = '' OR transaction_type IS NULL OR transaction_type = 'issue' OR transaction_type = 'renew' OR transaction_type = 're-write') AND
            policy_effective >= '2017-04-26'
        ");

        $counter = $success_counter = $error_counter = 0;
        foreach($ok_props_that_need_auto_enrolling as $ok_property)
        {
            $property = Property::model()->findByPk($ok_property->pid);
            $counter++;
            $property->response_status = 'enrolled';
            $property->res_status_date = $property->policy_effective;
            $property->response_enrolled_date = $property->policy_effective;
            $property->response_auto_enrolled = 1;

            //while we are at it, if the transaction_type is blank lets set it to 'renew'
            if(empty($property->transaction_type))
            {
                $property->transaction_type = 'renew';
            }

            if($property->save())
            {
                fputcsv($results_fh, array($property->pid, 'successfully updated prop with auto enroll'));
                $success_counter++;
            }
            else
            {
                fputcsv($results_fh, array($property->pid, 'ERROR updating prop with auto enroll. Details: '.var_export($property->getErrors(), true)));
                $error_counter++;
            }

            if($counter % 50 == 0)
                print "Processed $counter records so far (success: $success_counter, error: $error_counter)\n";
        }

        print "Processed $counter records (success: $success_counter, error: $error_counter)\n";

        print "DONE WITH USAA OK AUTO ENROLL FIX\n\n";
    }
}