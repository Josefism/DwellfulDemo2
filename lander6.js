//page load
var validator = null;
var didLT = false;
var bShowAgentQuestion = false;
var agentMatchToken = "";

$(document).ready(function() {
    if (window.self !== window.top) { $(document.body).html(''); } //check for iframed

    AddHandlers();
    SetModernizr();

    jQuery.validator.addMethod("integer", function(value, element, param) {
        return this.optional(element) || ((value > 0) && (value == parseInt(value, 10)));
    }, "Please enter a valid integer.");

    jQuery.validator.addMethod("usphone", function(phone, element) {
      sph = phone.replace( /[^\d]/g, '' );
      bPhone = (sph.length == 10);
    	return this.optional(element) || bPhone;
    }, "Please specify a valid phone number");

    jQuery.validator.addMethod("postalcode", function(postalcode, element) {
    	return this.optional(element) || postalcode.match(/(^\d{5}(-\d{4})?$)|(^[ABCEGHJKLMNPRSTVXYabceghjklmnpstvxy]{1}\d{1}[A-Za-z]{1} ?\d{1}[A-Za-z]{1}\d{1})$/);
    }, "Please specify a valid postal/zip code");

    jQuery.validator.addMethod("tfa", function(tfacode, element) {
    	return this.optional(element) || tfacode.match(/^\d{6}$/);
    }, "Please specify a valid 6 digit code");

    jQuery.validator.addMethod("goodcredit", function(creditratingvalue, element) {
    	return this.optional(element) || true; //(creditratingvalue != 'POOR');
    }, "Please specify good credit");

    jQuery.validator.addMethod("validname", function(nm, element) {
    	return this.optional(element) || (nameValidate(nm) == 0);
    }, "Please specify a valid name");

    jQuery.validator.addMethod("gooddate", function(dt, element) {
    	return this.optional(element) || dateValidate(dt);
    }, "Please enter a valid date");

    validator = $('#frmMain').validate({
      validClass: "success",
      rules: {
        PRODUCT: {
            required: true
        },
        FNAME: {
            required: true,
            validname: true
        },
        LNAME: {
            required: true,
            validname: true
        },
        PROP_DESC: {
            required: true
        },
        EST_VAL: {
            integer: true,
            required: true,
        },
        SERIOUS: {
            required: true
        },
        DOB: {
            gooddate: true,
            required: true,
        },
        BAL_ONE: {
            integer: true,
            required: true,
        },
        VA_STATUS: {
            required: true
        },
        SELF_EMPLOYED: {
            required: true
        },
        PROP_ZIP: {
            postalcode: true,
            required: true
        },
        ZIP: {
            postalcode: true,
            required: true
        },
        TFA: {
            tfa: true,
            required: true
        },
        PRI_PHONE: {
           usphone: true,
           required: true
        },
        SPEC_HOME: {
            required: true
        },
        AGENT_FOUND: {
            required: true
        },
        ACCEPT_MATCH: {
            required: true
        },
        TIMEFRAME: {
            required: true
        },
        CRED_GRADE: {
            goodcredit: true,
            required: true
        },
        COSTCO_MEMBER: {
            required: true
        }
      },
   });

	$( "#slider" ).slider({
		min: 200000,
		max: 1500000,
		value: 255000,
		step: 10000,
		slide: function( event, ui ) {
         updateSlider(ui.value);
		},
		stop: function( event, ui ) {
		   updateSlider(ui.value);
         updateValueSelects();
         update_down_payment();
		}
	});
	$( "#sliderb" ).slider({
		min: 100000,
		max: 1500000,
		value: 162500,
		step: 10000,
		slide: function( event, ui ) {
         updateSliderb(ui.value);
		},
		stop: function( event, ui ) {
		   updateSliderb(ui.value);
		}
	});
	updateSlider ($( "#slider" ).slider( "value" ));
	updateSliderb($( "#sliderb" ).slider( "value" ));
	updateValueSelects();
	update_down_payment();
	//setLoanTypeDetails();

   raiseGAEvent('Step 0 - PRODUCT');

   showTrustedForm();
});

function dateValidate(dt) {
    // your desired pattern
    var pattern = /(0\d{1}|1[0-2])\/([0-2]\d{1}|3[0-1])\/(19|20)(\d{2})/
    var m = dt.match(pattern);
    if (!m) return false;
    var d = new Date(dt);
    // Now let's ensure that the date is not out of index
    if (d.getMonth()+1 == parseInt(m[1], 10) && d.getDate() ==parseInt(m[2], 10)) {
        return true;
    }
    return false;
}

function nameValidate(s) {
   //trim up the value
   var t = (new String(s)).trim();
   //1. single letter input
   if (t.length <= 1) return 1;
   //2. numbers or symbols
   var r = t.replace(/[^a-z A-Z]+/g,'');
   if (r.length != t.length) return 2;
   //3. excluded words
   var arBadNames=['doe'];
   var v = arBadNames.indexOf(t.toLowerCase());
   if (v != -1) return 3;
   //4. excluded substrings
   var arBadStrings=['test','fake','abc','xyz','junk','qwer','asd','schmoe'];
   for (var i=0; i<arBadStrings.length; i++) {
      var p = t.toLowerCase().indexOf(arBadStrings[i]);
      if (p != -1) return 4;
   }
   //5. more than three consonants together, ie "fff"
   var x = t.replace(/([a-z])\1\1/g,'');
   if (x.length != t.length) return 5;
   //looks good
   return 0;
}

