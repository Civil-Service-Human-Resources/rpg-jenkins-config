<?php
global $ow_custom_statuses;

// sanitize the data
$selected_user = (isset( $_GET['user'] ) && sanitize_text_field( $_GET["user"] )) ? intval( sanitize_text_field( $_GET["user"] ) ) : get_current_user_id();
$page_number = (isset( $_GET['paged'] ) && sanitize_text_field( $_GET["paged"] )) ? intval( sanitize_text_field( $_GET["paged"] ) ) : 1;
$die = false;




//ARE ABLE TO SEE USERS INBOX?  NEED TO CHECK THAT REQUESTED USER IS IN THE SAME TEAM
if(!OW_Utility::instance()->is_in_team(wp_get_current_user()->ID, $selected_user)){
	$die = true;
}

$ow_inbox_service = new OW_Inbox_Service();
$ow_process_flow = new OW_Process_Flow();
$ow_workflow_service = new OW_Workflow_Service();

// get assigned posts for selected user
$inbox_items = $ow_process_flow->get_assigned_post( null, $selected_user );
$count_posts = count( $inbox_items );
$per_page = OASIS_PER_PAGE;

// get custom terminology
$workflow_terminology_options = OW_Utility::instance()->get_custom_workflow_terminology();
$sign_off_label = $workflow_terminology_options['signOffText'];
$abort_workflow_label = $workflow_terminology_options['abortWorkflowText'];
$display_name = OW_Utility::instance()->get_display_name($selected_user);
$header = $ow_inbox_service->get_table_header();
?>
<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
    <h1><?php _e( 'Inbox', 'oasisworkflow' ); if($display_name){echo ' &mdash; '. $display_name; } ?></h1>

	<?php if($die){
		echo '<p>Unable to display requested inbox</p>';
	}else{
	?>
    <div id="workflow-inbox">
        <div class="tablenav top">

            <!-- Bulk Actions Start -->
            <?php do_action( 'owf_bulk_actions_section' ); ?>
            <!-- Bulk Actions End -->

            <input type="hidden" id="hidden_task_user" value="<?php echo esc_attr( $selected_user ); ?>" />
            <?php if( current_user_can( 'ow_view_others_inbox' ) ) { ?>
               <div class="alignleft actions">
					<?php
                    $assigned_users = $ow_process_flow->get_assigned_users();
                   if( $assigned_users ) { ?>
				   <select id="inbox_filter">
                       <option value=<?php echo get_current_user_id(); ?> selected="selected"><?php echo __( "View inbox of ", "oasisworkflow" ) ?></option>
                       <?php
                          foreach ( $assigned_users as $assigned_user ) {
                             if( ( isset( $_GET['user'] ) && $_GET["user"] == $assigned_user->ID ) )
                                echo "<option value={$assigned_user->ID} selected>{$assigned_user->display_name}</option>";
                             else
                                echo "<option value={$assigned_user->ID}>{$assigned_user->display_name}</option>";
                          }
                       ?>
                   </select>

                   <a href="javascript:window.open('<?php echo admin_url( 'admin.php?page=oasiswf-inbox&user=' ) ?>' + jQuery('#inbox_filter').val(), '_self')">
                       <input type="button" class="button-secondary action" value="<?php echo __( "Show", "oasisworkflow" ); ?>" />
                   </a>
				   <?php } ?>
               </div>
            <?php } ?>
            <ul class="subsubsub"></ul>
            <div class="tablenav-pages">
                <?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $per_page ); ?>
            </div>
        </div>
        <table class="wp-list-table widefat fixed posts" cellspacing="0" border=0>
            <thead><?php echo $header; ?></thead>
            <tfoot><?php echo $header; ?></tfoot>
            <tbody id="coupon-list">
				<?php
					$wf_process_status = get_site_option( "oasiswf_status" ) ;
					$space = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" ;
					if( $inbox_items ):
						$count = 0;
						$start = ( $page_number - 1 ) * $per_page;
						$end = $start + $per_page;
						foreach ( $inbox_items as $inbox_item ){
							if ( $count >= $end )
								break;
							if ( $count >= $start )
							{
								$post = get_post( $inbox_item->post_id );
								$user = get_userdata( $post->post_author ) ;
								$stepId = $inbox_item->step_id;
								if ( $stepId <= 0 || $stepId == "" ) {
									$stepId = $inbox_item->review_step_id;
								}
								$step = $ow_workflow_service->get_step_by_id( $stepId ) ;
								$workflow = $ow_workflow_service->get_workflow_by_id( $step->workflow_id );
								$needs_to_be_claimed = $ow_process_flow->check_for_claim( $inbox_item->ID ) ;
								$original_post_id = get_post_meta( $inbox_item->post_id, '_oasis_original', true );
								/*Check due date and make post item background color in red to notify the admin*/
		                        $current_date = Date( " F j, Y " );
								$due_date = OW_Utility::instance()->format_date_for_display( $inbox_item->due_date );
								$past_due_date_row_class = '';
		                        $past_due_date_field_class = '';
								if( $due_date != "" && strtotime( $due_date ) < strtotime( $current_date ) ) {
   								$past_due_date_row_class = 'past-due-date-row';
   								$past_due_date_field_class = 'past-due-date-field';

								 }
                        echo "<tr id='post-{$inbox_item->post_id}'
                        	class='post-{$inbox_item->post_id} post type-post $past_due_date_row_class
                        	status-pending format-standard hentry category-uncategorized alternate iedit author-other'> " ;
                        $workflow_post_id = esc_attr( $inbox_item->post_id );
								echo "<td><strong>" . esc_html( $post->post_title )."</strong><br/><span style='font-size:12px;'>";
										_post_states( $post );
								echo "</span>" ;
								// create the action list
								if( $needs_to_be_claimed ){ // if the item needs to be claimed, only "Claim" action is visible
									echo "<div class='row-actions'>
											<span>
												<a href='#' class='claim' actionid={$inbox_item->ID}>" . __( "Claim", "oasisworkflow") . "</a>
												<span class='loading'>$space</span>
											</span>
										</div>" ;
								} else {
                           echo "<div class='row-actions'>
										</div>" ;

									echo "<div class='row-actions'>" ;
									if( OW_Utility::instance()->is_post_editable( $inbox_item->post_id ) ){
										echo "<span><a href='post.php?post={$inbox_item->post_id}&action=edit&oasiswf={$inbox_item->ID}&user={$selected_user}' class='edit' real={$inbox_item->post_id}>" . __("Edit", "oasisworkflow"). "</a></span>&nbsp;|&nbsp;" ;
									}

									echo "<span><a target='_blank' href='" . get_preview_post_link( $inbox_item->post_id ) . "'>" . __( "View", "oasisworkflow" ) . "</a></span>&nbsp;|&nbsp;";
                           
									if( current_user_can( 'ow_sign_off_step' ) && OW_Utility::instance()->is_post_editable( $inbox_item->post_id ) ){
										echo "<span>
												<a href='#' wfid='$inbox_item->ID' postid='$inbox_item->post_id' class='quick_sign_off'>" . $sign_off_label. "</a>
												<span class='loading'>$space</span>
											</span>&nbsp;|&nbsp;" ;
									}
									if ( current_user_can( 'ow_reassign_task' ) ) {
										echo "<span>
												<a href='#' wfid='$inbox_item->ID' class='reassign'>" . __( "Reassign", "oasisworkflow" ) . "</a>
												<span class='loading'>$space</span>
											</span>&nbsp;|&nbsp;";
									}
									if ( current_user_can( 'ow_abort_workflow' ) ) {
										echo "<span>
										<a href='#' wfid='$inbox_item->ID' postid='$inbox_item->post_id' class='abort_workflow'>" . $abort_workflow_label . "</a>
										<span class='loading'>$space</span>
										</span>&nbsp;|&nbsp;" ;
									}
									if ( current_user_can( 'ow_view_workflow_history' ) ) {
	                           $nonce_url = wp_nonce_url("admin.php?page=oasiswf-history&post=$inbox_item->post_id", 'owf_view_history_nonce');
	                           echo "<span><a href='$nonce_url'> " . __( "View History", "oasisworkflow" ) . "</a></span>";
									}

									echo "</div>";
									get_inline_data( $post );
								}
								echo "</td>";
								echo "<td>".(strlen($inbox_item->team)==0?'&mdash;' :$inbox_item->team)."</td>";
								echo "<td>" . OW_Utility::instance()->get_user_name( $user->ID ) . "</td>" ;
								$workflow_name = $workflow->name;
								if ( ! empty( $workflow->version )) {
									$workflow_name .= " (" . $workflow->version . ")";
								}

								echo "<td>{$ow_workflow_service->get_gpid_dbid( $workflow->ID, $stepId, 'lbl' )}<br/><span style='font-size:9px;'>{$workflow_name}</span></td>" ;

								if( get_option( 'oasiswf_priority_setting' ) == 'enable_priority') {
									//priority settings
								 	$priority = get_post_meta( $post->ID, '_oasis_task_priority', true );
								 	if ( empty( $priority ) ) {
								 		$priority = '2normal';
								 	}

								 	$priority_array = OW_Utility::instance()->get_priorities();
								 	$priority_value = $priority_array[$priority];
								 	echo "<td>". $priority_value ."</td>";
								}

                        $post_status = $ow_custom_statuses->get_single_term_by( 'slug', get_post_status( $post->ID ) );
                        $post_status = is_object( $post_status ) && isset( $post_status->name ) ? $post_status->name : $wf_process_status[$ow_workflow_service->get_gpid_dbid( $workflow->ID, $stepId, 'process' )];
								// if the due date is passed the current date show the field in a different color
                        echo "<td><span class=' . $past_due_date_field_class . '>" . OW_Utility::instance()->format_date_for_display( $inbox_item->due_date ) . "</span></td>" ;

								echo "<td class='comments column-comments'>
										<div class='post-com-count-wrapper'>
											<strong>
												<a href='#' actionid={$inbox_item->ID} class='post-com-count post-com-count-approved' data-comment='inbox_comment' post_id={$inbox_item->post_id}>
													<span class='comment-count-approved'>{$ow_process_flow->get_comment_count( $inbox_item->ID, TRUE, $inbox_item->post_id )}</span>
												</a>
												<span class='loading'>$space</span>
											</strong>
										</div>
									  </td>" ;
								echo "</tr>" ;
							}
							$count++;
						}
					else:
						echo "<tr>" ;
						echo "<td class='hurry-td' colspan='8'>
								<label class='hurray-lbl'>";
						echo __( "No assignments", "oasisworkflow" );
						echo "</label></td>" ;
						echo "</tr>" ;
					endif;
				?>
			</tbody>
        </table>
        <div class="tablenav">
            <div class="tablenav-pages">
                <?php OW_Utility::instance()->get_page_link( $count_posts, $page_number, $per_page ); ?>
            </div>
        </div>
    </div>
	<?php } ?>
</div>
<span id="wf_edit_inline_content"></span>
<div id ="step_submit_content"></div>
<div id="reassign-div"></div>
<div id="post_com_count_content"></div>
<input type="hidden" name="owf_claim_process_ajax_nonce" id="owf_claim_process_ajax_nonce" value="<?php echo wp_create_nonce( 'owf_claim_process_ajax_nonce' ); ?>" />
<input type="hidden" name="owf_inbox_ajax_nonce" id="owf_inbox_ajax_nonce" value="<?php echo wp_create_nonce( 'owf_inbox_ajax_nonce' ); ?>" />