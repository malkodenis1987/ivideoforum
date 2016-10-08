/**
*	Prepare form
**/
wppc_jQuery(document).ready(function()
{
	loadEnterForm();
});


function loadEnterForm()
{
	HighlightActiveInput();		

	// Ajax form - login
	if (wppc_jQuery('#photocontest-enter_form'))
	{
		wppc_jQuery('#photocontest-enter_form').ajaxForm(
		{
			beforeSubmit: CheckEnterForm,
			success: function(msg)
			{
				wppc_jQuery('html, body').animate( { scrollTop: 0 }, 0 );
				wppc_jQuery('.content_wppc').html(msg).show();
			}
		});
	}

}
// -----------------------------------------------------------------------

/**
*	Validates the recipient form
**/
function CheckEnterForm() 
{
	// Hide feedback
	wppc_jQuery('.feedback').hide();

	var errors = new Array();

	// Check achternaam
	if (!CheckNotEmpty('upload_email')) 
	{
		errors.push(CreateValidationErrorObject('upload_email', false));
	}
	// Check voornaam
	if (!CheckNotEmpty('imagefile')) 
	{
		errors.push(CreateValidationErrorObject('imagefile', false));
	}
	// Check achternaam
	if (!CheckNotEmpty('image_title')) 
	{
		errors.push(CreateValidationErrorObject('image_title', false));
	}
	// Check achternaam
	if (!CheckNotEmpty('image_comment')) 
	{
		errors.push(CreateValidationErrorObject('image_comment', false));
	}		
	// Check errors
	if (errors.length == 0)
	{
		return true;
	}
	else
	{	
		// Validation failed
		ShowValidationErrors(errors);
		return false;
	}
}