function preLoad(s) {
    var iStep = parseInt(s);
    if (isNaN(iStep)) return;
    if (iStep <= 0) return;

    //first make sure the form knows the type
    setLoanTypeDetails();

    //set the step
    CURR_STEP = iStep;

    //get the step id
    var sStepId = CURR_PATH[CURR_STEP][DIV_CURR];

    //configure the form and set the right things visible
    $('#'+sStepId).show();
    $('#backcontainer').show();
    showHideContinueButton('#'+sStepId);

    if (iStep >= 1) {
        $('#SERIOUS').val('SERIOUS');
    }
    if (iStep >= 2) {
        updateZipInfo($('#PROP_ZIP').val());
    }
    if (iStep >= 3) {
        var sCredBtn = 'credex';
        if ($('#CRED_GRADE').val() == 'VERY GOOD') sCredBtn = 'credvg';
        if ($('#CRED_GRADE').val() == 'GOOD') sCredBtn = 'credgd';
        if ($('#CRED_GRADE').val() == 'POOR') sCredBtn = 'credpr';
        $('#'+sCredBtn).addClass('sel');
    }
    if (iStep >= 4) {
        if ($('#VA_STATUS').val().toUpperCase() == 'YES') {
            $('#vayes').addClass('sel');
        } else {
            $('#vano').addClass('sel');
        }
    }
    if (iStep >= 5) {
        var sv = $('#EST_VAL').attr('data-preload');
        $('#slider').slider("value", sv);
        updateSlider(sv);
        updateValueSelects();
        update_down_payment();
    }
    if (iStep >= 6) {
        //for refi:
        var sv = $('#BAL_ONE').attr('data-preload');
        $('#sliderb').slider("value", sv);
        updateSliderb(sv);
        //for purch:
        var dp = $('#DOWN_PMT').attr('data-preload');
        $('#DOWN_PMT').find('option').remove().end();
        var optiontxt = '$'+toMoneyInt(dp);
        $('#DOWN_PMT').append('<option value="'+dp+'" selected="selected">'+optiontxt+'</option>');
    }
    if (iStep >= 7) {
        //REFI: Rate is already set in the dropdown
        //PURCH: Timeframe
        var tf = $('#TIMEFRAME').attr('data-preload');
        $('#TIMEFRAME').val(tf);
    }
    if (iStep >= 8) {
        //REFI: current home styles/selection already set
        //PURCH: property type
        var pt = $('#PROP_DESC').attr('data-preload');
        $('#PROP_DESC').val(pt);
    }
    if (iStep >= 9) {
        //PURCH: Found a home
        var fh = $('#SPEC_HOME').attr('data-preload');
        $('#SPEC_HOME').val(fh);
        if (fh.toUpperCase() == 'YES') {
            $('#shyes').addClass('sel');
        } else {
            $('#shno').addClass('sel');
        }
    }
    if (iStep >= 10) {
        //PURCH: Working with agent
        var af = $('#AGENT_FOUND').attr('data-preload');
        $('#AGENT_FOUND').val(af);
        if (af.toUpperCase() == 'YES') {
            $('#afyes').addClass('sel');
        } else {
            $('#afno').addClass('sel');
        }
        var am = $('#ACCEPT_MATCH').attr('data-preload');
        $('#ACCEPT_MATCH').val(af);
        if (af.toUpperCase() == 'YES') {
            $('#matchyes').addClass('sel');
        } else {
            $('#matchno').addClass('sel');
        }
        var mt = $('MATCH_TOKEN').attr('data-preload');
        $('#MATCH_TOKEN').val(mt);
    }
    if (iStep >= 11) {
        //REFI: self employed
        if ($('#SELF_EMPLOYED').val().toUpperCase() == 'YES') {
            $('#seyes').addClass('sel');
        } else {
            $('#seno').addClass('sel');
        }
    }
}

//set up event handlers
function AddHandlers()
{
    $('.overlayclose').click(function(event) {
        $('.overlay').hide();
        event.preventDefault();
    });
    $( "#CostcoExternalLink" ).dialog({
      	autoOpen: false,
      	width: 600,
      	modal: true,
      	buttons: [
      		{
      			text: "Cancel",
      			class: "cancelbtn",
      			click: function() {
      				$( this ).dialog( "close" );
      			}
      		},
      		{
      			text: "Visit External Link",
      			class: "visitbtn",
      			click: function() {
      				launchCostcoForm();
      			}
      		}
      	]
      });
    $('#cmyes').click(function(event) {
        $('#COSTCO_MEMBER').val('Yes');
        toggleSelectedBtn(['cmyes','cmno'],'cmyes');
      	$( "#CostcoExternalLink" ).dialog( "open" );
      	event.preventDefault();
    });
    $('#cmno').click(function() {
        $('#COSTCO_MEMBER').val('No');
        toggleSelectedBtn(['cmyes','cmno'],'cmno');
        continue_form(true);
    });
    $('#btnlooking').click(function() {
        $('#SERIOUS').val('LOOKING');
        toggleSelectedBtn(['btnlooking','btnserious'],'btnlooking');
        continue_form(true);
    });
    $('#btnserious').click(function() {
        $('#SERIOUS').val('SERIOUS');
        toggleSelectedBtn(['btnlooking','btnserious'],'btnlooking');
        continue_form(true);
    });
    $('#hppurch').click(function() {
        $('#PRODUCT').val('PP_NEWHOME');
        $('#ltprod').val('Purchase');
        setLoanTypeDetails();
        toggleSelectedBtn(['hprefi','hppurch'],'hppurch');
        continue_form(true);
    });
    $('#hprefi').click(function() {
        $('#PRODUCT').val('PP_REFI');
        $('#ltprod').val('Refinance');
        setLoanTypeDetails();
        toggleSelectedBtn(['hprefi','hppurch'],'hprefi');
        continue_form(true);
    });
    $('#credex').click(function() {
        $('#CRED_GRADE').val('EXCELLENT');
        toggleSelectedBtn(['credex','credgd','credvg','credpr'],'credex');
        continue_form(true);
    });
    $('#credgd').click(function() {
        $('#CRED_GRADE').val('GOOD');
        toggleSelectedBtn(['credex','credgd','credvg','credpr'],'credgd');
        continue_form(true);
    });
    $('#credvg').click(function() {
        $('#CRED_GRADE').val('VERY GOOD');
        toggleSelectedBtn(['credex','credgd','credvg','credpr'],'credvg');
        continue_form(true);
    });
    $('#credpr').click(function() {
        $('#CRED_GRADE').val('POOR');
        toggleSelectedBtn(['credex','credgd','credvg','credpr'],'credpr');
          var callback = function() {
            window.location='../LeadpointRates/rates.php?CRED_GRADE=POOR&PRODUCT='+encodeURIComponent($("#PRODUCT").val())+'&PROP_ST='+encodeURIComponent($("#PROP_ST").val())+'&PPCID='+encodeURIComponent($("#PPCID").val())+'&CID='+encodeURIComponent($("#CID").val())+'&GCLID='+encodeURIComponent($("#GCLID").val());
          };
          gtag('event', 'User sent to rate table (credit)', {
            'event_category' : 'lander: RZ/fly2hp2p',
            'event_label' : 'PPCID '+encodeURIComponent($("#PPCID").val()),
            'event_callback' : callback
          });
        return;
    });
    $('#pdsingle').click(function() {
        $('#PROP_DESC').val('single_fam');
        toggleSelectedBtn(['pdsingle','pdcondo','pdmulti','pdmobile'],'pdsingle');
        continue_form(true);
    });
    $('#pdcondo').click(function() {
        $('#PROP_DESC').val('condo');
        toggleSelectedBtn(['pdsingle','pdcondo','pdmulti','pdmobile'],'pdcondo');
        continue_form(true);
    });
    $('#pdmulti').click(function() {
        $('#PROP_DESC').val('multi_fam');
        toggleSelectedBtn(['pdsingle','pdcondo','pdmulti','pdmobile'],'pdmulti');
          var callback = function() {
           if (!didLT) {
                didLT = true;
                addLTFields(); $("#frmLT").attr('action',(ism() ? '../LeadpointRates/rates-mobile.php' : '../LeadpointRates/rates.php'));
                $("#frmLT").submit();
            }
          };
          gtag('event', 'User sent to LT (multi_fam)', {
            'event_category' : 'lander: RZ/fly2hp2p',
            'event_label' : 'PPCID '+encodeURIComponent($("#PPCID").val()),
            'event_callback' : callback
          });
          return;
    });
    $('#pdmobile').click(function() {
        $('#PROP_DESC').val('mobilehome');
        toggleSelectedBtn(['pdsingle','pdcondo','pdmulti','pdmobile'],'pdmobile');
        continue_form(true);
    });
    $('#vano').click(function() {
        $('#VA_STATUS').val('no');
        toggleSelectedBtn(['vano','vayes','vahaveloan'],'vano');
        $('#vaBenTxt').hide();
        continue_form(true);
    });
    $('#vayes').click(function() {
        $('#VA_STATUS').val('yes');
        toggleSelectedBtn(['vano','vayes','vahaveloan'],'vayes');
        $('#vaBenTxt').show();
        continue_form(true);
    });
    $('#vahaveloan').click(function() {
        $('#VA_STATUS').val('yes');
        toggleSelectedBtn(['vano','vayes','vahaveloan'],'vahaveloan');
        $('#vaBenTxt').show();
        continue_form(true);
    });
    $('#afno').click(function() {
        $('#AGENT_FOUND').val('no');
        toggleSelectedBtn(['afno','afyes'],'afno');
        queryDwellful("request");
        setTimeout(function() {
            if (!$('#matchresult').hasClass('ready')) {
                continue_form(true);
            }    
        }, 1000);
    });
    $('#afyes').click(function() {
        $('#AGENT_FOUND').val('yes');
        toggleSelectedBtn(['afno','afyes'],'afyes');
        continue_form(true);
    });
    $('#matchyes').click(function() {
        $('#ACCEPT_MATCH').val('yes');
        toggleSelectedBtn(['matchno','matchyes'],'matchyes');        
        continue_form(true);
    });
    $('#matchno').click(function() {
        $('#ACCEPT_MATCH').val('no');
        toggleSelectedBtn(['matchno','matchyes'],'matchno');
        continue_form(true);
    });
    $('#seno').click(function() {
        $('#SELF_EMPLOYED').val('no');
        toggleSelectedBtn(['seno','seyes'],'seno');
        continue_form(true);
    });
    $('#seyes').click(function() {
        $('#SELF_EMPLOYED').val('yes');
        toggleSelectedBtn(['seno','seyes'],'seyes');
        continue_form(true);
    });
    $('#shno').click(function() {
        $('#SPEC_HOME').val('no');
        toggleSelectedBtn(['shno','shyes'],'shno');
        bShowAgentQuestion = true;
        continue_form(true);
    });
    $('#shyes').click(function() {
        $('#SPEC_HOME').val('yes');
        toggleSelectedBtn(['shno','shyes'],'shyes');
        bShowAgentQuestion = false;
        continue_form(true);
    });
    $('#DOWN_PMT').change(function() {
        continue_form(true);
    });
    $('#PRI_PHONE').blur(function() {
      FormatPhone();
      UpdateElementStatus($(this).attr('id'));
    });
    $('#ADDRESS, #CITY, #STATE, #ZIP, #EMAIL').blur(function() {
        UpdateElementStatus($(this).attr('id'));
    });
    $('#FNAME, #LNAME').blur(function() {
        UpdateElementStatus($(this).attr('id'));
        checkNames();
    });
    $('#DOBMM, #DOBDD, #DOBYYYY').change(function() {
      $('#DOB').val(formatBirthday());
      UpdateElementStatus('DOB');
    });
    $('#finishbtn').click(function() {
        if ($('#ACCEPT_MATCH').val() == "yes") {
            queryDwellful("accept");
        }
        setTimeout(function() {
            continue_form(true);
            return false;   
        }, 1000);
    });
}

