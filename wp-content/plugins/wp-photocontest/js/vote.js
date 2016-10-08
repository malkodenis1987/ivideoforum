/**
*	Prepare form
**/
wppc_jQuery(document).ready(function()
{
	loadForm();
});


function loadForm()
{
	HighlightActiveInput();		

	// Ajax form - login
	if (wppc_jQuery('#photocontest-vote_form'))
	{
		
		wppc_jQuery('#photocontest-vote_form').ajaxForm(
		{
			beforeSubmit: CheckVoteForm,
			success: function(msg)
			{
				if (wppc_jQuery('.content_wppc').length <= 0)
				{
					alert("Please add a class content_wppc to the div in the template!");
				}
				wppc_jQuery('.content_wppc').html(msg).show();
			}
		});
		
		if (wppc_jQuery('#voter_email'))
		{
			wppc_jQuery('#voter_email').focus();
		}	
	}
}
// -----------------------------------------------------------------------

/**
*	Validates the recipient form
**/
function CheckVoteForm() 
{
	// Hide feedback
	wppc_jQuery('.feedback').hide();

	var errors = new Array();

	// Check voornaam
	if (!CheckNotEmpty('voter_email')) 
	{
		errors.push(CreateValidationErrorObject('voter_email', false));
	}
	// Check captcha
	if (!CheckNotEmpty('captcha')) 
	{
		errors.push(CreateValidationErrorObject('captcha', false));
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
