SELECT user_login, user_email, display_name, first_names.meta_value as voornaam, last_names.meta_value as achternaam
FROM wp_users as users
         JOIN (SELECT meta_value, user_id FROM `wp_usermeta` WHERE `meta_key` LIKE 'first_name')
             AS first_names ON users.ID = first_names.user_id
         JOIN (SELECT meta_value, user_id FROM `wp_usermeta` WHERE `meta_key` LIKE 'last_name')
             AS last_names ON users.ID = last_names.user_id
