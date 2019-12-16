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

        /* Create settings section */
        add_settings_section(
            'survey_page',                   // Section ID
            "Options du questionnaire à l'inscription",  // Section title
            'registration_survey_options_description', // Section callback function
            'general'                          // Settings page slug
        );

        /* Create settings field */
        add_settings_field(
            'survey_page_selector',       // Field ID
            'Page du questionnaire',       // Field title
            'survey_page_field', // Field callback function
            'general',                    // Settings page slug
            'survey_page'               // Section ID
        );
    }

    /* Sanitize Callback Function */
    function registration_survey_page_sanitize($input) {
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