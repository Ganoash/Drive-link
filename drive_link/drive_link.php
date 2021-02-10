<?php
include_once('admin/user_api.php');
include_once('admin/Admin_AP/Access_point.php');


add_action('admin_init', 'link_set_roles');
add_action('set_yeti_tools_menu', 'link_admin_menu');
add_action('rest_api_init', 'api_init');
add_action('admin_enqueue_scripts', 'enqueue_script');
add_action('wp_print_scripts', "deregister_script");





function link_admin_menu() {

    //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
    //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);


    add_submenu_page(
        'yeti_tools', // parent slug
        'Drive Link', // page title
        'Drive Link', // menu title
        'webcie_yeti_link', //cap
        'webcie_yeti_link', //slug
        'serve_admin' // function
    );
}



function link_set_roles() {
    $custom_cap = 'webcie_yeti_link';
    $grant      = false;
    $roles = get_editable_roles();

    foreach ($GLOBALS['wp_roles']->role_objects as $key => $role) {
        if($key == 'administrator' || $key == 'bestuur') {
            $role->add_cap($custom_cap, true);
        }
        if (isset($roles[$key]) && !$role->has_cap($custom_cap)) {
            $role->add_cap($custom_cap, $grant);
        }
    }
}

function api_init() {

    register_rest_route("yeti_drive_link/v1", "/directory/(?P<req>\d+)", array(
            "methods" => ["GET", "POST"],
            "callback" => "api_delegate",
            "permission_callback" => function() {
                return current_user_can("webcie_yeti_link");
            }
    ));

}

function enqueue_script() {
    wp_enqueue_script( 'yeti_dl_admin', '/wp-content/plugins/yeti_tools/drive_link/admin/Client/API_manager.js', ['jquery'], '1.0.5', true);
    wp_localize_script('yeti_dl_admin', 'ajax_var', array(
        "url" => esc_url_raw("https://yeti.alpenclub.nl/wp-json/yeti_drive_link/v1/directory/"),
        "nonce" => wp_create_nonce('wp_rest')
    ));
}

/**
 * function to make sure script only gets loaded on drive link page
 */
function deregister_script() {
    if(isset($_GET["page"]) && strcmp($_GET["page"], "webcie_yeti_link")) {
        wp_deregister_script('yeti_dl_admin');
    }
}