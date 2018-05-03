/*
 * function to invoke ACF validation, if ACF Pro plugin is installed and activated, [and validation is active].
 */
function workflowSubmitWithACF(fnParam)
{
	/*
	 * if ACf validation is not active, then by pass the ACF validation
	 */
	if( acf.validation.active == 0) {
		normalWorkFlowSubmit(fnParam);
		return;
	} 
	
	var $form = jQuery('#post');
	acf.do_action('submit', $form);

    acf.validation.fetch($form);
}