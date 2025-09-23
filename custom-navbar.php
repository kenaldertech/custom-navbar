<?php
/*
Plugin Name: Custom Navbar
Plugin URI: https://kennethalvarenga.com
Description: Adds a fully customizable top navigation bar with logo, colors, sticky option, responsive burger menu, optional custom button, and social media icons. Removes default theme navbars automatically for common themes. Built by Kenneth Alvarenga (Tekai Labs LLC).
Version: 1.2
Author: Kenneth Alvarenga
Author URI: https://kennethalvarenga.com
License: GPLv2 or later
Text Domain: custom-navbar
*/

if ( ! defined( 'ABSPATH' ) ) exit;

class CustomNavbarPlugin {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        // Renderiza TU navbar en el header
        add_action('get_header', [$this, 'render_navbar']);

        // Detecta el theme activo y quita el menú por defecto
        add_action('after_setup_theme', [$this, 'remove_theme_nav']);
    }

    public function add_admin_page() {
        add_options_page('Custom Navbar', 'Custom Navbar', 'manage_options', 'custom-navbar', [$this, 'settings_page']);
    }

    public function register_settings() {
        // General settings
        register_setting('custom_navbar_group', 'custom_navbar_logo');
        register_setting('custom_navbar_group', 'custom_navbar_bg_color');
        register_setting('custom_navbar_group', 'custom_navbar_text_color');
        register_setting('custom_navbar_group', 'custom_navbar_sticky');
        register_setting('custom_navbar_group', 'custom_navbar_menu');
        register_setting('custom_navbar_group', 'custom_navbar_button_text');
        register_setting('custom_navbar_group', 'custom_navbar_button_link');

        // Socials
        register_setting('custom_navbar_group', 'custom_navbar_facebook');
        register_setting('custom_navbar_group', 'custom_navbar_instagram');
        register_setting('custom_navbar_group', 'custom_navbar_twitter');
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h1>Custom Navbar Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('custom_navbar_group'); ?>
                
                <h2>General</h2>
                <label>Logo URL:</label><br>
                <input type="text" name="custom_navbar_logo" value="<?php echo esc_attr(get_option('custom_navbar_logo')); ?>" size="50"><br><br>

                <label>Background Color:</label><br>
                <input type="color" name="custom_navbar_bg_color" value="<?php echo esc_attr(get_option('custom_navbar_bg_color', '#333')); ?>"><br><br>

                <label>Text Color:</label><br>
                <input type="color" name="custom_navbar_text_color" value="<?php echo esc_attr(get_option('custom_navbar_text_color', '#fff')); ?>"><br><br>

                <label>Menu Location (slug):</label><br>
                <input type="text" name="custom_navbar_menu" value="<?php echo esc_attr(get_option('custom_navbar_menu', 'primary')); ?>"><br><br>

                <label><input type="checkbox" name="custom_navbar_sticky" value="1" <?php checked(1, get_option('custom_navbar_sticky'), true); ?>> Sticky Menu</label><br><br>

                <h2>Custom Button</h2>
                <label>Button Text:</label><br>
                <input type="text" name="custom_navbar_button_text" value="<?php echo esc_attr(get_option('custom_navbar_button_text')); ?>"><br><br>
                <label>Button Link:</label><br>
                <input type="text" name="custom_navbar_button_link" value="<?php echo esc_attr(get_option('custom_navbar_button_link')); ?>"><br><br>

                <h2>Social Media</h2>
                <label>Facebook URL:</label><br>
                <input type="text" name="custom_navbar_facebook" value="<?php echo esc_attr(get_option('custom_navbar_facebook')); ?>"><br><br>
                <label>Instagram URL:</label><br>
                <input type="text" name="custom_navbar_instagram" value="<?php echo esc_attr(get_option('custom_navbar_instagram')); ?>"><br><br>
                <label>Twitter (X) URL:</label><br>
                <input type="text" name="custom_navbar_twitter" value="<?php echo esc_attr(get_option('custom_navbar_twitter')); ?>"><br><br>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_assets() {
        // CSS y JS con versionado dinámico para evitar cache
        wp_enqueue_style(
            'custom-navbar-style',
            plugin_dir_url(__FILE__) . 'css/navbar.css',
            [],
            time()
        );

        wp_enqueue_script(
            'custom-navbar-js',
            plugin_dir_url(__FILE__) . 'js/navbar.js',
            ['jquery'],
            time(),
            true
        );
    }

    public function render_navbar() {
        $logo = get_option('custom_navbar_logo');
        $bg_color = get_option('custom_navbar_bg_color', '#333');
        $text_color = get_option('custom_navbar_text_color', '#fff');
        $sticky = get_option('custom_navbar_sticky') ? 'sticky' : '';
        $menu_slug = get_option('custom_navbar_menu', 'primary');

        echo "<nav class='custom-navbar $sticky' style='background:$bg_color; color:$text_color;'>";

        // ==== LOGO ====
        if ($logo) {
            echo "<div class='navbar-logo'>
                    <img src='".esc_url($logo)."' alt='logo' 
                         style='max-height:50px; width:auto; height:auto; display:block;'>
                  </div>";
        }

        // ==== NAV MENU ====
        wp_nav_menu([
            'theme_location' => $menu_slug,
            'container' => 'div',
            'menu_class' => 'navbar-menu',
        ]);

        // ==== CUSTOM BUTTON ====
        $btn_text = get_option('custom_navbar_button_text');
        $btn_link = get_option('custom_navbar_button_link');
        if ($btn_text && $btn_link) {
            echo "<a class='navbar-button' href='".esc_url($btn_link)."' style='color:$text_color;'>".esc_html($btn_text)."</a>";
        }

        // ==== SOCIAL ICONS ====
        echo "<div class='navbar-socials'>";
        if ($fb = get_option('custom_navbar_facebook')) {
            echo "<a href='".esc_url($fb)."' target='_blank' aria-label='Facebook'>
                <svg width='20' height='20' fill='currentColor' viewBox='0 0 24 24'><path d='M22 12a10 10 0 1 0-11.6 9.87v-6.99H8.9v-2.88h1.5V9.41c0-1.48.88-2.3 2.22-2.3.64 0 1.31.11 1.31.11v1.44h-.74c-.73 0-.95.45-.95.91v1.09h1.62l-.26 2.88h-1.36v6.99A10 10 0 0 0 22 12z'/></svg>
            </a>";
        }
        if ($ig = get_option('custom_navbar_instagram')) {
            echo "<a href='".esc_url($ig)."' target='_blank' aria-label='Instagram'>
                <svg width='20' height='20' fill='currentColor' viewBox='0 0 24 24'><path d='M12 2.2c3.2 0 3.6 0 4.9.1 1.2.1 2 .3 2.5.5.6.2 1 .5 1.5 1s.8.9 1 1.5c.2.5.4 1.3.5 2.5.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c-.1 1.2-.3 2-.5 2.5-.2.6-.5 1-1 1.5s-.9.8-1.5 1c-.5.2-1.3.4-2.5.5-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2-.1-2-.3-2.5-.5-.6-.2-1-.5-1.5-1s-.8-.9-1-1.5c-.2-.5-.4-1.3-.5-2.5C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9c.1-1.2.3-2 .5-2.5.2-.6.5-1 1-1.5s.9-.8 1.5-1c.5-.2 1.3-.4 2.5-.5C8.4 2.2 8.8 2.2 12 2.2m0-2.2C8.7 0 8.3 0 7 .1 5.7.2 4.6.4 3.8.7c-.9.3-1.7.8-2.5 1.6-.8.8-1.3 1.6-1.6 2.5C-.4 5.6-.6 6.7-.7 7.9-.8 9.2-.8 9.6-.8 12c0 2.4 0 2.8.1 4.1.1 1.2.3 2.3.6 3.1.3.9.8 1.7 1.6 2.5.8.8 1.6 1.3 2.5 1.6.9.3 2 .5 3.2.6 1.3.1 1.7.1 4.1.1s2.8 0 4.1-.1c1.2-.1 2.3-.3 3.1-.6.9-.3 1.7-.8 2.5-1.6.8-.8 1.3-1.6 1.6-2.5.3-.9.5-2 .6-3.2.1-1.3.1-1.7.1-4.1s0-2.8-.1-4.1c-.1-1.2-.3-2.3-.6-3.1-.3-.9-.8-1.7-1.6-2.5C20.7.4 19.9-.1 19-.4c-.8-.3-1.9-.5-3.1-.6C14.8-.8 14.4-.8 12-.8z'/><circle cx='12' cy='12' r='3.2'/></svg>
            </a>";
        }
        if ($tw = get_option('custom_navbar_twitter')) {
            echo "<a href='".esc_url($tw)."' target='_blank' aria-label='Twitter'>
                <svg width='20' height='20' fill='currentColor' viewBox='0 0 24 24'><path d='M23 3c-.8.4-1.6.7-2.5.8A4.3 4.3 0 0 0 22.4 1a8.6 8.6 0 0 1-2.7 1 4.3 4.3 0 0 0-7.5 2.9c0 .3 0 .6.1.9C8.6 5.6 5 3.7 2.7.9c-.4.6-.6 1.3-.6 2.1 0 1.4.7 2.7 1.8 3.5-.7 0-1.3-.2-1.9-.5v.1c0 2 1.4 3.6 3.2 4a4.3 4.3 0 0 1-1.9.1c.6 1.9 2.3 3.2 4.3 3.2A8.7 8.7 0 0 1 1 19.6a12.2 12.2 0 0 0 6.6 1.9c8 0 12.4-6.6 12.4-12.4v-.6A9.1 9.1 0 0 0 23 3z'/></svg>
            </a>";
        }
        echo "</div>";

        // ==== BURGER MENU ====
        echo "<div class='burger-menu'>☰</div>";

        echo "</nav>";
    }

    public function remove_theme_nav() {
        $theme = wp_get_theme(); // obtiene el theme activo

        switch ($theme->get('Name')) {
            case 'Astra':
                if (function_exists('astra_primary_navigation')) {
                    remove_action('astra_header', 'astra_primary_navigation');
                }
                break;

            case 'GeneratePress':
                if (function_exists('generate_navigation')) {
                    remove_action('generate_header', 'generate_navigation');
                }
                break;

            case 'Twenty Twenty-One':
                // Oculta con CSS
                add_action('wp_head', function () {
                    echo "<style>header nav { display:none !important; }</style>";
                });
                break;

            case 'Twenty Twenty-Three':
                // Oculta bloque de navegación
                add_action('wp_head', function () {
                    echo "<style>.wp-block-navigation { display:none !important; }</style>";
                });
                break;

            default:
                // Fallback genérico
                add_action('wp_head', function () {
                    echo "<style>header nav, header .main-navigation { display:none !important; }</style>";
                });
                break;
        }
    }
}

new CustomNavbarPlugin();
