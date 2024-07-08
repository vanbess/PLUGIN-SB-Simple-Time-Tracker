<?php
// Add admin menu item
function job_time_tracker_menu()
{
    add_menu_page(
        'Time Tracker',
        'Time Tracker',
        'manage_options',
        'job-time-tracker',
        'job_time_tracker_page',
        'dashicons-clock',
        6
    );
}
add_action('admin_menu', 'job_time_tracker_menu');

// Display admin page content
function job_time_tracker_page()
{

    $clients = get_option('job_time_tracker_clients') ? maybe_unserialize(get_option('job_time_tracker_clients')) : array();
    $jobs    = get_posts(array('post_type' => 'jobs', 'posts_per_page' => -1, 'post_status' => 'publish'));
    $activities = get_option('job_time_tracker_activities') ? maybe_unserialize(get_option('job_time_tracker_activities')) : array();

?>
    <div id="time-tracker-wrap" class="wrap">

        <h1>Time Tracker</h1>

        <div class="container">

            <!-- timer cont -->
            <div class="timer-container">
                <h2>Timer:</h2>
                <div id="timer">00:00:00</div>
            </div>

            <!-- clients -->
            <div class="form-group">
                <label for="client-name">Client/Company:</label>

                <!-- dropdown -->
                <select id="client-name">
                    <option value="">Select or add client</option>
                    <?php if (is_array($clients) && !empty($clients)) : ?>
                        <?php foreach ($clients as $client) : ?>
                            <option value="<?php echo $client; ?>"><?php echo $client; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <!-- actions -->
                <button id="add-client-btn" title="add new client" class="button">Add Client</button>
                <button id="save-clients" title="save current client list" class="button">Save Clients</button>
                <button id="remove-client" title="remove currently selected client" disabled class="button">Remove Client</button>
            </div>


            <!-- activity type -->
            <div class="form-group">
                <label for="activity-type">Activity Type:</label>

                <!-- dropdown -->
                <select id="activity-type">
                    <option value="">Select or add activity</option>
                    <?php if (is_array($activities) && !empty($activities)) : ?>
                        <?php foreach ($activities as $activity) : ?>
                            <option value="<?php echo $activity; ?>"><?php echo $activity; ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>

                <!-- actions -->
                <button id="add-activity" class="button">Add Activity</button>
                <button id="save-activities" class="button">Save Activities</button>
                <button id="rem-activity" class="button" disabled>Remove Activity</button>
            </div>

            <!-- job/activity name -->
            <div class="form-group">
                <label for="job-description">Job/Activity Description:</label>
                <input type="text" id="job-description">
                <button id="add-job-btn" class="button button-primary">Add New Job/Activity</button>
            </div>

            <!-- job list -->
            <h2 id="job-list-tile">Jobs/Activities</h2>
            <table id="job-list" class="wp-list-table widefat fixed striped table-view-list">

                <thead>
                    <th title="Job ID">ID</th>
                    <th title="Date/Time added">Date</th>
                    <th title="Client the job is being done for (if applicable)">Client</th>
                    <th title="Job/Activity description">Descr.</th>
                    <th title="Job/Activity type">Type</th>
                    <th title="Total duration of the job/activity">Duration</th>
                    <th title="Total chargeable units which can be invoiced/billed">Billable Units</th>
                    <th title="Actions">Actions</th>
                </thead>

                <tbody>

                    <?php

                    if ($jobs) {

                        foreach ($jobs as $job) {

                            $jobMeta     = get_post_meta($job->ID);
                            $jobID       = $job->ID;
                            $jobDateTime = date('j F Y, h:i:s', strtotime($jobMeta['date_time'][0]));
                            $client      = $jobMeta['client'][0];
                            $description = $jobMeta['description'][0];
                            $activity    = $jobMeta['activity'][0];
                            $duration    = $jobMeta['duration'][0] ?: 'N/A';
                            $units       = $jobMeta['units'][0] ?: 'N/A';

                            echo "<tr>";
                            echo "<td class='job-id'>{$jobID}</td>";
                            echo "<td>{$jobDateTime}</td>";
                            echo "<td>{$client}</td>";
                            echo "<td>{$description}</td>";
                            echo "<td>{$activity}</td>";
                            echo "<td>{$duration}</td>";
                            echo "<td>{$units}</td>";
                            echo "<td class='job-actions'><span class='job-btns'><button class='button' id='start-tracking-{$jobID}'>Start</button> <button id='stop-tracking-{$jobID}' class='button '>Stop</button></span> <span class='job-btns'><button class='button button-primary' id='save-job-{$jobID}'>Save</button> <button class='button button-danger' id='delete-job-{$jobID}'>Delete</button></span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td class='no-jobs' colspan='8'>No jobs/activities found.</td></tr>";
                    }

                    ?>

                </tbody>
            </table>
        </div>
    </div>

<?php
}
