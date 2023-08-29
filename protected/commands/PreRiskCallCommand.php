<?php

/*
 * Import a csv from the GIS selection. These are people whom have been selected to call, based on their geographic location, and are from the properties table
 * to run: yiic preriskcall "path to file.csv"
 */

class PreRiskCallCommand extends CConsoleCommand
{
    public function run($args)
    {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');

            $this->importList($args[0]); //path to csv
    }
    
    public function importList($fileToImport)
    {
        $callList = fopen($fileToImport, 'r');
        
       // Read through the csv, 1 row at a time
        while($data = fgetcsv($callList))
        {
                //New record (insert)
                $preRisk = new PreRisk();
                
                //Assign values from csv to columns in database
                $preRisk->engine = trim($data[0]);
                $preRisk->week_to_schedule = date("Y-m-d H:i:s", strtotime($data[1]));
                //$preRisk->company=$data[2];
                //$preRisk->member_number=$data[3];
                $preRisk->call_center_comments = $data[4];
                //$preRisk->member_name = $data[5];
                //$preRisk->street_address = $data[6];
                //$preRisk->city = $data[7];
                //$preRisk->county = $data[8];
                //$preRisk->state = $data[9];
                //$preRisk->zip_code = floatval($data[10]);
                //$preRisk->home_phone = $data[11];
                //$preRisk->work_phone = $data[12];
                //$preRisk->cell_phone = $data[13];
                //$preRisk->client_email = $data[14];
                $preRisk->call_list_year = $data[15];
                $preRisk->call_list_month = strtoupper($data[16]);
                $preRisk->received_date_of_list = date("Y-m-d H:i:s", strtotime($data[17]));
                $preRisk->assignment_date_start = date("Y-m-d H:i:s", strtotime($data[18]));
                $preRisk->status = strtoupper($data[19]);
                $preRisk->property_pid = $data[20];
                
                //Save and print status of import (take out for large file)
                if($preRisk->save())
                {
                    echo "Member # ".$data[3]." successfully inserted \n";  
                }
                else
                {
                    echo "Member # ".$data[3]." failed \n"; 
                }

        }
        fclose($callList); //close csv
       
    }
    
}

?>
