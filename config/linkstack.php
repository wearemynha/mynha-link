<?php

return [
    /*
    | Runtime options must be read through config(), never env(), outside the
    | config directory. This makes the application compatible with
    | `php artisan config:cache`.
    */
    'admin_email' => env('ADMIN_EMAIL'),
    'allow_custom_backgrounds' => (bool) env('ALLOW_CUSTOM_BACKGROUNDS', true),
    'allow_custom_code_in_themes' => (bool) env('ALLOW_CUSTOM_CODE_IN_THEMES', false),
    'allow_registration' => (bool) env('ALLOW_REGISTRATION', false),
    'allow_user_export' => (bool) env('ALLOW_USER_EXPORT', true),
    'allow_user_html' => (bool) env('ALLOW_USER_HTML', false),
    'allow_user_import' => (bool) env('ALLOW_USER_IMPORT', true),
    'custom_meta_tags' => (bool) env('CUSTOM_META_TAGS', false),
    'display_credit' => (bool) env('DISPLAY_CREDIT', true),
    'display_credit_footer' => (bool) env('DISPLAY_CREDIT_FOOTER', true),
    'display_footer' => (bool) env('DISPLAY_FOOTER', true),
    'display_footer_contact' => (bool) env('DISPLAY_FOOTER_CONTACT', true),
    'display_footer_home' => (bool) env('DISPLAY_FOOTER_HOME', true),
    'display_footer_privacy' => (bool) env('DISPLAY_FOOTER_PRIVACY', true),
    'display_footer_terms' => (bool) env('DISPLAY_FOOTER_TERMS', true),
    'enable_admin_bar' => (bool) env('ENABLE_ADMIN_BAR', true),
    'enable_admin_bar_users' => (bool) env('ENABLE_ADMIN_BAR_USERS', true),
    'enable_button_editor' => (bool) env('ENABLE_BUTTON_EDITOR', true),
    'enable_report_icon' => (bool) env('ENABLE_REPORT_ICON', true),
    'enable_social_login' => (bool) env('ENABLE_SOCIAL_LOGIN', false),
    'force_https' => (bool) env('FORCE_HTTPS', false),
    'force_route_https' => (bool) env('FORCE_ROUTE_HTTPS', false),
    'hide_verification_checkmark' => (bool) env('HIDE_VERIFICATION_CHECKMARK', false),
    'home_url' => env('HOME_URL', ''),
    'home_footer_link' => env('HOME_FOOTER_LINK', ''),
    'maintenance_mode_available' => true,
    'maintenance_mode' => (bool) env('MAINTENANCE_MODE', false),
    'manual_user_verification' => (bool) env('MANUAL_USER_VERIFICATION', false),
    'notify_events' => (bool) env('NOTIFY_EVENTS', false),
    'register_auth' => env('REGISTER_AUTH', 'auth'),
    'supported_domains' => env('SUPPORTED_DOMAINS', ''),
    'use_theme_preview_iframe' => (bool) env('USE_THEME_PREVIEW_IFRAME', true),

    'footer_titles' => [
        'contact' => env('TITLE_FOOTER_CONTACT', ''),
        'home' => env('TITLE_FOOTER_HOME', ''),
        'privacy' => env('TITLE_FOOTER_PRIVACY', ''),
        'terms' => env('TITLE_FOOTER_TERMS', ''),
    ],

    'single_user_mode' => (bool) env('SINGLE_USER_MODE', false),
    'disable_random_link_ids' => (bool) env('DISABLE_RANDOM_LINK_IDS', false),
    'disable_random_user_ids' => (bool) env('DISABLE_RANDOM_USER_IDS', false),
    'link_id_length' => (int) env('LINK_ID_LENGTH', 9),
    'user_id_length' => (int) env('USER_ID_LENGTH', 6),
    'user_cap' => env('USER_CAP'),
];
