<?php
/*

    File:   functions.inc
    Date:   December, 2010
    Author: Greg Berchelmann (greg.berchelmann@gmail.com)
    Dependencies:  none

    Purpose:

        Contains various functions
*/


// return the text cleaned up for any HTML injection
function getValidTxt( $sTxt )
{
    return trim(strip_tags($sTxt));
}

// return the text cleaned up for any HTML injection
function getValidTxtDefault( $sTxt, $sDefault )
{
    $sTxt = getValidTxt( $sTxt );
    if (strlen($sTxt) == 0) return $sDefault;
    return $sTxt;
}

// return the text cleaned up for any HTML injection
function getValidTxtDefArray( $sTxt, $sDefault, $validArray )
{
    $sTxt = getValidTxtDefault( $sTxt, $sDefault );
    if (!in_array($sTxt, $validArray)) return $sDefault;
    return $sTxt;
}


// return an integer
function getValidIntNR( $sVal, $iDefault )
{
    $sVal = trim(str_replace("$","",strip_tags($sVal)));
    $sVal = trim(str_replace(",","",$sVal));
    if (!is_numeric($sVal))       { return $iDefault; }
    if (!settype($sVal,"float"))  { return $iDefault; };
    $sVal = floor($sVal);
    return $sVal;
}

// return an integer between iMin and iMax, with default value provided if
// the input fails the validation checks
function getValidInt( $sVal, $iDefault, $iMin, $iMax )
{
    $sVal = getValidIntNR( $sVal, $iDefault );
    if (($sVal > $iMax) || ($sVal < $iMin)) { return $iDefault; };
    return $sVal;
}


//get the IP of the current client or a default if we are debugging locally
function getIP() {
    $sIP = (empty($_SERVER['HTTP_CLIENT_IP']) ? (empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR']) : $_SERVER['HTTP_CLIENT_IP']);
    if ($sIP == '127.0.0.1') return '67.192.39.115';
    return $sIP;
}

//get the current page url
function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

//return the checked string if this value matches
function GetChecked($sVal, $sChkVal)
{
    if (strtoupper($sVal) == strtoupper($sChkVal)) return 'checked="checked"';
    return '';
}

//return the selected string if this value matches
function GetSelected($sVal, $sSelVal)
{
    if (strtoupper($sVal) == strtoupper($sSelVal)) return 'selected="selected"';
    return '';
}


//get a tracking  value from query string, cookie, or default
function getTrackingVal( $sKey, $sDefault )
{
    //lowercase the key
    $sKey = strtolower($sKey);

    //get the array of values we'll use
    $vars = array_merge($_COOKIE, $_GET, $_POST);
    $vars = array_change_key_case($vars,CASE_LOWER);

    //see if we have a querystring value
    if (isset($vars[$sKey])) {
        if (strlen($vars[$sKey]) > 0) {
            return $vars[$sKey];
        }
    }

    //no cookie or querystring value so just use default
    return $sDefault;
}

//get and write out tracking variables
function writeTrackingFormValues($supressIDs = FALSE) {

    //1) get tracking info, with defaults
    $ppcID  = getTrackingVal('PPCID', '1');
    $gclID  = getTrackingVal('GCLID', '');
    $cidVar = getTrackingVal('CID'  , 'RateZip');
    $sidVar = getTrackingVal('SID'  , '');
    $midVar = getTrackingVal('MID'  , $ppcID);
    $tidVar = getTrackingVal('TID'  , '');
    $tsidVr = getTrackingVal('TSID' , '');

    //2) write out the tracking form fields
    writeHiddenFld('PPCID', $ppcID, $supressIDs);
    writeHiddenFld('GCLID', $gclID, $supressIDs);
    writeHiddenFld('CID'  , $cidVar, $supressIDs);
    writeHiddenFld('SID'  , $sidVar, $supressIDs);
    writeHiddenFld('MID'  , $midVar, $supressIDs);
    writeHiddenFld('TID'  , $tidVar, $supressIDs);
    writeHiddenFld('TSID' , $tsidVr, $supressIDs);

    //2) Deal with Apollo Interactive special fields
    $ai_sidVar  = getTrackingVal('subid'  , '');
    $ai_refVar  = getTrackingVal('referrer'  , '');
    $ai_srefVar = getTrackingVal('subreferrer'  , '');
    writeHiddenFld('SUBID'  ,       $ai_sidVar, $supressIDs);
    writeHiddenFld('REFERRER',      $ai_refVar, $supressIDs);
    writeHiddenFld('SUBREFERRER',   $ai_srefVar, $supressIDs);

    //3) Deal with brownbag
    $bb_trackingid = getTrackingVal('trackingid'  , '');
    writeHiddenFld('trackingid', $bb_trackingid, TRUE);

}
function writeHiddenFld( $sName, $sVal, $supressIDs = FALSE ) {
    if ($supressIDs) {
        echo "<input type=\"hidden\" name=\"$sName\" value=\"".htmlentities($sVal)."\" />";
    } else {
        echo "<input type=\"hidden\" id=\"$sName\" name=\"$sName\" value=\"".htmlentities($sVal)."\" />";
    }
}

?>
