<?php

class GroupBuilder
{
    private string $group_email;

    private ?string $name;
    private ?string $description;

    public function __construct() {}

    public function with_email($group_email) {
        $this->group_email = $group_email;
    }

    public function with_name($name): GroupBuilder
    {
        $this->name = $name;
        return $this;
    }

    public function with_description($description): GroupBuilder
    {
        $this->description = $description;
        return $this;
    }

    public function build(): Group
    {
        return new Group($this->group_email, $this->name, $this->description);
    }

}