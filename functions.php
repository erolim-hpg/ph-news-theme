<?php

/**
 * Theme functions and definitions
 * 
 * @package WordPress
 */

// Exit if accessed directly.
if (!defined("ABSPATH")) {
    exit;
}

// Core constants 
define("THEME_DIR", get_template_directory());
define("THEME_URI", get_template_directory_uri());

/**
 * Theme class
 */
final class Theme_Functions
{

    /**
     * Add hooks and load theme functions 
     * 
     * @since 1.0
     */
    public function __construct()
    {
        // Define theme constants
        $this->theme_constants();

        // Import theme files
        $this->theme_imports();

        // Add post metadata as a REST field 
        $this->register_metadata_in_rest();

        // Import theme files
        $this->theme_imports();

        add_action("admin_enqueue_scripts", array($this, "theme_admin_scripts"));

        // Setup theme support, nav menus, etc.
        add_action("after_setup_theme", array($this, "theme_setup"));

        // Add action to define custom excerpt
        add_filter("excerpt_length", array($this, "custom_excerpt_len"), 999);
        add_filter('excerpt_more', array($this, 'new_excerpt_more'));
    }

    /**
     * Define theme constants
     *
     * @since 1.0
     */
    public static function theme_constants()
    {
        $version = self::get_theme_version();

        define("THEME_VERSION", $version);

        // Assets
        define("THEME_ASSETS_DIR", THEME_DIR . "/assets/");
        define("THEME_ASSETS_URI", THEME_URI . "/assets/");

        // Includes
        define("THEME_INC_DIR", THEME_DIR . "/inc/");
        define("THEME_INC_URI", THEME_URI . "/inc/");
    }

    /**
     * Include theme classes and files
     *
     * @since 1.0
     */
    public static function theme_imports()
    {
        // Directory of files to be included
        $dir = THEME_INC_DIR;

        require_once($dir . 'meta-fields-category.php');
        require_once($dir . 'meta-fields-post.php');
    }

    /**
     * Register REST API field for the post and term metadata 
     *
     * @since 1.0
     */
    public static function register_metadata_in_rest()
    {
        register_rest_field('post', 'metadata', array(
            'get_callback' => function ($data) {
                return get_post_meta($data['id'], 'featured', true);
            },
        ));

        add_filter(
            'rest_prepare_category',
            function ($response, $item, $request) {
                $response->data['color'] = get_term_meta($item->term_id, '_category_color', true);
                return $response;
            },
            10,
            3
        );
    }

    /**
     * Setup theme support, nav menus, etc.
     *
     * @since 1.0
     */
    public static function theme_setup()
    {
        // Register nav menus
        register_nav_menus(
            array(
                "main_menu"   => esc_html__("Principal"),
                "footer_menu"   => esc_html__("Rodapé"),
            )
        );

        // Enable support for site logo
        add_theme_support(
            "custom-logo",
            apply_filters(
                "custom_logo_args",
                array(
                    "flex-height" => true,
                    "flex-width"  => true,
                )
            )
        );

        add_filter('nav_menu_css_class', function ($classes, $item, $args) {
            if (isset($args->li_class)) {
                $classes[] = $args->li_class;
            }
            return $classes;
        }, 1, 3);

        // Enable support for Post Formats.
        add_theme_support('post-formats', array('video', 'gallery', 'audio', 'quote', 'link'));

        // Enable support for Post Thumbnails on posts and pages.
        add_theme_support('post-thumbnails');
    }

    /**
     * Enqueue theme scripts for admin
     *
     * @since 1.0
     */
    public static function theme_admin_scripts()
    {
        $dir = THEME_ASSETS_URI;

        $version = THEME_VERSION;

        wp_enqueue_script('theme-admin-js', $dir . 'js/admin.js', ["jquery"], $version, false);
    }

    /**
     * Get theme version
     *
     * @return string Theme Version
     * @since 1.0
     */
    public static function get_theme_version()
    {
        $theme = wp_get_theme();
        return $theme->get("Version");
    }

    /**
     * Set custom excerpt length
     *
     * @param int $length
     * @since 2.0
     */
    public static function custom_excerpt_len($length)
    {
        return 30;
    }

    /**
     * Change excerpt more ellipsis
     * 
     * @param string more
     * @since 3.0
     */
    public static function new_excerpt_more($more)
    {
        return '...';
    }
}

new Theme_Functions();
