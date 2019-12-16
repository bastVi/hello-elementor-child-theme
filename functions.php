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
    }

    add_action( 'wp_login' , 'check_user_approval');


    function manageNonApprovedUser($request){
        $user_id = get_current_user_id();
        if(empty($user_id)) return;
        $page_name = $request->request;
        $page = get_page_by_title($page_name);
        $survey_page = get_post(get_option('registration_survey_page'));
        $user_status = get_user_meta($user_id, 'account_status', true);
        $nav = wp_get_nav_menu_items( 'primary');
        if(!empty($user_status) && $user_status == 'awaiting_admin_review') {

            echo "<pre>";
            var_dump($page);
            var_dump($nav);
            var_dump($request);
            var_dump(get_nav_menu_locations());
            var_dump(get_current_user_id());
            echo "</pre>";
        }
    }

    add_action( "parse_request", "manageNonApprovedUser" );