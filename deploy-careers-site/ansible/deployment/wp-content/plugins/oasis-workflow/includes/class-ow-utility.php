<?php

/*
 * Utilities class for Oasis Workflow
 *
 * @copyright   Copyright (c) 2016, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 *
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) )
   exit;

/*
 * Utilities class - singleton class
 *
 * @since 2.0
 */

class OW_Utility {

   /**
    * Private constructor so nobody else can instance it
    */
   private function __construct() {
      
   }

   /**
    * Call this method to get singleton
    *
    * @return OW_Utility
    */
   public static function instance() {

      static $instance = NULL;
      if( is_null( $instance ) ) {
         $instance = new OW_Utility();
      }

      return $instance;
   }

   /**
    * logging utility function
    * prints log statements in debug.log is logging is turned on in wp-config.php
    *
    * @since 2.0
    */
   public function logger( $message ) {
      if( WP_DEBUG === true ) {
         if( is_array( $message ) || is_object( $message ) ) {
            error_log( print_r( $message, true ) );
         } else {
            error_log( $message );
         }
      }
   }

   /**
    * get workflow table name
    * @return string
    *
    * @since 2.0
    */
   public function get_workflows_table_name() {
      global $wpdb;
      return $wpdb->prefix . "fc_workflows";
   }

   /**
    * get workflow steps table name
    * @return string
    *
    * @since 2.0
    */
   public function get_workflow_steps_table_name() {
      global $wpdb;
      return $wpdb->prefix . "fc_workflow_steps";
   }

   /**
    * get workflow history table name
    * @return string
    *
    * @since 2.0
    */
   public function get_action_history_table_name() {
      global $wpdb;
      return $wpdb->prefix . "fc_action_history";
   }

   /**
    * get workflow history action table name
    * @return string
    *
    * @since 2.0
    */
   public function get_action_table_name() {
      global $wpdb;
      return $wpdb->prefix . "fc_action";
   }

   /**
    * get workflow emails table name
    * @return string
    *
    * @since 2.0
    */
   public function get_emails_table_name() {
      global $wpdb;
      return $wpdb->prefix . "fc_emails";
   }

   /**
    * returns integer value of the date
    *
    * @param string $date input date
    * @param string $date_separator example, "-" in 10-2-2014"
    * @return int integer value of the date
    */
   public function get_date_int( $date = null, $date_separator = "-" ) {
      $date = ( $date ) ? $date : current_time( 'mysql', 0 );
      $date_split = explode( $date_separator, $date );
      $date_time_split = preg_split('/[\s]+/', $date_split[2] );
      $date_int = $date_split[0] * 10000 + $date_split[1] * 100 + $date_time_split[0] * 1;
      return $date_int;
   }

   /**
    * formats the date to OASISWF_EDIT_DATE_FORMAT
    *
    * @param string $date string to be formatted
    * @return string formatted date
    *
    * @since 2.0
    */
   public function format_date_for_display_and_edit( $date ) {
      if( $date == "0000-00-00" ) {
         return "";
      }
      if( $date ) {
         $formatted_date = mysql2date( OASISWF_EDIT_DATE_FORMAT, $date, false );
         return $formatted_date;
      }
      return "";
   }

   /**
    * displays the date in current/site format
    *
    * @param string $date string to be formatted
    * @param string $date_form could be date or datetime
    * @return string formatted date
    */
   public function format_date_for_display( $date, $format = "-", $date_form = "date" ) {

		if(strlen($date)==0) return "&mdash;";

	  // only date
      if( $date_form == "date" ) {
         if( $date == "0000-00-00" ) {
            return "&mdash;";
         }
         if( $date ) {
            $formatted_date = mysql2date( get_option( 'date_format' ), $date );
            return $formatted_date;
         }
      } else { //date and time both
         $date_time = explode( " ", $date );

         if( $date_time[0] == "0000-00-00" ) {
            return "&mdash;";
         }

         if( $date_time[0] ) {
            $date_time_format = get_option( 'date_format' ) . " " . get_option( 'time_format' );
            $formatted_date = mysql2date( $date_time_format, $date );
            return $formatted_date;
         }
      }
   }

