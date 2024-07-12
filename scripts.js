document.addEventListener('DOMContentLoaded', () => {

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
            
            // update timer every second
            interval = setInterval(() => {
                duration++;
                timer.textContent = new Date(duration * 1000).toISOString().substr(11, 8);
            }, 1000);

            // disable all start buttons
            startBtns.forEach(btn => btn.disabled = true);

            // disable all stop buttons except the closest one
            const stopBtn = btn.nextElementSibling;
            stopBtns.forEach(btn => btn.disabled = true);
            stopBtn.disabled = false;


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

    // --------------------------
    // Update job/activity units
    // --------------------------
    document.querySelectorAll('.units-input').forEach(input => {

        let userConfirmed;

        input.addEventListener('click', () => {
            userConfirmed = false;
            input.removeAttribute('readonly');
            input.parentElement.querySelector('.jt-update-prompt').style.display = 'block';
        });

        input.addEventListener('keypress', (event) => {

            if (event.key === 'Enter') {

                userConfirmed = confirm('Are you sure you want to update the units for this job/activity?');

                if (userConfirmed) {

                    input.setAttribute('readonly', true);

                    const jobID = input.getAttribute('job-id');
                    const units = input.value;

                    jQuery.ajax({
                        url: jobTimeTracker.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'jt_update_units',
                            nonce: jobTimeTracker.nonce_update_units,
                            jobID: jobID,
                            units: units
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

            }
        })

    })

    // -----------------------------
    // Update job/activity duration
    // -----------------------------
    document.querySelectorAll('.duration-input').forEach(input => {

        let userConfirmed;

        input.addEventListener('click', () => {
            userConfirmed = false;
            input.removeAttribute('readonly');
            input.parentElement.querySelector('.jt-update-prompt').style.display = 'block';
        });

        input.addEventListener('keypress', (event) => {

            if (event.key === 'Enter') {

                userConfirmed = confirm('Are you sure you want to update the duration for this job/activity?');

                if (userConfirmed) {

                    const jobID = input.getAttribute('job-id');
                    duration = input.value.replace(/(\d+h)(\d+m)(\d+s)/, '$1 $2 $3');

                    jQuery.ajax({
                        url: jobTimeTracker.ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'jt_update_duration',
                            nonce: jobTimeTracker.nonce_update_duration,
                            jobID: jobID,
                            duration: duration
                        },
                        success: function (response) {
                            console.log(response); return;

                            alert(response);
                            location.reload();
                        },
                        error: function (error) {
                            alert(error);
                        }
                    });
                }
            }
        });


    })


    console.log('all loaded');
});