var theForm = document.editMD;

if (typeof theForm !== 'undefined') {
	if (theForm.description.value == "") {
		theForm.description.focus();
	} else {
		theForm.destdial.focus();
	}
}

function editMD_onsubmit()
{
	var msgInvalidDescription = _('Please enter a valid Description');
	var msgInvalidDial = _('Please enter a valid Dial string');
	var msgduplicate = _('Misc Destination already exists');

	defaultEmptyOK = false;
	
	if (!isAlphanumeric(theForm.description.value))
		return warnInvalid(theForm.description, msgInvalidDescription);
	if (miscdests.indexOf($("#description").val()) >= 0) {
		return warnInvalid($("#description"),msgduplicate);
	}

	// go thru text and remove the {} bits so we only check the actual dial digits
	var fldText = theForm.destdial.value;
	var chkText = "";

	if ( (fldText.indexOf("{") > -1) && (fldText.indexOf("}") > -1) ) { // has one or more sets of {mod:fc}

		var inbraces = false;
		for (var i=0; i<fldText.length; i++) {
			if ( (fldText.charAt(i) == "{") && (inbraces == false) ) {
				inbraces = true;
			} else if ( (fldText.charAt(i) == "}") && (inbraces == true) ) {
				inbraces = false;
			} else if ( inbraces == false ) {
				chkText += fldText.charAt(i);
			}
		}

		// if there is nothing in chkText but something in fldText
		// then the field must contain a featurecode only, therefore
		// there really is something in thre!
		if ( (chkText == "") && (fldText != "") )
			chkText = "0";

	} else {
		chkText = fldText;
	}
	// now do the check using the chkText var made above
	if (!isDialDigits(chkText))
		return warnInvalid(theForm.destdial, msgInvalidDial);

	return true;
}

//Delete intercept
$( "#delete" ).click(function() {
	if($('[id = iteminuse]').length > 0){
		var result = confirm(_("This destination is in use. Deleting it may cause things to not work properly"));
		if(result == false){
			return false;
		}
	}
});
