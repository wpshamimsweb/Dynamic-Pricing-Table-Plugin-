<?php
/**
 * Plugin Name:       R3 Pricing Table
 * Plugin URI:        https://r3stemcell.com
 * Description:       A standalone shortcode-based pricing table plugin with a global WP Admin settings panel. No Elementor required. Use [r3_pricing_table id="turkey"] anywhere.
 * Version:           2.1.1
 * Author:            Al-Amin Shamim
 * Author URI:        https://r3stemcell.com
 * Text Domain:       r3-pricing-table
 * License:           GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ── Constants ──────────────────────────────────────────────────────────────────
define( 'R3PT_VERSION',     '2.1.1' );
define( 'R3PT_PLUGIN_FILE', __FILE__ );
define( 'R3PT_DIR',         plugin_dir_path( __FILE__ ) );
define( 'R3PT_URL',         plugin_dir_url( __FILE__ ) );

// ── Autoload includes ──────────────────────────────────────────────────────────
require_once R3PT_DIR . 'includes/class-r3-settings.php';
require_once R3PT_DIR . 'includes/class-r3-svg-icons.php';
require_once R3PT_DIR . 'includes/class-r3-shortcode.php';
require_once R3PT_DIR . 'includes/class-r3-admin.php';

// ── Bootstrap ──────────────────────────────────────────────────────────────────
final class R3_Pricing_Table_Plugin {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init',            [ $this, 'init' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'maybe_enqueue_frontend_assets' ], 20 );

        // Admin
        if ( is_admin() ) {
            new R3PT_Admin();
        }

        // Shortcode
        new R3PT_Shortcode();
    }

    public function init() {
        load_plugin_textdomain( 'r3-pricing-table', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    /**
     * Enqueue frontend CSS on pages where the shortcode is used.
     * Uses a global flag set by the shortcode renderer for late loading,
     * or falls back to enqueuing on all pages (safe, tiny file).
     */
    public function maybe_enqueue_frontend_assets() {
        // Always enqueue — CSS is lightweight and we can't reliably detect
        // shortcode usage before wp_head fires in all themes.
        wp_enqueue_style(
            'r3-pricing-table',
            R3PT_URL . 'assets/css/r3-pricing-table-front.css',
            [],
            R3PT_VERSION
        );

        // Google Fonts
        wp_enqueue_style(
            'r3-pricing-table-fonts',
            'https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap',
            [],
            null
        );
    }
}

R3_Pricing_Table_Plugin::instance();
