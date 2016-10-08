// -----------------------------------------------------------------------

/**
*	Show all form validation errors
*
*	Highlights the inputs on which validation failed
*	using CSS class change; will reset all fields
*	first. Will use an element near the input
*	to show the error message in it.
*
*	Requires Prototype.
*
*	Arguments:
*	- array of validation error objects, each of which contains 
*	  the id of an input (obj_id), and the error string to show (msg)
**/
function ShowValidationErrors(errors) {

	// Hide all old error messages and highlighting
	HideValidationErrors();

	// Show current errors
	for (var i = 0; i < errors.length; i++) {

		// Determine id of errorbox
		if (typeof(errors[i].input_id) == 'string')
		{
			// Use string
			var errorbox = dollarsigndollarsign('error_' + errors[i].input_id);
		}
		else
		{
			// Pick first one from array
			var errorbox = dollarsigndollarsign('error_' + errors[i].input_id[0]);
		}

		// Show errorbox
		if (errorbox)
		{
			if (errors[i].msg)
			{
				errorbox.innerHTML = errors[i].msg;
			}
			errorbox.style.display = 'block';
		}
		else
		{
			alert('errorbox "' + 'error_' + errors[i].input_id + '" not found!');
		}

		// Add highlighting
		if (typeof(errors[i].input_id) == 'string')
		{
			if (dollarsigndollarsign(errors[i].input_id))
			{
				// Highlight 1 input
				window.jQuery('#' + errors[i].input_id).addClass('input_error');
			}
			else
			{
				//alert(errors[i].input_id + ' not found!');
			}
		}
		else 
		{
			// Highlight all inputs from array
			var inputs = errors[i].input_id;
			for (var j = 0; j < inputs.length; j++)
			{
				window.jQuery('#' + inputs[j]).addClass('input_error');
			}
		}
		
	}
	
	// Focus the input with the first error
	if (typeof(errors[0].input_id) == 'string')
	{
		var inp = window.jQuery('#' + errors[0].input_id).get(0);
	}
	else
	{
		// Take first one from array
		var inp = window.jQuery('#' + errors[0].input_id[0]).get(0);
	}
	
	if (inp)
	{
		inp.focus();	// Works in FF & useful in case of checkboxes/radio buttons
		if (inp.select)
		{
			inp.select();	// Works in IE & FF
		}
	}
		
}

function HideValidationErrors()
{
	// Hide all old error messages
	window.jQuery('.val_error').hide();
	/*
	var old_err = document.getElementsByClassName('val_error');
	for (var i = 0; i < old_err.length; i++) 
	{
		window.jQuery(old_err[i]).hide();
	}
	*/

	// Remove all highlighting from inputs
	window.jQuery('.input_error').removeClass('input_error');
	/*
	var old_err = document.getElementsByClassName('input_error');
	for (var i = 0; i < old_err.length; i++) 
	{
		window.jQuery('#' + old_err[i].removeClassName('input_error');
	}
	*/
}

// -----------------------------------------------------------------------

/**
*	Make validation error object
*	
*	- input_id may also contain an array of inputs
**/
function CreateValidationErrorObject(input_id, msg) {
	var error_obj = new Object;
	error_obj.input_id = input_id;
	error_obj.msg = msg;
	return error_obj;
}

// -----------------------------------------------------------------------
// VALIDATION FUNCTIONS

// Check object exists
function CheckObject(obj_id)
{
	if (!dollarsigndollarsign(obj_id))
	{
		alert('There is no object with id ' + obj_id);
		return false;
	}
	return true;
}

// Check for value not empty
function CheckNotEmpty(obj_id) 
{
	if (!CheckObject(obj_id))
	{
		return false;
	}
	var obj = dollarsigndollarsign(obj_id);
	return (dollarsigndollarsign(obj_id).value.length > 0);
}

// Check for value not zero
function CheckNotZero(obj_id) 
{
	if (!CheckObject(obj_id))
	{
		return false;
	}
	var obj = dollarsigndollarsign(obj_id);
	return (dollarsigndollarsign(obj_id).value != 0);
}

// Check for alpha numeric value (A-Z, 0-9)
function wppc_checkAlphaNumeric(obj_id) 
{
	if (!CheckObject(obj_id))
	{
		return false;
	}
	var obj = dollarsigndollarsign(obj_id);
	var pattern = /^[a-z0-9]+$/i;
	return pattern.test(obj.value);
}

// Check for integer number
function CheckNumber(obj_id) {
	var obj = dollarsigndollarsign(obj_id);
	var level = /^[1-9]{1}[0-9]*$/i;
	if (level.test(obj.value)) {
		return true;
	} else {
		return false;
	}
}

