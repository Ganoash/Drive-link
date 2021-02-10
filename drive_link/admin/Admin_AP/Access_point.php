<?php




function api_delegate($request) {
    try {
        switch ($request["req"]) {
            case 1:
                return user_json();
            case 2:
                return group_json();
            case 3:
                return members_json();
            case 4:
                return add_user_to_google();
            case 5:
                return add_member();
            case 6:
                return remove_member();

        }
    } catch(Exception $e) {
        return $e->getTrace();
    }
}

function user_json() : array {
    $con = Google_Directory_Connection::get_instance();
    $users = $con->get_all_users();
    $users_json = array();

    foreach ($users as $user) {
        array_push($users_json, $user->to_array());
    }

    return $users_json;
}

function group_json() : array {
    $con = Google_Directory_Connection::get_instance();
    $groups = $con->get_all_groups();
    $group_json = array();

    foreach ($groups as $group) {
        array_push($group_json, $group->to_array());
    }

    return $group_json;
}

function members_json() : array {
    $con = Google_Directory_Connection::get_instance();
    $memberships = $con->get_all_groupmembers();
    $membership_json = array();
    $users_json = user_json();
    $groups_json = group_json();


    foreach ($memberships as $membership) {
        array_push($membership_json, $membership->to_array());
    }

    return [$users_json, $groups_json, $membership_json];
}

function add_user_to_google() : bool {
    $user_builder = new UserBuilder();
    $user_builder->with_firstname($_POST["voornaam"])
        ->with_lastname($_POST["achternaam"])
        ->with_secondary_email($_POST["email"])
        ->with_username($_POST["gebruikersnaam"]);

    $con = Google_Directory_Connection::get_instance();
    $con->add_user($user_builder->build());
    return true;
}

function add_member() : bool
{
    if(!(isset($_POST["user_email"]) && isset($_POST["group_email"]))) throw new Exception("post parameters invalid", 400);
    $con = Google_Directory_Connection::get_instance();
    $all_users = $con->get_all_users();
    $all_groups = $con->get_all_groups();
    $user = search_user_email($all_users, $_POST["user_email"]);
    $group = search_group_by_email($all_groups, $_POST["group_email"]);

    if(!($user && $group)) {
        throw new Exception("post parameters invalid", 400);
    }

    $con->add_groupmember($group, $user);

    return true;
}

function remove_member() : bool
{
    if(!(isset($_POST["user_email"]) && isset($_POST["group_email"]))) throw new WP_Error("post parameters invalid", __('user_email or group_email not set'), array('status' => 400));
    $con = Google_Directory_Connection::get_instance();
    $all_users = $con->get_all_users();
    $all_groups = $con->get_all_groups();
    $user = search_user_email($all_users, $_POST["user_email"]);
    $group = search_group_by_email($all_groups, $_POST["group_email"]);

    if(!($user && $group)) {
        throw new Exception("post parameters invalid", 400);
    }

    $con->remove_member($group, $user);

    return true;
}