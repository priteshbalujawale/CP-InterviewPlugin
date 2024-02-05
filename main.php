<?php

/**
 * Plugin Name: CP InterviewPlugin
 * Description: Custom post type Interview Plugin.
 * Version: 1.0
 * Author: Pritesh
 */

if (!defined('ABSPATH')) {
    header("Location:/");
    die;
}


class InterviewPlugin
{
    public function __construct()
    {
        add_action('init', array($this, 'register_custom_post_type'));
        add_action('init', array($this, 'register_taxonomy'));
        add_action('admin_menu', array($this, 'add_submenu_page'));
        add_action('init', array($this, 'register_shortcodes'));
        add_action('init', array($this, 'enqueue_custom_styles'));
    }

    function activate()
    {
        $this->register_custom_post_type();
        flush_rewrite_rules();
    }
    function deactivate()
    {
    }




    public function register_custom_post_type()
    {
        $labels = array(
            'name'          => 'Interviews',
            'singular_name' => 'Interview',
            'add_new'       => 'Add New',
            'add_new_item'  => 'Add New Interview',
            'edit_item'     => 'Edit Interview',
            'new_item'      => 'New Interview',
            'view_item'     => 'View Interview',
            'search_items'  => 'Search Interviews',
            'not_found'     => 'No interviews found',
            'not_found_in_trash' => 'No interviews found in trash',
        );

        $args = array(
            'labels'        => $labels,
            'public'        => true,
            'supports'      => array('title', 'editor', 'thumbnail', 'excerpt'),
            'has_archive'   => true,
            'rewrite'       => array('slug' => 'interview'),
        );

        register_post_type('interview', $args);
    }

    // Langauge Taxanomy
    public function register_taxonomy()
    {
        $labels = array(
            'name'          => 'Languages',
            'singular_name' => 'Language',
            'search_items'  => 'Search Languages',
            'all_items'     => 'All Languages',
            'edit_item'     => 'Edit Language',
            'update_item'   => 'Update Language',
            'add_new_item'  => 'Add New Language',
            'new_item_name' => 'New Language Name',
            'menu_name'     => 'Languages',
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'language'),
        );

        register_taxonomy('language', 'interview', $args);
    }

    // submenu to display api data
    public function add_submenu_page()
    {
        add_submenu_page(
            'edit.php?post_type=interview',
            'Task',
            'Task',
            'manage_options',
            'interview_api_data',
            array($this, 'display_title_body')
        );
    }



    // fetching the data
    public function fetch_external_data()
    {
        $url = "https://jsonplaceholder.typicode.com/posts";

        $ch = curl_init($url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        } else {
            // Save the fetched data
            update_option('curl_fetched_data', $response);
        }

        // Close cURL session
        curl_close($ch);
    }


    public function display_title_body()
    {
        $this->fetch_external_data();

        // Display the fetched data
        $api_data = get_option('curl_fetched_data');
        $api_data_array = json_decode($api_data, true);

?>
        <div class="wrap">
            <h2>Tasks</h2>
            <strong>Task 1: </strong><span> To display all Id and Body</span>
            <p>ShortCode: [display_all_id_body]</p><br />
            <strong>Task 2: </strong><span> To display id by title</span>
            <p>ShortCode: [display_id_by_yourtitle]</p><br />
            <strong>Task 1: </strong><span> To display all titles in ascending order</span>
            <p>ShortCode: [display_all_titles_asc]</p><br />

            <?php if ($api_data_array) : ?>
                <!-- fetched the data from curl display to admin panel inside task submenu -->
                <table table id="myTable" class="display">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Body</th>
                            <th>Short Code to show id by title</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($api_data_array as $item) : ?>
                            <tr>
                                <td><?php echo esc_html($item['id']); ?></td>
                                <td><?php echo esc_html($item['body']); ?></td>
                                <td>[display_id_by_<?php echo (str_replace(" ", "_", $item['title'])); ?>]</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <script>
                    jQuery(document).ready(function($) {
                        let table = $('#myTable').DataTable();
                    });
                </script>
            <?php else : ?>
                <p>No API data available.</p>
            <?php endif; ?>
        </div>

<?php
    }




    public function register_shortcodes()
    {
        // Register shortcodes
        $api_data = get_option('curl_fetched_data');
        $api_data_array = json_decode($api_data, true);

        if ($api_data_array) {
            // display body content by id
            foreach ($api_data_array as $item) {
                add_shortcode('display_id_' . $item['id'], function () use ($item) {
                    $output = esc_html($item['id']) . ": " . esc_html($item['body']);
                    return $output;
                });
            }

            // display id content by title

            foreach ($api_data_array as $item) {
                add_shortcode('display_id_by_' . str_replace(" ", "_", $item['title']), function () use ($item) {
                    $output = '<p class="interview_id_by_title">' . esc_html($item['id']) . '</p>';
                    return $output;
                });
            }

            // display all ids and body's contnent
            add_shortcode('display_all_id_body', function () use ($api_data_array) {
                foreach ($api_data_array as $item) {
                    $output .= '<p class="interview_id_body_content"> <span class="interview_id_content">' . esc_html($item['id']) . '</span> <span class="interview_body_content">' . esc_html($item['body']) . '</span></p>';
                }
                return $output;
            });

            // display all title in ascending order
            add_shortcode(
                'display_all_titles_asc',
                function () use ($api_data_array) {
                    $formatted_titles = array();
                    foreach ($api_data_array as $item) {
                        $formatted_titles[] = '<p class="interview_title">' . esc_html($item['title']) . ',</p>';
                    }
                    sort($formatted_titles);
                    return implode('', $formatted_titles);
                }
            );
        }
    }

    public function enqueue_custom_styles()
    {

        wp_enqueue_style('datatable-style', 'https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css');

        wp_enqueue_script('datatable-script', 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js', array('jquery'), '', true);
    }
}

if (class_exists('InterviewPlugin')) {
    $interview_plugin = new InterviewPlugin();
}
register_activation_hook(__FILE__, array($interview_plugin, 'activate'));
register_activation_hook(__FILE__, array($interview_plugin, 'activate'));
