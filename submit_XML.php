<?php
    include 'functions.inc';

    //**** One last check to see if the state is authorized
    include_once '../common/peklava-boberdoo-allowed-states.php';
    if (!pbas_validateState()) {
        exit;
    }
    //****

    //make sure we have posted to this page
    if(is_array($_POST) && count($_POST) > 0){ // w. case
    } else {  header('Location: index.php'); exit; }

    //FRMRC = Form Retry Count (0 = initial lead post, 1 = first correction, 2+=second correction)
    $frmc = isset($_POST['FRMRC']) ? intval($_POST['FRMRC']) : 0;
    if ($frmc <= 0) { $frmc = 1; }

    $gtag_PPCID = isset($_POST['PPCID']) ? ($_POST['PPCID']) : "NA";

    include_once '../Mobile-Detect/Mobile_Detect.php';
    $detect = new Mobile_Detect();
    $isIphone = $detect->isiOS();
    $mobile_numberpattern = $isIphone ? ' pattern="[0-9]*" ' : ' inputmode="numeric" ';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
  "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <?php include_once '../common/tracking-head.php'; ?>
    <title>NeighborhoodAssistance.com | Get Rates</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" name="viewport">
    <meta name="robots" content="noindex" />
    <link rel="shortcut icon" href="/wp-content/themes/ratezip/img/icons/favicon.ico"/>

   <link rel='stylesheet' id='normalize-css'  href='//www.ratezip.com/wp-content/themes/ratezip/css/normalize.css?ver=4.0' type='text/css' media='all' />
   <link rel='stylesheet' id='foundation-css'  href='//www.ratezip.com/wp-content/themes/ratezip/css/foundation.min.css?ver=4.0' type='text/css' media='all' />
   <?php /* <link rel='stylesheet' id='app-css'  href='organic.css' type='text/css' media='all' /> */ ?>

    <link href="https://fonts.googleapis.com/css?family=Lora|Nanum+Gothic:400,700" rel="stylesheet">
    <link rel="stylesheet" type="text/css" media="screen" href="getrates.css?v=26" />

    <script type="text/javascript" src="../common/jquery/jquery.js"></script>
    <script type="text/javascript" src="jquery.validate.min.js"></script>
    <script type="text/javascript" src="jquery-ui-1.10.3.custom.min.js"></script>
    <script type="text/javascript" src="jquery.ui.touch-punch.min.js"></script>
    <script type="text/javascript" src="modernizr.js"></script>
    <script type="text/javascript" src="ty.js"></script>
</head>
<body>
<?php $NO_FIXEDHEIGHT=TRUE;
 include "./template/headerx.php";

    //call the foreign IP Address check
    include_once "../common/foreign-ip-check.php";
    $ip_score = ip_IsValidIP();

    //only do the whitepages check if this is a local IP
    include_once "../Whitepages/whitepages-phonecheck.php";
    $wp_score = WP_INVALID_NUMBER; //assume bad
    if ($ip_score > 0) {
        //call the whitepages API to get a verification score
        $wp_score = wp_GetVerifyScore($_POST);
    }

    //build raw post string for reporting API
    $pstVars = '';
    foreach($_POST as $key=>$value) {
       if (($key=="DOWN_PMT") || ($key=="BAL_ONE")) {
         $pstVars .= "$key=".urlencode(preg_replace("/[^0-9]/", "",$value))."&";
       } else {
         if ($key!="TSID") {
             $pstVars .= "$key=".urlencode($value)."&";
         }
       }
    }
    $pstVars .= "IP_ADDRESS=".urlencode(getIP());
    $pstVars .= "&EKATA=".urlencode($wp_score);

    /**** ADD THE SERIOUS DATA HERE *****/
    if ($gtag_PPCID === '16') {
        //special case for maxbounty -- we actually want the real TSID
        $pstVars .= "&TSID=".urlencode(isset($_POST['TSID']) ? ($_POST['TSID']) : "NA");
    } else {
        $pstVars .= "&TSID=".urlencode(isset($_POST['SERIOUS']) ? ($_POST['SERIOUS']) : "");
    }
    /**** ADD THE SERIOUS DATA HERE *****/

    include_once "../Mobile-Detect/Detect-Device-Type.php";
    $pstVars .= "&DEVICE_TYPE=".urlencode(pek_getDeviceType());

    //build a submit log
    $submitLog = "<!-- \n SUBMIT LOG \n";

    $IS_LTHP = FALSE; //are we submitting to the LT host and post

    //make sure we include both submit files
    include "../LendingTree/peklava-lt-hostpost.php";
    include "../common/peklava-boberdoo-leadpost.php";


