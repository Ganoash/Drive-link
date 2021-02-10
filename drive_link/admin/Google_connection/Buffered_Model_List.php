<?php


class Buffered_Model_List
{
    private ?array $user_list = null;
    private ?array $group_list = null;
    private ?array $member_list = null;

    public function buffer($type, $data) {
        switch ($type) {
            case "user":
                $this->user_list= $data;
                break;
            case "group":
                $this->group_list = $data;
                break;
            case "member":
                $this->member_list = $data;
                break;
            default:
                break;
        }
    }

    public function get_list($type): ?array {
        switch ($type) {
            case "user":
                return $this->user_list;
            case "group":
                return $this->group_list;
            case "member":
                return $this->member_list;
        }
    }

    public function add_user($user) {
        if($this->user_list) {
            array_push($this->user_list, $user);
        }
    }

    public function add_member($member) {
        if($this->member_list) {
            array_push($this->member_list, $member);
        }
    }

    public function add_group($group) {
        if($this->group_list) {
            array_push($this->group_list, $group);
        }
    }

}