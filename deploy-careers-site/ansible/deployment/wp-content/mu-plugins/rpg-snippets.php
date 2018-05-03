<?php
/*
Plugin Name: RPG Snippets
Description: Allows ablity to create snippets that can be used in templates/pages
Version: 1.0.0
Author: Valtech Ltd
Author URI: http://www.valtech.co.uk
Copyright: Valtech Ltd
Text Domain: rpgsnippets
Domain Path: /lang
*/

if(!defined('ABSPATH')) exit; //EXIT IF ACCESSED DIRECTLY

if(!class_exists('rpgsnippets')):

class rpgsnippets{

    var $version = '1.0.0';
    var $settings = array();
    
    function __construct(){
        /* DO NOTHING HERE - ENSURE ONLY INITIALIZED ONCE */
    }

    function initialize(){
        $this->settings = array(
            'name'                => __('RPG Snippets', 'rpgsnippets'),
            'version'            => $this->version,
            'capability'        => 'manage_rpgsnippets',
            'posttype'            => 'rpg-snippets'
        );

        //REGISTER CUSTOM POST TYPE
        add_action('init', array($this,'register_post_types'), 5);

        //ADD MENU ITEM
        add_action('admin_menu', array($this,'admin_menu'));

        //ADD SHORTCODE
        add_shortcode('rpg_snippet', array($this,'snippet_shortcode'));

        //REGISTER FILTERS + ACTIONS
        add_filter('manage_rpg-snippets_posts_columns', array($this,'modify_columns'));
        add_action('manage_rpg-snippets_posts_custom_column', array($this,'column_content'));
        add_filter('manage_edit-rpg-snippets_sortable_columns', array($this,'columns_sortable'));
        add_action('post_row_actions', array($this,'snippet_row_actions'), 10, 2);
        add_filter('months_dropdown_results', '__return_empty_array');
        add_filter('bulk_actions-edit-rpg-snippets', '__return_empty_array');
        add_filter('views_edit-rpg-snippets',array($this,'snippet_quick_links'));
        add_action('manage_posts_extra_tablenav', array($this,'bespoke_js_script'));
        add_filter('post_updated_messages', array($this,'snippet_post_update_message'), 10, 1);
        add_filter( 'bulk_post_updated_messages', array($this,'snippet_post_bulk_update_message'), 10, 2 );
    }

    function bespoke_js_script($which){
        if(get_post_type() === $this->get_setting('posttype')){
            if($which==='bottom'){
                //NOT PRETTY BUT GETS JOB DONE...
                if (!wp_script_is('jquery','done')) {
                    wp_enqueue_script('jquery');
                }
       ?>
<script type="text/javascript">(function(){jQuery('div.tablenav.top').attr('style','display:none;');jQuery('p.search-box').attr('style','display:none;');jQuery('input[id^="cb-select-"]').attr('style','display:none;');jQuery('ul.subsubsub').attr('style','margin-bottom:3px;');})();</script>
    <?php
            }
        }
    }

    function admin_menu(){
        $slug = 'edit.php?post_type='.$this->get_setting('posttype');
        $cap = $this->get_setting('capability');

        //ADD THE MENU PAGE TO THE MAIN ADMIN LH MENU
        add_menu_page(__('Snippets','rpgsnippets'), __('Snippets','rpgsnippets'), $cap, $slug, '', 'dashicons-admin-customizer', '80.075');
    }

