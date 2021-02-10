<?php

class Group
{
    private string $group_email;

    private string $name;
    private string $description;

    private array $members = array();

    public function __construct($group_email, $name, $description) {
        $this->group_email = $group_email;
        $this->name = $name;
        $this->description = $description;
    }

    public function get_email(): string {
        return $this->group_email;
    }

    public function with_member($member) {
        array_push($this->members, $member);
    }

    public function get_members() {
        return $this->members;
    }

    public function to_array(): array
    {
        $group = array(
            "email" => $this->group_email
        );

        if($this->name) {
            $group["name"] = $this->name;
        }

        if($this->description) {
            $group["description"] = $this->description;
        }

        return $group;
    }


    public function to_html(): string
    {
        return "<div id='group_{$this->group_email}' class='item'>
                    <div class='within'><p>{$this->group_email}</p></div>
                    <div class='within'><p>{$this->name}</p></div>
                    <div class='within'><p>{$this->description}</p></div>
                </div>";
    }

    public function equals($other): bool{
        if($other instanceof Group) {
            return $this->get_email() == $other->get_email();
        }
        return false;
    }



}