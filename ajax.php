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

    // get existing duration and units
    $existingDuration = get_post_meta($jobID, 'duration', true);
    $existingUnits    = get_post_meta($jobID, 'units', true);

    // calculate units (1 unit = 1 hour) to 2 decimals
    $units = number_format($duration / 3600, 2);

    // get total duration in seconds
    $duration = $existingDuration ? $existingDuration + $duration : $duration;

    // get total units
    $units = $existingUnits ? $existingUnits + $units : $units;

    // check if existing units equals duration; if not, update duration appropriately
    if ($units != $duration / 3600) {
        $duration = $units * 3600;
    }

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

/**
 * Update units
 */
add_action('wp_ajax_jt_update_units', 'jt_update_units');

function jt_update_units()
{
    check_ajax_referer('jt_update_units', 'nonce');

    $jobID = sanitize_text_field($_POST['jobID']);
    $units = sanitize_text_field($_POST['units']);

    $unitsUpdated = update_post_meta($jobID, 'units', $units);

    // also convert to and update duration
    $duration = $units * 3600;
    $hours    = floor($duration / 3600);
    $minutes  = floor(($duration / 60) % 60);
    $seconds  = $duration % 60;
    $duration = "{$hours}h {$minutes}m {$seconds}s";

    $durationUpdated = update_post_meta($jobID, 'duration', $duration);

    if ($unitsUpdated  && $durationUpdated) {
        wp_send_json("Units updated for job ID: {$jobID}");
    } else {
        wp_send_json("There was an error updating units for job ID {$jobID}, or the data has not changed.");
    }
}

/**
 * Update duration
 */
add_action('wp_ajax_jt_update_duration', 'jt_update_duration');

function jt_update_duration()
{
    check_ajax_referer('jt_update_duration', 'nonce');

    // wp_send_json($_POST);

    $jobID        = sanitize_text_field($_POST['jobID']);
    $durationOrig = preg_replace('/(h|m|s)(?!\s)/', '$1 ', sanitize_text_field($_POST['duration']));

    // convert duration to units from hours, mins, secs.
    $durationArr     = preg_split('/h|m|s/', $durationOrig, -1, PREG_SPLIT_NO_EMPTY);
    $hours           = (int)$durationArr[0];
    $minutes         = (int)$durationArr[1];
    $seconds         = (int)$durationArr[2];
    $durationSeconds = ($hours * 3600) + ($minutes * 60) + $seconds;

    // calculate and update units
    $units           = number_format($durationSeconds / 3600, 2);
    $unitsUpdated    = update_post_meta($jobID, 'units', $units);
    $durationUpdated = update_post_meta($jobID, 'duration', $durationOrig);

    if ($unitsUpdated && $durationUpdated) {
        wp_send_json("Duration updated for job ID: {$jobID}");
    } else {
        wp_send_json("There was an error updating duration for job ID {$jobID}, or the data has not changed.");
    }
}