function queryDwellful(method) {
        // Translate property description into allowable strings, default to "Other"
        // TODO: Current page offers Condo/Townhome as single choice, but API accepts "Condo" or "Townhome", using Condo for now
        var rawPropType = $('#PROP_DESC').val();
        var propType = "Other";
        if (rawPropType == "single_fam") { propType = "Single-Family"; }
        if (rawPropType == "condo") { propType = "Condominium"; }
        
        // Translate timeframe into allowable strings, default to "Undecided"
        // TODO: Assuming "Offer pending" & "Signed Purch Agreement" will never be selected if Agent = No
        // TODO: Current page has no option for "More than 12 months", but API accepts that as well as "Undecided", using Undecided for now
        var rawTimeline = $('#TIMEFRAME').val();
        var timeline = "Undecided";
        if (rawTimeline == "TP1" || rawTimeline == "TP3") { timeline = "ASAP"; }
        if (rawTimeline == "TP2") { timeline = "Within%203-6%20Months"; }
        if (rawTimeline == "TP4") { timeline = "Within%206-12%20Months"; }

        // Translate credit grade to correct space char to %20
        var rawCredit = $('#CRED_GRADE').val();
        var credit = (rawCredit == "VERY GOOD") ? "VERY%20GOOD" : rawCredit;

        // Use the IP address and ZIP to create an internal ref ID for matches from the current user
        var rawIP = $('#IP_ADDRESS').val();
        var rawZIP = $('#PROP_ZIP').val();
        var refId = rawIP.replace( /[^\d]/g, '' ) + "_" + rawZIP;

        // Default to Request method
        var reqTarget = "callDwellfulQuery.php";

        // Build the request data serialized string for Request method
        var reqData = "purchasePriceField="+$('#EST_VAL').val();
        reqData += "&hasAgentField=false&propertyLocationField="+$('#PROP_ZIP').val();
        reqData += "&propertyTypeField="+propType;
        reqData += "&timelineField="+timeline;
        reqData += "&refIdField="+refId;

        // Add additional parameters needed for Accept method, and change target url
        if (method == "accept") {
            reqTarget = "callDwellfulAccept.php";
            reqData += "&fnameField="+$('#FNAME').val();
            reqData += "&lnameField="+$('#LNAME').val();
            reqData += "&emailField="+$('#EMAIL').val();
            reqData += "&phoneField="+$('#PRI_PHONE').val();
            reqData += "&matchTokenField="+$('#MATCH_TOKEN').val();
            reqData += "&downPaymentField="+$('#DOWN_PMT').val();
            reqData += "&creditField="+credit;
        }

        $.ajax({
            url: reqTarget,
            method: "POST",
            data: reqData,
            dataType: "json",
            contentType: "application/x-www-form-urlencoded",
            success: function(res) {
                var response = res;
                console.log(response);
                if (response["message"].toLowerCase() == "qualified") {
                    var newspan = $( "<span>" + response["broker"] + "</span>" );
                    $("#brokername").append(newspan);
                    $("#MATCH_TOKEN").val(response["match_token"]);
                    $("#matchresult").removeClass("not_ready");
                    $("#matchresult").addClass("ready");    
                }
                if (method == "accept") {
                    var acceptResult = (response["success"] == true) ? "Successfully Accepted Match. " : "Match Acceptance Failed. Error: ";
                    acceptResult += response["message"];
                    console.log(acceptResult);
                }
            },
            error: function(err) {
                console.log(err);
            }
        });
}

