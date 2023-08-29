<?php

    /*
     * Test unit for the property class
    */
    // require_once("../bootstrap.php");

    class AuthenticateTest extends CTestCase
    {
        public function testCorrectcredentials(){

            $property1 = new LoginForm;
            $property1->setAttributes(array(
                //'username'=>'kewat',
                //'password'=>'kewat',
                'username'=>'testciuser',
                'password'=>'testcipassword',
                'rememberMe'=>NULL
            ), false);

            $this->assertTrue($property1->login(false));
        }

        public function testNousername(){
            $property2 = new LoginForm;
            $property2->setAttributes(array(
                'password'=>'kewat',
                'rememberMe'=>NULL
            ), false);
            $this->assertFalse($property2->login(false));
        }

        public function testNopassword(){
            $property3 = new LoginForm;
            $property3->setAttributes(array(
                'username'=>'kewat',
                'rememberMe'=>NULL
            ), false);
            $this->assertFalse($property3->login(false));
        }

        public function testNousernameandpassword(){
            $property4 = new LoginForm;
            $property4->setAttributes(array(
                'rememberMe'=>NULL
            ), false);
            $this->assertFalse($property4->login(false));
        }
    }

?>