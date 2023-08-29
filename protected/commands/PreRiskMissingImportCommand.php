<?php

/*
 * Import a csv that is exported from the Access database. This is used to import entries who did not make it into SQL from the merge.
 * To run: yiic preriskmissingimport "path to file.csv"
 */

class PreRiskMissingImportCommand extends CConsoleCommand
{
    public function run($args)
    {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');

            $this->importList($args[0]); //path to csv
    }
    
    public function convertTime($time)
    {
        if(!empty($time))
        {
            $converted = date("Y-m-d H:i:s", strtotime($time));
            //echo $converted . "\n";
        }
        else
            $converted = NULL;
        
        return $converted;
    }
    
    public function cleanData($data)
    {
        $pattern = "/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s";
        $replacement = '';
        $cleanData = preg_replace($pattern, $replacement, $data);
        return $cleanData;
    }
    
    public function importList($fileToImport)
    {
        //Get CSV to insert
        $missingCSV = fopen($fileToImport, 'r');
        
       // Read through the csv, 1 row at a time
        while($data = fgetcsv($missingCSV, 1000, ",", '"'))
        {
            
            //New record (insert)
            $preRisk = new PreRisk();
                
            //Skip $data[0] because it has the id from access
            $preRisk->property_pid = 0; //Set to 0 for default, than run the linkpreriskproperty command to find match in property/member table
            $preRisk->member_number = floatval($data[1]);
            $preRisk->renewal_date = $this->convertTime($data[2]);
            $preRisk->company=$data[3];
            $preRisk->member_name = $this->cleanData($data[4]);
            $preRisk->home_phone = $data[5];
            $preRisk->work_phone = $data[6];
            $preRisk->cell_phone = $data[7];
            $preRisk->street_address = $data[8];
            $preRisk->city = $data[9];
            $preRisk->county = $data[10];
            $preRisk->state = $data[11];
            $preRisk->zip_code = $data[12];
            $preRisk->plus_4 = $data[13];
            $preRisk->client_email = $data[14];
            $preRisk->call_attempt_1 = $data[15];
            $preRisk->time_1 = $this->convertTime($data[16]);
            $preRisk->call_attempt_2 = $data[17];
            $preRisk->time_2 = $this->convertTime($data[18]);
            $preRisk->call_attempt_3 = $data[19];
            $preRisk->time_3 = $this->convertTime($data[20]);
            $preRisk->call_attempt_4 = $data[21];
            $preRisk->time_4 = $this->convertTime($data[22]);
            $preRisk->call_attempt_5 = $data[23];
            $preRisk->time_5 = $this->convertTime($data[24]);
            $preRisk->call_attempt_6 = $data[25];
            $preRisk->time_6 = $this->convertTime($data[26]);
            $preRisk->call_attempt_7 = $data[27];
            $preRisk->time_7 = $this->convertTime($data[28]);
            $preRisk->assigned_by = $data[29];
            $preRisk->status = $data[30];
            $preRisk->wds_callers = $data[31];
            $preRisk->homeowner_to_be_present = $data[32];
            $preRisk->ok_to_do_wo_member_present = $data[33];
            $preRisk->authorization_by_affadivit = $data[34];
            $preRisk->engine = $data[35];
            $preRisk->ha_time = $data[36];
            $preRisk->ha_date = $this->convertTime($data[37]);
            $preRisk->contact_date = $this->convertTime($data[38]);
            $preRisk->week_to_schedule = $this->convertTime($data[39]);
            $preRisk->call_list_month = $data[40];
            $preRisk->call_list_year = $data[41];
            $preRisk->completion_date = $this->convertTime($data[42]);
            $preRisk->call_center_comments = $this->cleanData($data[43]);
            $preRisk->wds_ha_writers = $this->cleanData($data[44]);
            $preRisk->recommended_actions = $this->cleanData($data[45]);
            $preRisk->cycle_time_in_days = $data[46];
            $preRisk->ha_field_assessor = $data[47];
            $preRisk->fire_review = $data[48];
            $preRisk->received_date_of_list = $this->convertTime($data[49]);
            $preRisk->assignment_date_start = $this->convertTime($data[50]);
            
            //This set of questions may not be populated for everybody
            if(count($data)>51)
            {
                $preRisk->appointment_information = $data[51];
            }
            if(count($data)>52)//follow up questions...not every data set has these
            {
                echo "here \n";
                $preRisk->follow_up_question_1 = $data[52];
                $preRisk->question_1_response = $data[53];
                $preRisk->follow_up_question_2 = $data[54];
                $preRisk->question_2_response = $data[55];
                $preRisk->follow_up_question_3 = $data[56];
                $preRisk->question_3_response = $data[57];
                $preRisk->follow_up_question_4 = $data[58];
                $preRisk->question_4_response = $data[59];
                $preRisk->follow_up_attempt_1 = $data[60];
                $preRisk->follow_up_time_date_1 = $this->convertTime($data[61]);
                $preRisk->follow_up_attempt_2 = $data[62];
                $preRisk->follow_up_time_date_2 = $this->convertTime($data[63]);
                $preRisk->follow_up_attempt_3 = $data[64];
                $preRisk->follow_up_time_date_3 = $this->convertTime($data[65]);
                $preRisk->follow_up_attempt_4 = $data[66];
                $preRisk->follow_up_time_date_4 = $this->convertTime($data[67]);
                $preRisk->follow_up_status = $data[68];
                $preRisk->follow_up_month = $data[69];
                $preRisk->follow_up_year = $data[70];
                $preRisk->delivery_method = $data[71];
                $preRisk->mailing_address = $data[72];
                $preRisk->delivery_date = $this->convertTime($data[73]);
            }
            //Either put the following in another if, or get rid of all together. Pretty unlikely that anybody would have these questions populated.
//            $preRisk->fs_offered = $data[74];
//            $preRisk->fs_accepted = $data[75];
//            $preRisk->fs_notes = $data[76];
//            $preRisk->follow_up_2_question_1 = $data[77];
//            $preRisk->follow_up_2_question_2 = $data[78];
//            $preRisk->follow_up_2_question_3 = $data[79];
//            $preRisk->follow_up_2_question_4 = $data[80];
//            $preRisk->follow_up_2_question_5= $data[81];
//            $preRisk->follow_up_2_question_6= $data[82];
//            $preRisk->follow_up_2_question_6a= $data[83];
//            $preRisk->follow_up_2_question_6b= $data[84];
//            $preRisk->follow_up_2_question_6c= $data[85];
//            $preRisk->follow_up_2_question_6d= $data[86];
//            $preRisk->follow_up_2_question_6e= $data[87];
//            $preRisk->follow_up_2_question_6f= $data[88];
//            $preRisk->follow_up_2_answer_1= $data[89];
//            $preRisk->follow_up_2_answer_2= $data[90];
//            $preRisk->follow_up_2_answer_3= $data[91];
//            $preRisk->follow_up_2_answer_4= $data[92];
//            $preRisk->follow_up_2_answer_5= $data[93];
//            $preRisk->follow_up_2_answer_6a= $data[94];
//            $preRisk->follow_up_2_answer_6b= $data[95];
//            $preRisk->follow_up_2_answer_6c= $data[96];
//            $preRisk->follow_up_2_answer_6d= $data[97];
//            $preRisk->follow_up_2_answer_6e= $data[98];
//            $preRisk->follow_up_2_answer_6f= $data[99];
//            $preRisk->follow_up_2_answer_7= $data[100];
//            $preRisk->follow_up_2_answer_8= $data[101];
//            $preRisk->follow_up_2_6a_response= $data[102];
//            $preRisk->follow_up_2_6b_response= $data[103];
//            $preRisk->follow_up_2_6c_response= $data[104];
//            $preRisk->follow_up_2_6d_response= $data[105];
//            $preRisk->follow_up_2_6e_response= $data[106];
//            $preRisk->follow_up_2_6f_response= $data[107];

            //Save and print status of import (take out for large file)
            if($preRisk->save())
            {
                echo "Member # ".$data[1]." successfully inserted \n";  
            }
            else
            {
                echo "Member # ".$data[1]." failed \n"; 
            }
           
        }
        fclose($missingCSV); //close csv
        
    }
    
}

?>
