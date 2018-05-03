<?php

/**
 * User Groups Hooks
 *
 * @package Plugins/Users/Groups/Hooks
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Enqueue assets
add_action( 'admin_head', 'wp_user_groups_admin_assets' );

// WP User Profiles
add_filter( 'wp_user_profiles_sections', 'wp_user_groups_add_profile_section' );
