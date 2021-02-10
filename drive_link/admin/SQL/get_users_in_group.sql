SELECT users.ID, user_email, user_login, first_names.meta_value AS voornaam, last_names.meta_value AS achternaam
from wp_users as users
    JOIN (SELECT * FROM `wp_bp_groups_members`) as groups on groups.user_id = users.ID
    JOIN (SELECT meta_value, user_id FROM `wp_usermeta` WHERE `meta_key` LIKE 'first_name') AS first_names ON users.ID = first_names.user_id
    JOIN (SELECT meta_value, user_id FROM `wp_usermeta` WHERE `meta_key` LIKE 'last_name') AS last_names ON users.ID = last_names.user_id
GROUP BY users.ID