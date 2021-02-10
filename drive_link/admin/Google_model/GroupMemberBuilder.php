
<?php

//TODO constructor en with functies
class GroupMemberBuilder
{
    private Group $group;
    private User $user;

    public function __construct() {}

    public function from_group($group) {
        $this->group = $group;
        return $this;
    }

    public function from_user($user) {
        $this->user = $user;
        return $this;
    }

    public function build() {
        return new GroupMember($this->user, $this->group);
    }

}