   /**
    * create HTML checkbox section for post types
    *
    * @param string $list_name name of the control/element
    * @param string $selected
    *
    * @return HTML for post type checkbox
    *
    * @since 2.0
    */
   public function owf_checkbox_post_types_multi( $list_name, $selected ) {
      $selected_row = '';
      // get all custom types
      $types = OW_Utility::instance()->owf_get_post_types();
      $checked = '';

      foreach ( $types as $post_type ) {
         // If post type is wordpress builtin then ignore it.
         if( is_array( $selected ) && in_array( esc_attr( $post_type['name'] ), $selected ) ) { // preselect specified role
            $checked = " ' checked='checked' ";
         } else {
            $checked = '';
         }

         $selected_row .= "<label style='display: block;'> <input type='checkbox' class='owf-checkbox'
					name='" . $list_name . "' value='" . esc_attr( $post_type['name'] ) . "'" . $checked . "/>";
         $selected_row .= $post_type['label'];
         $selected_row .= "</label>";
      }
      echo $selected_row;
   }

   /**
    * create HTML drop down section for post types
    *
    * @param string $list_name name of the control/element
    * @param string $selected
    *
    * @return HTML for post type drop down
    *
    * @since 2.0
    */
   public function owf_dropdown_post_types_multi( $selected_option ) {
      $selected_row = '';
      // get all custom types
      $types = OW_Utility::instance()->owf_get_post_types();
      $selected = '';

      foreach ( $types as $post_type ) {
         // If post type is wordpress builtin then ignore it.
         if( !empty( $selected_option ) && $post_type['name'] == $selected_option ) { // preselect specified role
            $selected = " selected='selected' ";
         } else {
            $selected = '';
         }

         $selected_row .= "<option value=" . esc_attr( $post_type['name'] ) . $selected . ">";
         $selected_row .= $post_type['label'];
         $selected_row .= "</option>";
      }
      echo $selected_row;
   }

   /**
    * TODO: Stop Gap arrangement to get all the post types from all the slave sites, by marking them public = true and show_ui = false in the main site
    * get_post_types is not multi-site aware
    * need to add a hook to let the users implement it and get a list of post types
    * We do not support attachments yet
    */
   public function owf_get_post_types() {
      global $wpdb;
      $all_types = array();
      $types = get_post_types( array( 'show_ui' => true ), 'objects' );
      foreach ( $types as $post_type ) {
         if( $post_type->name != 'attachment' ) {
            $temp_post_type = array( "name" => $post_type->name, "label" => $post_type->label );
            if( in_array( $temp_post_type, $all_types ) ) {
               continue;
            } else {
               array_push( $all_types, $temp_post_type );
            }
         }
      }

      // public = true, but, show_ui = false
      $types = get_post_types( array( 'show_ui' => false, 'public' => true ), 'objects' );
      foreach ( $types as $post_type ) {
         if( $post_type->name != 'attachment' ) {
            $temp_post_type = array( "name" => $post_type->name, "label" => $post_type->label );
            if( in_array( $temp_post_type, $all_types ) ) {
               continue;
            } else {
               array_push( $all_types, $temp_post_type );
            }
         }
      }

      return $all_types;
   }

   /**
    * get dropdown of post status
    * @param array $selected
    * @return HTML for post status drop down
    * 
    * @since 2.0
    */
   public static function owf_dropdown_post_status_multi( $selected ) {
      $row = '';
      $selected_row = '';
      $status_array = get_post_stati( array( 'show_in_admin_status_list' => true ), 'objects' );
      foreach ( $status_array as $status_id => $status_object ) {
         if( $status_id == "future" ) { // we do not consider future (scheduled posts) to be valid of auto submit
            continue;
         }
         if( is_array( $selected ) && in_array( $status_id, $selected ) ) // preselect specified status
            $selected_row .= "\n\t<option selected='selected' value='" . $status_id . "'>$status_object->label</option>";
         else
            $row .= "\n\t<option value='" . $status_id . "'>$status_object->label</option>";
      }
      echo $selected_row . $row;
   }

