<?php

class FormLoader {

    //Submitted lead data
    private $leadData = NULL;

    private $preLoadedFields = array(
        "PRODUCT"       => array("VALUE"=>NULL, "VALID"=>FALSE),
        "ZIP"           => array("VALUE"=>NULL, "VALID"=>FALSE),
        "CRED_GRADE"    => array("VALUE"=>NULL, "VALID"=>FALSE),
        "PROP_DESC"     => array("VALUE"=>NULL, "VALID"=>FALSE),
        "VA_STATUS"     => array("VALUE"=>NULL, "VALID"=>FALSE),
        "EST_VAL"       => array("VALUE"=>NULL, "VALID"=>FALSE),
        "LOAN_VAL"      => array("VALUE"=>NULL, "VALID"=>FALSE),
        "CURR_RATE"     => array("VALUE"=>NULL, "VALID"=>FALSE),
        "SELF_EMPLOYED" => array("VALUE"=>NULL, "VALID"=>FALSE),
        "TIMEFRAME"     => array("VALUE"=>NULL, "VALID"=>FALSE),
        "AGENT_FOUND"   => array("VALUE"=>NULL, "VALID"=>FALSE),
        "ACCEPT_MATCH"  => array("VALUE"=>NULL, "VALID"=>FALSE),
        "MATCH_TOKEN"   => array("VALUE"=>NULL, "VALID"=>FALSE),
        "SPEC_HOME"     => array("VALUE"=>NULL, "VALID"=>FALSE)
    );

    //Constructor
    public function __construct() {

        $vars = array_merge($_GET, $_POST);
        $this->leadData = array_change_key_case($vars,CASE_UPPER);

        //load each of the potential vars
        $this->preLoadedFields["PRODUCT"]["VALUE"]    = $this->getProduct();
        $this->preLoadedFields["ZIP"]["VALUE"]        = $this->getZip();
        $this->preLoadedFields["CRED_GRADE"]["VALUE"] = $this->getCredit();
        $this->preLoadedFields["PROP_DESC"]["VALUE"]  = $this->getPropDesc();
        $this->preLoadedFields["VA_STATUS"]["VALUE"]  = $this->getVAStatus();
        $this->preLoadedFields["EST_VAL"]["VALUE"]    = $this->getEstVal();
        $this->preLoadedFields["LOAN_VAL"]["VALUE"]   = $this->getLoanVal();
        $this->preLoadedFields["CURR_RATE"]["VALUE"]  = $this->getCurrRate();
        $this->preLoadedFields["SELF_EMPLOYED"]["VALUE"] = $this->getSelfEmployed();
        $this->preLoadedFields["TIMEFRAME"]["VALUE"]     = $this->getTimeFrame();
        $this->preLoadedFields["AGENT_FOUND"]["VALUE"]   = $this->getAgentFound();
        $this->preLoadedFields["ACCEPT_MATCH"]["VALUE"]   = $this->getAcceptMatch();
        $this->preLoadedFields["MATCH_TOKEN"]["VALUE"]   = $this->getMatchToken();
        $this->preLoadedFields["SPEC_HOME"]["VALUE"]     = $this->getSpecHome();

        //now set the validations
        $this->preLoadedFields["PRODUCT"]["VALID"]    = (strlen($this->preLoadedFields["PRODUCT"]["VALUE"]) > 0);
        $this->preLoadedFields["ZIP"]["VALID"]        = (strlen($this->preLoadedFields["ZIP"]["VALUE"]) == 5);
        $this->preLoadedFields["CRED_GRADE"]["VALID"] = (strlen($this->preLoadedFields["CRED_GRADE"]["VALUE"]) > 0);
        $this->preLoadedFields["PROP_DESC"]["VALID"]  = (strlen($this->preLoadedFields["PROP_DESC"]["VALUE"]) > 0);
        $this->preLoadedFields["VA_STATUS"]["VALID"]  = (strlen($this->preLoadedFields["VA_STATUS"]["VALUE"]) > 0);
        $this->preLoadedFields["EST_VAL"]["VALID"]    = ($this->preLoadedFields["EST_VAL"]["VALUE"] > 0);
        $this->preLoadedFields["LOAN_VAL"]["VALID"]   = ($this->preLoadedFields["LOAN_VAL"]["VALUE"] > 0);
        $this->preLoadedFields["CURR_RATE"]["VALID"]  = ($this->preLoadedFields["CURR_RATE"]["VALUE"] > 0);
        $this->preLoadedFields["SELF_EMPLOYED"]["VALID"] = (strlen($this->preLoadedFields["SELF_EMPLOYED"]["VALUE"]) > 0);
        $this->preLoadedFields["TIMEFRAME"]["VALID"]     = (strlen($this->preLoadedFields["TIMEFRAME"]["VALUE"]) > 0);
        $this->preLoadedFields["AGENT_FOUND"]["VALID"]   = (strlen($this->preLoadedFields["AGENT_FOUND"]["VALUE"]) > 0);
        $this->preLoadedFields["ACCEPT_MATCH"]["VALID"]  = (strlen($this->preLoadedFields["ACCEPT_MATCH"]["VALUE"]) > 0);
        $this->preLoadedFields["MATCH_TOKEN"]["VALID"]   = (strlen($this->preLoadedFields["MATCH_TOKEN"]["VALUE"]) > 0);
        $this->preLoadedFields["SPEC_HOME"]["VALID"]     = (strlen($this->preLoadedFields["SPEC_HOME"]["VALUE"]) > 0);
    }

