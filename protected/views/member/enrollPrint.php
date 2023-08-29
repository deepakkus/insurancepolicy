<div id = "main">
    <div id = "wrapper">
        <div id = "contents">
            <div id = "content-head">
                <div id = "content-head-left">
                    <h1>Wildfire Response Program - Enrollment Form</h1>
                    <br/>
                    <h2><?php echo 'Hello, ' . $member->first_name . ' ' . $member->last_name ?></h2>
                </div>

            </div><!-- content-head -->
            <p>
                Please check the following properties that you would like to enroll in the Wildfire Response Program:
            </p>
            <div id = "properties-confirm">
                <div id = "container-property">
                    <table>
                        <tr>
                            <td></td>
                            <td><span class = "bold-underline">Property</span></td>
                            <td class = "center"><span class = "bold-underline">Status</span></td>
                        </tr>
                        <?php
                            $enrolled_effective_date = null;

                            foreach($member->properties as $property)
                            {	
                                if($property->response_status != 'enrolled' && $property->policy_status == 'active')
                                {
                                    $enrolled_effective_date = strtotime($property->response_enrolled_date . " +4 days");
                                    if($enrolled_effective_date < strtotime('2013-06-01'))
                                        $enrolled_effective_date = strtotime('2013-06-01');

                                    $address = $property->address_line_1;
                                    if (!empty($property->address_line_2))
                                        $address = $address . '<br/>' . $property->address_line_2;
                                    $address = $address . '<br/>' . $property->city . ', ' . $property->state;

                                    echo '
                                    <tr>
                                        <td><input type="checkbox" /></td>
                                        <td>'.$address.'</td>
                                        <td class = "center">Enrolled Effective '.date('F j, Y',$enrolled_effective_date).'</td>
                                    </tr>
                                    ';
                                }
                            }
                        ?>
                    </table>
                    <?php
                        if($enrolled_effective_date == strtotime('2013-06-01'))
                            echo '<p>NOTE: June 1, 2013 is the first day this program is available.</p>';
                    ?>
                </div><!-- container-border -->
            </div><!-- properties -->
            <div>
                <h3>Extra wildfire help when you need it.</h3>
                <p>If a wildfire gets close to your home, certified wildland firefighters with the right tools and equipment will attempt to go into evacuation zones. While monitoring USAA members' homes, they may try to:</p>
                <ul class="enrollList">
                    <li>Close windows and garage doors.</li>
                    <li>Move wood piles and debris away from your home.</li>
                    <li>Clear gutters and roof debris.</li>
                    <li>Monitor hot spots to prevent flare-ups.</li>
                    <li>Report damage.</li>
                </ul>
                <h3>Below is the authorization and enrollment details of the program:</h3>
                <p>
                    By enrolling in this program, I hereby authorize Wildfire Defense Systems, Inc., to enter the grounds of my properties insured with USAA and enrolled in the program in order to provide wildfire suppression and structural protection services. I understand representatives of Wildfire Defense Systems will determine the most appropriate methods to mitigate fire loss to my home, which could include, but are not limited to, brush removal, fuel source mitigation, and closing of structure entryways. I further understand my authorization will not be immediately available to WDS wildfire response teams.  Authorizations will be available to response teams several days after I enroll. 
                </p>
                <p>
                    I understand that Wildfire Defense Systems will use their own judgment to determine the necessity, extent, or nature of the services provided.    I understand that there may be instances when Wildfire Defense Systems will not be able to provide the services to my property, and there is no promise that services will be provided or will prevent damage.  I hereby waive any claim against USAA or Wildfire Defense Systems related to personal injury, property damage, or any other liability arising out of any act or omission by Wildfire Defense Systems within the scope of this authorization. I further understand this authorization does not alter my insurance coverage. 
                </p>
                <p>
                    I recognize that it is my responsibility to provide accurate and current contact information to USAA.  I understand that Wildfire Defense Systems will not provide services if I or USAA terminates my insurance coverage for the properties enrolled in this program.  If I want to discontinue my authorization and participation of this program, I will need to call USAA at 800-531-USAA (8722). If I move to another address I understand that I will need to enroll the new property in the program, if available.  I understand USAA retains the right to change or discontinue this program at any time without notice or further obligation to me.  This is a loss mitigation program and I will not be charged for any services provided by USAA or WDS.  
                </p>

                <h3>Site Terms &amp; Agreement:</h3>
                <p>The terms "we/our/us" indicate Wildfire Defense Systems.  The terms "you/your" indicate the person enrolling in the USAA/WDS Fire Loss Mitigation Program and that person's authorized representative.  The Site Terms &amp; Agreement for Electronic Transaction ("Agreement") applies to usage of this site.</p>
                <div id = "terms">
                    <ol class="enrollList">
                        <li><u>Privacy of information.</u>  We share information only as permitted by law for our everyday business purposes and to fulfill the USAA/WDS Fire Loss Mitigation Program.  We do not sell your personal information.</li>
                        <li><u>Agreement.</u> You agree we may deliver information electronically during this transaction.
                            <ol type="A">
                                <li><span class = "bold">Your ability to receive electronic Documents:</span>  By signing this Agreement, you are demonstrating that you have the ability to view and save documents through electronic means.</li>
                                <li><span class = "bold">Documents you agree to receive electronically:</span> You agree to receive electronic delivery of: (1) information presented to you as part of an online transaction such as disclosures, forms, notices, and other information; and (2) documents reflecting your online transactions.</li>
                                <li><span class = "bold">Updating your contact information:</span> You may update your contact information for electronic delivery of documents by accessing and updating your profile on usaa.com or calling USAA at 800-531-USAA (8722).</li>
                                <li><span class = "bold">Your responsibilities:</span>  YOU REPRESENT THAT YOU HAVE THE EQUIPMENT TO RECEIVE DOCUMENTS ONLINE. It is your responsibility to maintain a record of the electronic transaction. You can do so by saving or printing a copy of the confirmation screen and other agreements applicable to the transaction. If you choose to receive documents electronically, it is your responsibility to log on and check that the documents have been delivered, and to open and read your documents. Promptly notify us if any documents are not accessible or are incomplete or unreadable. </li> 
                                <li><span class = "bold">Changes to this Agreement:</span>  We may amend this Agreement at any time. You accept any amendment to this Agreement made by us by your continued use of this Site.</li>
                            </ol>
                        </li>
                        <li><u>Electronic Delivery Procedures.</u>  This describes the procedures for electronic delivery, requesting paper copies, and cancellation. 
                            <ol type="A">
                                <li><span class = "bold">Delivery process:</span>  We will deliver electronic documents by posting them on this Site, by electronic mail or by other reasonable methods of electronic delivery. </li>								
                            </ol>
                        </li>
                    </ol>

                </div><!-- agreement -->
            </div>
            <div id="signatureContainer">
                <div>
                    Signature: __________________________________________________  Date: _________________________________
                </div>
                <div>
                    Member: <?php echo $member->first_name . ' ' . $member->last_name ?> (#<?php echo $member->member_num; ?>)
                </div>
            </div>
            <div>
                <div><b>Return To: WDS - Boz</b></div>
                <div>Wildfire Defense Systems</div>
                <div>201 Evergreen Dr.</div>
                <div>Suite 1</div>
                <div>Bozeman, MT 59715</div>
            </div>
        </div> <!-- contents -->
    </div> <!-- wrapper -->

</div> <!-- main -->
