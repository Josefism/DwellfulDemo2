//page load
var validator = null;

$(document).ready(function() {

    AddHandlers();
    SetModernizr();

    validator = $('#frmMain').validate({
      validClass: "success",
      rules: {
        PRODUCT: {
            required: true
        }
      },
   });

   raiseGAEvent('Step 0 - PRODUCT');

});


//set up event handlers
function AddHandlers()
{
    $('#hppurch').click(function() {
        $('#PRODUCT').val('PP_NEWHOME');
        $('#ltprod').val('Purchase');
        openForm();
        setTimeout('continue_form(true);',250);
    });
    $('#hprefi').click(function() {
        $('#PRODUCT').val('PP_REFI');
        $('#ltprod').val('Refinance');
        openForm();
        setTimeout('continue_form(true);',250);
    });

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

function openForm() {
   var sFldsToSend = GetActiveFieldList();
   var flds = $(sFldsToSend).serialize();
   var fldsnoBlanks = flds.replace(/[^&]+=\.?(?:&|$)/g, '') + '&CAPTURE_TIME='+$('#CAPTURE_TIME').val();
   window.open('form.php?'+fldsnoBlanks);
}

//only get the fields the user has already seen/processed
function GetActiveFieldList()
{
    //initialize with first step and hard-coded fields
    var arActiveFlds = new Array('PRODUCT','CRED_GRADE','PPCID','GCLID','CID','MID','TID');
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

var bSubmitFlag = false;
function continue_form(bFromButton)
{
   var formValid = validator.element($('#PRODUCT'));
   if (!formValid) return false; //if the form isn't valid then halt
   if (bFromButton) {
      if (!bSubmitFlag) {
          bSubmitFlag = true; //so we don't double submit
          $('#frmLT').submit(); //submit the form
      }
      return false;
   } else {
      return true;  //from the submit handler, so let the form submit
   }
}

function disableEnterKey(e)
{
    try
    {
        var key;
        if(window.event){key=window.event.keyCode;}
        else{key=e.which;}
        //if(!isLastStep()&&key==13) {continue_form(false);}
        if (key==13) continue_form(false);
        return (key!=13);
    }
    catch(err)
    {}
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


