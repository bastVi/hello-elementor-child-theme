<?php
    add_action('wp_enqueue_scripts', 'he_child_enqueue_styles');
    function he_child_enqueue_styles() {
        $parent_style = 'parent-style';

        wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
        wp_enqueue_style(
            'child-style',
            get_stylesheet_directory_uri() . '/style.css',
            array($parent_style),
            wp_get_theme()->get('Version')
        );
    }

    if (is_admin()) {
        require get_theme_file_path() . '/includes/admin-functions.php';
    }

    function manageNotApprovedLogin($username) {
        global $wpdb;
        if (!username_exists($username)) {
            return;
        }
        $user = get_user_by('login', $username);
        $user_status = get_user_meta($user->ID, 'account_status', true);

        if (!empty($user_status) && $user_status == 'awaiting_admin_review') {
            if(awaitingUserSurvey($user->ID)) {
                add_action('wp_head', 'alert_empty_survey');
                wp_clear_auth_cookie();
                wp_set_current_user($user->ID);
                wp_set_auth_cookie($user->ID);
                $survey_page_id = get_option('registration_survey_page');
                wp_safe_redirect(empty($survey_page_id) ? get_home_url() : get_permalink($survey_page_id));
                exit();
            } else {
                wp_safe_redirect(get_home_url());
            }
        } else if($user_status == 'approved'){
            $after_login_page_id = get_option('approved_login_redirection');
            wp_safe_redirect(empty($after_login_page_id) ? get_home_url() : get_permalink($after_login_page_id));
            exit();
        }
    }

    function awaitingUserSurvey($user_id) {
        if(!defined('FLUENTFORM')) return false;
        global $wpdb;
        $form = get_option('registration_survey_form');
        $res = $wpdb->get_col( "SELECT * FROM {$wpdb->prefix}fluentform_submissions WHERE user_id = {$user_id} AND form_id = {$form};");
        return empty($res);
    }

    add_action('wp_login', 'manageNotApprovedLogin');

    function getNavRestrictions() {
        $menu_restriction_data = array();
        $menus = get_posts('post_type=nav_menu_item&numberposts=-1');

        foreach ($menus as $data) {
            $_nav_roles_meta = get_post_meta($data->ID, 'menu-item-um_nav_roles', true);

            $um_nav_roles = array();
            if ($_nav_roles_meta) {
                foreach ($_nav_roles_meta as $key => $value) {
                    if (is_int($key)) {
                        $um_nav_roles[] = $value;
                    }
                }
            }

            $menu_restriction_data[$data->ID] = array(
                'um_nav_public' => get_post_meta($data->ID, 'menu-item-um_nav_public', true),
                'um_nav_roles' => $um_nav_roles,
            );
        }

        return $menu_restriction_data;
    }


    function manageNonApprovedUserNav($request) {
        $user_id = get_current_user_id();
        if (empty($user_id)) {
            add_filter( 'body_class', function( $classes ) {
                return array_merge( $classes, array( 'no-user-logged' ));
            } );
            return $request;
        }
        /* Check if UltimateMember Plugin is enabled*/
        if (!isset($GLOBALS['ultimatemember']) || !function_exists('UM')) {
            return $request;
        }

        $page_name = $request->request;
        $page = get_page_by_title($page_name);
        $survey_page = get_post(get_option('registration_survey_page'));
        $user_status = get_user_meta($user_id, 'account_status', true);
        $nav_item_restriction = getNavRestrictions();

        if (!empty($user_status) && $user_status == 'awaiting_admin_review') {
            add_filter( 'body_class', function( $classes ) {
                return array_merge( $classes, array( 'awaiting-admin-review' ));
            } );

            if(awaitingUserSurvey($user_id)) {
                add_filter( 'body_class', function( $classes ) {
                    return array_merge( $classes, array( 'survey-not-sent' ));
                } );
            } else if($page->ID == get_option('registration_survey_page')) {
                wp_safe_redirect(get_home_url());
            }
        }

        return $request;
    }

    add_action("parse_request", "manageNonApprovedUserNav");

	add_action( 'user_register', 'manageNewUser', 150, 1);
    function manageNewUser($user_id) {
        //wp_clear_auth_cookie();
        wp_set_current_user($user_id);
        $survey_page_id = get_option('registration_survey_page');
        $user = get_user_by( 'id', $user_id );
        // set the WP login cookie
        $secure_cookie = is_ssl() ? true : false;
        wp_set_auth_cookie( $user_id, true, $secure_cookie );
		update_user_meta( $user_id, 'account_status', 'awaiting_admin_review' );
        do_action( 'wp_login', $user->user_login, $user );
        wp_safe_redirect(empty($survey_page_id) ? get_home_url() : get_permalink($survey_page_id), 302);
        exit();
    }

    function displayElementorAlert($title, $type = 'info', $link="", $text = "") {
        echo "
            <div class='elementor-alert elementor-alert-${type} eo-alert' role='alert'>
                <span class='elementor-alert-title'>${title}</span>".
                (!empty($text) ? "<span class='elementor-alert-description'>${text}</span>" : "") .
            (!empty($link) ? "<a class='elementor-alert-link' href='${link}'>${link}</a>" : "") . "
                <button type='button' class='elementor-alert-dismiss'>
                    <span aria-hidden='true'>×</span>
                    <span class='elementor-screen-only'>Rejeter l’alerte</span>
                </button>
            </div>
        ";
    }