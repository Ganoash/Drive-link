<?php

class User
{
    private string $username;
    private string $first_name;
    private string $last_name;
    private ?string $secondary_email;

    private array $membership = array();

    public function __construct($username, $first_name, $last_name, $secondary_email)
    {
        $this->username = $username;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->secondary_email = $secondary_email;
    }

    public function get_email() {
        return  $this->username . "@yeti.alpenclub.nl";
    }

    public function in_group($group) {
        array_push($this->membership, $group);
    }

    public function to_array($set_password = false) {

        $user_array = array(
            "primaryEmail" => $this->username . "@yeti.alpenclub.nl",
            "name" => array(
                "givenName" => $this->first_name,
                "familyName" => $this->last_name,
                "fullName" => $this->first_name . " " . $this->last_name
            ),
            "recoveryEmail" => $this->secondary_email,
        );

        if($set_password) {
            $user_array["password"] = sha1(wp_generate_password(15, true, true));
            $user_array["changePasswordAtNextLogin"] = true;
            $user_array["hashFunction"] = "SHA-1";
        }

        return $user_array;
    }

    public function to_html(): string
    {
        return "<div id='user_{$this->username}' class='item'>
                    <div class='within'><p>{$this->username}</p></div>
                    <div class='within'><p>{$this->first_name}</p></div>
                    <div class='within'><p>{$this->last_name}</p></div>
                    <div class='within'><p>{$this->secondary_email}</p></div>
                </div>";
    }

    public function equals($other): bool{
        if($other instanceof User) {
            return $this->get_email() == $other->get_email();
        }
        return false;
    }

}