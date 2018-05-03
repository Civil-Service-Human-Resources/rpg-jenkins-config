<?php
/*
 * Service class for Inbox
 *
 * @copyright   Copyright (c) 2016, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
   exit;
}

/**
 * OW_Inbox_Service Class
 *
 * @since 2.0
 */
class OW_Inbox_Service {

	/**
	 * Set things up.
	 *
	 * @since 2.0
	 */
	public function __construct() {
		add_action( 'wp_ajax_get_edit_inline_html', array( $this, 'get_edit_inline_html' ) );
		add_action( 'wp_ajax_get_step_signoff_page', array( $this, 'get_step_signoff_page' ) );
		add_action( 'wp_ajax_get_reassign_page', array( $this, 'get_reassign_page' ) );
		add_action( 'wp_ajax_get_step_comment_page', array( $this, 'get_step_comment_page' ) );
	}

   /**
	 * AJAX function - Get the inline edit data
	 * TODO: see if we can find an alternative for this.
	 */
	public function get_edit_inline_html() {
		global $current_screen;
      
      // nonce check
		check_ajax_referer( 'owf_inbox_ajax_nonce', 'security' );

		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$current_screen->post_type = sanitize_text_field( $_POST["post_type"] );
		$wp_list_table->inline_edit();
      wp_send_json_success();
	}
   
   /**
	 *  AJAX function - get step sign off page
	 *
	 * @since 2.0
	 */
	public function get_step_signoff_page() {
      // nonce check
		check_ajax_referer( 'owf_inbox_ajax_nonce', 'security' );
		ob_start();
		include( OASISWF_PATH . "includes/pages/subpages/submit-step.php" );
		$result = ob_get_contents();
		ob_end_clean();

		wp_send_json_success( htmlentities( $result ) );
	}

   /**
	 * AJAX function - Get reassign page
	 *
	 * @since 2.0
	 */
	public function get_reassign_page() {
      // nonce check
		check_ajax_referer( 'owf_inbox_ajax_nonce', 'security' );
		ob_start();
		include( OASISWF_PATH . "includes/pages/subpages/reassign.php" );
		$result = ob_get_contents();
		ob_end_clean();

		wp_send_json_success( htmlentities( $result ) );
	}
   
   /**
	 * AJAX function - Get comments page
	 *
	 * @since 2.0
	 */
	public function get_step_comment_page() {
      // nonce check
		check_ajax_referer( 'owf_inbox_ajax_nonce', 'security' );
		ob_start();
		include( OASISWF_PATH . "includes/pages/subpages/action-comments.php" );
		$result = ob_get_contents();
		ob_end_clean();

		wp_send_json_success( htmlentities( $result ) );
	}
   
	/**
	 * Generate the table header for the inbox page
	 * @return mixed HTML for the inbox page
	 *
	 * @since 2.0
	 */
	public function get_table_header() {
		$sortby = ( isset( $_GET['order'] ) && sanitize_text_field( $_GET["order"] ) == "desc" ) ? "asc" : "desc";

		// sorting the inbox page via Author, Due Date, Post title and Post Type
		$author_class = $workflow_class = $due_date_class = $post_order_class = $post_type_class = $priority_class = '';
		if ( isset( $_GET['orderby'] ) && isset( $_GET['order'] ) ) {
			$orderby = sanitize_text_field( $_GET['orderby'] );
			switch ( $orderby ) {
				case 'author':
					$author_class = $sortby;
					break;
				case 'due_date':
					$due_date_class = $sortby;
					break;
				case 'post_title':
					$post_order_class = $sortby;
					break;
				case 'post_type':
					$post_type_class = $sortby;
					break;
				case 'priority':
					$priority_class  = $sortby;
				   break;
			}
		}
		$workflow_terminology_options = get_option( 'oasiswf_custom_workflow_terminology' );
		$due_date = ! empty( $workflow_terminology_options['dueDateText'] ) ? $workflow_terminology_options['dueDateText'] : __( 'Due', 'oasisworkflow' );
		$priority = ! empty( $workflow_terminology_options['taskPriorityText'] ) ? $workflow_terminology_options['taskPriorityText'] : __( 'Priority', 'oasisworkflow' );
		$return_html = "<tr>";
		$sorting_args = add_query_arg( array( 'orderby' => 'post_title', 'order' => $sortby ) );
		$return_html .= "<th width='35%' scope='col' class='sorted $post_order_class'>
		<a href='$sorting_args'>
		<span>" . __( "Page", "oasisworkflow" ) . "</span>
					<span class='sorting-indicator'></span>
				</a>
				</th>";
		$return_html .= "<th scope='col' width='12%'>". __( "Team", "oasisworkflow" ) . "</th>";
		$sorting_args = add_query_arg( array( 'orderby' => 'post_author', 'order' => $sortby ) );
		$return_html .= "<th scope='col' class='sorted $author_class' width='12%'>
		<a href='$sorting_args'>
		<span>" . __( "Author", "oasisworkflow" ) . "</span>
					<span class='sorting-indicator'></span>
			</a>
			</th>";
		$return_html .= "<th class='inbox-workflow'>" . __( "Step", "oasisworkflow" ) . "</th>";
		if ( get_option( 'oasiswf_priority_setting' ) == 'enable_priority' ) {
         $sorting_args = add_query_arg( array( 'orderby' => 'priority', 'order' => $sortby ) );
         $return_html .= "<th scope='col' class='sorted $priority_class'>
		          <a href='$sorting_args'>
		             <span>" . $priority . "</span>
		             <span class='sorting-indicator'></span>
		          </a>
              </th>";
		}
		$return_html .= "<th scope='col' class='sorted $due_date_class'>
		<a href='admin.php?page=oasiswf-inbox&orderby=due_date&order=$sortby" . "'>
					<span>" . $due_date . "</span>
					<span class='sorting-indicator'></span>
			</a>
			</th>";
		$return_html .= "<th class='inbox-comments'>" . __( "Comments", "oasisworkflow" ) . "</th>";
		$return_html .= "</tr>";

		return $return_html;
	}

   /**
    * Include reassign js
    */
	public function enqueue_and_localize_script() {
		wp_enqueue_script( 'owf_reassign_task',
				OASISWF_URL. 'js/pages/subpages/reassign.js',
				array('jquery'),
				OASISWF_VERSION,
				true);

		wp_localize_script( 'owf_reassign_task', 'owf_reassign_task_vars', array(
				'selectUser' => __( 'Select a user to reassign the task.', 'oasisworkflow' )
		) );
	}
}

// construct an instance so that the actions get loaded
$inbox_service = new OW_Inbox_Service();
?>