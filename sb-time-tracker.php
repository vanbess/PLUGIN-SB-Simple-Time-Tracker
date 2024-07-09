<?php

/**
 * Plugin Name: Silverback Time Tracker
 * Description: A simple job/activity time tracker plugin.
 * Version: 1.0.2
 * Author: WC Bessinger
 * Author URI: https://silverbackdev.co.za
 * License: GPL2
 */

// bail if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_enqueue_scripts', 'job_time_tracker_scripts');

function job_time_tracker_scripts()
{

    wp_enqueue_script('job-time-tracker-js', plugin_dir_url(__FILE__) . 'scripts.js', array('jquery'), '1.0.0', true);
    wp_localize_script('job-time-tracker-js', 'jobTimeTracker', array(
        'ajaxurl'                   => admin_url('admin-ajax.php'),
        'nonce_delete_job'          => wp_create_nonce('jt_delete_job'),
        'nonce_save_clients'        => wp_create_nonce('jt_save_clients'),
        'nonce_save_activities'     => wp_create_nonce('jt_save_activities'),
        'nonce_add_job_activity'    => wp_create_nonce('jt_add_job_activity'),
        'nonce_update_time_entries' => wp_create_nonce('jt_update_time_entries'),
    ));
    wp_enqueue_style('job-time-tracker-css', plugin_dir_url(__FILE__) . 'styles.css');
}

// include admin page
require_once plugin_dir_path(__FILE__) . 'admin.php';

// include ajax functions
require_once plugin_dir_path(__FILE__) . 'ajax.php';

// include custom post type
require_once plugin_dir_path(__FILE__) . 'cpt.php';
