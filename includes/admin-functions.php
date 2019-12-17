<?php
    /* Admin init */
    add_action('admin_init', 'survey_page_url_settings');

    /* Settings Init */
    function survey_page_url_settings() {
        register_setting(
            'general',
            'registration_survey_page',      // Option name/database
            'registration_survey_page_sanitize' // Sanitize callback function
        );

        register_setting(
            'general',
            'approved_login_redirection',      // Option name/database
            'registration_survey_page_sanitize' // Sanitize callback function
        );

        /* Create settings section */
        add_settings_section(
            'survey_page',                   // Section ID
            "Options du questionnaire à l'inscription",  // Section title
            'registration_survey_options_description', // Section callback function
            'general'                          // Settings page slug
        );

        add_settings_field(
            'survey_page_selector',       // Field ID
            'Page du questionnaire',       // Field title
            'survey_page_field', // Field callback function
            'general',                    // Settings page slug
            'survey_page'               // Section ID
        );

        add_settings_field(
            'approved_redirect_selector',       // Field ID
            "Après la connexion, rediriger sur : ",       // Field title
            'approved_redirection_field', // Field callback function
            'general',                    // Settings page slug
            'survey_page'               // Section ID
        );

        if(defined('FLUENTFORM')) {
            register_setting(
                'general',
                'registration_survey_form',      // Option name/database
                'registration_survey_form_sanitize' // Sanitize callback function
            );

            add_settings_field(
                'survey_form_selector',       // Field ID
                'Formulaire du questionnaire',       // Field title
                'survey_form_field', // Field callback function
                'general',                    // Settings page slug
                'survey_page'               // Section ID
            );
        }
    }

    /* Sanitize Callback Function */
    function registration_survey_page_sanitize($input) {
        return isset($input) ? $input : null;
    }

    function registration_survey_form_sanitize($input) {
        return isset($input) ? $input : null;
    }

    /* Setting Section Description */
    function registration_survey_options_description() {
        echo wpautop("Modifier ici les paramètres concernant le questionnaire soumis aux nouveaux inscrits");
    }

    /* Settings Field Callback */
    function survey_page_field() {
        $pages = get_pages();
        ?>
        <label for="survey_page_selector">
            <select name="registration_survey_page" id="survey_page_selector">
                <?php foreach ($pages as $page): ?>
                    <option
                        value='<?= $page->ID; ?>'
                        <?php selected(get_option('registration_survey_page'),$page->ID); ?>
                    >
                        <?= $page->post_title; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php
    }

    function approved_redirection_field() {
        $pages = get_pages();
        ?>
        <label for="approved_redirect_selector">
            <select name="approved_login_redirection" id="approved_redirect_selector">
                <?php foreach ($pages as $page): ?>
                    <option
                        value='<?= $page->ID; ?>'
                        <?php selected(get_option('approved_login_redirection'),$page->ID); ?>
                    >
                        <?= $page->post_title; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php
    }

    function survey_form_field() {
        if(!defined('FLUENTFORM')) return;
        global $wpdb;
        $forms = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fluentform_forms" );
        $selected = get_option('registration_survey_form');
        ?>
        <label for="survey_form_selector">
            <select name="registration_survey_form" id="survey_form_selector">
                <option value="" <?php selected($selected, ""); ?>>Veuillez sélectionner un formulaire FluentForm</option>
                <?php foreach ($forms as $form): ?>
                    <option
                        value='<?= $form->id; ?>'
                        <?php selected(get_option('registration_survey_form'),$form->id); ?>
                    >
                        <?= $form->title; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <?php
    }