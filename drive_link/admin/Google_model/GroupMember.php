<?php

//TODO constructor en to_array en to_html functies
class GroupMember
{
    private User $user;
    private Group $group;

    public function __construct($user, $group)
    {
        $this->user = $user;
        $this->group = $group;
        $user->in_group($this);
        $group->with_member($this);
    }

    public function from_group($group) {
        return $this->group->equals($group);
    }

    public function is_user($user) {
        return $this->user->equals($user);
    }

    public function get_group() {
        return $this->group;
    }

    public function get_user() {
        return $this->user;
    }

    public function to_array() {
        return array(
            "user" => $this->user->to_array(),
            "group" => $this->group->to_array()
        );
    }

    public function to_html() {
        return "<div class='item'>
                    <div class='within'><p>{$this->user->get_email()}</p></div>
                    <div class='within'><p>{$this->group->get_email()}</p></div>
                    <div class='within'><p>{$this->role}</p></div>
                </div>";
    }


}