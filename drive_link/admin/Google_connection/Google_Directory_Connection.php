<?php
include_once("Buffered_Data_List.php");
include_once ("Buffered_Model_List.php");
include_once ("Google_Insertion_Module.php");

class Google_Directory_Connection
{
    private static ?Google_Directory_Connection $con = null;
    private Buffered_Data_List $data;
    private Buffered_Model_List $model;
    private Google_Insertion_Module $insert;



    private function __construct($scopes) {
        $admin = $this->initialize_admin_connection($scopes);
        $this->data = new Buffered_Data_List($admin);
        $this->insert = new Google_Insertion_Module($admin);
        $this->model = new Buffered_Model_List();
    }

    private function initialize_admin_connection($scopes) {
        global $GOOGLE_AUTH;
        $client = new Google_Client();
        $client -> setApplicationName("Yeti user control");
        $client -> setAuthConfig($GOOGLE_AUTH);
        $client -> setScopes($scopes);
        $client -> setSubject("mbahaarman@yeti.alpenclub.nl");

        return new Google_Service_Directory($client);
    }

    public static function get_instance() : Google_Directory_Connection {
        if(!Google_Directory_Connection::$con) {
            Google_Directory_Connection::$con = new Google_Directory_Connection(["https://www.googleapis.com/auth/admin.directory.user", "https://www.googleapis.com/auth/admin.directory.group"]);
        }

        return Google_Directory_Connection::$con;
    }

    public function get_all_user_emails() {
        $users_in_admin = $this->data->get_user_list()["modelData"]["users"];
        $user_emails = [];

        foreach ($users_in_admin as $user) {
            array_push($user_emails, $user["primaryEmail"]);
        }

        return $user_emails;
    }

    public function get_all_users() {
        /**
         * check wether the user model is already buffered
         */
        $buffered_users = $this->model->get_list("user");
        if($buffered_users) {
            return $buffered_users;
        }


        $users_in_admin = $this->data->get_user_list()["modelData"]["users"];
        $users = array();

        foreach ($users_in_admin as $item) {
            $builder = new UserBuilder();
            $builder
                ->with_firstname($item["name"]["givenName"])
                ->with_lastname($item["name"]["familyName"])
                ->with_username(explode("@", $item["primaryEmail"])[0]);
            if(isset($item["recoveryEmail"])) {
                $builder->with_secondary_email($item["recoveryEmail"]);
            }
            array_push($users, $builder->build());
        }

        //buffer user model
        $this->model->buffer("user", $users);

        return $users;
    }

    public function get_all_groups() {
        /**
         * check wether the group model is already buffered
         */
        $buffered_groups = $this->model->get_list("group");
        if($buffered_groups) {
            return $buffered_groups;
        }

        $groups_in_admin = $this->data->get_group_list();
        $groups = array();

        foreach ($groups_in_admin as $item) {
            $builder = new GroupBuilder();
            $builder->with_name($item["name"])
                ->with_description($item["description"])
                ->with_email($item["email"]);
            array_push($groups, $builder->build());
        }
        //buffer group model
        $this->model->buffer("group", $groups);

        return $groups;
    }

    public function get_all_groupmembers() {
        /**
         * check wether the member model is already buffered
         */
        $buffered_members = $this->model->get_list("member");
        if($buffered_members) {
            return $buffered_members;
        }

        //get group and user lists
        $groups = $this->get_all_groups();
        $users = $this->get_all_users();
        $members_in_admin = $this->data->get_member_list();

        $members = array();
        foreach ($members_in_admin as $key => $member) {
            $group = search_group_by_email($groups, $key);
            foreach ($member["members"] as $user_data) {
                $user = search_user_email($users, $user_data["email"]);
                if(!$user) {
                    continue;
                }

                $m = new GroupMemberBuilder();
                $m->from_user($user)
                    ->from_group($group);
                array_push($members, $m->build());
            }
        }


        //buffer member model
        $this->model->buffer("member", $members);

        return $members;
    }

    public function add_groupmember($group, $user): bool{
        $google_user = new Google_Service_Directory_Member(array(
                "email" => $user->get_email()
            ));

        try {
            $this->insert->member($google_user, $group->get_email());
        } catch (Exception $e) {
            echo "er is iets fout gegaan bij het toevoegen van gebruiker {$user->get_email()} aan groep {$group->get_email()}";
            var_dump($e);
            return false;
        }
        return true;
    }

    public function add_user($user): bool{
        $google_user = new Google_Service_Directory_User($user->to_array(true));

        try {
            $this->insert->user($google_user);
        } catch (Exception $e) {
            echo "er is iets fout gegaan bij het toevoegen van gebruiker {$user->get_email()} aan de database";
            var_dump($e);
            var_dump($user);
            return false;
        }

        return true;
    }

    public function add_group($group): bool {
        $google_group = new Google_Service_Directory_Group($group->to_array());

        try {
            $this->insert->group($google_group);
        } catch (Exception $e) {
            echo "er is iets fout gegaan bij het toevoegen van groep {$group->get_email()} aan de database";
            var_dump($e);
            return false;
        }
        $this->model->add_group($group);
        return true;
    }

    public function update_user($user): bool {
        $user_email = $user->get_email();
        $google_user = new Google_Service_Directory_User($user->to_array());

        try {
            $this->insert->user_update($user_email, $google_user);
        } catch (Exception $e) {
            echo "er is iets fout gegaan bij het updaten van gebruiker {$user->get_email()} aan de database";
            var_dump($e);
            return false;
        }
        return true;
    }

    public function remove_member($group, $user) {
        try {
            $this->insert->member_removal($user->get_email(), $group->get_email());
        } catch (Exception $e) {
            echo "er is iets fout gegaan bij het verwijderen van gebruiker {$user->get_email()} aan groep {$group->get_email()}";
            var_dump($e);
            return false;
        }
        return true;
    }

}