// Check select for number > 0
function CheckSelectNotZero(obj_id) 
{
	if (!CheckObject(obj_id))
	{
		return false;
	}

	var obj = dollarsigndollarsign(obj_id);
	if (obj.selectedIndex == -1)
	{
		return false;
	}

	var value = obj.options[obj.selectedIndex].value;
	if (parseInt(value) > 0) {
		return true;
	} else {
		if (isNaN(parseInt(value))) {
			return true;	
		} else {
			return false;
		}
	}
}

// Checks that not the first option of a select is selected
function CheckSelectNotFirst(obj_id) 
{
	if (!CheckObject(obj_id))
	{
		return false;
	}
	var obj = dollarsigndollarsign(obj_id);
	return (obj.selectedIndex != 0);
}

// Check for valid name
function CheckValidName(obj_id) {
	var obj = dollarsigndollarsign(obj_id);
	var name = /^[A-Z\-\.\' ]+$/i;
	if (name.test(obj.value)) {
		return true;
	} else {
		return false;
	}
}

// Check for valid address
function CheckValidAddress(obj_id) {
	var obj = dollarsigndollarsign(obj_id);
	var name = /^[A-Z0-9\-\.\' ]+$/i;
	if (name.test(obj.value)) {
		return true;
	} else {
		return false;
	}
}

// Check for valid initials
function CheckCapitals(obj_id) {
	var obj = dollarsigndollarsign(obj_id);
	var name = /^[A-Z. ]+$/i;
	if (name.test(obj.value)) {
		dollarsigndollarsign(obj_id).value = dollarsigndollarsign(obj_id).value.toUpperCase();
		return true;
	} else {
		return false;
	}
}

// Check for valid dutch postal code
function CheckValidPostalcode(obj_id) {
	var obj = dollarsigndollarsign(obj_id);
	var name = /^[0-9]{4}[ ]{0,1}[A-z]{2}$/;
	if (name.test(obj.value)) {
		return true;
	} else {
		return false;
	}
}

// Check for valid dutch, belgian or german postal code
function CheckPostalcodePerCountry(obj_id, country) 
{
	switch (country)
	{
		case 'NL':
			var pattern = /^[1-9][0-9]{3}[ ]{0,1}[A-z]{2}$/;
			break;
		case 'BE':
			var pattern = /^[1-9][0-9]{3}$/;
			break;
		case 'DE':
			var pattern = /^[1-9][0-9]{4}$/;
			break;
	}
	var obj = dollarsigndollarsign(obj_id);
	if (pattern.test(obj.value)) {
		return true;
	} else {
		return false;
	}
}

// Check for valid dutch date (dd-mm-yyyy)
function CheckValidDutchDate(obj_id) {
	var obj = dollarsigndollarsign(obj_id);
	var date = /^(0[1-9]|[12][0-9]|3[01])-(0[1-9]|1[012])-(19|20)\d\d$/i;
	if (date.test(obj.value)) {
		return true;
	} else {
		return false;
	}
}

// Check for valid ISO date (yyyy-mm-dd)
function CheckIsoDate(obj_id) {
	var obj = dollarsigndollarsign(obj_id);
	var date = /^20\d\d-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01])$/i;
	if (date.test(obj.value)) {
		return true;
	} else {
		return false;
	}
}

// Check for valid time (24h format, 12:34)
function CheckValidTime(obj_id) 
{
	if (!CheckObject(obj_id))
	{
		return false;
	}
	var obj = dollarsigndollarsign(obj_id);
	var pattern = /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/;
	if (pattern.test(obj.value)) {
		return true;
	} else {
		return false;
	}
}

// Check email address
function CheckValidEmail(obj_id) 
{
	var obj = dollarsigndollarsign(obj_id);
	var email = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-\.])+\.)+([a-zA-Z0-9]{2,4})+$/;
	if (email.test(obj.value)) {
		return true;
	} else {
		return false;
	}
}

// Check URL
function CheckValidUrl(obj_id) 
{
	var obj = dollarsigndollarsign(obj_id);
	if (!CheckObject(obj_id))
	{
		return false;
	}
	var pattern = /https?:\/\/[A-Za-z0-9\-\.\/]+/;
	if (pattern.test(obj.value)) {
		return true;
	} else {
		return false;
	}
}

// Check if a checkbox is checked.
function CheckSingleCheckbox(obj_id) 
{
	var obj = dollarsigndollarsign(obj_id);
	if (obj) 
	{
		return (obj.checked)
	}
	else
	{
		alert(obj_id + ' not found!');
		return false;
	}
}

