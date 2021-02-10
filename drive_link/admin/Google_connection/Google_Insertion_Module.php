<?php


class Google_Insertion_module
{
    private Google_Service_Directory $admin;

    public function __construct($admin) {
        $this->admin = $admin;
    }

    public function member($google_member, $group_email) {
        $this->admin->members->insert($group_email, $google_member);
    }

    public function user($google_user) {
        $this->admin->users->insert($google_user);
    }

    public function group($google_group) {
        $this->admin->groups->insert($google_group);
    }

    public function user_update($user_email, $google_user) {
        $this->admin->users->update($user_email, $google_user);
    }

    public function member_removal($google_user, $group_email) {
        $this->admin->members->delete($group_email, $google_user);
    }
}