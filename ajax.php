<?php

/**
 * Delete job
 */
add_action('wp_ajax_jt_delete_job', 'jt_delete_job');

function jt_delete_job()
{
    check_ajax_referer('jt_delete_job', 'nonce');

    $jobID   = sanitize_text_field($_POST['jobID']);
    $deleted = wp_delete_post($jobID);

    if ($deleted) {
        wp_send_json("Job/Activity deleted successfully.");
    } else {
        wp_send_json("There was an error deleting the job/activity.");
    }
}


/**
 * Save clients
 */
add_action('wp_ajax_jt_save_clients', 'jt_save_clients');

function jt_save_clients()
{
    check_ajax_referer('jt_save_clients', 'nonce');

    $clients = array_filter($_POST['clients']);
    $saved   = update_option('job_time_tracker_clients', maybe_serialize($clients));

    if ($saved) {
        wp_send_json('Clients saved successfully.');
    } else {
        wp_send_json('There was an error saving clients. Most likely reason is that the data has not changed.');
    }
}

/**
 * Update time entries
 */
add_action('wp_ajax_jt_update_time_entries', 'jt_update_time_entries');

function jt_update_time_entries()
{
    check_ajax_referer('jt_update_time_entries', 'nonce');

    $jobID    = sanitize_text_field($_POST['jobID']);
    $duration = sanitize_text_field($_POST['duration']);

    // calculate units (1 unit = 1 hour) to 2 decimals
    $units = number_format($duration / 3600, 2);

    // calculate duration in hours, minutes, seconds
    $hours    = floor($duration / 3600);
    $minutes  = floor(($duration / 60) % 60);
    $seconds  = $duration % 60;
    $duration = "{$hours}h {$minutes}m {$seconds}s";

    // update post meta
    update_post_meta($jobID, 'units', $units);
    update_post_meta($jobID, 'duration', $duration);

    wp_send_json("Time entries updated for job ID: {$jobID}");
}

/**
 * Save activity types
 */
add_action('wp_ajax_jt_save_activities', 'jt_save_activities');

function jt_save_activities()
{
    check_ajax_referer('jt_save_activities', 'nonce');

    $activities = array_filter($_POST['activities']);
    $saved      = update_option('job_time_tracker_activities', maybe_serialize($activities));

    if ($saved) {
        wp_send_json('Activities saved successfully.');
    } else {
        wp_send_json('There was an error saving activities. Most likely reason is that the data has not changed.');
    }
}

/**
 * Add job/activity
 */
add_action('wp_ajax_jt_add_job_activity', 'jt_add_job_activity');

function jt_add_job_activity()
{
    check_ajax_referer('jt_add_job_activity', 'nonce');

    // wp_send_json($_POST);

    $clientName   = sanitize_text_field($_POST['clientName']);
    $description  = sanitize_text_field($_POST['description']);
    $activityType = sanitize_text_field($_POST['activityType']);
    $dateTimeNow  = date('Y-m-d H:i:s');

    $jobAdded = wp_insert_post(array(
        'post_title'   => $description,
        'post_type'    => 'jobs',
        'post_status'  => 'publish',
        'meta_input'   => [
            'date_time'   => $dateTimeNow,
            'client'      => $clientName,
            'activity'    => $activityType,
            'description' => $description,
            'duration'    => '',
            'units'       => '',
        ],
    ));

    if (is_wp_error($jobAdded)) {
        wp_send_json("Job/Activity could not be added. Error: {$jobAdded->get_error_message()}");
    } else {
        wp_send_json("Job/activity added successfully. Job ID: {$jobAdded}");
    }
}