function toggleSelectedBtn(arOffs, sOn) {
   for (var i=0; i<arOffs.length; i++) {
      $('#'+arOffs[i]).removeClass('sel');
   }
   $('#'+sOn).addClass('sel');
}

function launchCostcoForm() {
   COSTCO_REDIRECT = true; // so we don't get leave page error
   $('#frmMain').attr('target','');
   $('#frmMain').attr('action','https://costcohomefinance.com/getratesp20/');
   $('#frmMain').attr('onsubmit','');
   //$('#frmMain').submit();
   var callback = function() {
       $('#frmMain').submit();
   };
   gtag('event', 'User sent to CHF', {
    'event_category' : 'lander: RZ/fly2hp2p',
    'event_label' : 'PPCID '+encodeURIComponent($("#PPCID").val()),
    'event_callback' : callback
   });
}

function FormatPhone() {
   var sph = $('#PRI_PHONE').val();
   sph = sph.replace( /[^\d]/g, '' );
   sph = sph.replace( /(\d{3})(\d{3})(\d{4})/, '($1) $2-$3' );
   $('#PRI_PHONE').val(sph);
}

function formatBirthday() {
  // formats in mm/dd/yyyy
  return $('#DOBMM').val()+'/'+$('#DOBDD').val()+'/'+$('#DOBYYYY').val();
}

function SetModernizr() {
    if(!Modernizr.input.placeholder){

    	$('[placeholder]').focus(function() {
    	  var input = $(this);
    	  if (input.val() == input.attr('placeholder')) {
    		input.val('');
    		input.removeClass('placeholder');
    	  }
    	}).blur(function() {
    	  var input = $(this);
    	  if (input.val() == '' || input.val() == input.attr('placeholder')) {
    		input.addClass('placeholder');
    		input.val(input.attr('placeholder'));
    	  }
    	}).blur();
    	$('[placeholder]').parents('form').submit(function() {
    	  $(this).find('[placeholder]').each(function() {
    		var input = $(this);
    		if (input.val() == input.attr('placeholder')) {
    		  input.val('');
    		}
    	  })
    	});
    }
}

function UpdateElementStatus(formElemID)
{
   var sVal = $('#'+formElemID).val();
   //validate if we have a length
   if (sVal.length > 0) {
       //validate the item so it will show error message, if needed
       if (validator == null) return;
       return validator.element($('#'+formElemID));
   }
}

function submitForm()
{
    return tfa_submitForm(false);
}

var DIV_CURR = 0;
var DIV_NEXT = 1;
var STEP_FLD  = 2;
var ZIP_STEP  = 'rq1a';
var PHONE_STEP  = 'rq9';
var COSTCO_STEP = 'rqc';
var NAME_STEP = 'rq8';
var RULE_CHECK_STEP = 'rq1';
var LV_CHECK_STEPS = ['rq5', 'nh1'];
var CONNECTING_STEP = 'rq11';
var PRE_AUTH_STEP = 'rq3';
var TIMEFRAME_STEP = 'nh9';
var AGENT_FOUND_STEP = 'rqhp3';
var SPEC_HOME_STEP = 'nh3';

var BUTTON_STEPS = ['rq1a','rq4','rq5','nh1','nh9','nh3','rqhp3','rq8', 'rq10', 'rqcr', 'rqhp4', 'rqhp5', 'rqhp6','rqhp7','rqhp9'];
function showHideContinueButton( stepId ) {
    var sId = stepId.replace('#','');
    if (BUTTON_STEPS.includes(sId)) {
        $('#maincontinuebtn').show();
    } else {
        $('#maincontinuebtn').hide();
    }
}


var REFI_FORM_PATH = [
    ['rq0','rq1a',['PRODUCT']],
    ['rq1a','rq1',['PROP_ZIP']],
    ['rq1','rq6',['CRED_GRADE']],
    //['rqc','rq4',['COSTCO_MEMBER']],
    ['rq6','rq4',['VA_STATUS']],
    ['rq4','rq5',['EST_VAL']],
    ['rq5','rqhp9',['BAL_ONE']],
    ['rqhp9','rqcr',['CURR_LOAN_TYPE']],
    ['rqcr','rq3',['CURR_RATE']],
    ['rq3','rq11',['PROP_DESC']],
    ['rq11','rq8',['SELF_EMPLOYED']],
    ['rq8','rq9',['FNAME','LNAME']],
    //['rq10','rq9',['ADDRESS','CITY','STATE','ZIP']],
    ['rq9','rq99',['PRI_PHONE','EMAIL']]
];
var REFI_HP_FORM_PATH = [
    ['rq0','rq1a',['PRODUCT']],
    ['rq1a','rq1',['PROP_ZIP']],
    ['rq1','rq6',['CRED_GRADE']],
    //['rqc','rq4',['COSTCO_MEMBER']],
    ['rq6','rq4',['VA_STATUS']],
    ['rq4','rq5',['EST_VAL']],
    ['rq5','rqhp9',['BAL_ONE']],
    ['rqhp9','rqcr',['CURR_LOAN_TYPE']],
    ['rqcr','rq3',['CURR_RATE']],
    ['rq3','rqhp4',['PROP_DESC']],

    //HP STEPS HERE....
    //['rqhp5','rqhp4',['PROP_USE']],
    ['rqhp4','rqhp6',['MonthlyPayment']],
    ['rqhp6','rq11',['BANK_FORC']],

    ['rq11','rq8',['SELF_EMPLOYED']],
    ['rq8','rqhp7',['FNAME','LNAME']],

    ['rqhp7','rq10',['DOB']],
    ['rq10','rq9',['ADDRESS','CITY','STATE','ZIP']],

    ['rq9','rq99',['PRI_PHONE','EMAIL']]
];

