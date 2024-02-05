<?php
/*
*Triger this file on plugin uninstall
*/

if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

//clear Database stored data related to post
// $inteviews = gwt_posts(array('post_type' => 'interview', 'numberposts' => -1));
// foreach ($interviews as $interview) {
//     wp_delete_post($interview->ID, true);
// }

global $wpdb;
$wpdb->query("DELETE FROM wp_posts WHERE post_type ='interview'");
$wpdb->query("DELETE FROM wp_postmets WHERE post_id NOT IN (SELECT id FROM wp_posts)");
$wpdb->query("DELETE FROM wp_term_relationships WHERE object_id NOT IN (SELECT id FROM wp_posts)");
