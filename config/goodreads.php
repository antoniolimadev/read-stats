<?php
return [
    'wait_time' => 1, // seconds
    'refresh_data_rate' => 12, // hours
    'storage' => [
        'storage_folder' => 'userdata\\',
        'xml_user_info' => '-user-info',
        'xml_shelf_read' => '-shelf-read-',
        'xml_extension' => '.xml',
    ],
    'status' => [
        'profile_valid' => 40,
        'profile_absent' => 41,
        'profile_private' => 43,
        'profile_not_found' => 44,
        'profile_no_data' => 45,
        'request_failed' => 46,
    ],
    'strings' => [
        'profile_private_message' => 'This profile is currently private.',
        'profile_not_found_message' => 'Profile not found.',
        'profile_no_books_read' => 'This user hasn\'t added any book yet.',
        'request_failed' => 'Request failed. Try again in a few seconds.',
    ],
];