var NEWHOME_FORM_PATH = [
    ['rq0','rq1a',['PRODUCT']],
    ['rq1a','rq1',['PROP_ZIP']],
    ['rq1','rq6',['CRED_GRADE']],
    //['rqc','nh3',['COSTCO_MEMBER']],
    //['nh3','rq6',['SPEC_HOME']],
    ['rq6','rq4',['VA_STATUS']],
    ['rq4','nh1',['EST_VAL']],
    ['nh1','nh9',['DOWN_PMT']],
    ['nh9','rq3',['TIMEFRAME']],
    ['rq3','nh3',['PROP_DESC']],
    ['nh3','rqhp3',['SPEC_HOME']],
    ['rqhp3','rq11',['AGENT_FOUND']],
    ['rq11','rq8',['SELF_EMPLOYED']],
    ['rq8','rq9',['FNAME','LNAME']],
    //['rq10','rq9',['ADDRESS','CITY','STATE','ZIP']],
    ['rq9','rq99',['PRI_PHONE','EMAIL']]
];
var NEWHOME_HP_FORM_PATH = [
    ['rq0','rq1a',['PRODUCT']],
    ['rq1a','rq1',['PROP_ZIP']],
    ['rq1','rq6',['CRED_GRADE']],
    //['rqc','nh3',['COSTCO_MEMBER']],
    ['rq6','rq4',['VA_STATUS']],
    ['rq4','nh1',['EST_VAL']],
    ['nh1','nh9',['DOWN_PMT']],
    ['nh9','rq3',['TIMEFRAME']],
    ['rq3','nh3',['PROP_DESC']],

    //HP STEPS HERE....
    ['nh3','rqhp6',['SPEC_HOME']],
    //['rqhp5','rqhp3',['PROP_USE']],
    //['rqhp3','rqhp6',['AGENT_FOUND']],
    ['rqhp6','rq11',['BANK_FORC']],

    ['rq11','rq8',['SELF_EMPLOYED']],
    ['rq8','rqhp7',['FNAME','LNAME']],

    ['rqhp7','rq10',['DOB']],
    ['rq10','rq9',['ADDRESS','CITY','STATE','ZIP']],

    ['rq9','rq99',['PRI_PHONE','EMAIL']]
];


var CURR_PATH = REFI_FORM_PATH;
var CURR_STEP = -1; //first step in the array

//redirect API information
var REDIRECT_ACTIVE = false; //this is the flag that tells us whether or not we need to redirect
var REDIRECT_URL = ''; //the url we need to redirect to

function setLoanTypeDetails()
{
    var bIsNewHome = isNewHome();
    CURR_PATH = (bIsNewHome ? NEWHOME_FORM_PATH : REFI_FORM_PATH);
    $('#prop_val_text').html( (bIsNewHome ? 'Purchase price of new home:<span>(it\'s ok to estimate)</span>' : 'What is the property value?<span>(it\'s ok to estimate)</span>') );

    if (bIsNewHome) {
      $('#prodescq').removeClass('fbtn').addClass('thbtn');
      $('#pdmobile').hide();
    } else {
      $('#prodescq').removeClass('thbtn').addClass('fbtn');
      $('#pdmobile').show();
    }

    //update home value slider
    var homevalmin = (bIsNewHome ? 200000 : 200000);
    $('#slider').slider( "option", "min",  homevalmin);
    updateSlider ($( "#slider" ).slider( "value" ));

}

var downOptions=[0,3.5,5,10,15,20,25,30,35,40,50,60,70,80,90,100];
function update_down_payment()
{
    try{
        ev = parseInt($('#EST_VAL').val());
        $('#DOWN_PMT').find('option').remove().end();
        var pv=parseInt($('#EST_VAL').val(),10);
        for(var i=0;i<downOptions.length;i++){
            var value=Math.round(pv*(downOptions[i]/100));
            var text="("+downOptions[i]+"%) $"+toMoneyInt(value);
            lv = ev - value;
            if (lv >= 100000) {
                var selAttr = (i == 1) ? ' selected="selected"' : '';
                $('#DOWN_PMT').append('<option value="'+value+'"'+selAttr+'>'+text+'</option>');
            }
        }
    }
    catch(e){}
}


function updateSlider( val ) {
   $('#slVal').html('$'+toMoneyInt(val));
   if (!ism()) {
        //$('#slVal').css('left',$('#slider > .ui-slider-handle').css('left'));
   }
   $('#EST_VAL').val(val);
}
function updateSliderb( val ) {
   $('#slbVal').html('$'+toMoneyInt(val));
   if (!ism()) {
        //$('#slbVal').css('left',$('#sliderb > .ui-slider-handle').css('left'));
   }
   $('#BAL_ONE').val(val);
}

function updateValueSelects()
{
   //update the max to be 90% of the EST_VAL
   var v = 0.9 * parseFloat($('#slider').slider("value"));
   v = ((v < 200000) ? 200000 : v );
   $('#sliderb').slider( "option", "max", v );
   $('#sliderb').slider('option','step',(v < 350000) ? 1000 : (v < 500000) ? 5000 : 10000);
   var midval = ($('#sliderb').slider("option","max") - $('#sliderb').slider("option","min")) / 2;
   $('#sliderb').slider("value", $('#sliderb').slider("option","min")+midval);
   //make sure the bal one slider is set correctly
   updateSliderb( $('#sliderb').slider("value") );
}

function goBack()
{
   $('#rqAmSav').hide();//just in case

   if ($('#rqtfa').is(':visible')) {
        $('#rqtfa').hide();
        var idxLast = CURR_PATH.length-1;
        var prevDivId = '#'+CURR_PATH[idxLast][DIV_CURR];
        $(prevDivId).fadeIn();
        window.scrollTo(0,0);
        dismissKeypad();
        return;
   }
   var currDivId = '#'+CURR_PATH[CURR_STEP][DIV_CURR];
   if (CURR_STEP >= 1) {
      $(currDivId).hide(); //hide current step
      CURR_STEP -= 2;
      //BEGIN CHECK FOR COSTCO SKIP
      var tmpStepId = CURR_PATH[CURR_STEP+1][DIV_CURR];
      if (bSkipCostco && (tmpStepId == COSTCO_STEP)) {
         CURR_STEP -= 1; //add one more to skip past costco question
      }
      //END CHECK FOR COSTCO SKIP
      //ALSO CHECK FOR AGENT QUESTION SKIP
      if (bShowAgentQuestion == false) {
          CURR_STEP -= 1;
      }
      continue_form(true);
   } else {
      CURR_STEP--;
      $(currDivId).hide(); //hide current step
      toggleLanderPage(true);
   }
}

function isNewHome() {
   return ($('#PRODUCT').val() == 'PP_NEWHOME' ? true : false );
}

var COSTCO_REDIRECT = false;
var bSkipCostco = false;

function skipCostcoQuestion() {
   //all flows get this now
   return false;

   //skip on refi
   if (!isNewHome()) return true;

   //skip on poor only
   var cred = $('#CRED_GRADE').val();
   if (cred == 'POOR') return true;

   return false; // keep flow going
}


