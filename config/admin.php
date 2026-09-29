<?php

return [
    // Set these to the actual roleid values in your users table.
    'administrator' => (int) env('ADMIN_ROLE_ADMINISTRATOR', 1),
    'standard' => (int) env('ADMIN_ROLE_STANDARD', 2),
    'editor' => (int) env('ADMIN_ROLE_EDITOR', 3),
];