    //Get a variable, defaulting to scrubbing (htmlentities) the output
    public function getVar($sKey, $default="", $CLEAN_OUTPUT = TRUE) {
        $k = strtoupper($sKey);
        $v = $default;
        if (array_key_exists($k,$this->leadData)) {
          $v = trim($this->leadData[$k]);
        }
        if ($CLEAN_OUTPUT === TRUE) {
            return htmlentities($v);
        }
        return $v;
    }

    function isPurch() {
        if ($this->preLoadedFields["PRODUCT"]["VALUE"] !== NULL) {
            return (strtoupper($this->preLoadedFields["PRODUCT"]["VALUE"]) === 'PP_NEWHOME');
        }
        return FALSE;
    }
    function isRefi() {
        if ($this->preLoadedFields["PRODUCT"]["VALUE"] !== NULL) {
            return (strtoupper($this->preLoadedFields["PRODUCT"]["VALUE"]) === 'PP_REFI');
        }
        return FALSE;
    }

    public function hasProduct() { return $this->preLoadedFields["PRODUCT"]["VALID"]; }
    public function getProduct() {
        if ($this->preLoadedFields["PRODUCT"]["VALUE"] !== NULL) return $this->preLoadedFields["PRODUCT"]["VALUE"];
        $v = strtoupper($this->getVar("PRODUCT"));
        if ($v == 'REFI') return "PP_REFI";
        if ($v == "PURCHASE") return "PP_NEWHOME";
        if (($v != "PP_REFI") && ($v != "PP_NEWHOME")) return '';
        return $v;
    }

    public function hasCredit() { return $this->preLoadedFields["CRED_GRADE"]["VALID"]; }
    public function getCredit() {
        if ($this->preLoadedFields["CRED_GRADE"]["VALUE"] !== NULL) return $this->preLoadedFields["CRED_GRADE"]["VALUE"];
        $v = strtoupper($this->getVar("CRED_GRADE"));
        if ($v == 'FAIR') $v = "GOOD"; //a mapping
        $arValues = array("EXCELLENT","VERY GOOD","GOOD","FAIR","POOR");
        if (in_array($v,$arValues)) return $v;
        return '';
    }

    public function hasPropDesc() { return $this->preLoadedFields["PROP_DESC"]["VALID"]; }
    public function getPropDesc() {
        if ($this->preLoadedFields["PROP_DESC"]["VALUE"] !== NULL) return $this->preLoadedFields["PROP_DESC"]["VALUE"];
        $v = strtolower($this->getVar("PROP_DESC"));
        $arValues = array("single_fam","condo","multi_fam","mobilehome");
        if (in_array($v,$arValues)) return $v;
        return 'single_fam'; //default value
    }

    public function hasSelfEmployed() { return $this->preLoadedFields["SELF_EMPLOYED"]["VALID"]; }
    public function getSelfEmployed() {
        if ($this->preLoadedFields["SELF_EMPLOYED"]["VALUE"] !== NULL) return $this->preLoadedFields["SELF_EMPLOYED"]["VALUE"];
        $v = strtoupper($this->getVar("SELF_EMPLOYED"));
        $arValues = array("YES","NO");
        if (in_array($v,$arValues)) return $v;
        return 'NO'; //default value
    }

    public function hasTimeFrame() { return $this->preLoadedFields["TIMEFRAME"]["VALID"]; }
    public function getTimeFrame() {
        if ($this->preLoadedFields["TIMEFRAME"]["VALUE"] !== NULL) return $this->preLoadedFields["TIMEFRAME"]["VALUE"];
        $v = strtoupper($this->getVar("TIMEFRAME"));
        $arValues = array("TP1","TP2","TP3","TP4","CS1","CS4","CS5");
        if (in_array($v,$arValues)) return $v;
        return $arValues[0]; //default value
    }