function resetConnection() {
    $('#rq11w').hide();
    $('.srchTxt').hide();
    $('.chkimg').hide();
    $('#search2').removeClass('conok');
    $('#search3').removeClass('conokg');
}
function showConnection(nextDivId,iStep) {
    if (iStep == 4) {
        resetConnection();
        $('#backbtn').show();
        showHideContinueButton(nextDivId);
        $(nextDivId).fadeIn(); //show next step
        window.scrollTo(0,0);
        dismissKeypad();
        return;
    }
    if (iStep == 0) {
        $('#backbtn').hide();
        $('#rq11w').show();
        $('#search1').fadeIn(1000);
        setTimeout('showConnection("'+nextDivId+'",1);',1500);
        return;
    }
    if (iStep == 1) {
        $('#si1').fadeIn(300);
        $('#search2').fadeIn(1000);
        setTimeout('showConnection("'+nextDivId+'",2);',2000);
        return;
    }
    if (iStep == 2) {
        $('#search2').addClass('conok');
        $('#si2').fadeIn(300);
        setTimeout('showConnection("'+nextDivId+'",3);',2000);
        return;
    }
    if (iStep == 3) {
        $('#search3').fadeIn(200).addClass('conokg');
        $('#si3').fadeIn(200);
        setTimeout('showConnection("'+nextDivId+'",4);',2000);
        return;
    }
}


function checkNames() {
    $('#errNameMatch').hide();
    if($('#FNAME').val().length > 0) {
        if ($('#FNAME').val().toLowerCase() == $('#LNAME').val().toLowerCase()) {
            $('#errNameMatch').show();
            return false;
        }
    }
    return true;
}

var PREAUTH_COMPLETE = false;

function lead_preAuth() {
    var bIsNewHome = isNewHome();
    CURR_PATH = (bIsNewHome ? NEWHOME_FORM_PATH : REFI_FORM_PATH);
    $('#LTHP').val('0');
    PREAUTH_COMPLETE = true;
    continue_form(true);
    /*
    var sUrl = '/rate-quotes/common/peklava-preauth.php';
    $.post( sUrl, $( "#frmMain" ).serialize(), function( data ) {
        var bIsHP      = (data.status == 'FILTER');
        $('#LTHPSCORE').val(data.score);
        var bIsNewHome = isNewHome();
        if (bIsHP) {
            CURR_PATH = (bIsNewHome ? NEWHOME_HP_FORM_PATH : REFI_HP_FORM_PATH);
            $('#LTHP').val('1');
            $('#propusehdr').text(bIsNewHome ? 'How will this property be used?' : 'How is this property used?');
        } else {
            CURR_PATH = (bIsNewHome ? NEWHOME_FORM_PATH : REFI_FORM_PATH);
            $('#LTHP').val('0');
        }
        PREAUTH_COMPLETE = true;
        continue_form(true);
    });
    */
}


function tfa_readySubmit() {
    $('#tfaErr').hide();
    $('#tfaConfirmBtn').hide();
    $('#tfaFinishMsg').show();
    $('#tfaFinishBtn').fadeIn();
}

function checkTFA() {
    if (validator.element($('#TFA'))) {
       $('#TFA').css({
    			'background-image': 'url(images/ajax-loading.gif)',
    			'background-repeat': 'no-repeat',
    			'background-position': '94% center'
    	});
    	var stfaId = $('#TFAID').val();
    	var stfaCd = $('#TFA').val();
        var sUrl = '/rate-quotes/common/tfa-auth.php?m=2&c='+stfaCd+'&i='+stfaId+'&callback=?';
        $.getJSON(sUrl, function(data) {
            $('#TFA').css({ 'background-image': 'none' });
            var sErr = new String(data.error);
            if (sErr.length == 0) {
                tfa_readySubmit();
            } else {
                if (sErr == 'invalid') {
                    $('#tfaErr').html('The entered code is invalid. <a href="#" onclick="tfa_resend(); return false;">Resend?</a>');
                    $('#tfaErr').fadeIn();
                } else {
                    $('#tfaErr').html('An unknown error has occurred.');
                    $('#tfaErr').fadeIn();
                }
            }
        });
    }
}

function tfa_resend() {
    var stfaId = $('#TFAID').val();
    var sPH   = $('#PRI_PHONE').val();
    var sUrl = '/rate-quotes/common/tfa-auth.php?m=1&p='+sPH+'&i='+stfaId+'&callback=?';
    $('#tfaErr').hide();
    $.getJSON(sUrl, function(data) {
        var sErr = new String(data.error);
        if (sErr.length != 0) {
            $('#tfaErr').html('An error has occurred while sending you a text. <a href="#" onclick="goBack(); return false;">Use a different number?</a>');
            $('#tfaErr').fadeIn();
            $('#tfaConfirmBtn').hide();
        }
    });
}

function launchTFA() {
    var currDivId = '#'+CURR_PATH[CURR_STEP][DIV_CURR];
    $(currDivId).hide(); //hide current step
    $('#tfaErr').hide();
    $('#tfaConfirmBtn').show();
    $('#rqtfa').fadeIn();
    window.scrollTo(0,0);
    dismissKeypad();
    tfa_resend();
}

function tfa_submitForm(bFromButton) {
    if (bFromButton) {
        if (!bSubmitFlag) {
            bSubmitFlag = true; //so we don't double submit
            $('#frmMain').submit(); //submit the LP form
        }
        return false;
    } else {
        //setTimeout('showUpsells();',200);
        return true;  //from the submit handler, so let the form submit
    }
}

var bSubmitFlag = false;

