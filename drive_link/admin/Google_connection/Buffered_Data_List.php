<?php

/**
 * Class Buffered_List class keeping the userlist for the Google_Directory_Connection class. Keeps the userlist buffered during a session.
 *
 */
class Buffered_Data_List
{
    private ?Object $user_list = null;
    private ?Object $group_list = null;
    private ?array $member_list = null;
    private Google_Service_Directory $admin;

    public function __construct($admin) {
        $this->admin = $admin;
    }

    private function buffer($type) {
        switch ($type) {
            case "user":
                $this->user_list  = $this->admin->users->listUsers(array(
                    "domain" => "yeti.alpenclub.nl"
                ));
                break;
            case "group":
                $this->group_list = $this->admin->groups->listGroups(array(
                        "domain" => "yeti.alpenclub.nl"
                    )
                );
                break;
            case "member":
                $this->buffer_members();
                break;
            default:
                break;
        }
    }

    private function buffer_members(){
        $groups = $this->get_group_list();
        $members = array();

        foreach($groups as $group) {
            $members[$group["email"]] = $this->admin->members->listMembers($group["id"]);
        }

        $this->member_list = $members;
    }

    public function get_user_list() {
        if(!$this->user_list) {
            $this->buffer("user");
        }
        return $this->user_list;
    }

    public function get_group_list() {
        if(!$this->group_list) {
            $this->buffer("group");
        }
        return $this->group_list;
    }

    public function get_member_list() {
        if(!$this->member_list) {
            $this->buffer("member");
        }
        return $this->member_list;
    }


}