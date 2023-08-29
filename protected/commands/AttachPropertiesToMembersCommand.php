<?php
class AttachPropertiesToMembersCommand extends CConsoleCommand
{
	public function run($args)
	{
		error_reporting(E_ALL);
		ini_set('display_errors', '1');

        print "\n -----STARTING COMMAND--------\n";

        //we don NOT want to ever scramble prod
        if(Yii::app()->params['env'] == 'dev' || Yii::app()->params['env'] == 'local')
        {
            if(!isset($args[0]) || !isset($args[1]))
                print "please supply a starting and ending member id \n";
            elseif(!isset($args[2]))
                print "please supply a client_id \n";
            else
                $this->attach($args[0], $args[1], $args[2]);
        }
        else
            echo "only works in dev or local environments";

        print "\n -----DONE WITH COMMAND-------\n";
    }

    private function attach($startingMid, $endingMid, $clientID)
    {
        print "attaching... \n";

        $i = $startingMid;
        $properties = true;

        while($i <= $endingMid || !$properties){
            $sql = "select top 1000 pid from properties where client_id = :client_id and member_mid is null";
            $properties = Yii::app()->db->createCommand($sql)->bindParam(':client_id', $clientID, PDO::PARAM_INT)->queryAll();

            print "Now at entry #$i \n";
            foreach($properties as $property){
                $update = "update properties set member_mid = :member_mid where pid = :pid";
                $result = Yii::app()->db->createCommand($update)->execute(array(':pid'=>$property['pid'], ':member_mid' => $i));

                $i+=1;
            }
        }
    }

}