function continue_form(bFromButton)
{
    if (isLastStep())
    {
        var formValid = validator.form();
        if (!formValid) return false; //if the form isn't valid then halt
        //launchTFA();
        //toggle the spinner
        $('#finishbtn').hide();
        $('#finishspinner').show();
        tfa_submitForm(bFromButton);
        return false;
    }

    //check for transition from first page
    if (CURR_STEP == -1) {
        //need to validate product field, if not valid return false;
        if (!validator.element($('#SERIOUS')))
        {
            return false;
        }
        toggleLanderPage(false);
        CURR_STEP = 0; //set to first step of refi or new home
        $('#'+CURR_PATH[CURR_STEP][DIV_CURR]).show();
        showHideContinueButton(CURR_PATH[CURR_STEP][DIV_CURR]);
        window.scrollTo(0,0);
        dismissKeypad();
        //$('#'+CURR_PATH[CURR_STEP][STEP_FLD][0]).focus();
        raiseGAEvent('Step '+(CURR_STEP+1)+' - '+CURR_PATH[CURR_STEP][STEP_FLD][0]);
        return false;
    }

    //get our step information
    var currDivId = '#'+CURR_PATH[CURR_STEP][DIV_CURR];
    var nextDivId = '#'+CURR_PATH[CURR_STEP][DIV_NEXT];
    var stepFlds  = CURR_PATH[CURR_STEP][STEP_FLD];

    var iBadFld = 0; //which field was bad, if any
    var bFldsValid = true;

    //validate the fields on this step
    for (var i=0; i<stepFlds.length; i++)
    {
      if (!validator.element($('#'+stepFlds[i])))
      {
            iBadFld = i; //a bad field index
            bFldsValid = false;
      }
    }

    //if this step field is valid
    if (bFldsValid) {

        if (CURR_PATH[CURR_STEP][DIV_NEXT] == PRE_AUTH_STEP) {
               PREAUTH_COMPLETE = false;
        }

        if (CURR_PATH[CURR_STEP][DIV_CURR] == PRE_AUTH_STEP) {
            if (!PREAUTH_COMPLETE) {
                lead_preAuth();
                return;
            }
        }

        if (CURR_PATH[CURR_STEP][DIV_CURR] == TIMEFRAME_STEP) {
            var tfv = $('#TIMEFRAME').val();
            var bHasAgent = ((tfv == 'CS1') || (tfv == 'CS5'));
            $('#AGENT_FOUND').val(bHasAgent ? 'yes' : 'no');
        }

        if (CURR_PATH[CURR_STEP][DIV_CURR] == SPEC_HOME_STEP) {
            if (bShowAgentQuestion == false) {
                // Skip the agent question if needed
                CURR_STEP++;
                nextDivId = '#'+CURR_PATH[CURR_STEP][DIV_NEXT];
            }
        }

        if (CURR_PATH[CURR_STEP][DIV_CURR] == AGENT_FOUND_STEP) {

        }

        if (CURR_PATH[CURR_STEP][DIV_CURR] == NAME_STEP) {
            if (!checkNames()) {
                return;
            }
            $('#errNameMatch').hide();
        }

        if (CURR_PATH[CURR_STEP][DIV_CURR] == RULE_CHECK_STEP) {
            var sStCd = $('#PROP_ST').val();
            if (checkPeklavaSt(sStCd)) return;
            if (checkProduct(sStCd)) return;
        }
        if (LV_CHECK_STEPS.indexOf(CURR_PATH[CURR_STEP][DIV_CURR]) >= 0) {
            if (checkLTV()) return;
        }

        if (CURR_PATH[CURR_STEP][DIV_NEXT] == COSTCO_STEP) {
            bSkipCostco = skipCostcoQuestion(); //see if we should skip the question
            if (bSkipCostco) {
               //skip past costco question, get next question div
               CURR_STEP++;
               nextDivId = '#'+CURR_PATH[CURR_STEP][DIV_NEXT];
            }
        }

        $(currDivId).hide(); //hide current step
        if (CURR_PATH[CURR_STEP][DIV_CURR] == CONNECTING_STEP) {
            showConnection(nextDivId,0);
        } else {
            showHideContinueButton(nextDivId);
            $(nextDivId).show(); //show next step
        }
        window.scrollTo(0,0);
        dismissKeypad();
        if (CURR_PATH[CURR_STEP][DIV_CURR] == ZIP_STEP) {
            //we validated the zip field so get teh zip info
            updateZipInfo($('#'+stepFlds[0]).val());
        }
        CURR_STEP++; //go to next step
        if (CURR_PATH[CURR_STEP][DIV_CURR] == PHONE_STEP) {
            $('#formbg').removeClass('fixedheight');
        } else {  }
        raiseGAEvent('Step '+(CURR_STEP+1)+' - '+CURR_PATH[CURR_STEP][STEP_FLD][0]);
        var nextFld = '#'+CURR_PATH[CURR_STEP][STEP_FLD][0];
        //$(nextFld).focus(); //focus the next field
        if (isLastStep()) {
            $('#laststeptxt').show();
            //$('#backcontainer').hide();
            $('.mainform').addClass('laststep');
        } else {
            $('.mainform').removeClass('laststep');
        }
    }
    else
    {
        //$('#'+stepFlds[iBadFld]).focus(); //focus the bad field in the set
    }

    return false; //prevents the form from being submitted
}

//turn off keyboard for mobile
function dismissKeypad() {
    document.activeElement.blur();
    $("input").blur();
}

function showUpsells() {
   $('#frmMain').attr("target","");
   if (ism()) {
        $('#frmMain').attr("action","../LeadpointRates/rates-mobile.php");
   } else {
        $('#frmMain').attr("action","../LeadpointRates/rates.php");
   }
   //$('#frmMain').attr("action","../comparison3/");
   $('#frmMain').submit();
}


function showTrustedForm() {
    var field = 'xxTrustedFormCertUrl';
    var provideReferrer = true;
    var tf = document.createElement('script');
    tf.type = 'text/javascript'; tf.async = true;
    tf.src = 'http' + ('https:' == document.location.protocol ? 's' : '') +
    '://api.trustedform.com/trustedform.js?provide_referrer=' + escape(provideReferrer) + '&field=' + escape(field) + '&l='+new Date().getTime()+Math.random();
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(tf, s);
}


function updateZipInfo(sZip)
{
    //add the json callback
    var sUrl = 'http' + ('https:' == document.location.protocol ? 's' : '') + '://www.ratezip.com/rate-quotes/zipinfo/zi.php?zip='+sZip+'&callback=?';
    $.getJSON(sUrl, function(data) {
        var sErr = new String(data.error);
        if (sErr.length == 0) {
            $('#CITY').val(data.city);
            $('#ZIP').val(data.zip); //the second (correction) zip code field
            $('#STATE').val(data.statecode);
            $('#PROP_ST').val(data.statecode);
            $('#currloc').html(data.city + ', ' + data.statecode + ' ' + data.zip);
        }
    });
}

function showAmerisaveOffer() {
    $('#'+CURR_PATH[CURR_STEP][DIV_CURR]).hide();
    CURR_STEP++; //so the back button will work
    $('#rqAmSav').fadeIn();
}

function checkPeklavaSt(st) {
    var sts = isNewHome() ? stlic() : stlic_refi();
    var ret = sts.indexOf(st);
    if (ret < 0) {
          //*
          var callback = function() {
            if (!didLT) {
                didLT = true;
                //addLTFields(); $("#frmLT").attr('action',(ism() ? '../LeadpointRates/rates-mobile.php' : '../LeadpointRates/rates.php'));
                //$('#frmLT').attr('target','_blank').submit();
                //setTimeout('showUpsells();',200);
                $('#frmLT').submit();
            }
          };
          gtag('event', 'User sent to LT (zip)', {
            'event_category' : 'lander: RZ/fly2hp2p',
            'event_label' : 'PPCID '+encodeURIComponent($("#PPCID").val()),
            'event_callback' : callback
          });
          //*/
          //showAmerisaveOffer();
        return true;
    }
    return false;
}

function addLTFields() {
    var sProd = $('#PRODUCT').val();
    var sSer  = $('#SERIOUS').val();
    var sSt   = $('#PROP_ST').val();
    var sCred = $('#CRED_GRADE').val();
    if ($('#DYNLTPROD').length == 1) {
        $('#DYNLTSER').val(sSer);
        $('#DYNLTPROD').val(sProd);
        $('#DYNLTST').val(sSt);
        $('#DYNLTCRED').val(sCred);
    } else {
        $('<input>').attr({ type: 'hidden', name: 'PRODUCT',    value: sProd, id: 'DYNLTPROD'  }).appendTo('#frmLT');
        $('<input>').attr({ type: 'hidden', name: 'SERIOUS',    value: sSer,  id: 'DYNLTSER'   }).appendTo('#frmLT');
        $('<input>').attr({ type: 'hidden', name: 'PROP_ST',    value: sSt,   id: 'DYNLTST'    }).appendTo('#frmLT');
        $('<input>').attr({ type: 'hidden', name: 'CRED_GRADE', value: sCred, id: 'DYNLTCRED'  }).appendTo('#frmLT');
    }
}