    function snippet_post_update_message($messages){
        global $post;
        if(get_post_type() === $this->get_setting('posttype')){
            $messages['rpg-snippets'] = array(
                0 => '', //NOT USED
                1 => __('Snippet updated'),
                2 => __('Snippet field updated'),
                3 => __('Snippet field deleted'),
                4 => __('Snippet updated'),
                5 => isset($_GET['revision']) ? sprintf(__('Snippet restored to revision from %s'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
                6 => __('Snippet published'),
                7 => __('Snippet saved'),
                8 => __('Snippet submitted'),
                9 => sprintf( __('Snippet scheduled for: <strong>%1$s</strong>'), date_i18n(__('M j, Y @ G:i'),strtotime($post->post_date))),
                10 => __('Snippet draft updated'),
            );
        }
        return $messages;
    }

    function snippet_post_bulk_update_message($bulk_messages, $bulk_counts) {
        $bulk_messages['rpg-snippets'] = array(
            'updated'   => _n( "%s snippet updated.", "%s snippets updated.", $bulk_counts["updated"] ),
            'locked'    => _n( "%s snippet not updated, somebody is editing it.", "%s snippets not updated, somebody is editing them.", $bulk_counts["locked"] ),
            'deleted'   => _n( "%s snippet permanently deleted.", "%s snippets permanently deleted.", $bulk_counts["deleted"] ),
            'trashed'   => _n( "%s snippet moved to the Trash.", "%s snippets moved to the Trash.", $bulk_counts["trashed"] ),
            'untrashed' => _n( "%s snippet restored from the Trash.", "%s snippets restored from the Trash.", $bulk_counts["untrashed"] ),
        );

        return $bulk_messages;
    }

    function snippet_row_actions($actions, $post) {
        //REMOVE THE QUICK EDIT LINK
        if ($this->get_setting('posttype') === $post->post_type)
        unset($actions['inline hide-if-no-js']);
        return $actions;
    }

    function snippet_quick_links($views) {
		if (isset($views['publish']) || array_key_exists('publish', $views)) { 
			$views['publish'] = preg_replace('/Published/','Active',$views['publish'],1);
		}
        return $views;
    }

    function snippet_shortcode($atts, $content = '') {
        extract(shortcode_atts(array('tagcode' => false), $atts));

        //NO tagcode RETURN EMPTY STRING    
        if (!isset($tagcode)||!$tagcode){
            return '';
        }
        
        $snippet = '';

        //TEST STATUS OF THE SNIPPET - IF PUBLISHED THEN CARRY ON
        if(get_post_status($tagcode)==='publish'){
            //GET THE SNIPPET BODY FROM post_meta
            $snippet = get_post_meta($tagcode, 'snippet_body', true);
        }
        
        return $snippet;
    }

    function modify_columns( $columns ){
        unset($columns['date']);
        $columns['snippet_id'] = 'Tag code';
        $columns['snippet_body'] = 'Excerpt';
        return $columns;
    }

    function column_content($column){
        global $post;
        if('snippet_id' === $column) {
            echo $post->ID;
        }
        if('snippet_body' === $column) {
            echo wp_trim_words(htmlentities(get_field('snippet_body', $post->ID, false)),10);
        }
    }

    function columns_sortable( $columns ) {
        $columns['snippet_id'] = 'snippet_id';
        return $columns;
    }

    function get_setting($name, $value = null){
        if( isset($this->settings[$name])) {
            $value = $this->settings[$name];
        }
        return $value;
    }

    function register_post_types() {
        $cap = $this->get_setting('capability');
        
        register_post_type('rpg-snippets', array(
            'labels'            => array(
                'name'                    => __( 'Snippets', 'rpgsnippets' ),
                'singular_name'            => __( 'Snippet', 'rpgsnippets' ),
                'add_new'                => __( 'Add New' , 'rpgsnippets' ),
                'add_new_item'            => __( 'Add New Snippet' , 'rpgsnippets' ),
                'edit_item'                => __( 'Edit Snippet' , 'rpgsnippets' ),
                'new_item'                => __( 'New Snippet' , 'rpgsnippets' ),
                'view_item'                => __( 'View Snippet', 'rpgsnippets' ),
                'search_items'            => __( 'Search Snippets', 'rpgsnippets' ),
                'not_found'                => __( 'No Snippets found', 'rpgsnippets' ),
                'not_found_in_trash'    => __( 'No Snippets found in Trash', 'rpgsnippets' ), 
            ),
            'public'            => false,
            'show_ui'            => true,
            '_builtin'            => false,
            'capability_type'    => 'rpg-snippets',
            'capabilities'        => array(
                'edit_post'            => $cap,
                'delete_post'        => $cap,
                'edit_posts'        => $cap,
                'delete_posts'        => $cap,
                'edit_others_posts'     => $cap,
                'delete_others_posts'   => $cap,
                'delete_private_posts'  => $cap,
                'edit_private_posts'    => $cap,
                'read_private_posts'    => $cap,
                'edit_published_posts'  => $cap,
                'publish_posts'         => $cap,
                'delete_published_posts'=> $cap,
                'read_post'             => $cap,
            ),
            'hierarchical'        => false,
            'rewrite'            => false,
            'query_var'            => false,
            'supports'             => array('title'),
            'show_in_menu'        => false,
        ));
    }
}

function rpgsnippets() {
    global $rpgsnippets;
    
    if( !isset($rpgsnippets) ) {
        $rpgsnippets = new rpgsnippets();
        $rpgsnippets->initialize();
    }
    
    return $rpgsnippets;
}

//KICK OFF
rpgsnippets();

endif;
?>
