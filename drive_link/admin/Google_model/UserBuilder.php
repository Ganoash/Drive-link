<?php

class UserBuilder
{
    private string $username;
    private string $first_name;
    private string $last_name;
    private ?string $secondary_email = null;

    public function with_username($username): UserBuilder
    {
        $this->username = $username;
        return $this;
    }

    public function with_firstname($first_name): UserBuilder
    {
        $this->first_name = $first_name;
        return $this;
    }

    public function with_lastname($last_name): UserBuilder
    {
        $this->last_name = $last_name;
        return $this;
    }

    public function with_secondary_email($secondary_email): UserBuilder
    {
        $this->secondary_email = $secondary_email;
        return $this;
    }

    public function build() : User{
        return new User($this->username, $this->first_name, $this->last_name, $this->secondary_email);
    }


}