   /**
    * returns the pagination
    *
    * @param int $total_posts
    * @param int $current_page_number
    * @param int $per_page
    * @param string $action
    * @return HTML for displaying pagination
    *
    * @since 2.0
    */
   public function get_page_link( $total_posts, $current_page_number, $per_page = 20, $action = null ) {
      $allpages = ceil( $total_posts / $per_page );
      $base = "";
      if( empty( $action ) ) {
         $base = esc_url_raw( @add_query_arg( 'paged', '%#%' ) );
      } else if( !empty( $action ) ) {
         $base .= esc_url_raw( @add_query_arg( array( 'paged' => '%#%', 'action' => $action ) ) );
      }
      $page_links = paginate_links( array(
          'base' => $base,
          'format' => '',
          'total' => $allpages,
          'current' => $current_page_number
              ) );

      $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s-%s of %s', "oasisworkflow" ) . '</span>%s', number_format_i18n( ( $current_page_number - 1 ) * $per_page + 1 ), number_format_i18n( min( $current_page_number * $per_page, $total_posts ) ), number_format_i18n( $total_posts ), $page_links );

      echo $page_links_text;
   }

   /**
    * Utility function to get the current user's role
    *
    * @since 2.0
    */
   public function get_current_user_role() {
      global $wp_roles;
      foreach ( $wp_roles->role_names as $role => $name ) :
         if( current_user_can( $role ) )
            return $role;
      endforeach;
   }

   /**
    * Utility function to get the role of the a given user_id
    * @param int $user_id
    *
    * @since 2.0
    */
   public function get_user_role( $user_id ) {
      global $wp_roles;

      // sanitize the data
      $user_id = intval( $user_id );

      foreach ( $wp_roles->role_names as $role => $name ) :
         if( user_can( $user_id, $role ) )
            return $role;
      endforeach;
   }
   
   /**
    * Utility function to get the user’s roles with multiple roles
    *
    * @since 2.2
    */
   public function get_current_user_roles()
    {
        global $wp_roles;

        $user_roles = array();

        foreach ( $wp_roles->role_names as $role => $name ) :
            if ( current_user_can( $role ) )
            {
                array_push($user_roles, $role);
            }
        endforeach;

        return $user_roles;
    }

   /**
    * given the user_id, get the full name of the user (could return nickname too)
    *
    * @param int $user_id
    * @return string display name of the user
    *
    * @since 2.0
    */
   public function get_user_name( $user_id ) {
      // sanitize the data
      $user_id = intval( $user_id );

      $user = get_userdata( $user_id );
      if( $user )
         return $user->data->display_name;
   }

   /**
    * Return the user name and its id for given users array
    * @param array $users
    * @param string $group_by
    * @return object
    * 
    * @since 2.0
    */
   public function get_step_users( $users, $post_id, $group_by = 'users' ) {
      $users_list = array();
      switch ( $group_by ) {
         case 'roles':
            // lets convert value to key
            $users_list = $this->get_users_by_role( array_flip( $users ), $post_id );
            break;
         case 'users':
            foreach ( $users as $key => $user ) {
               $user_obj = new WP_User( $user );
               
               // FIXED: if user is deleted then skip the further
               if( empty( $user_obj->ID ) || $user_obj->ID == 0 ) {
                  continue;
               }
               $users_list[$key] = new stdClass();
               $users_list[$key]->ID = $user;
               $users_list[$key]->name = $user_obj->display_name;
            }
            break;
      }
      return (object) $users_list;
   }

