<?php
    include 'functions.inc';

    //try to load up the incoming values
    include_once 'FormLoader.php';
    $fl = new FormLoader();

    //get incoming values, mapped by our sc object
	$org_product  = $fl->getProduct();
	$org_zip      = $fl->getZip();
	$org_cred     = $fl->getCredit();
	$org_propdesc = $fl->getPropDesc();
	$org_vastatus = $fl->getVAStatus();
	$org_estval   = $fl->getEstVal();
	$org_loanval  = $fl->getLoanVal();
	$org_rate     = $fl->getCurrRate();
	$org_employed = $fl->getSelfEmployed();
	$org_timeframe = $fl->getTimeFrame();
    $org_agentfound = $fl->getAgentFound();
    $org_spechome = $fl->getSpecHome();
    $org_acceptmatch = $fl->getAcceptMatch();
    $org_matchtoken = $fl->getMatchToken();
	$org_downpmt  = 0;
	if ($fl->isPurch()) {
	    $org_downpmt = $org_estval - $org_loanval;
	    if ($org_downpmt < 0) {
	        $org_downpmt = 0;
	    }
	}

    //determine our starting step
    $STARTING_STEP = 0;
    if ($fl->hasProduct()) {  $STARTING_STEP++;
        if ($fl->hasZip()) {  $STARTING_STEP++;
            if ($fl->hasCredit()) {  $STARTING_STEP++;
                if ($fl->hasVAStatus()) {  $STARTING_STEP++;
                    if ($fl->hasEstVal()) {  $STARTING_STEP++;
                        if ($fl->hasLoanVal()) {  $STARTING_STEP++;
                            if ($fl->hasCurrRate() || $fl->isPurch()) {  $STARTING_STEP++;
                                if ($fl->hasPropDesc()) {  $STARTING_STEP++;
                                    if ($fl->hasSpecHome()) { $STARTING_STEP++;
                                        if ($fl->hasAgentFound()) { $STARTING_STEP++;
                                            if ($fl->hasSelfEmployed()) {  $STARTING_STEP++;
    }}}}}}}}}}}

    //load up the mobile detect
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

    <link rel="stylesheet" type="text/css" media="screen" href="jquery-ui.css" />
    <link rel="stylesheet" type="text/css" media="screen" href="getrates.css?v=27" />
    <link href="https://fonts.googleapis.com/css?family=Lora|Nanum+Gothic:400,700" rel="stylesheet">
   <script type="text/javascript">
     (function() {
       var rza = document.createElement('script'); rza.type = 'text/javascript'; rza.async = true;
       rza.src = 'all.php?v=8&preload=<?php echo $STARTING_STEP; ?>';
       var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(rza, s);
     })();
   </script>
    <style type="text/css">
        #matchresult.not_ready {
            display: none;
        }
        #matchresult.ready {
            display: block;
        }
        #brokername {
            font-size: 30px;
            color: #FFF;
        }
        #brokername > span {
            display: block;
            margin-bottom: 1em;
        }
    </style>
    <?php
        require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Mobile-Detect'.DIRECTORY_SEPARATOR.'Mobile_Detect.php';
        $detect = new Mobile_Detect();
        $bMobile = ( $detect->isMobile() && !$detect->isTablet() ) ? "true" : "false";
        //$bMobile = true; //For testing on local
    ?>
    <script type="text/javascript">function ism() { return <?php echo $bMobile; ?>; }</script>
    <?php
        require_once "../common/peklava-boberdoo-allowed-states.php";
    ?>
    <script type="text/javascript">function stlic() { return [<?php echo "'".implode("','",$PEKLAVA_STATES)."'"; ?>]; }</script>
    <script type="text/javascript">function stlic_refi() { return [<?php echo "'".implode("','",$PEKLAVA_REFI_STATES)."'"; ?>]; }</script>
</head>
<body>

<?php /** BEGIN INTRO POPUP **/ ?>
<div class="overlay">
  <div class="overlay-content">
      <a href="#" class="overlay-x overlayclose">&times;</a>
      <img src="images/ratezip-logo-02.png" srcset="images/ratezip-logo-02@2x.png 2x, images/ratezip-logo-02@3x.png 3x">
      <p class="overlay-hdr">You're just minutes away from seeing great offers, absolutely FREE!</p>
      <a href="#" class="overlay-closebutton overlayclose">Click to Continue</a>
      <p class="overlay-terms">Terms &amp; conditions apply. NMLS #1592292</p>
  </div>
</div>
<?php /** END INTRO POPUP **/ ?>