function checkProduct(st) {
    return false; //turn off product check for now
    if ((!isNewHome()) && (st != 'CA')) {
      var callback = function() {
        addLTFields(); $("#frmLT").attr('action',(ism() ? '../LeadpointRates/rates-mobile.php' : '../LeadpointRates/rates.php'));
        $("#frmLT").submit();
      };
      gtag('event', 'User sent to LT (refi)', {
        'event_category' : 'lander: RZ/fly2hp2p',
        'event_label' : 'PPCID '+encodeURIComponent($("#PPCID").val()),
        'event_callback' : callback
      });
      return true;
    }
    return false;
}

function checkLTV() {
   var lv  = 0;
   if (isNewHome()) {
      var ev  = parseInt($('#EST_VAL').val().replace(/[\D]/g,''));
      var dp  = parseInt($('#DOWN_PMT').val().replace(/[\D]/g,''));
      lv = ev - dp;
   } else {
      var bal = parseInt($('#BAL_ONE').val().replace(/[\D]/g,''));
      lv = bal;
   }
   if (lv < 80000) {
      if (lv < 90000) {
        //adjust campaign accordingly
        $('input[name="c"]').val('2232');
      }
      var callback = function() {
       if (!didLT) {
            didLT = true;
            addLTFields(); $("#frmLT").attr('action',(ism() ? '../LeadpointRates/rates-mobile.php' : '../LeadpointRates/rates.php'));
            $("#frmLT").submit();
        }
      };
      gtag('event', 'User sent to LT (loan value)', {
        'event_category' : 'lander: RZ/fly2hp2p',
        'event_label' : 'PPCID '+encodeURIComponent($("#PPCID").val()),
        'event_callback' : callback
      });

      return true;
   }
   return false;
}

//is ths the last step?
function isLastStep()
{
   return (CURR_STEP >= (CURR_PATH.length-1));
}
function isFirstStep()
{
    return (CURR_STEP < 0);
}
function isSecondToLastStep()
{
    return (CURR_STEP == (CURR_PATH.length-2));
}

var first_exit_attempt = false;
function SetRedirect() {
     window.onbeforeunload=function(){
       if( !first_exit_attempt && !isFirstStep() && !isLastStep() && !REDIRECT_ACTIVE && !COSTCO_REDIRECT ){
         first_exit_attempt = true;
         var random_number = Math.floor(Math.random() * 30)+12;
         var visitor_location = "your area";
         var today = new Date();
         var curr_date = today.getDate();
         var months =["January","February","March","April","May","June","July","August","September","October","November","December"];
         var curr_month = months[today.getMonth()]; //Months are zero based
         var curr_year = today.getFullYear();
         today = curr_month + " " +curr_date + ", " + curr_year;
         var message = "Want to see real-time rate quotes for "+today+" from lenders without filling out a form?  Just click See Rates and we'll return real-time results immediately.\n\nYou can compare rates from lenders in " + visitor_location + " for free instantly.  Just click See Rates and you will see the rates immediately."
         var sFldsToSend = GetActiveFieldList();
         var flds = $(sFldsToSend).serialize();
         var fldsnoBlanks = flds.replace(/[^&]+=\.?(?:&|$)/g, '') + '&CAPTURE_TIME='+$('#CAPTURE_TIME').val();
         var getFlds = fldsnoBlanks.replace('zip=','ZIP=').replace('cred=','CRED_GRADE=').replace('prop_desc=','PROP_DESC=').replace('loan_type=','PRODUCT=').replace('home_val=','EST_VAL=').replace('m_bal=','BAL_ONE=').replace('m_bal2=','BAL_TWO=').replace('second_mtg=','MTG_TWO=').replace('mil=','VA_STATUS=').replace('email=','EMAIL=');
         confirm(message);
         setTimeout(function() { document.location.href = "../LeadpointRates/rates.php?"+getFlds;}, 75);
         if ( navigator.userAgent.indexOf("Firefox") !=-1 ){
           return confirm( message )
         } else {
          return message;
         }

       }
     }
}


//turn on or off the lander page UI
function toggleLanderPage( isLanderPage ) {
    if (isLanderPage) {
        $('#rqf').show();
        window.scrollTo(0,0);
        dismissKeypad();
        $('#backcontainer').hide();
    }
    else {
        $('#rqf').hide();
        $('#backcontainer').show();
    }
}

function raiseGAEvent(stepdesc) {
   gtag('event', stepdesc, {
    'event_category' : 'lander: RZ/fly2hp2p',
    'event_label' : 'PPCID '+encodeURIComponent($("#PPCID").val())
   });
}


(function($) {
    $.QueryString = (function(a) {
        if (a == "") return {};
        var b = {};
        for (var i = 0; i < a.length; ++i)
        {
            var p=a[i].split('=');
            if (p.length != 2) continue;
            b[p[0]] = decodeURIComponent(p[1].replace(/\+/g, " "));
        }
        return b;
    })(window.location.search.substr(1).split('&'))
})(jQuery);



//only get the fields the user has already seen/processed
function GetActiveFieldList()
{

    //initialize with first step and hard-coded fields
    var arActiveFlds = new Array('PRODUCT','PROP_ZIP','PPCID','CID','MID','TID');

    //now scan through the fields the user has already seen
    for (var j=0; j<=CURR_STEP; j++)
    {
       var fieldlen = (CURR_PATH[j][STEP_FLD]).length;
       for (var k=0; k<=fieldlen; k++)
       {
        arActiveFlds.push(CURR_PATH[j][STEP_FLD][k]);
      }
    }

    var sBaseSelector= "#frmMain :input[value][value!='0']";
    var sResult = new String();
    for (var i=0; i<arActiveFlds.length; i++)
    {
        if (sResult.length > 0) sResult += ",";
        sResult += ",";
        sResult += sBaseSelector+"[name='"+arActiveFlds[i]+"']";
    }
    return sResult;
}

function change_address2() {
    $('#address2_box').show();
    $('#zip_out').hide();
}


/** Makes 10000 look like 10,000, etc. */
function toMoneyInt(num)
{
    str = num + ""
    len = str.length
    s = ""

    for(var i=len,j=0; i>=0; i--,j++)
    {
        s = str.charAt(i) + s;

        if (j==3 && i > 0)
        {
            s = "," + s;
            j = 0;
        }
    }
    return s;
}
