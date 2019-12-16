<?php
    add_action( 'wp_enqueue_scripts', 'he_child_enqueue_styles' );
    function he_child_enqueue_styles() {
        $parent_style = 'parent-style';

        wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
        wp_enqueue_style( 'child-style',
            get_stylesheet_directory_uri() . '/style.css',
            array( $parent_style ),
            wp_get_theme()->get('Version')
        );
    }

    if ( is_admin() ) {
        require get_theme_file_path(). '/includes/admin-functions.php';
    }

    function check_user_approval($username) {
        global $wpdb;

        if ( ! username_exists( $username ) ) {
            return;
        }
        $user = get_user_by( 'login', $username );
        $user_status = get_user_meta($user->ID, 'account_status', true);

        if(!empty($user_status) && $user_status == 'awaiting_admin_review') {
            add_action('wp_head', 'alert_empty_survey');
            wp_clear_auth_cookie();
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            $survey_page_id = get_option('registration_survey_page');
            wp_safe_redirect( empty($survey_page_id) ? get_home_url() : get_permalink($survey_page_id));
            exit();
        }

        echo "<pre>";
        var_dump($user_status);
        var_dump(get_option('registration_survey_page_url'));
        var_dump(get_home_url());
        echo "</pre>";
        exit;
    }

    add_action( 'wp_login' , 'check_user_approval');


    function my_function(){
        var_dump('lol');
    }

    add_action( "template_redirect", "my_function" );