    public function hasAgentFound() { return $this->preLoadedFields["AGENT_FOUND"]["VALID"]; }
    public function getAgentFound() {
        if ($this->preLoadedFields["AGENT_FOUND"]["VALUE"] !== NULL) return $this->preLoadedFields["AGENT_FOUND"]["VALUE"];
        $v = strtoupper($this->getVar("AGENT_FOUND"));
        $arValues = array("YES","NO");
        if (in_array($v,$arValues)) return $v;
        return 'NO'; //default value
    }

    public function hasAcceptMatch() { return $this->preLoadedFields["ACCEPT_MATCH"]["VALID"]; }
    public function getAcceptMatch() {
        if ($this->preLoadedFields["ACCEPT_MATCH"]["VALUE"] !== NULL) return $this->preLoadedFields["ACCEPT_MATCH"]["VALUE"];
        $v = strtoupper($this->getVar("ACCEPT_MATCH"));
        $arValues = array("YES","NO");
        if (in_array($v,$arValues)) return $v;
        return 'NO'; //default value
    }

    public function hasMatchToken() { return $this->preLoadedFields["MATCH_TOKEN"]["VALID"]; }
    public function getMatchToken() {
        if ($this->preLoadedFields["MATCH_TOKEN"]["VALUE"] !== NULL) return $this->preLoadedFields["MATCH_TOKEN"]["VALUE"];
        $v = $this->getVar("MATCH_TOKEN");
        if ($v != "") return $v;
        return ''; //default value
    }

    public function hasSpecHome() { return $this->preLoadedFields["SPEC_HOME"]["VALID"]; }
    public function getSpecHome() {
        if ($this->preLoadedFields["SPEC_HOME"]["VALUE"] !== NULL) return $this->preLoadedFields["SPEC_HOME"]["VALUE"];
        $v = strtoupper($this->getVar("SPEC_HOME"));
        $arValues = array("YES","NO");
        if (in_array($v,$arValues)) return $v;
        return 'NO'; //default value
    }

    public function hasVAStatus() { return $this->preLoadedFields["VA_STATUS"]["VALID"]; }
    public function getVAStatus() {
        if ($this->preLoadedFields["VA_STATUS"]["VALUE"] !== NULL) return $this->preLoadedFields["VA_STATUS"]["VALUE"];
        $v = strtoupper($this->getVar("VA_STATUS"));
        $arValues = array("YES","NO");
        if (in_array($v,$arValues)) return $v;
        return '';
    }

    public function hasZip() { return $this->preLoadedFields["ZIP"]["VALID"]; }
    public function getZip() {
        if ($this->preLoadedFields["ZIP"]["VALUE"] !== NULL) return $this->preLoadedFields["ZIP"]["VALUE"];
        return preg_replace("/[^0-9]/", "", $this->getVar("ZIP"));
    }

    public function hasEstVal() { return $this->preLoadedFields["EST_VAL"]["VALID"]; }
    public function getEstVal() {
        if ($this->preLoadedFields["EST_VAL"]["VALUE"] !== NULL) return $this->preLoadedFields["EST_VAL"]["VALUE"];
        $v = preg_replace("/[^0-9]/", "", $this->getVar("EST_VAL"));
        if (is_numeric($v)) {
            $v = intval($v);
            if (($v > 0) || ($v < 2000000)) {
                return $v;
            }
        }
        return 0;
    }

    public function hasLoanVal() { return $this->preLoadedFields["LOAN_VAL"]["VALID"]; }
    public function getLoanVal() {
        if ($this->preLoadedFields["LOAN_VAL"]["VALUE"] !== NULL) return $this->preLoadedFields["LOAN_VAL"]["VALUE"];
        $v = preg_replace("/[^0-9]/", "", $this->getVar("LOAN_VAL"));
        if (is_numeric($v)) {
            $v = intval($v);
            if (($v > 0) || ($v < 2000000)) {
                return $v;
            }
        }
        return 0;
    }

    public function hasCurrRate() { return $this->preLoadedFields["CURR_RATE"]["VALID"]; }
    public function getCurrRate() {
        if ($this->preLoadedFields["CURR_RATE"]["VALUE"] !== NULL) return $this->preLoadedFields["CURR_RATE"]["VALUE"];
        $v = preg_replace("/[^0-9.]/", "", $this->getVar("CURR_RATE"));
        if (is_numeric($v)) {
            $v = floatval($v);
            if (($v > 0.0) || ($v <= 10.0)) {
                return floatval(number_format($v,"3"));
            }
        }
        return 4.0; //DEFAULT VALUE
    }
}

?>