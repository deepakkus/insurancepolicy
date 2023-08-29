<?php

    /*
     * Test unit for the property class
    */
    // require_once("../bootstrap.php");

    class PropertyTest extends CTestCase
    {

		// protected $fixtures = array(
		//     ''=>':Properties'
		// );

        /**
         * Tests the enrollment functionality - policyholder get's saved into our system as enrolled and enrollment date should be populated
         */
		public function testEnrolled()
		{

		    $property = new Property;
            $property->setAttributes(array(
                'address_line_1'=>'819 N 15th Ave',
                'city'=>'Bozeman',
                'state'=>'MT',
                'zip'=>'59715',
                'policy'=>'POL1',
                'lat' => '37.354678902',
                'long' => '-110.2',
                'wds_lat' => '37.32',
                'wds_long' => '-110.2'
            ), false);


		    $this->assertTrue($property->save(false));
            // var_dump(Yii::app()->db->getLastInsertID());
            // die(print_r($property->pid));
            // die;
		    //--------------Property 1 - verify the property is valid
		    $property = Property::model()->findByPk(Yii::app()->db->getLastInsertID());
		    $this->assertTrue($property instanceof Property);

		    //Check enrollment and enrollment date
		    $this->assertEquals('37.354678902',$property->lat);
		    $this->assertEquals('-110.2',$property->long);
		}
    }

?>