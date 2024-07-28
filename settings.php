<?php

add_action('admin_menu', 'free_products_menu');
// Register settings, sections, and fields
add_action('admin_init', 'free_products_settings_init');

function free_products_menu() {
    add_menu_page(
        'Free Products Settings',    // Page title
        'Free Products',             // Menu title
        'manage_options',            // Capability
        'free-products-settings',    // Menu slug
        'free_products_settings_page', // Callback function
        'dashicons-admin-generic',   // Icon
        90                           // Position
    );
}

// Display the settings page content
function free_products_settings_page() {
    ?>
    <div class="wrap">
        <h1>Free Products Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('free_products_settings_group');
            do_settings_sections('free-products-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function free_products_settings_init() {
    // Register settings
    register_setting('free_products_settings_group', 'free_products_enable_checkbox');
    register_setting('free_products_settings_group', 'minimum_order_price');

    // Add settings section
    add_settings_section(
        'free_products_settings_section',
        'Settings',
        'free_products_settings_section_cb', 
        'free-products-settings'
    );

    // Add settings fields
    add_settings_field(
        'free_products_enable_checkbox',
        'Enable',
        'free_products_enable_checkbox_cb',
        'free-products-settings',
        'free_products_settings_section'
    );

    add_settings_field(
        'minimum_order_price',
        'Minimum Price',
        'minimum_order_price_cb',
        'free-products-settings',
        'free_products_settings_section'
    );
}

function free_products_settings_section_cb() {
    echo '<p>Configure the settings for Free Products</p>';
}

function minimum_order_price_cb() {
    $setting = get_option('minimum_order_price');
    ?>
    <input type="text" name="minimum_order_price" value="<?php echo isset($setting) ? esc_attr($setting) : ''; ?>">
    <?php
}

function free_products_enable_checkbox_cb() {
    $option = get_option('free_products_enable_checkbox');
    ?>
    <input type="checkbox" name="free_products_enable_checkbox" value="1" <?php checked(1, $option, true); ?> />
    <?php
}
