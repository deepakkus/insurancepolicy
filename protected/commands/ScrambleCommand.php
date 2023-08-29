<?php
class ScrambleCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        print "\n -----STARTING COMMAND--------\n";

        // We do NOT want to ever scramble prod
        if (Yii::app()->params['env'] == 'dev' || Yii::app()->params['env'] == 'local')
        {
            if(!isset($args[0]))
                print "Please supply a function name: streetNumber, policyNumber \n";
            elseif($args[0] == 'streetNumber')
                $this->streetNumber();
            elseif($args[0] == 'policyNumber')
                $this->policyNumber();
            elseif($args[0] == 'randomEnroll')
                $this->randomEnroll();
            elseif($args[0] == 'all')
                $this->scrambleAll();
            else
                print "The argument you supplied does not match the functions - streetNumber, policyNumber, randomEnroll, all \n";
        }
        else
            print "Only works in dev or local environments \n";

        print "\n -----DONE WITH COMMAND-------\n";
    }

    private function scrambleAll()
    {
        $criteria = new CDbCriteria;
        $criteria->condition = "[policy] NOT LIKE 'POL%'";
        $properties = Property::model()->findAll($criteria);
        $count = 7777777;

        $dbCommand = Yii::app()->db->createCommand();

        // Get min and max pids and mids
        $pid_mid_min_max = $dbCommand
            ->select('MIN(pid) AS min_pid, MAX(pid) AS max_pid, MIN(member_mid) AS min_mid, MAX(member_mid) AS max_mid')
            ->from('properties')
            ->queryRow();

        // Get max ids for first/last name tables
        $firstNameMaxId = (int)$dbCommand->select('MAX(id) [count]')->from('names_first')->queryScalar();
        $lastNameMaxId = (int)$dbCommand->select('MAX(id) [count]')->from('names_last')->queryScalar();

        //foreach($iterator as $property)
        foreach ($properties as $property)
        {
            $count++;

            $member = Member::model()->findByPk($property->member_mid);
            if (isset($member))
            {
                // Select random first/last names
                $randFirstName = $dbCommand->setText('SELECT [name] FROM [names_first] WHERE [id] = :id')->queryScalar(array(':id' => rand(1, $firstNameMaxId)));
                $randLastName = $dbCommand->setText('SELECT [name] FROM [names_last] WHERE [id] = :id')->queryScalar(array(':id' => rand(1, $lastNameMaxId)));
                srand($count+microtime());
                // Assign member values
                $member->member_num = "MEM".$count;
                $member->first_name = $randFirstName;
                $member->last_name = $randLastName;
                if (!$member->save())
                    print 'ERROR saving Member with MID:'.$member->mid."\n (details:".preg_replace('/\s+/', ' ', trim(var_export($member->getErrors(), true))) . "\n";
            }
            $random_prop = null;
            while (!isset($random_prop))
            {
                $random_pid = rand($pid_mid_min_max['min_pid'], $pid_mid_min_max['max_pid']);
                $random_prop = Property::model()->findByPk($random_pid);
            }
            $property->policy = "POL".$count;
            $property->address_line_1 = $random_prop->address_line_1;
            $property->address_line_2 = $random_prop->address_line_2;
            //$property->city = $random_prop->city;
            //$property->state = $random_prop->state;
            //$property->zip = $random_prop->zip;
            if (!$property->save())
                print 'ERROR saving Property with PID:'.$property->pid."\n (details:".preg_replace('/\s+/', ' ', trim(var_export($property->getErrors(), true))) . "\n";

            if($count % 100 == 0)
                print "Scrambled ".$count." so far\n";
        }
    }

    /**
     * Add street number to address
     */
    public function streetNumber()
    {
        // Chunk the update statement - don't want to select all properties into memory
        $endingPID = 427121; //Property::model()->count();
        $increment = 5000;
        $pid = 396406;

        print "Incrimenting by: " . $increment . "\n";

        //Loop through each chunk
        while ($pid <= $endingPID)
        {
            $previousCount = $pid;
            $pid += $increment;
            $criteria = new CDbCriteria;
            $criteria->addCondition("pid <= $pid");
            $criteria->addCondition("pid > $previousCount");
            $criteria->addCondition("client_id = 1007");

            //Get the given chunk of models
            $models = Property::model()->findAll($criteria);

            foreach ($models as $model)
            {
                $parts = explode(" ", $model->address_line_1, 2);
                $model->address_line_1 = rand(0, 9000) . " " . $parts[1];
                if (!$model->save())
                {
                    print 'ERROR saving Property with PID:'.$model->pid."\n (details:".preg_replace('/\s+/', ' ', trim(var_export($model->getErrors(), true))) . "\n";
                }
            }

            print "Finished updating records up to $pid \n";
        }

        print "Finished updating addresses \n";
    }

    /**
     * Hardcoded to enroll properties for the fake 'Insurance Client'
     */
    public function randomEnroll()
    {
        print "Enrolling... \n";
        $i = 0;

        while ($i < 20000)
        {
            $beginningPid = 396406;
            $endingPID = 427121;

            $pid = rand($beginningPid, $endingPID);

            $property = Property::model()->findByPk($pid);
            $property->response_status = 'enrolled';
            $property->save();

            $i+=1;
            print $i . "\n";
        }

        print "Done with enrollments!";
    }

    /**
     * Removes the CA liberty user types
     */
    public function policyNumber()
    {
        $criteria = new CDbCriteria;
        $criteria->addCondition("type like '%Dash LM CA Safeco%'");
        $criteria->addCondition("type like '%Dash LM CA Liberty%'", "OR");
    }
}