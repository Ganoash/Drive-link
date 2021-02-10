<?php
include_once ("database_functions.php");
include_once ("Google_connection/Google_Directory_Connection.php");
include_once ("Google_model/Group.php");
include_once("Google_model/GroupBuilder.php");
include_once ("Google_model/User.php");
include_once("Google_model/UserBuilder.php");
include_once ("Google_model/GroupMember.php");
include_once ("Google_model/GroupMemberBuilder.php");
include_once ("Google_model/parser/model_parser.php");

    function serve_admin() {
        $css = "<style>" . file_get_contents("CSS/drive_link.css", true) . "</style>";
        echo $css;

        $html = file_get_contents("index.html", true);
        echo $html;

        insert_js_users();

        if(isset($_GET["add_user"])) {
            show_users_per_group();
        }
    }

    function show_all_groups() {
        $con = Google_Directory_Connection::get_instance();
        $groups = $con->get_all_groups();
        echo "<div class='container'>";
        echo "<div class='item'>
                <div class='within'><p>groep email</p></div>
                <div class='within'><p>groep id</p></div>
                <div class='within'><p>groep naam</p></div>
                <div class='within'><p>groep beschrijving</p></div>
                </div>";
        foreach ($groups as $group) {
            echo $group->to_html();
        }



    }

    function show_all_users() {
        $con = Google_Directory_Connection::get_instance();
        $users = $con->get_all_users();

        echo "<div class='container'>";
        echo "<div class='item'>
                <div class='within'><p>yeti username</p></div>
                <div class='within'><p>voornaam</p></div>
                <div class='within'><p>achternaam</p></div>
                <div class='within'><p>eigen email</p></div>
                </div>";
        foreach ($users as $user) {
            echo $user->to_html();
        }
        echo "</div>";
    }

    function show_all_groupmembers() {
        $con = Google_Directory_Connection::get_instance();
        $members = $con->get_all_groupmembers();

        echo "<div class='container'>";
        echo "<div class='item'>
                <div class='within'><p>gebruiker email</p></div>
                <div class='within'><p>gebruiker groep</p></div>
                <div class='within'><p>rol</p></div>
                </div>";
        foreach ($members as $member) {
            echo $member->to_html();
        }
        echo "</div>";
    }

    function show_users_per_group() {
        $con = Google_Directory_Connection::get_instance();
        $groups = $con->get_all_groups();
        $members = $con->get_all_groupmembers();
        foreach ($groups as $group) {
            echo $group->to_html();
            $member_part = $group->get_members();
            foreach ($member_part as $member) {
                echo $member->get_user()->to_html();
            }
            echo "<br>";
        }
        echo "</div>";
    }

    function create_user_json() {
        $con = Google_Directory_Connection::get_instance();
        $google_users = $con->get_all_user_emails();
        $users = \google_link\execute_query("get_users_in_group.sql");
        $user_list = array();
        foreach ($users as $user) {
            if(in_array($user["user_login"] . "@yeti.alpenclub.nl", $google_users)) {
                echo "user has account: " . $user["voornaam"] . "<br>";
            } else {
                array_push($user_list, $user);
            }

        }

        echo "these users weren't found: ";
        var_dump($user_list);

    }

    function insert_js_users() {
        $content = \google_link\execute_query("get_all_users.sql");
        $script = file_get_contents("user_data.js", true);

        $html = '<datalist id="users">';

        foreach ($content as $item) {
            $html .= "<option value='{$item["display_name"]}'>";
        }

        $html .= '</datalist>';

        echo $html;

        echo '<script>' . str_replace("%user_data%", json_encode($content), $script) . '</script>';
    }