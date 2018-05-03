<?php
if( isset( $_GET['post'] ) && sanitize_text_field( $_GET["post"] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'owf_view_history_nonce' ) ) {
   $selected_post_id = intval( sanitize_text_field( $_GET["post"] ) );
} else {
   $selected_post_id = NULL;
}
$pagenum = (isset( $_GET['paged'] ) && sanitize_text_field( $_GET["paged"] )) ? intval( sanitize_text_field( $_GET["paged"] ) ) : 1;
$trashed = (isset( $_GET['trashed'] ) && sanitize_text_field( $_GET["trashed"] )) ? sanitize_text_field( $_GET["trashed"] ) : "";

if( $selected_post_id && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'owf_view_history_nonce' ) ) {
   $selected_post = $selected_post_id;
} else {
   $selected_post = NULL;
}

$ow_history_service = new OW_History_Service();
$workflow_service = new OW_Workflow_Service();
$ow_process_flow = new OW_Process_Flow();

$histories = $ow_history_service->get_workflow_history_all( $selected_post );
$count_posts = count($histories);
$per_page = OASIS_PER_PAGE;
$workflow_history = OW_Utility::instance()->get_custom_workflow_terminology( 'workflowHistoryText' );
$header = $ow_history_service->get_table_header();
$row_added_count = 0;
?>
<div class="wrap">
    <div id="icon-edit" class="icon32 icon32-posts-post"><br></div>
    <h1><?php echo $workflow_history; if($selected_post){ echo ' &mdash; '.get_the_title($selected_post); }else{ echo ' &mdash; all pages'; } ?></h1>
    <div id="view-workflow" class="workflow-history">
        <div class="tablenav">
           <form method="post" 
                 id="workflow-history" 
                 action="<?php echo wp_nonce_url( admin_url( 'admin.php?page=oasiswf-history' ), 'owf-workflow-history', 'security' ); ?>">
            <div class="alignleft actions">
               <select id="post_filter" name="post_filter">
                    <option selected="selected" value="0"><?php echo __( "Filter by page", "oasisworkflow" ) ?></option>
                    <?php
                    $wf_posts = $ow_process_flow->get_posts_in_all_workflow();
                    if ( $wf_posts ) {
                       $option = '';
                       foreach ( $wf_posts as $wf_post ) {
                          
                          $selected = selected( $selected_post, $wf_post->post_id );
                          $option .= "<option value='$wf_post->post_id' $selected >$wf_post->title</option>";
                       }
                       echo $option;
                    }
                    ?>
                </select>

                <a style="margin-right:10px;" href="javascript:window.open('<?php echo admin_url( 'admin.php?page=oasiswf-history&post=' ) ?>' + jQuery('#post_filter').val() + '<?php echo '&_wpnonce=' . wp_create_nonce( 'owf_view_history_nonce' ); ?>', '_self')">
                    <input type="button" class="button-secondary action" value="<?php echo __( "Apply", "oasisworkflow" ); ?>" /></a>
                <?php
                if ( current_user_can( 'ow_download_workflow_history' ) ) {
                   ?>
                   <input type="submit" class="button-secondary action" name="download_history" id="download" value="<?php echo __( "Download Workflow History", "oasisworkflow" ); ?>" />
                   <?php
                }
                ?>
            </div>
           </form>
            <div id="hist-nav-top" class="tablenav-pages">
                <?php if($count_posts > 0) OW_Utility::instance()->get_page_link( $count_posts, $pagenum, $per_page ); ?>
            </div>
        </div>
        <table class="wp-list-table widefat fixed posts" cellspacing="0" border=0>
            <thead><?php echo $header; ?></thead>
            <tfoot><?php echo $header; ?></tfoot>
            <tbody id="coupon-list">
                <?php
                if ( $histories ):
                   $count = 0;
                   $start = ($pagenum - 1) * $per_page;
                   $end = $start + $per_page;
                   foreach ( $histories as $row ) {
                      $workflow_name = $row->wf_name;
                      if ( ! empty( $row->version ) ) {
                         $workflow_name .= " (v" . $row->version . ")";
                      }
                      if ( $count >= $end )
                         break;
                      if ( $count >= $start ) {
                         if ( $row->assign_actor_id != -1 ) { //assignment and/or publish steps
                            echo "<tr>";
                            echo "<td>
										<a href='post.php?post={$row->post_id}&action=edit'><strong>{$row->post_title}</strong></a>
									</td>";
                            if ( $row->userid == 0 ) {
                               $actor = "System";
                            } else {
							   $actor = $row->assign_actor;
                               if ( empty( $actor ) ) { // in case the actor is deleted or non existent
                                  $actor = "System";
                               }
                            }
							$row_added_count++;
                            echo "<td>".(strlen($row->team)==0?'&mdash;' :$row->team)."</td>";
							echo "<td>{$actor}</td>";
                            echo "<td>{$workflow_service->get_step_name( $row )}<br/><span style='font-size:9px;'>{$workflow_name}</span></td>";              
                            echo "<td>" . OW_Utility::instance()->format_date_for_display( $row->create_datetime, "-", "datetime" ) . "</td>";
                            echo "<td>" . OW_Utility::instance()->format_date_for_display( $ow_process_flow->get_sign_off_date( $row ), "-", "datetime" ) . "</td>";
                            echo "<td>{$ow_process_flow->get_sign_off_status( $row )}</td>";
                            echo "<td class='comments column-comments'>
										<div class='post-com-count-wrapper'>
											<strong>
												<a href='#' actionid={$row->ID} class='post-com-count post-com-count-approved' real='history'>
													<span class='comment-count-approved'>{$ow_process_flow->get_sign_off_comment_count( $row )}</span>
												</a>
												<span class='loading' style='display:none'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
											</strong>
										</div>
									  </td>";
                            echo "</tr>";
                         }
                         if ( $row->assign_actor_id == -1 ) { //review step
                            $review_rows = $ow_history_service->get_review_action_by_history_id( $row->ID, "update_datetime" );
                            if ( $review_rows ) {
                               foreach ( $review_rows as $review_row ) {

                                  echo "<tr>";
                                  echo "<td><a href='post.php?post={$row->post_id}&action=edit'><strong>{$row->post_title}</strong></a></td>";
                                  if ( $review_row->actor_id == 0 ) {
                                     $actor = "System";
                                  } else {
									$actor = $review_row->assign_actor;
                                    if ( empty( $actor ) ) { // in case the actor is deleted or non existent
										$actor = "System";
                                    }
                                  }
								  $row_added_count++;
								  echo "<td>&mdash;</td>";
                                  echo "<td>{$actor}</td>";
                                  echo "<td>{$workflow_service->get_step_name( $row )}<br/><span style='font-size:9px;'>{$workflow_name}</span></td>";
                                  echo "<td>" . OW_Utility::instance()->format_date_for_display( $row->create_datetime, "-", "datetime" ) . "</td>";
                                  $signoff_date = $review_row->update_datetime;
                                  echo "<td>" . OW_Utility::instance()->format_date_for_display( $signoff_date, "-", "datetime" ) . "</td>";
                                  // If editors' review status is "no_action" (Not acted upon) then set user status as "No action taken"
								  $review_status = "&mdash";
                                  if ( $review_row->review_status == "no_action" || $review_row->review_status == "abort_no_action" ) {
                                     $review_status = __( "No Action Taken", "oasisworkflow" );
                                  } else {
                                     if ( $ow_process_flow->get_next_step_sign_off_status( $row ) == "complete" ) {
                                        $review_status = __( "Workflow completed", "oasisworkflow" );
                                     } else {
                                        $review_status = $ow_process_flow->get_review_sign_off_status( $row, $review_row );
                                     }
                                  }
                                  echo "<td>$review_status</td>";
                                  echo "<td class='comments column-comments'>
												<div class='post-com-count-wrapper'>
													<strong>
														<a href='#' actionid={$review_row->ID} class='post-com-count post-com-count-approved' real='review'>
															<span class='comment-count-approved'>{$ow_process_flow->get_review_sign_off_comment_count( $review_row )}</span>
														</a>
														<span class='loading' style='display:none'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
													</strong>
												</div>
											  </td>";
                                  echo "</tr>";
                                  $count ++;
                               }
                            }
                         }
                      }
                      $count ++;
                   }
                else:
                   echo "<tr>";
                   echo "<td colspan='9' class='no-found-td'><label>";
                   echo __( "No workflow history data found", "oasisworkflow" );
                   echo "</label></td>";
                   echo "</tr>";
                endif;
                ?>
            </tbody>
        </table>
        <?php wp_nonce_field( 'owf-workflow-history', 'owf_workflow_history_nonce' ); ?>
        <input type="hidden" name="owf_inbox_ajax_nonce" id="owf_inbox_ajax_nonce" value="<?php echo wp_create_nonce( 'owf_inbox_ajax_nonce' ); ?>" />
        <div class="tablenav">
            <div id="hist-nav-bot" class="tablenav-pages">
                <?php if($row_added_count > 0) OW_Utility::instance()->get_page_link( $row_added_count, $pagenum, $per_page ); ?>
            </div>
			<?php 
				if($count_posts > 0 && $row_added_count > 0) {
					if($row_added_count != $count_posts){
						echo '<script type="text/javascript">(function(){document.getElementById("hist-nav-top").innerHTML = document.getElementById("hist-nav-bot").innerHTML;})();</script>';
					}
				}
			?>
        </div>
    </div>
</div>
<div id="post_com_count_content"></div>
<div id="ajaxcc"></div>