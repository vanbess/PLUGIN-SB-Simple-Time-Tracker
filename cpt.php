<?php
/**
 * Custom post type for jobs
 */
add_action('init', function () {

    $labels = array(
        'name'               => 'Jobs',
        'singular_name'      => 'Job',
        'menu_name'          => 'Jobs',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Job',
        'edit'               => 'Edit',
        'edit_item'          => 'Edit Job',
        'new_item'           => 'New Job',
        'view'               => 'View',
        'view_item'          => 'View Job',
        'search_items'       => 'Search Jobs',
        'not_found'          => 'No jobs found',
        'not_found_in_trash' => 'No jobs found in Trash',
        'parent'             => 'Parent Job'
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'has_archive'        => false,
        'publicly_queryable' => true,
        'query_var'          => true,
        'rewrite'            => array('slug' => 'jobs'),
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'supports'           => array(
            'title',
            'editor',
            'author',
        ),
        'taxonomies'          => array(),
        'menu_position'       => 5,
        'exclude_from_search' => true
    );

    register_post_type('jobs', $args);
});
?>