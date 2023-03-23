<?php

function get_current_user_roles()
{
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $roles = (array) $user->roles;
        return $roles; // This will returns an array
    } else {
        return array();
    }
}
