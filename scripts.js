// vars
const jobList = document.getElementById('job-list');
const addJobBtn = document.getElementById('add-job-btn');
const clearJobsBtn = document.getElementById('clear-jobs-btn');
const saveClientsBtn = document.getElementById('save-clients');
const clientSelect = document.getElementById('client-name');
const jobDescription = document.getElementById('job-description');
const jobActivity = document.getElementById('activity-type');
const addToClientsBtn = document.getElementById('add-client-btn');
const removeClientBtn = document.getElementById('remove-client');
const addActivity = document.getElementById('add-activity');
const remActivity = document.getElementById('rem-activity');
const saveActivities = document.getElementById('save-activities');

let duration = 0;
let interval;

// add new activity
addActivity.addEventListener('click', () => {
    const activityType = prompt('Enter new activity type:');
    if (activityType) {
        const option = document.createElement('option');
        option.value = activityType;
        option.textContent = activityType;
        jobActivity.appendChild(option);
    }
});

// remove activity
remActivity.addEventListener('click', () => {
    const selectedActivity = jobActivity.value;
    if (selectedActivity) {
        jobActivity.removeChild(jobActivity.querySelector(`option[value="${selectedActivity}"]`));
    }
});

// save activities
saveActivities.addEventListener('click', () => {

    const activities = Array.from(jobActivity.options).map(option => option.value);

    jQuery.ajax({
        url: jobTimeTracker.ajaxurl,
        type: 'POST',
        data: {
            action: 'jt_save_activities',
            nonce: jobTimeTracker.nonce_save_activities,
            activities: activities
        },
        success: function (response) {
            alert(response);
            location.reload();
        },
        error: function (error) {
            alert(error);
        }
    });
});

// enable/disable remove activity button
jobActivity.addEventListener('change', (event) => {

    if (event.target.value && event.target.value !== 'not-applicable') {
        remActivity.disabled = false;
    } else {
        remActivity.disabled = true;
    }
});

// add/publish new job
addJobBtn.addEventListener("click", () => {

    const clientName = clientSelect.value;
    const description = jobDescription.value;
    const activityType = jobActivity.value;

    if (!clientName) {
        alert('Please select a client.');
        return;
    }

    if (!activityType) {
        alert('Please select an activity type.');
        return;
    }

    if (!description) {
        alert('Please enter a description for this job/activity.');
        return;
    }

    jQuery.ajax({
        url: jobTimeTracker.ajaxurl,
        type: 'POST',
        data: {
            action: 'jt_add_job_activity',
            nonce: jobTimeTracker.nonce_add_job_activity,
            clientName: clientName,
            description: description,
            activityType: activityType
        },
        success: function (response) {
            alert(response);
            location.reload();
        },
        error: function (error) {
            alert(error);
        }
    });

})

// enable remove client button
clientSelect.addEventListener('change', (event) => {
    if (event.target.value && event.target.value !== 'not-applicable') {
        removeClientBtn.disabled = false;
    } else {
        removeClientBtn.disabled = true;
    }
});

// ---------------
// remove client
// ---------------
const removeClient = () => {
    const selectedClient = clientSelect.value;
    if (selectedClient) {
        clientSelect.removeChild(clientSelect.querySelector(`option[value="${selectedClient}"]`));
    }
};

removeClientBtn.addEventListener('click', removeClient);

// ---------------
// add to clients
// ---------------
const addToClients = () => {

    const clientName = prompt('Enter client name:');

    if (clientName) {
        const option = document.createElement('option');
        option.value = clientName;
        option.textContent = clientName;
        clientSelect.appendChild(option);
    }

};

addToClientsBtn.addEventListener('click', addToClients);

// ---------------
// save clients
// ---------------
const saveClients = () => {

    const clients = Array.from(clientSelect.options).map(option => option.value);

    jQuery.ajax({
        url: jobTimeTracker.ajaxurl,
        type: 'POST',
        data: {
            action: 'jt_save_clients',
            nonce: jobTimeTracker.nonce_save_clients,
            clients: clients
        },
        success: function (response) {
            alert(response);
            location.reload();
        },
        error: function (error) {
            alert(error);
        }
    });

}

saveClientsBtn.addEventListener('click', saveClients);

// --------------------
// start/stop tracking
// --------------------
const startBtns = document.querySelectorAll('button[id^="start-tracking-"]');

startBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        // start timer
        const timer = document.getElementById('timer');
        interval = setInterval(() => {
            duration++;
            timer.textContent = new Date(duration * 1000).toISOString().substr(11, 8);
        }, 1000);

    });
});

const stopBtns = document.querySelectorAll('button[id^="stop-tracking-"]');

stopBtns.forEach(btn => {
    btn.addEventListener('click', () => {

        const jobID = btn.id.split('-')[2];

        // stop timer
        clearInterval(interval);

        jQuery.ajax({
            url: jobTimeTracker.ajaxurl,
            type: 'POST',
            data: {
                action: 'jt_update_time_entries',
                nonce: jobTimeTracker.nonce_update_time_entries,
                jobID: jobID,
                duration: duration
            },
            success: function (response) {
                alert(response);
                location.reload();
            },
            error: function (error) {
                alert(error);
            }
        });

    });
});

// -----------
// delete job
// -----------
const deleteBtns = document.querySelectorAll('button[id^="delete-job-"]');

deleteBtns.forEach(btn => {
    btn.addEventListener('click', () => {

        const jobID = btn.id.split('-')[2];
        const confirmed = confirm('Are you sure you want to delete this job/activity?');

        if (confirmed) {
            jQuery.ajax({
                url: jobTimeTracker.ajaxurl,
                type: 'POST',
                data: {
                    action: 'jt_delete_job',
                    jobID: jobID,
                    nonce: jobTimeTracker.nonce_delete_job
                },
                success: function (response) {
                    alert(response);
                    location.reload();
                },
                error: function (error) {
                    alert(error);
                }
            });
        }
    });
});