/******** THIS IS ENABLED FOR TESTING ****************/
$ip_score = 100; $wp_score = 100;
/******** THIS IS ENABLED FOR TESTING ****************/

    if (($wp_score < WP_INVALID_NUMBER) && ($ip_score > 0)) {
      /** BEGIN WHITEPAGES/ip PROTECTED SUBMIT **/

        //BEGIN TF Certificate Claim
        include_once '../common/peklava-boberdoo-tfcertclaim.php';
        $tf_cert_claim_id = claim_TFCertificate();
        $pstVars .= "&TFClaimID=".urlencode($tf_cert_claim_id);
        //END TF Certificate Claim

        //BEGIN TFA validation (server-side)
        include_once '../common/tfa-auth.php';
        $tfaid   = isset($_POST['TFAID']) ? ($_POST['TFAID']) : "";
        $tfacode = isset($_POST['TFA'])   ? ($_POST['TFA'])   : "";
        $tfa_isAuthenticated = tfa_authenticate($tfaid, $tfacode);
        $pstVars .= "&TFA_AUTHENTICATED=".($tfa_isAuthenticated ? "Y" : "N");
        //END TFA Validation

        $debugLog  = "[DEBUG] Debug Log\r\n";

        //see if we are posting to LT or boberdoo
        $lthpFlag = intval(isset($_POST['LTHP']) ? ($_POST['LTHP']) : "0");
        $IS_LTHP   = ($lthpFlag == 1);
        $pstVars .= "&VENDOR=".($IS_LTHP ? "LENDINGTREE" : "PEKLAVA");

        $lthpscore = floatval(isset($_POST['LTHPSCORE']) ? ($_POST['LTHPSCORE']) : "0");
        $pstVars .= "&MISC=EST_SALE_PRICE=".number_format($lthpscore,2,".","");

        $result = "";

          if ($IS_LTHP) {
              /** BEGIN LENDINGTREE SUBMIT **/

                $result = lt_submitLead();
                $submitLog .= "Peklava partner response: $result \n";

                //now that we've submitted to LT, we want to also post to Boberdoo with our special
                //source flag
                $ltBBUrl = peklava_getPostURL($uniqueEmbraceId,$wp_score,$tf_cert_claim_id,$tfa_isAuthenticated);
                $ltBBUrl = peklava_force_SRC($ltBBUrl, 'LT_HP');
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $ltBBUrl);
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                try {
                    $resultLTBB = curl_exec($ch);
                    $submitLog .= "LT/BB Result: ".$resultLTBB."\n";

                } catch(Exception $ex) { }

             /** END LENDINGTREE SUBMIT **/

          } else {
              /** BEGIN PEKLAVA SUBMIT **/

                  //declare the unique id variable that will be passed by reference and
                  //get set when the URL is constructed
                  $uniqueEmbraceId = 0;

                  //build Affinity posting URL/string
                  $url = peklava_getPostURL($uniqueEmbraceId,$wp_score,$tf_cert_claim_id,$tfa_isAuthenticated);
                  $debugLog .= "[DEBUG]Posting URL: ".$url."\r\n";
                  $debugLog .= "[DEBUG]Posting Start: ".date("H:i:s:u")."\r\n";

                  //submit to Peklava/Boberdoo
                  $ch = curl_init();
                  curl_setopt($ch, CURLOPT_URL, $url);
                  curl_setopt($ch, CURLOPT_POST, false);
                  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                  try {
                    $result = curl_exec($ch);
                    $debugLog .= "[DEBUG]Posting End: ".date("H:i:s:u")."\r\n";
                  } catch(Exception $ex) {
                      //notify everyone there is a problem?
                  }

                  $debugLog .= "[DEBUG]Partner response: '".htmlentities($result)."'\r\n";
                  if(curl_errno($ch)){
                    $debugLog .= "[DEBUG]Curl error: " . htmlentities(curl_error($ch)) . "\r\n";
                  } else {
                    $debugLog .= "[DEBUG]Curl Info:\r\n";
                    $info = curl_getinfo($ch);
                    foreach ($info as $i => $v) {
                       if (is_array($v)) {
                          foreach ($v as $ix => $vx) {
                             $debugLog .= "[DEBUG]Curl Info [".htmlentities($i)."] (".htmlentities($ix)."): ".htmlentities($vx)."\r\n";
                          }
                       } else {
                         $debugLog .= "[DEBUG]Curl Info (".htmlentities($i)."): ".htmlentities($v)."\r\n";
                       }
                    }
                  }

                  $submitLog .= "Peklava partner response: $result \n";

                 /*
                 ***** SAMPLE RETURN VALUES *****

                 <?xml version="1.0" encoding="UTF-8"?><response>
                  <status>Unmatched</status>
                  <lead_id>39735</lead_id>
                 </response>

                 <?xml version="1.0" encoding="UTF-8"?><response>
                  <status>Error</status>
                  <error>Insert Error #8: Required value for US Phone type &quot;Day Phone&quot; contains invalid value.</error>
                 </response>

                 <response><status>Matched</status><lead_id>30494</lead_id><total_price>20.00</total_price></response>

                 */

              /** END PEKLAVA SUBMIT **/

          }


        //*** BEGIN INITIAL BR SUBMIT ****/
            //BEGIN submit URL parameter
            $submiturl = (isset($_SERVER['HTTPS']) == 'on' ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $pstVars .= "&SUBMIT_URL=" . urlencode($submiturl);
            //END submit URL parameter
        	$impLeadBR = curl_init();
        	curl_setopt($impLeadBR, CURLOPT_URL, 'http://api.bankradar.com/import-lead-data.php');
        	curl_setopt($impLeadBR, CURLOPT_POST, true);
        	curl_setopt($impLeadBR, CURLOPT_POSTFIELDS, $pstVars);
        	curl_setopt($impLeadBR, CURLOPT_SSL_VERIFYPEER, false);
        	curl_setopt($impLeadBR, CURLOPT_RETURNTRANSFER, 1);
        	$importResultBR = curl_exec($impLeadBR);

    		if(is_numeric($importResultBR))
    		{
    			$lead_idBR = $importResultBR;
    		} else $lead_idBR = 'NONE';

    		$submitLog .= "import-lead-data: $importResultBR (lead_id) [posted: $pstVars] \n";
		//*** END INITIAL BR SUBMIT ****/


      /** END WHITEPAGES/ip PROTECTED SUBMIT **/

   } else {
      /** BEGIN WHITEPAGES/ip INVALID LEAD LOGIC **/
      $errmsg = "";
      if ($ip_score <= 0) { $errmsg .= "Foreign IP detected. "; }
      if ($wp_score >= WP_INVALID_NUMBER) { $errmsg .= "Lead failed Whitepages verification test."; }
      $result = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response>
          <status>Error</status>
          <error>".$errmsg."</error>
         </response>";
      /** END WHITEPAGES INVALID LEAD LOGIC **/
   }

    $xml = null; //this will store our result XML

    if($result && (trim(strlen($result)) >= 0))
    {
    	$xml = new SimpleXMLElement($result);
    	$errC = 0;
    	$statusMsg = '';
    	$errMsg = '';
    	$totalPrice = '';
    	if ($IS_LTHP) {
    	    //process the lt-style xml
    	    if (count($xml->Response->Errors) == 1) {
    	        $errC = count($xml->Response->Errors->Error);
        	    if ($errC > 0) {
        	        foreach($xml->Response->Errors->Error as $er) {
        	            $errMsg .= $er.'. ';
        	        }
        	    }
    	    }
    	    $statusMsg = ($errC == 0) ? 'Matched' : 'Error';
    	    $totalPrice = '20.00'; //HARD CODED LEAD VALUE

    	} else {
            //boberdoo style xml
    	    $errC = count($xml->error);
    	    $errMsg = ''.trim($xml->error);
    	    $statusMsg = $xml->status;
    	    $totalPrice = ''.$xml->total_price;
    	}
    	$leadid = ''.$xml->lead_id;
    	$leadid = strlen($leadid) == 0 ? '999999' : $leadid;
    	$conv_amt=0.0;

      //See if we have a valid lead (non status='Error' or manual match)
      $afErrMsg = strtolower(($errC > 0) ? $errMsg : '');
      $bisManual = !(strpos($afErrMsg,'manually') === FALSE);
      if ($bisManual) {
         $statusMsg = 'MANUAL REVIEW';
      }

      //****** FINISH REPORTING API ******

      //use the resuting XML to generate a LP-style response
      $conv_amt=0.0;
      if (strpos($result, "Error") === FALSE) {

         // Purchase  = $10 if matched/no-error,  0 if unmatched
         // Refinance = $45 if matched/non-error, 0 if unmatched
         $conv_PROD = isset($_POST['PRODUCT']) ? strtoupper($_POST['PRODUCT']) : "PP_REFI";
         $conv_amt  = ($conv_PROD === 'PP_REFI') ? 45.0 : 10.0;

         if (strtoupper($statusMsg) == 'UNMATCHED') {
            $conv_amt=0.0; //unmatched leads
         }

         //use the real price if we have one
         if (strlen(trim($totalPrice)) > 0) {
            if (is_numeric(trim($totalPrice))) {
                $conv_amt = floatval($totalPrice);
            }
         }

         //*********************************

         //add conversion amount logic here

         //*********************************
      }
      $xml_errs = "";
      for($i = 0; $i < $errC; $i++)
      {
         $xml_errs = "<err><type>error</type><name>".htmlentities(''.$errMsg)."</name></err>";
      }
      $result = "<result><sm>$statusMsg</sm><lid>$leadid</lid><amt>$conv_amt</amt>$xml_errs</result>";

		if($lead_idBR != 'NONE' && is_numeric($lead_idBR))
		{
			$impLeadRespBR = curl_init();
			$resultDBR = preg_replace("/&#?[a-z0-9]+;/i","",$result);
			curl_setopt($impLeadRespBR, CURLOPT_URL, 'http://api.bankradar.com/import-lead-response.php');
			curl_setopt($impLeadRespBR, CURLOPT_POST, true);
			curl_setopt($impLeadRespBR, CURLOPT_POSTFIELDS, "lead_id=$lead_idBR&response=$resultDBR&useamount=YES");
			curl_setopt($impLeadRespBR, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($impLeadRespBR, CURLOPT_RETURNTRANSFER, 1);
			$importRespBR = curl_exec($impLeadRespBR);
			$submitLog .= "import-lead-response: $importRespBR [posted: lead_id=$lead_idBR&response=$result] \n";

		}
		else $submitLog .= "lead_id is not number [$lead_idBR], skipping /import-lead-response \n";
        /****** END REPORTING API ******/

        //dump out the log
        echo $submitLog . "-->";

    	if( (!($xml->status == 'Error')) || $bisManual)
    	{
    	    //This lead is good enough to pass on to the TY page.

            //** BEGIN ClearOne Leadpost API
            include "../ClearOne/clearone-leadpost.php";
            //** END ClearOne LeadpostAPI

            //** BEGIN LexLaw Leadpost API
            include "../BHM/lexingtonlaw-leadpost.php";
            //** END LexLaw Leadpost API

            //load up the offer engine
            spl_autoload_register(function ($class) {include dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'common'.DIRECTORY_SEPARATOR.'offers'.DIRECTORY_SEPARATOR.str_replace('\\', '/', $class).'.php'; });
            $oe = new Peklava\OfferEngine();
            $oe_submitstatus = $IS_LTHP ? $oe::SUBMITTED_TO_LT : $oe::SUBMITTED_TO_BOBERDOO;
            $oe->set_submit_status($oe_submitstatus);
            $offer_count = $oe->offer_count();

            //MATCHING PARTNERS API (Boberdoo)
            //get the matching partners count
            include_once "../common/peklava-boberdoo-matching.partners-api-V2.php";
            $partner_count = 0;
            if (!$IS_LTHP) {
                $partner_count = pbapi_getMatchCount($leadid);
            }

    	    // *** BEGIN TYP SCREEN
            ?>
                <div style="margin: 0px; padding: 0 8px; line-height: normal;">
                     <div style="padding: 5px; line-height: normal;">
                         <!-- begin notification text area -->
                         <?php
                       	echo '<h3 style="text-align:center;color:#ffffff; padding: 10px 0; margin:0;">YOUR LOAN OPTIONS</h3>';
//                       	echo '<p id="thank-you-heading" style="text-align:center;color:#ffffff; padding: 10px 0; margin:0;" align="center">Congratulations '.htmlentities($_POST['FNAME']).', You have completed the first step in starting your loan process.</p>';
//                       	echo '<p style="text-align:center;color:#ffffff; padding: 10px 0; font-size: 1.25em; margin:0;" align="center">The following lenders have offers for you based on the data you entered in previous steps.</p>';
                       	echo '<p style="color:#fffc00;font-family:Arial,Helvetica,sans-serif;font-size:1em;font-weight:bold; padding: 10px 0; margin:0;" align="center">NOTICE: You must select 1-4 offers below to match and get more information.</p>';

                            echo '<div style="background-color: #ffffff; padding: 30px 30px 1px; border-radius: 10px;">';

                                  $arBanks = array();

                                  //get the total offer and matching partner counts
                                  $total_typ_count = $offer_count + $partner_count;

                                  //see if we are showing matching partners and offers
                                  //or the rate table data
                                  if ($total_typ_count > 0) {

                                        //render the offers
                                        $oe->render_offers();

                                        //render the matching partners if we have a boberdoo
                                        // (non-LT H&P lead)
                                        if (!$IS_LTHP) {
                                            //BEGIN MATCHING PARTNERS API
                                            //****************************************
                                            $arBanks = pbapi_showMatches($leadid);
                                            //****************************************
                                            //END MATCHING PARTNERS API
                                        }

                                  } else {

                                        //we have no matching partners or offers to show
                                        //so render the rate table data
                                        include_once '../LeadpointRates/LeadPointRateTable.php';
                                        $rateTable = generateRateTableFromArray($_POST,1);
                                        echo $rateTable;

                                  }


                                  if (peklava_isPhoneCallOk($tf_cert_claim_id)) {
                                      //** BEGIN Twilio SMS Notification
                                      $TWILIO_MATCHED_BANKS = $arBanks; //so the SMS can include our matched banks
                                      include "../common/twilio-sms-notification.php";
                                      //** END Twilio SMS Notification

                                      //** BEGIN RVM Notification
                                      $RVM_MATCHED_BANKS = $arBanks; //so the SMS can include our matched banks
                                      include "../common/stratics-rvm-notification.php";
                                      //** END RVM Notification
                                  }

                                  //** BEGIN GetResponse Leadpost API
                                  include "../GetResponse/getresponse-leadpost.php";
                                  gr_AddLead($arBanks);
                                  //** END GetResponse Leadpost API

                            echo '</div>';
                         ?>
                     </div>
                </div>
                <script>
                   gtag('event', "STEP 12 - TYP", {
                    'event_category' : 'lander: RZ/fly2hp2p',
                    'event_label' : 'PPCID <?php echo htmlentities($gtag_PPCID); ?>'
                   });
                </script>
            <?php
            // *** END TYP SCREEN

            //show conversion tracking
            $GA_CONV_AMT = $conv_amt; //default from above
            $GA_LEAD_SRC = 'PEKLAVA';
            $GA_LP_MSG   = $statusMsg;
            include "../common/tracking-thankyou.php";

    	}
    	else
    	{

    	    if ($frmc >= 3) {
    	        ?>
    	        <h2>Thank you for your inquiry.</h3>
    	        <h3><strong>Please wait while we find rates for you...</strong></h3>
    	        <script type="text/javascript">
                    function showRateTable() {
                        window.location = 'https://www.ratezip.com/rate-quotes/LeadpointRates/rates.php';
                        <?php /* window.location = 'https://www.ratezip.com/rate-quotes/comparison3/?PPCID=<?php echo htmlentities($gtag_PPCID); ?>&FROMRZ=Y'; */ ?>
                    }
                    // self executing function here
                    (function() {
                       gtag('event', "STEP 11a - CORRECTION LIMIT RATE TABLE", {
                       <?php /*  gtag('event', "STEP 11a - CORRECTION LIMIT COMPARISON3", { */ ?>
                        'event_category' : 'lander: RZ/fly2hp2p',
                        'event_label' : 'PPCID <?php echo htmlentities($gtag_PPCID); ?>'
                       });
                       setTimeout('showRateTable();',1500);
                    })();
    	        </script>
    	        <?php

    	    } else {
                //this is invalid, so we want to show the field correction screen
                // ***** BEGIN FIELD CORRECTION SCREEN
                ?>
                <script>
                   gtag('event', "STEP 11 - CORRECTION FORM", {
                    'event_category' : 'lander: RZ/fly2hp2p',
                    'event_label' : 'PPCID <?php echo htmlentities($gtag_PPCID); ?>'
                   });
                </script>
                <form method="post" action="submit_XML.php" id="frmMain">
                   <div class="form_ob">
                      <div class="form_text">Please correct the following:</div>
                      <div class="form_element ctr pii">

                           <input type="text" id="FNAME" name="FNAME" value="<?php echo htmlentities($_POST['FNAME']); ?>" class="required" placeholder="First Name" />
                           <div class="errbox"><label for="FNAME" class="error">We need your real name to process your request.</label></div>

                           <input type="text" id="LNAME" name="LNAME" value="<?php echo htmlentities($_POST['LNAME']); ?>" class="required" placeholder="Last Name" />
                           <div class="errbox"><label for="LNAME" class="error">We need your real name to process your request.</label></div>
                           <div class="errbox" style="min-height: auto !important;"><label id="errNameMatch" class="error" style="display: none;">First and last name must be different.</label></div>

                           <input type="text" id="PRI_PHONE" name="PRI_PHONE" <?php echo $mobile_numberpattern; ?> value="<?php echo htmlentities($_POST['PRI_PHONE']); ?>" class="required" placeholder="Phone Number" />
                           <div class="errbox"><label for="PRI_PHONE" class="error">Please enter your Phone Number.</label></div>

                           <input type="text" id="EMAIL" name="EMAIL" value="<?php echo htmlentities($_POST['EMAIL']); ?>" class="required email" placeholder="Email" />
                           <div class="errbox"><label for="EMAIL" class="error">Please enter a valid Email Address.</label></div>

                             <?php
                                 //output all posted fields not already dealt with above (non-error fields)
                                 $sErrFlds = array('PRI_PHONE','FNAME','LNAME','EMAIL','FRMRC');
                                 foreach($_POST as $key=>$value) {
                                     if (!in_array($key, $sErrFlds))
                                     {
                                         echo '<input type="hidden" name="'.$key.'" value="'.htmlentities($value).'" />';
                                     }
                                 }
                             ?>
                             <input type="hidden" name="FRMRC" value="<?php echo htmlentities(($frmc+1)); ?>" />

                            <a href="#" class="continuebtn" onclick="$('#frmMain').submit(); return false;"><span>Continue</span></a>
                            <p>&nbsp;</p>
                      </div>
                   </div>
                </form>
                <?php
                // ***** ENDFIELD CORRECTION SCREEN
            }
    	}
    }
    else
    {
        $debugLog .= "[DEBUG]Missing reponse. Sending email...\r\n";

        //notify everyone there is a problem
        include_once "../common/posting-error-alert.php";
        EA_SendErrorMessage( "Debug Log: ".$debugLog );

        //this is when we get no result from LP, so there is something wrong.
        //we'll just dump out the submit log and show a blank error page.
        echo "<h1>Processing Error</h1>" . $submitLog . "-->";
        exit;
    }

include "./template/footerx.php";
include "../common/tracking.php";
include "../common/leadid-script.php";
?>

</body>
</html>