<?php include "./template/headerx.php"; ?>

      <form id="frmMain" action="submit_XML.php" method="post" onsubmit="return submitForm();">


                <div id="rqf" <?php echo (($STARTING_STEP > 0) ? 'style="display:none;"' : ''); ?>>
                    <div class="form_ob">
                        <div class="form_text">Compare today's <u>top</u> mortgage rates</div>
                        <div class="form_element ctr">
                            <div id="prodctr">
                                <div class="prod-btn" id="btnlooking"><div class="sbih refi"></div><span class="fq">I'm Just Looking Show Me Rates</span></div>
                                <div class="prod-btn" id="btnserious"><div class="sbih purch"></div><span class="fq tall">I'm Serious. Show Live Rates &amp; Let Me Pick a Lender</span></div>
                            </div>
                            <div style="clear: both;"></div>
                            <div class="errbox"><label for="SERIOUS" class="error" style="display: none;">This field is required.</label></div>
                        </div>
                    </div>
                </div>

                <div id="rq0" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text">Loan Type:</div>
                        <div class="form_element ctr">
                            <div id="prodctr">
                                <div class="prod-btn" id="hprefi"><div class="sbih refi"></div><span>Home Refinance &nbsp;&gt;</span></div>
                                <div class="prod-btn" id="hppurch"><div class="sbih purch"></div><span>Home Purchase &nbsp;&gt;</span></div>
                            </div>
                            <div style="clear: both;"></div>
                            <div class="errbox"><label for="PRODUCT" class="error" style="display: none;">Please select purchase or refinance.</label></div>
                        </div>
                    </div>
                </div>

                <div id="rq1a" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text">Zip Code:</div>
                        <div class="form_element stxt">
                            <input maxlength="5" id="PROP_ZIP" type="text" name="PROP_ZIP"  <?php echo $mobile_numberpattern; ?> value="<?php echo htmlentities($org_zip); ?>" autocomplete="postal-code" />
                            <div class="errbox"><label class="error hidden" for="PROP_ZIP">Zip code must be 5 numeric digits in length</label></div>
                        </div>
                    </div>
                </div>


                <div id="rq1" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text">Rate your credit:<span>(select one)</span></div>
                        <div class="form_element ctr">
                            <div class="credbtn crgr" id="credex">
                              <div class="bdr"></div>
                              <span>Excellent</span>
                              <div class="credesc"><strong>750-800+</strong>Very strong credit history with few credit problems. Pay your bills on time, most of the time. Have no recent late payments.</div>
                            </div>
                            <div class="credbtn cryl" id="credvg">
                              <div class="bdr"></div>
                              <span>Very Good</span>
                              <div class="credesc"><strong>700-750</strong>Average Consumer falls in this category. Usually pay your bills on time, but have a couple of late payments showing on your credit report.</div>
                            </div>
                            <div class="credbtn cror" id="credgd">
                              <div class="bdr"></div>
                              <span>Good</span>
                              <div class="credesc"><strong>621-700</strong>You generally make your payments but occasional late payments, lack of established credit or other factors are pulling your score down.</div>
                            </div>
                            <div class="credbtn crrd" id="credpr">
                              <div class="bdr"></div>
                              <span>Poor</span>
                              <div class="credesc"><strong>620 or below</strong>If you think you have poor credit and/or significant credit problems there are still options available to you.</div>
                            </div>
                            <div class="errbox"><label for="CRED_GRADE" class="error" style="display: none;">Please rate your credit.</label></div>
                        </div>
                    </div>
                </div>


                <div id="rqc" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text">Are you a Costco member?
                           <span>Not a Costco Member, not a problem!</span>
                        </div>
                        <div class="form_element ctr">
                            <div class="selbtn yesno" id="cmno">
                                <span>No</span>
                            </div>
                            <div class="selbtn yesno" id="cmyes">
                              <span>Yes</span>
                              <div class="costcoexlink">External Link for<br />Costco Members</div>
                            </div>
                            <div style="clear: both;"></div>
                            <div class="errbox"><label for="COSTCO_MEMBER" class="error" style="display: none;">This field is required.</label></div>
                        </div>
                    </div>
                </div>
               <div id="CostcoExternalLink" title="Visit External Link">
                 <p>You are leaving RateZip.com to visit a website not hosted by RateZip.com. RateZip.com is not responsible for content provided by third party sites.</p>
                 <p>You are subject to the destination site's Privacy Policy and Terms &amp; Conditions.</p>
               </div>

                <div id="rq3" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text">What type of property is it?</div>
                        <div class="form_element ctr" id="prodescq">
                            <div class="propbtn <?php echo htmlentities((strtolower($org_propdesc) === 'single_fam') ? 'sel' : ''); ?>" id="pdsingle">
                              <div class="sbip sf"></div>
                              <span>Single Family</span>
                            </div>
                            <div class="propbtn <?php echo htmlentities((strtolower($org_propdesc) === 'condo') ? 'sel' : ''); ?>" id="pdcondo">
                              <div class="sbip condo"></div>
                              <span>Condo/Townhome</span>
                            </div>
                            <div class="propbtn <?php echo htmlentities((strtolower($org_propdesc) === 'multi_fam') ? 'sel' : ''); ?>" id="pdmulti">
                              <div class="sbip multi"></div>
                              <span>Residential 2-4 Unit</span>
                            </div>
                            <div class="propbtn <?php echo htmlentities((strtolower($org_propdesc) === 'mobilehome') ? 'sel' : ''); ?>" id="pdmobile">
                              <div class="sbip mobile"></div>
                              <span>Mobile Home</span>
                            </div>
                            <div style="clear: both;"></div>
                            <div class="errbox"><label for="PROP_DESC" class="error" style="display: none;">Please select a property type.</label></div>
                        </div>
                    </div>
                </div>

                <div id="rq4" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text" id="prop_val_text">What is the property value?<span>(OK to estimate)</span></div>
                        <div class="form_element">
                            <div id="slwin"><span id="slVal"></span></div>
                            <div id="slider"></div>
                            <div class="errbox"><label class="error hidden" for="EST_VAL">Please select a property value.</label></div>
                        </div>
                    </div>
                </div>

                <div id="rq5" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text">Total Mortgage Balance:<span>(OK to estimate. Include 1st &amp; 2nd mortgages plus any equity loans or credit lines.)</span></div>
                        <div class="form_element">
                            <div id="slbwin"><span id="slbVal"></span></div>
                            <div id="sliderb"></div>
                            <div class="errbox"><label class="error hidden" for="BAL_ONE">Please select an amount.</label></div>
                        </div>
                    </div>
                </div>

                <div id="rqcr" style="display:none">
                    <div class="form_ob">
                        <div class="form_text">Current Interest Rate:<span>(it's ok to estimate)</span></div>
                        <div class="form_element dd">
                            <select id="CURR_RATE" name="CURR_RATE" class="flydropdown required">
                                <option value="">Select One</option>
                                <?php $sel = ($org_rate < 2.0); ?>
                                <option <?php echo ($sel ? 'selected="selected"' : ''); ?> value="1.999">Less than 2%</option>
                                <?php for ($iRate = 2; $iRate<7; $iRate++) {
                                    $sel = ($org_rate >= $iRate) && ($org_rate < ($iRate+0.25)); ?>
                                    <option <?php echo ($sel ? 'selected="selected"' : ''); ?> value="<?php echo $iRate; ?>.000"><?php echo $iRate; ?>.00%</option>
                                    <?php $sel = ($org_rate >= ($iRate+.25)) && ($org_rate < ($iRate+0.50)); ?>
                                    <option <?php echo ($sel ? 'selected="selected"' : ''); ?> value="<?php echo $iRate; ?>.250"><?php echo $iRate; ?>.25%</option>
                                    <?php $sel = ($org_rate >= ($iRate+.50)) && ($org_rate < ($iRate+0.75)); ?>
                                    <option <?php echo ($sel ? 'selected="selected"' : ''); ?> value="<?php echo $iRate; ?>.500"><?php echo $iRate; ?>.50%</option>
                                    <?php $sel = ($org_rate >= ($iRate+.75)) && ($org_rate < ($iRate+1.00)); ?>
                                    <option <?php echo ($sel ? 'selected="selected"' : ''); ?> value="<?php echo $iRate; ?>.750"><?php echo $iRate; ?>.75%</option>
                                <?php } ?>
                                <?php $sel = ($org_rate >= 7.0); ?>
                                <option <?php echo ($sel ? 'selected="selected"' : ''); ?> value="7.000">7.00% or more</option>
                            </select>
                            <div class="errbox"><label class="error hidden" for="CURR_RATE">Please select an interest rate.</label></div>
                        </div>
                    </div>
                </div>


                <div id="nh1" style="display:none">
                    <div class="form_ob">
                        <div class="form_text">Down payment:<span>(it's ok to estimate)</span></div>
                        <div id="vaBenTxt">Veterans: Your military service means you may be eligible for a VA loan with 0% down</div>
                        <div class="form_element dd">
                            <select id="DOWN_PMT" name="DOWN_PMT" class="flydropdown required" data-preload="<?php echo htmlentities($org_downpmt); ?>">
                                <option value="">Select One</option>
                            </select>
                            <div class="errbox"><label class="error hidden" for="DOWN_PMT">Please select a down payment.</label></div>
                        </div>
                    </div>
                </div>

                <div id="rq6" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text">Have you or your spouse served in the U.S. Military?</div>
                        <div class="form_element ctr">
                            <div class="selbtn yesno" id="vano">
                              <span>No</span>
                            </div>
                            <div class="selbtn yesno" id="vayes">
                              <span>Yes</span>
                            </div>
                            <div class="selbtn" id="vahaveloan">
                              <span>I have a VA loan</span>
                            </div>
                            <div style="clear: both;"></div>
                            <div class="errbox"><label for="VA_STATUS" class="error" style="display: none;">This field is required.</label></div>
                        </div>
                    </div>
                </div>

                <div id="rq11" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text">Are you self-employed?</div>
                        <div class="form_element ctr">
                            <div class="selbtn yesno" id="seno">
                              <span>No</span>
                            </div>
                            <div class="selbtn yesno" id="seyes">
                              <span>Yes</span>
                            </div>
                            <div style="clear: both;"></div>
                            <div class="errbox"><label for="SELF_EMPLOYED" class="error" style="display: none;">This field is required.</label></div>
                        </div>
                    </div>
                </div>

<?php
/*
                <div id="rqAmSav" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text">&nbsp;</div>
                        <div class="form_element ctr">
                            <style>
                                #naf-cta
                                    {
                                        color: #000000;
                                        background-color: #ffffff;
                                        width: 95%; max-width: 900px; border: rgb(0,108,186) solid 3px; padding: 0; margin: 0 auto 55px;
                                        -webkit-box-shadow: 0px 3px 2px 0px rgba(50, 50, 48, 0.5);
                                        -moz-box-shadow: 0px 3px 2px 0px rgba(50, 50, 48, 0.5);
                                        box-shadow: 0px 3px 2px 0px rgba(50, 50, 48, 0.5);
                                    }
                                .naf-col { box-sizing: border-box; padding: 10px; float: left;}
                                .naf-100 { padding: 25px 40px 5px; font-weight: bold; font-size: 1.5em; width: 100%; }
                                .naf-40 { width: 40%; display: inline-block; }
                                .naf-60 { width: 60%; display: inline-block; }
                                .naf-pl30  { padding-left: 30px !important; }
                                .naf-plr30 { padding-left: 30px !important; padding-right: 30px !important; }
                                .naf-20 { width: 20%; display: inline-block; }
                                .naf-30 { width: 30%; display: inline-block; }
                                .naf-tl { text-align: left; }
                                .naf-call { font-weight: bold; font-size: 1.2em; }
                                .naf-call a { text-decoration: none; color: #000000; }
                                .naf-clear { clear: both; }
                                .naf-link { color: #000000; font-size: 1.2em; padding-top: 20px; display: inline-block; width: 100%; text-align: center; }
                                .naf-button {
                                    font-family: Gotham, "Helvetica Neue", Helvetica, Arial, sans-serif;
                                    color: #000;
                                    line-height: 39px;
                                    font-size: 30px;
                                    font-weight: 700;
                                    padding: 10px 0px;
                                    width: 100%;
                                    margin: 5px 0;
                                    border-radius: 5px;
                                    -webkit-border-radius: 5px;
                                    -moz-border-radius: 5px;
                                    -ms-border-radius: 5px;
                                    -webkit-box-shadow: 0px 2px 0px 0px rgba(26, 26, 26, 0.8);
                                    -moz-box-shadow: 0px 2px 0px 0px rgba(26, 26, 26, 0.8);
                                    box-shadow: 0px 2px 0px 0px rgba(26, 26, 26, 0.8);
                                    background: #FFD310;
                                    background: -moz-linear-gradient(top, #FFD310 0%, #FF9D29 100%);
                                    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#FFD310), color-stop(100%,#FF9D29));
                                    background: -webkit-linear-gradient(top, #FFD310 0%,#FF9D29 100%);
                                    background: -o-linear-gradient(top, #FFD310 0%,#FF9D29 100%);
                                    background: -ms-linear-gradient(top, #FFD310 0%,#FF9D29 100%);
                                    background: linear-gradient(to bottom, #FFD310 0%,#FF9D29 100%);
                                    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#FFD310', endColorstr='#FF9D29',GradientType=0 );
                                    display: inline-block;
                                    cursor: pointer;
                                    text-align: center;
                                }
                                .am-rate {
                                    display: block;
                                    font-size: 16px;
                                    font-weight: normal;
                                    margin: 10px 0;
                                }
                                .am-rate span {
                                    padding-right: 15px;
                                }
                                #amerisavechatbox {
                                    display: block;
                                    -webkit-border-radius: 14px;
                                    -moz-border-radius: 14px;
                                    border-radius: 14px;
                                    background-color: #116FB7;
                                    padding: 10px;
                                    color: #ffffff;
                                    text-decoration: none;
                                    width: 70%;
                                    text-align: center;
                                    border: #cccccc solid 3px;
                                    margin: 10px 0 0 -16px;
                                }
                                .naf-button:hover { background: #FF0; }
                                @media (max-width: 700px) {
                                  .naf-link { font-size: 1.3em; }
                                }
                                @media (max-width: 600px) {
                                  .naf-col { float:none; margin: 0 auto; width: 100% !important; text-align:center; }
                                  .naf-link { font-size: 2em; padding-top: 0px; }
                                  .naf-100 { font-size: 2em; line-height: 1.0em; padding: 25px 10px 5px; }
                                  .naf-pl30  { padding-left: 10px !important; }
                                  #amerisavechatbox { margin: 10px 0 0 0; width: 100%; }
                                }
                                .naf-disclaimer {
                                    color: #999999;
                                    font-size: 12px !important;
                                    font-weight: normal !important;
                                    text-align: center;
                                    padding-top: 0 !important;
                                }
                            </style>
                            <?php
                                $naf_ppcid = getTrackingVal('ppcid','99999');
                                $naf_cred  = strtoupper(getTrackingVal('cred_grade',''));
                                $naf_credurl = 'excellent';
                                if ($naf_cred == 'GOOD') $naf_credurl = 'fair';
                                if ($naf_cred == 'POOR') $naf_credurl = 'poor';
                                $naf_prod  = 'refinance'; //hard coded
                                $naf_url_purch = 'https://apply.amerisave.com/loan/ams-goal/?source=5176&utm_source=ratezip&LeadID='.urlencode(getTrackingVal('GCLID', '')).'&RateZipID='.urlencode($naf_ppcid);
                                $naf_url_refi  = 'https://apply.amerisave.com/loan/ams-goal/?source=5176&utm_source=ratezip&LeadID='.urlencode(getTrackingVal('GCLID', '')).'&RateZipID='.urlencode($naf_ppcid);

                                include_once '../Amerisave/amerisave-rates.php';
                                $amRateData = amr_GetRateData('30Y');
                            ?>
                            <script type="text/javascript">
                                  function naf_link_complete() {
                                      var callback = function() {
                                        var p  = $('#PRODUCT').val();
                                        if (p == 'PP_REFI') {
                                            document.location = '<?php echo $naf_url_refi; ?>';
                                        } else {
                                            document.location = '<?php echo $naf_url_purch; ?>';
                                        }
                                      };
                                      var prod = ($('#PRODUCT').val() == 'PP_REFI') ? 'REFI' : 'PURCH';
                                      gtag('event', 'AmeriSave 5176', {
                                        'event_category' : 'RZ 5176 fly2hp2p '+prod,
                                        'event_label' : 'PPCID <?php echo htmlentities($naf_ppcid); ?>',
                                        'event_callback' : callback,
                                        'value' : 14
                                      });
                                      return false;
                                  }
                                  function naf_call_link() {
                                      var prod = ($('#PRODUCT').val() == 'PP_REFI') ? 'REFI' : 'PURCH';
                                      gtag('event', 'AmeriSave Phone Click', {
                                        'event_category' : 'RZ fly2hp2p '+prod,
                                        'event_label' : 'PPCID <?php echo htmlentities($naf_ppcid); ?>',
                                        'value' : 0
                                      });
                                      return true;
                                  }
                                  var hc = false;
                                  function naf_link() {
                                      if (hc) return;
                                      var callback = function() {
                                        if (hc == false) {
                                            naf_link_complete();
                                            hc = true;
                                        }
                                      };
                                      qp('track', 'Generic'); //Quora conversion
                                      var s = 'AW-915627431/B2j8CKHu2c0BEKe7zbQD'; //refi and purch
                                      gtag('event', 'conversion', {
                                          'send_to': s,
                                          'event_callback': callback
                                      });
                                      return false;
                                  }
                            </script>
                            <div id="naf-cta">
                                <div class="naf-100 naf-col">You have been matched with:</div>
                                <div class="naf-30 naf-col naf-pl30"><a href="#" onclick="return naf_link();"><img src="../common/logos/amerisave.png" alt="AmeriSave" border="0"/></a></div>
                                <div class="naf-40 naf-col naf-pl30 naf-tl"><ul><li>Get Rates and Pre-Qualified in 3 Mins</li><li>No SSN Needed For Pre-Qualification</li><li>No Hard Credit Pull For Pre-Qualification</li></ul>
                                        <?php if ($amRateData['status'] == "OK") { ?>
                                            <span class="am-rate"><?php echo '<span>'.$amRateData['apr'].'</span> '.$amRateData['name'].' APR'; ?></span>
                                        <?php } ?>
                                        <span class="naf-call">Call: <a href="tel:8662126300" onclick="return naf_call_link();">(866) 212-6300</a></span>
                                        <div class="mapi_chatnow">
                                            <!-- Featured Lender chatbot -->
                                            <script type="text/javascript">
                                            window.BOTSPLASH_APP_ID="f24c30ff-9dde-4b30-a47c-c4acf4cab30d";
                                            (function(){d=document;s=d.createElement("script");s.src="https://chatcdn.botsplash.com/x.js";s.async=true;d.getElementsByTagName("head")[0].appendChild(s);})();
                                            </script>
                                            <a href="#" id="amerisavechatbox" onclick="javascript: window.$botsplash.open({ hideOnClose: true }); return false;">Chat Available Now</a>
                                        </div>
                                    </div>
                                <div class="naf-30 naf-col naf-plr30"><a class="naf-button" href="#" onclick="return naf_link();" >Get Quote &raquo;</a></div>
                                <?php if ($amRateData['status'] == "OK") { ?>
                                    <div class="naf-100 naf-col naf-disclaimer"><?php echo $amRateData['disclaimer_asof']; ?></div>
                                <?php } ?>
                                <div class="naf-clear"></div>
                            </div>

                        </div>
                    </div>
                </div>
*/
?>
                <div id="rq11w" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text mb">Connecting Your Request</div>
                        <div class="form_element ctr conctr">
                            <div id="search1" style="display:none;" class="srchTxt">
                                <div class="searchchk"><img id="si1" class="chkimg" src="images/searchchk.png" alt="" border="0" style="display:none;"/></div>
                                <div class="searchtxt conok">Searching Lender Network...</div>
                            </div>
                            <div id="search2" style="display:none;"  class="srchTxt">
                                <div class="searchchk"><img id="si2" class="chkimg" src="images/searchchk.png" alt="" border="0" style="display:none;"/></div>
                                <div class="searchtxt mb">Connecting...</div>
                            </div>
                            <div id="search3" style="display:none;"  class="srchTxt">
                                <div class="searchchk"><img id="si3" class="chkimg" src="images/searchchkg.png" alt="" border="0" style="display:none;"/></div>
                                <div class="searchtxt mb">Connected</div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php /* BEGIN LT ONLY HOST AND POST FIELDS */ ?>
                <div id="rqhp3" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text">Are you working with a real estate agent?</div>
                        <div class="form_element ctr">
                            <div class="selbtn yesno" id="afyes">
                              <span>Yes</span>
                            </div>
                            <div class="selbtn yesno" id="afno">
                              <span>No</span>
                            </div>
                            <div style="clear: both;"></div>
                            <div class="errbox"><label for="AGENT_FOUND" class="error" style="display: none;">Please select yes or no.</label></div>
                        </div>
                        <div id="matchresult" class="form_element ctr not_ready">
                            <div id="brokername"><strong>Proposed agent for your purchase: </strong></div>
                            <div class="form_text">Accept the proposed agent match?</div>
                            <div class="selbtn yesno" id="matchyes">
                              <span>Yes</span>
                            </div>
                            <div class="selbtn yesno" id="matchno">
                              <span>No</span>
                            </div>
                            <div style="clear: both;"></div>
                            <div class="errbox"><label for="ACCEPT_MATCH" class="error" style="display: none;">Please select yes or no.</label></div>
                        </div>
                    </div>
                </div>
                <div id="rqhp4" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text">What is your monthly mortgage payment?<span>(ok to estimate)</span></div>
                        <div class="form_element dd">
                            <select id="MonthlyPayment" name="MonthlyPayment" class="flydropdown required">
                                <?php
                                    $iAmt = 0;
                                    $gap  = 250;
                                    $mid  = $gap / 2;
                                    while($iAmt < 10000) {
                                        $iAmt = $iAmt + $gap;
                                        echo '<option value="'.($iAmt-$mid).'">$'.number_format($iAmt-$gap).' - $'.number_format($iAmt-1).'</option>';
                                    }
                                    echo '<option value="'.($iAmt).'">$'.number_format($iAmt).' or more</option>';
                                ?>
                            </select>
                            <div class="errbox"><label class="error hidden" for="MonthlyPayment">Please select an option.</label></div>
                        </div>
                    </div>
                </div>
              <div id="rqhp5" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text" id="propusehdr">How is this property used?</div>
                        <div class="form_element dd">
                            <select id="PROP_USE" name="PROP_USE" class="flydropdown required">
                                <option value="OWNEROCCUPIED">Primary Home</option>
                                <option value="SECONDHOME">Secondary Home</option>
                                <option value="INVESTMENTPROPERTY">Investment property</option>
                            </select>
                            <div class="errbox"><label class="error hidden" for="PROP_USE">Please select an option.</label></div>
                        </div>
                    </div>
                </div>
              <div id="rqhp6" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text">In the last 7 years, have you had a bankruptcy and/or foreclosure?</div>
                        <div class="form_element dd">
                            <select id="BANK_FORC" name="BANK_FORC" class="flydropdown required">
                                <option value="NO">No</option>
                                <option value="BANK">Yes, Bankruptcy</option>
                                <option value="FORC">Yes, Foreclosure</option>
                                <option value="BOTH">Both</option>
                            </select>
                            <div class="errbox"><label class="error hidden" for="BANK_FORC">Please select an option.</label></div>
                        </div>
                    </div>
                </div>

              <div id="rqhp9" style="display:none;">
                <div class="form_ob">
                    <div class="form_text">What type of loan do you currently have?</div>
                    <div class="form_element dd">
                        <select id="CURR_LOAN_TYPE" name="CURR_LOAN_TYPE" class="flydropdown required">
                            <option value="CONVENTIONAL">Conventional</option>
                            <option value="FHA">FHA</option>
                            <option value="VA">VA</option>
                            <option value="OTHER">Other</option>
                        </select>
                        <div class="errbox"><label class="error hidden" for="CURR_LOAN_TYPE">Please select an option.</label></div>
                    </div>
                </div>
              </div>

                <div id="rqhp7" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text">Your date of birth<span>(for verification purposes only)</span></div>
                        <div class="form_element dd ctr">
                            <select id="DOBMM" name="DOBMM" class="flydropdown required">
                                <option value="01">Jan</option><option value="02">Feb</option>
                                <option value="03">Mar</option><option value="04">Apr</option>
                                <option value="05">May</option><option value="06">Jun</option>
                                <option value="07">Jul</option><option value="08">Aug</option>
                                <option value="09">Sep</option><option value="10">Oct</option>
                                <option value="11">Nov</option><option value="12">Dec</option>
                            </select>
                            <select id="DOBDD" name="DOBDD" class="flydropdown required">
                                <option value="01">01</option><option value="02">02</option><option value="03">03</option>
                                <option value="04">04</option><option value="05">05</option><option value="06">06</option>
                                <option value="07">07</option><option value="08">08</option><option value="09">09</option>
                                <option value="10">10</option><option value="11">11</option><option value="12">12</option>
                                <option value="13">13</option><option value="14">14</option><option value="15">15</option>
                                <option value="16">16</option><option value="17">17</option><option value="18">18</option>
                                <option value="19">19</option><option value="20">20</option><option value="21">21</option>
                                <option value="22">22</option><option value="23">23</option><option value="24">24</option>
                                <option value="25">25</option><option value="26">26</option><option value="27">27</option>
                                <option value="28">28</option><option value="29">29</option><option value="30">30</option>
                                <option value="31">31</option>
                            </select>
                            <select id="DOBYYYY" name="DOBYYYY" class="flydropdown required">
                                <?php
                                    $currYear = intval(date("Y"))-18;
                                    for($i=$currYear; $i > ($currYear-100); $i--) {
                                        echo '<option value="'.$i.'">'.$i.'</option>';
                                    }
                                ?>
                            </select>
                            <input type="hidden" name="DOB" id="DOB" value="" />
                            <div class="errbox"><label class="error hidden" for="DOB">Please select a valid birthday.</label></div>
                        </div>
                    </div>
                </div>
                <?php /* END LT ONLY HOST AND POST FIELDS */ ?>


                <div id="nh3" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text">Have you found a home?<span>(select one)</span></div>
                        <div class="form_element ctr">
                            <div class="selbtn yesno" id="shyes">
                              <span>Yes</span>
                            </div>
                            <div class="selbtn yesno" id="shno">
                              <span>No</span>
                            </div>
                            <div style="clear: both;"></div>
                            <div class="errbox"><label for="SPEC_HOME" class="error" style="display: none;">Please select yes or no.</label></div>
                        </div>
                    </div>
                </div>

                <div id="nh9" style="display:none;">
                    <div class="form_ob">
                        <div class="form_text">Timeframe to buy:</div>
                        <div class="form_element dd">
                            <select id="TIMEFRAME" name="TIMEFRAME" data-preload="<?php echo htmlentities($org_timeframe); ?>" class="flydropdown required">
                                <option value="TP1">Buying in 2 to 3 Months</option>
                                <option value="TP2">Buying in 3 to 6 Months</option>
                                <option value="TP3">Buying in 30 Days</option>
                                <option value="TP4">Buying in 6 to 12 Months</option>
                                <option value="CS1">Offer Pending / Found a House</option>
                                <option value="CS4">Researching options</option>
                                <option value="CS5">Signed a Purchase Agreement</option>
                            </select>
                            <div class="errbox"><label class="error hidden" for="TIMEFRAME">Please select a timeframe.</label></div>
                        </div>
                    </div>
                </div>

                <div id="rq8" style="display:none">
                    <div class="form_ob">
                        <div class="form_text">Your name<span>(we respect your privacy)</span></div>
                        <div class="form_element ctr pii">

                           <input type="text" id="FNAME" name="FNAME" value="" class="required" placeholder="First Name"  autocomplete="given-name" />
                           <div class="errbox"><label for="FNAME" class="error" style="display: none;">We need your real name to process your request.</label></div>

                           <input type="text" id="LNAME" name="LNAME" value="" class="required" placeholder="Last Name" autocomplete="family-name" />
                           <div class="errbox"><label for="LNAME" class="error" style="display: none;">We need your real name to process your request.</label></div>
                           <div class="errbox"><label id="errNameMatch" class="error" style="display: none;">First and last name must be different.</label></div>

                        </div>
                    </div>
                </div>

                <div id="rq9" style="display:none">
                    <div class="form_ob">
                        <div class="form_text">Your contact information<?php /* <span>(We'll send your phone number a text message to validate your identity.)</span> */ ?></div>
                        <div class="form_element ctr pii">

                           <input type="text"  <?php echo $mobile_numberpattern; ?> id="PRI_PHONE" name="PRI_PHONE" value="" class="required" placeholder="Phone Number" autocomplete="tel" />
                           <div class="errbox"><label for="PRI_PHONE" class="error" style="display: none;">Please enter your Phone Number.</label></div>
                           <input type="text" id="EMAIL" name="EMAIL" value="" class="required email" placeholder="Email" autocomplete="email" />
                           <div class="errbox"><label for="EMAIL" class="error" style="display: none;">Please enter your Email Address.</label></div>

                            <div id="laststeplbl">By clicking the button below, you acknowledge, consent, and agree to our terms at the bottom of this page</div>

                            <a href="#" id="finishbtn" class="finishbtn" onclick="continue_form(true); return false;"><span>View my rates &nbsp;&gt;</span></a>
                            <div  id="finishspinner" class="regCtrlWaiting hidden"><div class="loader"></div>Please wait...</div>

                            <div id="laststeptxt"><div id="finaldisclaimer"><label style="font-size: 1em;"><input type="hidden" id="leadid_tcpa_disclosure"/><?php include_once '../common/peklava-boberdoo-tcpa.php'; ?></label></div></div>


                        </div>
                    </div>
                </div>

                <div id="rqtfa" style="display:none">
                    <div class="form_ob">
                        <div class="form_text">Please confirm the code sent to your phone:
                                <span id="tfaNbrMsg"><a href="#" onclick="goBack(); return false;">&lt;&lt; use a different number</a></span>
                        </div>
                        <div class="form_element ctr pii">

                           <input type="text" maxlength="6" <?php echo $mobile_numberpattern; ?> id="TFA" name="TFA" value="" class="required" placeholder="" />
                           <div class="errbox"><label for="TFA" class="error" style="display: none;">Please enter the six digit code.</label></div>
                           <div class="errbox"><label id="tfaErr" class="error" style="display: none;"></label></div>
                           <?php
                              include_once '../common/tfa-auth.php';
                              $tfa_id = tfa_generate();
                           ?>
                           <input type="hidden" id="TFAID" NAME="TFAID" value="<?php echo htmlentities($tfa_id); ?>" />

                            <a href="#" id="tfaConfirmBtn" class="finishbtn" onclick="checkTFA(); return false;"><span>Confirm &gt;</span></a>


                            <div style="display:none;" id="tfaFinishMsg" class="errbox"><label>Code confirmed! Thank you. Press finish to be connected.</label></div>
                            <a href="#" style="display:none;" id="tfaFinishBtn" class="finishbtn tfagreen" onclick="tfa_submitForm(true); return false;"><span>Finish &gt;</span></a>

                        </div>
                    </div>
                </div>

                <div id="rq10" style="display:none">
                    <div class="form_ob">
                        <div class="form_text">Your current address:<span>(For verification purposes only. We do not mail to this address.)</span></div>
                        <div id="secrtrust" style="display:none;"></div>
                        <div class="form_element stxt pii">


                           <input type="text" id="ADDRESS" name="ADDRESS" value="" <?php /* class="required" */ ?> placeholder="Address" />
                           <div class="errbox">
                              <label for="ADDRESS" class="error" style="display: none;">Please enter your Address.</label>
                              <div id="zip_out"><span id="currloc"></span> (<a onclick="change_address2(); return false;" href="#">change</a>)<div style="clear:both;"></div></div>
                           </div>

                           <div style="display:none;" id="address2_box">

                              <input type="text" id="CITY" name="CITY" value="" class="required" placeholder="City" />
                              <div class="errbox"><label for="CITY" class="error" style="display: none;">Please enter your City.</label></div>

                              <select id="STATE" name="STATE" class="flydropdown required">
                                  <option selected="selected" value="">--select--</option>
                                  <option value="AL">Alabama</option>
                                  <option value="AK">Alaska</option>
                                  <option value="AZ">Arizona</option>
                                  <option value="AR">Arkansas</option>
                                  <option value="CA">California</option>
                                  <option value="CO">Colorado</option>
                                  <option value="CT">Connecticut</option>
                                  <option value="DE">Delaware</option>
                                  <option value="DC">District of Columbia</option>
                                  <option value="FL">Florida</option>
                                  <option value="GA">Georgia</option>
                                  <option value="HI">Hawaii</option>
                                  <option value="ID">Idaho</option>
                                  <option value="IL">Illinois</option>
                                  <option value="IN">Indiana</option>
                                  <option value="IA">Iowa</option>
                                  <option value="KS">Kansas</option>
                                  <option value="KY">Kentucky</option>
                                  <option value="LA">Louisiana</option>
                                  <option value="ME">Maine</option>
                                  <option value="MD">Maryland</option>
                                  <option value="MA">Massachusetts</option>
                                  <option value="MI">Michigan</option>
                                  <option value="MN">Minnesota</option>
                                  <option value="MS">Mississippi</option>
                                  <option value="MO">Missouri</option>
                                  <option value="MT">Montana</option>
                                  <option value="NE">Nebraska</option>
                                  <option value="NV">Nevada</option>
                                  <option value="NH">New Hampshire</option>
                                  <option value="NJ">New Jersey</option>
                                  <option value="NM">New Mexico</option>
                                  <option value="NY">New York</option>
                                  <option value="NC">North Carolina</option>
                                  <option value="ND">North Dakota</option>
                                  <option value="OH">Ohio</option>
                                  <option value="OK">Oklahoma</option>
                                  <option value="OR">Oregon</option>
                                  <option value="PA">Pennsylvania</option>
                                  <option value="RI">Rhode Island</option>
                                  <option value="SC">South Carolina</option>
                                  <option value="SD">South Dakota</option>
                                  <option value="TN">Tennessee</option>
                                  <option value="TX">Texas</option>
                                  <option value="UT">Utah</option>
                                  <option value="VT">Vermont</option>
                                  <option value="VA">Virginia</option>
                                  <option value="WA">Washington</option>
                                  <option value="WV">West Virginia</option>
                                  <option value="WI">Wisconsin</option>
                                  <option value="WY">Wyoming</option>
                              </select>
                              <div class="errbox"><label for="STATE" class="error" style="display: none;">Please select your State.</label></div>

                               <input  <?php echo $mobile_numberpattern; ?> type="text" id="ZIP" name="ZIP" value="" class="required" placeholder="Zip" autocomplete="postal-code" />
                              <div class="errbox"><label for="ZIP" class="error" style="display: none;">Please enter your 5 digit Zip Code.</label></div>

                           </div>

                        </div>
                    </div>
                </div>

                <div>
                  <?php /** FIELDS SET FROM QUESTIONS ABOVE **/ ?>
                  <input type="hidden" id="PRODUCT"    name="PRODUCT"    value="<?php echo htmlentities($org_product); ?>" />
                  <input type="hidden" id="CRED_GRADE" name="CRED_GRADE" value="<?php echo htmlentities($org_cred); ?>" />

                  <input type="hidden" id="PROP_DESC"  name="PROP_DESC" data-preload="<?php echo htmlentities($org_propdesc); ?>" value="" />
                  <input type="hidden" id="EST_VAL"    name="EST_VAL"  data-preload="<?php echo htmlentities($org_estval); ?>" value="" />
                  <input type="hidden" id="BAL_ONE"    name="BAL_ONE"  data-preload="<?php echo htmlentities($org_loanval); ?>" value="" />
                  <input type="hidden" id="VA_STATUS"  name="VA_STATUS"  value="<?php echo htmlentities($org_vastatus); ?>" />
                  <input type="hidden" id="SPEC_HOME" name="SPEC_HOME" data-preload="<?php echo htmlentities($org_spechome); ?>" value="" />
                  <input type="hidden" id="SELF_EMPLOYED" name="SELF_EMPLOYED" value="<?php echo htmlentities($org_employed); ?>" />

                  <input type="hidden" id="SERIOUS" name="SERIOUS"  value="<?php echo htmlentities(($STARTING_STEP > 0) ? 'SERIOUS' : ''); ?>" />

                  <?php /** COSTCO FIELDS **/ ?>
                  <input type="hidden" id="COSTCO_MEMBER" name="COSTCO_MEMBER" value="no" />

                  <?php /** OTHER LT FIELDS **/ ?>
                  <input type="hidden" id="AGENT_FOUND" name="AGENT_FOUND" data-preload="<?php echo htmlentities($org_agentfound); ?>" value="" />
                  <input type="hidden" id="ACCEPT_MATCH" name="ACCEPT_MATCH" data-preload="<?php echo htmlentities($org_acceptmatch); ?>" value="" />
                  <input type="hidden" id="MATCH_TOKEN" name="MATCH_TOKEN" data-preload="<?php echo htmlentities($org_matchtoken); ?>" value="" />
                  <input type="hidden" id="LTHP" name="LTHP"  value="0" />
                  <input type="hidden" id="LTHPSCORE" name="LTHPSCORE"  value="0" />

                  <?php /** LP REQUIRED FIELDS **/ ?>
                  <?php
                     $ppcid = getTrackingVal('PPCID', '7');
                     $aid = (($ppcid == "8") ? "30955" : "30551");
                  ?>
                  <input type="hidden" value="<?php echo $aid; ?>" name="AID" />
                  <input type="hidden" name="IP_ADDRESS" value="<?php echo urlencode(getIP()); ?>" />

                  <?php /* BEGIN Hard coded fields */ ?>
                  <input type="hidden" name="LOAN_TYPE" value="Fixed" />
                  <input type="hidden" name="PROP_ST"  id="PROP_ST" value="" />
                  <?php /* END Hard coded fields */ ?>

                  <?php /* The LeadiD field */ ?>
                  <input id="leadid_token" name="universal_leadid" type="hidden" value=""/>

                  <?php /* used by the redirect api */ ?>
                  <input type="hidden" name="LANDER_TIME"  id="CAPTURE_TIME" value="<?php echo date("m/d/Y H:i"); ?>" />

                  <?php /* used by the upsell page */ ?>
                  <input type="hidden" name="template"  value="ratesform" />

                  <?php
                  writeTrackingFormValues()
                  ?>

                </div>

      </form>

      <form id="frmVA" action="../getratesVA/" method="get">
         <input type="hidden" name="PRODUCT" value="PP_REFI" />
         <?php
         writeTrackingFormValues(TRUE);
         ?>
      </form>

      <?php
        //choose esource based on PPCID
        $campaignID="2229"; //default
        $ppcid = getTrackingVal('PPCID','');
        switch($ppcid) {
         case "78":
            $campaignID="2229";
            break;
         case "82":
            $campaignID="3810";
            break;
         case "83":
            $campaignID="2229";
            break;
        }
      ?>
      <form id="frmLT" action="//ck.lendingtree.com/" method="get">
        <div>
          <input type="hidden" name="a" value="64" />
          <input type="hidden" name="c" value="<?php echo htmlentities($campaignID); ?>" />
          <input type="hidden" name="s1" value="<?php echo htmlentities(getTrackingVal('PPCID', '7')); ?>" />
          <input type="hidden" name="s2" id="ltprod" value="" />
          <input type="hidden" name="s3" value="<?php echo htmlentities(getTrackingVal('CID', 'NOCID')); ?>" />
          <input type="hidden" name="ccreative" id="ccreative" value="rz" />
        </div>
      </form>


<?php include "./template/footerx.php"; ?>
<?php include "../common/tracking.php"; ?>
<?php include "../common/leadid-script.php"; ?>

</body>
</html>

