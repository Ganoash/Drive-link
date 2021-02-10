<?php

function search_group_by_email($groups, $email): ?Group {
    foreach ($groups as $group) {
        if($group->get_email() == $email) {
            return $group;
        }
    }
    return null;
}

function search_user_email($users, $email): ?User{
    foreach ($users as $user) {
        if($user->get_email() == $email){
            return $user;
        }

    }
    return null;
}