// Check if at least one checkbox is checked.
// Uses window.jQuery
function CheckCheckboxes(fieldname) 
{
	var checked = false;
	window.jQuery('input[name="' + fieldname + '"]').each(function()
	{
		if (window.jQuery(this).get(0).checked)
		{
			checked = true;
			return;
		}
	});
	return checked;
}

// Check phone number (generic)
// May start with +, followed by 0-9, -, ., comma, slash and spaces
function CheckValidPhoneNumber(obj_id) 
{
	var obj = dollarsigndollarsign(obj_id);
	var pattern = /^(\+*)[0-9\-\s\.\/\,]+$/i;
	if (pattern.test(obj.value)) {
		return true;
	} else {
		return false;
	}
}

// Check a date, formed by 3 selects.
// Only checks if the first option is not selected.
function CheckDateNotEmpty(d_obj_id, m_obj_id, y_obj_id) 
{
	var d_obj = dollarsigndollarsign(d_obj_id);
	var m_obj = dollarsigndollarsign(m_obj_id);
	var y_obj = dollarsigndollarsign(y_obj_id);
	return (d_obj.selectedIndex != 0 && m_obj.selectedIndex != 0 && y_obj.selectedIndex != 0);
}

// Compare the values of two inputs
// Returns true if identical, false otherwise.
function CompareValues(inp1, inp2) 
{
	var obj1 = dollarsigndollarsign(inp1);
	var obj2 = dollarsigndollarsign(inp2);
	var result = (obj1.value == obj2.value);
	return result;
}

/**
*	Checks for a strong password.
*
*	Requirements:
*	- Must be at least 8 characters long
*	- Must contain at least 2 digits
*	- Must contain at least 2 uppercase letters
*	- Must contain at least 2 lowercase letters
**/
function CheckStrongPassword(obj_id) 
{
	var pwd = dollarsigndollarsign(obj_id).value;
	// Check length
	if (pwd.length < 6)
	{
		return false;
	}
	
/*
	// Check length
	if (pwd.length < 8)
	{
		return false;
	}

	// Should contain at least two digits
	var pattern = /^.*[0-9].*[0-9].*$/;
	if (!pattern.test(pwd))
	{
		return false;
	}

	// Should contain at least two uppercase letters
	var pattern = /^.*[A-Z].*[A-Z].*$/;
	if (!pattern.test(pwd))
	{
		return false;
	}

	// Should contain at least two lowercase letters
	var pattern = /^.*[a-z].*[a-z].*$/;
	if (!pattern.test(pwd))
	{
		return false;
	}
*/
	
	return true;
}

// Check if one date comes before another
// Objects are assumed to be selects, which contain
// values like "2006-01-01"
function CheckDateSequence(obj_id1, obj_id2) {
	
	// Get values
	var sel1 = dollarsigndollarsign(obj_id1);
	var sel2 = dollarsigndollarsign(obj_id2);
	var date1 = sel1.options[sel1.selectedIndex].value;
	var date2 = sel2.options[sel2.selectedIndex].value;

	date1 = makeDateObject(date1);
	date2 = makeDateObject(date2);
	
	// Date1 before date2: error
	if (date1 > date2) {
		return false;
	} else {
		return true;
	}

}

// Check if one date/time comes before another.
// Objects are assumed to be selects.
// Expected dates are 2006-01-01, expected times are 10:00 (24h, leading zeroes)
function CheckDateTimeSequence(date_sel1, time_sel1, date_sel2, time_sel2) {
	
	// Get objects
	var date_sel1 = document.getElementById(date_sel1);
	var date_sel2 = document.getElementById(date_sel2);
	var time_sel1 = document.getElementById(time_sel1);
	var time_sel2 = document.getElementById(time_sel2);

	// Get values
	var date1 = date_sel1.options[date_sel1.selectedIndex].value;
	var date2 = date_sel2.options[date_sel2.selectedIndex].value;
	var time1 = time_sel1.options[time_sel1.selectedIndex].value;
	var time2 = time_sel2.options[time_sel2.selectedIndex].value;

	//alert(date1 + " " + time1 + "\n" + date2 + " " + time2);

	date1 = makeDateObject(date1, time1);
	date2 = makeDateObject(date2, time2);
	
	// Date1 occurs before date2, or date1 = date2: error
	if (date1 >= date2) {
		return false;
	} else {
		return true;
	}

}

// Check usernames (usera,userb)
function CheckUsernames(obj_id) 
{
	if (!CheckObject(obj_id))
	{
		return false;
	}
	var obj = dollarsigndollarsign(obj_id);
	var pattern = /^([a-z]{5,})(,[a-z]{5,})*$/;
	if (pattern.test(obj.value)) {
		return true;
	} else {
		return false;
	}
}
