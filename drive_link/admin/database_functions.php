<?php
namespace google_link;

function execute_query($query_location, $args=false) {
    global $wpdb;
    $query = file_get_contents("SQL/".$query_location, true);

    if($args) {
        $query= $wpdb->prepare($query, $args);
    }

    return $wpdb->get_results($query, ARRAY_A);
}

function update_query($query_location, $args=false) {
    global $wpdb;
    $query = file_get_contents("SQL/".$query_location, true);

    if($args) {
        $query= $wpdb->prepare($query, $args);
    }

    $wpdb->query($query, ARRAY_A);
}