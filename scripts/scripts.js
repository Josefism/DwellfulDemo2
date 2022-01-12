(function( $ ) {
	$(document).ready(function() {

		// Capture each response, hide the current question, and reveal the next question
		
		// Purchase Price
		$("#purchasePrice button").on("click", function(e) {
			e.preventDefault();
			$("#purchasePrice").removeClass("current");
			$("#hasAgent").addClass("current");		
		});
		
		// Has Agent
		$("#hasAgent button").on("click", function(e) {
			e.preventDefault();
			var hasAgentSelection;
			if ($(this).hasClass("affirm")) {
				hasAgentSelection = true;
			}
			if ($(this).hasClass("reject")) {
				hasAgentSelection = false;
			}
			
			setFieldValue("hasAgentField", hasAgentSelection);
			
			// Hide the question and reveal the next
			$(this).parent().parent().removeClass("current");
			$("#propertyLocation").addClass("current");
		});

		// Property Location
		$("#propertyLocation input").on("blur", function(e) {
			e.preventDefault();
			var locationSelection = $("#propertyLocation input").val();
			setFieldValue("propertyLocationField", locationSelection);
		});

		$("#propertyLocation button").on("click", function(e) {
			e.preventDefault();
			
			// Hide the question and reveal the next
			$(this).parent().parent().removeClass("current");
			$("#propertyType").addClass("current");
		});
		
		// Property Type
		$("#propertyType input").on("click", function(e) {
			e.preventDefault();
			var typeSelection = $('input[name="homeType"]:checked').val();
			setFieldValue("propertyTypeField", typeSelection);
			
			// Hide the question and reveal the next
			$(this).parent().parent().removeClass("current");
			$("#purchaseTimeline").addClass("current");
		});
		
		// Purchase Timeline
		$("#purchaseTimeline input").on("click", function(e) {
			e.preventDefault();
			var timelineSelection = $('input[name="timeline"]:checked').val();
			setFieldValue("timelineField", timelineSelection);
			
			// Hide the question and reveal the next
			$(this).parent().parent().removeClass("current");
			$("#wrapupSubmit").addClass("current");
		});
		
		// Handle navigation arrow clicks
		$("#navButtons button").on("click", function(e){
			e.preventDefault();
			var currentStep = $(".current").first();
			var targetStep;
			if ($(this).hasClass("prev") && !currentStep.hasClass("step-1")) {
				targetStep = currentStep.prev();
			} else if ($(this).hasClass("next") && !currentStep.hasClass("step-6")) {
				targetStep = currentStep.next();
			} else {
				return true;
			}
			currentStep.removeClass("current");
			targetStep.addClass("current");		
		});
		
		// Handle purchase price slider changes
		$( "#purchasePriceSlider" ).slider({
		  orientation: "horizontal",
		  range: "min",
		  max: 3000000,
		  value: 100000,
		  step: 10000,
		  slide: setPriceFieldValue,
		  change: setPriceFieldValue
		});
		
		$("#submit").on("click", function(e) {
			e.preventDefault();
			
			var formData = $("#summaryForm").serialize();
			
			$.ajax({
				url: "callApi.php",
				method: "POST",
				data: formData,
				dataType: "json",
				contentType: "application/x-www-form-urlencoded",
				success: function(res) {
					var response = res;
					console.log(response);
					$.each(response, function(index, element) {
						var $newdiv = $( "<div><p><span class='response-label'>" + index.toString() + "</span>: <span class='response-value'>" + element + "</span></p></div>" )
						$("#result").append($newdiv);
					});

					$("#result").removeClass("not-ready");
					$("#result").addClass("ready");
				},
				error: function(err) {
					console.log(err);
				}
			});
		});

	// END DOCUMENT READY
	});


    // Set value of each hidden field as buttons are clicked or selections are made
	function setFieldValue(whichQuestion, whichAnswer) {
		var questionId = "#" + whichQuestion;
		$(questionId).val(whichAnswer);
	}
	
	function setPriceFieldValue() {
		var selectedPrice = $("#purchasePriceSlider").slider("value");
		var priceLabel = $("#purchasePriceValue span");
		setFieldValue("purchasePriceField", selectedPrice);
		priceLabel.text(selectedPrice);
	}
	
})( jQuery );