   public function get_users_by_role( $role, $post_id = null ) {
      global $wpdb;
      if( count( $role ) > 0 ) {
         $user_string = array();
         $post_author_id = "";
         // Instead of using WP_User_Query, we have to go this route, because user role editor
         // plugin has implemented the pre_user_query hook and excluded the administrator users to appear in the list

         if( $post_id != null ) {
            $post = get_post( $post_id );
            $post_author_id = $post->post_author;
         }


         foreach ( $role as $k => $v ) {
            if( $k == 'owfpostauthor' ) { // this is a custom role added by oasis workflow
               $author_user = new WP_User( $post_author_id );
               $users = array( $author_user );
            } else {
               $user_role = '%' . $k . '%';
               $users = $wpdb->get_results( $wpdb->prepare( "SELECT users_1.ID, users_1.display_name FROM {$wpdb->base_prefix}users users_1
   				INNER JOIN {$wpdb->base_prefix}usermeta usermeta_1 ON ( users_1.ID = usermeta_1.user_id )
   				WHERE (usermeta_1.meta_key = '{$wpdb->prefix}capabilities' AND CAST( usermeta_1.meta_value AS CHAR ) LIKE %s)", $user_role ) );
            }
            foreach ( $users as $user ) {
               $current_user = get_current_user_id();
               $user_obj = new WP_User( $user->ID );
               if( !empty( $user_obj->roles ) && is_array( $user_obj->roles ) ) {
                  foreach ( $user_obj->roles as $user_role ) {
                     if( $user_role == $k || 'owfpostauthor' == $k ) { // if the selected role is 'postauthor'- the custom role.
                        $part["ID"] = $user->ID;

                        if( $user->ID == $post_author_id ) {
                           $part["name"] = $user->display_name . ' (' . __( "Post Author", "oasisworkflow" ) . ')';
                        } else {
                           $part["name"] = $user->display_name;
                        }
                        // check if the user already exists in the available list
                        $exists = false;
                        foreach ( $user_string as $available_user ) {
                           if( $part["ID"] == $available_user->ID ) {
                              $exists = true; // the user exists, so lets not add it again.
                           }
                        }
                        if( !$exists ) {
                           $user_string[] = (object) $part;
                        }

                        break;
                     }
                  }
               }
            }
         }
         return (object) $user_string;
      }
      return "";
   }

   /**
    * Get next or previous date depending on the number of days
    * @param $input_date
    * @param $direction "next" or "pre"
    * @param $days number of days
    *
    * @return date in Y-m-d format
    *
    * @since 2.0
    */
   public function get_pre_next_date( $input_date, $direction = "next", $days = 1 ) {
      $date = new DateTime( $input_date );

      $date_stamp = $date->format( "U" );

      if( $direction == "next" )
         $date_stamp = $date_stamp + 3600 * 24 * $days;
      else if( $direction == "pre" )
         $date_stamp = $date_stamp - 3600 * 24 * $days;

      return gmdate( "Y-m-d", $date_stamp );
   }

   /**
    * Convert a date format to a jQuery UI DatePicker format
    *
    * @param string $dateFormat a date format
    * @return string
    */
   public function owf_date_format_to_jquery_ui_format( $dateFormat ) {

      $chars = array(
          // Day
          'd' => 'dd', 'j' => 'd', 'l' => 'DD', 'D' => 'D',
          // Month
          'm' => 'mm', 'n' => 'm', 'F' => 'MM', 'M' => 'M',
          // Year
          'Y' => 'yy', 'y' => 'y'
      );

      return strtr( (string) $dateFormat, $chars );
   }

   public function str_array_pos( $string, $array ) {
      for ( $i = 0, $n = count( $array ); $i < $n; $i ++ ) {
         if( stristr( $string, $array[$i] ) !== false ) {
            return true;
         }
      }
      return false;
   }

   public function is_post_tag_in_array( $post_id, $keyword_array ) {
      $post_tags = wp_get_post_tags( $post_id );
      $search_array = array_map( 'sanitize_key', $keyword_array );
      foreach ( $post_tags as $post_tag ) {
         if( in_array( sanitize_key( $post_tag->name ), $search_array ) ) {
            return true;
         }
      }

      return false;
   }

   public function is_post_category_in_array( $post_id, $keyword_array ) {
      $post_categories = wp_get_post_categories( $post_id );
      $search_array = array_map( 'sanitize_key', $keyword_array );
      foreach ( $post_categories as $post_category ) {
         $cat = get_category( $post_category );
         if( in_array( sanitize_key( $cat->name ), $search_array ) ) {
            return true;
         }
      }

      return false;
   }

   /**
    * Get Post Categories including custom categories
    *
    * @param int $post_id
    * @return string, comma separated string of category names
    * 
    * @since 2.0
    */
   public function get_post_categories( $post_id ) {

      $post_id = intval( $post_id );
      $post = get_post( $post_id );

      $cats = array();
      $category_names = "----";

      //get ootb categories
      $post_categories = get_the_category( $post_id );
      if( !empty( $post_categories ) ) {
         foreach ( $post_categories as $cat ) {
            $cats[] = $cat->name;
         }
      }

      // get any custom taxonomy/categories.
      // heirarchical = 1 //category
      // _builtin != 1 //custom category
      // show_ui = 1
      // public = 1
      $taxonomies = get_object_taxonomies( $post, "object" );
      foreach ( $taxonomies as $key => $taxonomy ) {
         if( $taxonomy->hierarchical == 1 &&
                 $taxonomy->show_ui == 1 &&
                 $taxonomy->public == 1 &&
                 $taxonomy->_builtin != 1 ) {
            $terms = get_the_terms( $post, $key );
            if( !empty( $terms ) ) {
               foreach ( $terms as $term ) {
                  $cats[] = $term->name;
               }
            }
         }
      }
      if( !empty( $cats ) ) {
         $category_names = implode( ', ', $cats );
      }

      return $category_names;
   }

   /*
    * DB related functions
    */

   /*
    * returns date formatted for DB
    *
    * @param string OASISWF_EDIT_DATE_FORMAT date format
    * @return string date formatted for db
    *
    * @since 2.0
    */

   public function format_date_for_db_wp_default( $formatted_date ) {

      // incoming formatted date: 08-Août 24, 2016
      // remove the textual month so that the date looks like: 08 24, 2016
      $start = '-';
      $end  = ' ';
      $replace_string = '';
      $formatted_date = preg_replace('#('.preg_quote($start).')(.*?)('.preg_quote($end).')#si', '$1'. $replace_string .'$3', $formatted_date);
      $formatted_date = str_replace( "-", "", $formatted_date );

      $date = DateTime::createFromFormat( 'm d, Y', $formatted_date );
      $date_with_mysql_format = $date->format( 'Y-m-d' );
      return $date_with_mysql_format;
   }
   
    /*
    * returns date formatted for DB
    *
    * @param string 'm d, Y @ G:i' datetime format
    * @return string date formatted for db
    *
    * @since 4.2
    */
   public function format_datetime_for_db_wp_default( $formatted_date ) {
      // incoming formatted date: 08-Août 24, 2016 @ 09:45
      // remove the textual month so that the date looks like: 08 24, 2016 @ 09:45
      
	  //CHECK FOR ALPHA CHARACTERS IN THE INCOMING DATE
	  if(preg_match("/[a-z]/i", $formatted_date)){
			$start = '-';
			$end  = ' ';
			$replace_string = '';
			$formatted_date = preg_replace('#('.preg_quote($start).')(.*?)('.preg_quote($end).')#si', '$1'. $replace_string .'$3', $formatted_date);
			$formatted_date = str_replace( "-", "", $formatted_date );
			$date = DateTime::createFromFormat( 'm d, Y @ G:i', $formatted_date );
			 $date_with_mysql_format = $date->format( 'Y-m-d H:i:s' );
	  }else{
			 $date = str_replace('@', '', $formatted_date);
			 $new_date = new DateTime($date);
			 $date_with_mysql_format = $new_date->format('Y-m-d H:i:s');
	  }
     
      return $date_with_mysql_format;
   }
  

   /**
    * Given the table name and data will insert into the table and return the inserted id
    *
    * @param string $table - name of the table
    * @param mixed $data - data array to be inserted // should match the table format
    */
   public function insert_to_table( $table, $data ) {
      global $wpdb;

      $result = $wpdb->insert( $table, $data );
      if( $result ) {
         return $wpdb->insert_id;
      }

      return false;
   }

   /**
    * Display error or success message in the admin section
    *
    * @param array $data containing type and message
    * @return string with html containing the error message
    *
    * @since 1.0 initial version
    */
   public function admin_notice( $data = array() ) {
      // extract message and type from the $data array
      $message = isset( $data['message'] ) ? $data['message'] : "";
      $message_type = isset( $data['type'] ) ? $data['type'] : "";

      switch ( $message_type ) {
         case 'error':
            $admin_notice = '<div id="message" class="error notice is-dismissible">';
            break;
         case 'update':
            $admin_notice = '<div id="message" class="updated notice is-dismissible">';
            break;
         case 'update-nag':
            $admin_notice = '<div id="message" class="update-nag">';
            break;
         case 'review' :
            $admin_notice = '<div id="message" class="updated notice owf-rating is-dismissible">';
            break;
         default:
            $message = __( 'There\'s something wrong with your code...', 'oasisworkflow' );
            $admin_notice = '<div id="message" class="error">';
            break;
      }

      $admin_notice .= '<p>' . __( $message, 'oasisworkflow' ) . '</p>';
      $admin_notice .= '</div>';
      return $admin_notice;
   }

   /**
    * Get username and its role
    * @access public
    * @param int $user_id
    * @return object
    * 
    * @since 2.0
    */
   public function get_user_role_and_name( $user_id ) {
      if ( $user_id != 'System' ) {
         $user = new WP_User( $user_id );
         return (object) array( 'role' => trim( ucwords( array_shift( $user->roles ) ) ), 'username' => $user->display_name );
      } else {
         return (object) array( 'role' => 'System', 'username' => 'system' );
      }
   }

   /**
    * set priority levels
    * @return array
    * 
    * @since 2.0
    */
   public function get_priorities() {
      // adding numbers in front of the priority values, so that we can sort it using DB sort
      return apply_filters( 'owf_set_priority_status', array(
          '1low' => __( 'Low', 'oasisworkflow' ),
          '2normal' => __( 'Normal', 'oasisworkflow' ),
          '3high' => __( 'High', 'oasisworkflow' ),
          '4urgent' => __( 'Urgent', 'oasisworkflow' )
      ) );
   }

   /**
    * Set submit workflow, sign off priority option values
    * @param string $sel_priority default priority will be normal
    * @return HTML of priority options tag
    * 
    * @since 2.0
    */
   public function get_priority_dropdown( $sel_priority = '2normal' ) {
      $priorities = $this->get_priorities();
      $option = '';
      foreach ( $priorities as $key => $val ) {
         $option .= '<option value="' . $key . '" ' . selected( $sel_priority, $key, FALSE ) . '>' . __( $val, 'oasisworkflow' ) . '</option>';
      }
      echo $option;
   }

   /**
    * get all roles and show the assigneed roles as selected
    * @param array $step_info
    * @return string - option list html string with option group
    * 
    * @since 2.0
    */
   public function get_roles_option_list( $step_info ) {
      global $wp_roles;
      $task_assignee = '';

      if( isset( $step_info->task_assignee ) && !empty( $step_info->task_assignee ) ) {
         $task_assignee = $step_info->task_assignee;
      }

      // Sort roles by alphabetical order
      $roles = (array) $wp_roles->role_names;
      // add our custom role "Post Author" to this list
      $roles['owfpostauthor'] = __( 'Post Author', 'oasisworkflow' );
      asort( $roles );

      $options = '<optgroup label="' . __( 'Roles', 'oasisworkflow' ) . '">';
      foreach ( $roles as $role => $name ) {
         $selected = is_object( $step_info ) && isset( $task_assignee->roles ) && !empty( $task_assignee->roles ) && in_array( $role, $task_assignee->roles ) ? 'selected="selected"' : '';
         $options .= "<option value='r@{$role}' $selected>$name</option>";
      }

      $options .= '</optgroup>';

      return $options;
   }

   /**
    * get all user and show the assigneed users as selected
    * @param array $step_info
    * @return string - option list html string with option group
    * 
    * @since 2.0
    */
   public function get_users_option_list( $step_info ) {

      $task_assignee = '';

      if( isset( $step_info->task_assignee ) && !empty( $step_info->task_assignee ) ) {
         $task_assignee = $step_info->task_assignee;
      }

      // get all registered users in the site
      $args = array(
          'blog_id' => $GLOBALS['blog_id'],
          'fields' => array( 'ID', 'display_name' )
      );
      $users = get_users( $args );

      $options = '<optgroup label="' . __( 'Users', 'oasisworkflow' ) . '">';
      foreach ( $users as $user ) {
         $selected = is_object( $step_info ) && isset( $task_assignee->users ) && !empty( $task_assignee->users ) && in_array( $user->ID, $task_assignee->users ) ? 'selected="selected"' : '';
         $options .= "<option value='u@{$user->ID}' $selected>$user->display_name</option>";
      }

      $options .= '</optgroup>';

      return $options;
   }

   /**
    * create drop down for roles
    *
    * @param int $selected if passed, that will be selected by default
    * @param boolean $add_post_author_role, adds the custom Post Author role to the drop drop list
    * @return HTML for roles
    *
    * @since 2.0
    */
   public function owf_dropdown_roles_multi( $selected = null, $add_post_author_role = false ) {
      $row = '';
      $selected_row = '';
      $post_author_role_name = __( 'Post Author', 'oasisworkflow' );
      $post_author_role_value = 'owfpostauthor';

      // for single site
      $editable_roles = get_editable_roles();

      foreach ( $editable_roles as $role => $details ) {
         $name = translate_user_role( $details['name'] );
         if( is_array( $selected ) && in_array( esc_attr( $role ), $selected ) ) // preselect specified role
            $selected_row .= "\n\t<option selected='selected' value='" . esc_attr( $role ) . "'>$name</option>";
         else
            $row .= "\n\t<option value='" . esc_attr( $role ) . "'>$name</option>";
      }

      // add the custom post author role to the list
      if( $add_post_author_role ) {

         if( is_array( $selected ) && in_array( esc_attr( $post_author_role_value ), $selected ) ) // preselect specified role
            $selected_row .= "\n\t<option selected='selected' value='" . esc_attr( $post_author_role_value ) . "'>$post_author_role_name</option>";
         else
            $row .= "\n\t<option value='" . esc_attr( $post_author_role_value ) . "'>$post_author_role_name</option>";
      }
      echo $selected_row . $row;
   }

   /**
    * Show workflow on whitelisted post types
    * @return json encoded string
    * 
    * @since 2.1
    */
   public function allowed_post_types() {
      return get_option( 'oasiswf_show_wfsettings_on_post_types' );
   }

   /**
    * Return the custom workflow terminology settings
    * @param bool $index Optional.
    * @return string if $index is true | array if $index is false
    * 
    * @since 2.1
    */
   public function get_custom_workflow_terminology( $index = false ) {
      $settings = get_option( 'oasiswf_custom_workflow_terminology' );
      $default_settings = array(
          'submitToWorkflowText' => __( 'Submit to Workflow', 'oasisworkflow' ),
          'signOffText' => __( 'Sign Off', 'oasisworkflow' ),
          'assignActorsText' => __( 'Assign Actor(s)', 'oasisworkflow' ),
          'dueDateText' => __( 'Due Date', 'oasisworkflow' ),
          'publishDateText' => __( 'Publish Date', 'oasisworkflow' ),
          'abortWorkflowText' => __( 'Abort Workflow', 'oasisworkflow' ),
          'workflowHistoryText' => __( 'Workflow History', 'oasisworkflow' ),
          'taskPriorityText' => __( 'Priority', 'oasisworkflow' )
      );

      $default_settings = apply_filters( 'owf_default_custom_workflow_terminology_settings', $default_settings );

      /**
       * As we are not checking this stuff on neither activation nor upgrade so if it return false then
       * $setting will be our $default_settings for terminology
       */
      if( $settings ) {
         foreach ( $settings as $key => $val ) {
            $settings[$key] = empty( $val ) ? $default_settings[$key] : $val;
         }
      } else {
         $settings = $default_settings;
      }

      return $index ? $settings[$index] : $settings;
   }

   /**
    * Check whether user can edit other users posts
    * @param int $user_id
    * @return boolean
    * 
    * @since 2.1
    */
   public function can_user_edit_post( $user_id ) {
      if( current_user_can( 'edit_posts' ) ||
          current_user_can( 'edit_pages' ) ||
          current_user_can( 'edit_others_posts' ) ||
          current_user_can( 'edit_others_pages' ) ||
          get_current_user_id() === $user_id ) {
         return true;
      }

      return false;
   }

   public function owf_pro_features() {
      $str = "";
      $show_upgrade_notice = get_site_option( 'oasiswf_show_upgrade_notice' );
      if( $show_upgrade_notice != "no" ) {
         $str = '<div style="width:90%; float:left;  margin: 0px 50px 5px 7px; padding: 10px 10px 10px 10px; border: 1px solid #ddd; background-color:#FFFFE0;">
					   <form id="hide_notice">
						<div class="oasis_button_div">
								<a class="oasis_button" target="_blank" href="https://www.oasisworkflow.com/pricing-purchase"> ' . __( 'Learn More', 'oasisworkflow' ) . '</a>' .
                '</div>
	      			<div style="width:80%; float:left; align: center">' .
                '<p>' .
                'Looking for additional features like "Ability to create multiple workflows", ' .
                '<a target="_blank" href="https://www.oasisworkflow.com/documentation/working-with-workflows/revise-published-content">"Revise published content and add workflow support to revised content"</a>, ' .
                '<a target="_blank" href="https://www.oasisworkflow.com/documentation/working-with-workflows/auto-submit-to-workflow">"Auto Submit"</a>, ' .
                '<a target="_blank" href="https://www.oasisworkflow.com/extensions/oasis-workflow-groups">"Groups"</a>, ' .
                '<a target="_blank" href="https://www.oasisworkflow.com/extensions/oasis-workflow-editorial-comments">"Contextual Comments"</a>, ' .
                '<a target="_blank" href="https://www.oasisworkflow.com/extensions/oasis-workflow-front-end-actions">"Front End shortcodes"</a>, ' .
                ' and much more...' .
                '<p/><p>' .
                '<strong><a target="_blank" href="https://www.oasisworkflow.com/request-a-demo-site">Request a demo site to try out the "Pro" features</a></strong>' .
                '</p><p style="float:right;">
						 	<a href="admin.php?page=oasiswf-admin&action=hideNotice">' . 'I know, don\'t bug me.' .
                '</a></p>
	              	</div></form>
	             </div>';
      }
      echo $str;
   }
   
   /**
    * Check if the current user can edit posts/pages/custom post types
    *
    * @param null $post_id
    *
    * @return bool
    */
   public function is_post_editable( $post_id = null ) {
      // if empty post_id, then simply check if the user has atleast edit_posts or edit_pages capability
      if ( empty( $post_id ) ) {
         if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
            return false;
         }
      }

      $post_id = intval( $post_id );

      $post = get_post( $post_id );
      $post_author = get_userdata( $post->post_author );

      $post_type_obj = get_post_type_object( get_post_type( $post_id ) );
      if ( ! empty ( $post_type_obj ) ) {
         // get the caps for the given custom post type
         $caps = $post_type_obj->cap;
         $edit_posts_cap = $caps->edit_posts; // edit_posts cap equivalent
         $edit_others_posts_cap = $caps->edit_others_posts; //edit_others_posts cap equivalent

         if ( current_user_can( $edit_posts_cap ) && get_current_user_id() == $post_author->ID ) {
            return true;
         }

         if ( current_user_can( $edit_others_posts_cap ) ) {
            return true;
         }
      }  
      return false;

   }

   public function get_team_filter($table_alias_left,$table_alias_right, $id_column='post_id'){
		$nofilter = current_user_can('manage_options');
		//$nofilter = false;
		return " INNER JOIN (SELECT EE.*".(!$nofilter ?', FF.*':'')." FROM wp_posts EE ".(!$nofilter?'LEFT JOIN wp_postmeta FF ON EE.ID = FF.post_id  WHERE EE.post_type = "page" AND (FF.meta_key = "rpg-team" AND FF.meta_value IN (SELECT  t.term_id FROM wp_terms AS t  INNER JOIN wp_term_taxonomy AS tt ON t.term_id = tt.term_id INNER JOIN wp_term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy = "content_team" AND tr.object_id = '.get_current_user_id().'))':'') . ") AS ". $table_alias_right . " ON ". $table_alias_left . '.' . $id_column . " = " . $table_alias_right . ".ID ";
   }

   public function see_all_teams(){
		return current_user_can('manage_options');
   }

	public function is_in_team($user_id_with_teams, $user_id_to_check){
		if($this->see_all_teams()){
			return true;
		}

		global $wpdb;

		$user_id_teams = intval( $user_id_with_teams );
		$user_id_look_for = intval( $user_id_to_check );

		$sql = "SELECT COUNT(*) as row_count FROM wp_users AS USERS
				LEFT JOIN (SELECT t.term_id, t.name as team, tr.object_id FROM wp_terms AS t INNER JOIN wp_term_taxonomy AS tt ON t.term_id = tt.term_id INNER JOIN wp_term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy = 'content_team') AS D ON USERS.ID = D.object_id
				where D.term_id IN (SELECT D.term_id FROM wp_users AS USERS
				LEFT JOIN (SELECT t.term_id, t.name as team, tr.object_id FROM wp_terms AS t INNER JOIN wp_term_taxonomy AS tt ON t.term_id = tt.term_id INNER JOIN wp_term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy = 'content_team') 
				AS D ON USERS.ID = D.object_id
				where USERS.ID = %d) AND USERS.ID = %d";

		$result = $wpdb->get_results( $wpdb->prepare( $sql, $user_id_with_teams, $user_id_to_check ) );

		if($result){
			foreach ($result as $row){
				if($row->row_count > 0){
					return true;
				}

			}
		}
		return false;
	}

	public function get_display_name($user_id) {
		if (!$user = get_userdata($user_id))
		return false;
		return $user->data->display_name;
	} 
    
}
?>