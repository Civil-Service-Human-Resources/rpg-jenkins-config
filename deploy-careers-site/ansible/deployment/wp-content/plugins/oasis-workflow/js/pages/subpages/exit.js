jQuery( document ).ready( function () {
   if ( exit_wfid ) {
      jQuery( "#publishing-action" ).append( "<input type='button' id='exit_link' class='button'" + " value='" + owf_abort_workflow_vars.abortWorkflow + "' style='margin-top:-24px;' />" );
      jQuery( '.error' ).hide();
   }
   jQuery( document ).on("click", "#exit_link", function(){
		if(!confirm(owf_abort_workflow_vars.abortWorkflowConfirm))return ;
		data = {
			action: 'workflow_abort' ,
			history_id: exit_wfid,
			security: jQuery('#owf_exit_post_from_workflow').val(),
			command: 'exit_from_workflow'
		};
		jQuery(this).hide();
		jQuery(".loading").show();
		jQuery.post(ajaxurl, data, function( response ) {
			if ( response == -1 ) { // incorrect nonce
				return false;
			}

			if ( response.success ) {
				location.reload();
			}
		});
	})
} );