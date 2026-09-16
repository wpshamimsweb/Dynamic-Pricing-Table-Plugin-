<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * R3PT_Settings — manages storage and retrieval of all pricing table configs.
 *
 * Storage layout (wp_options):
 *  r3pt_global   → array  (global default values)
 *  r3pt_configs  → array  (keyed by config slug, each value = config array)
 */
class R3PT_Settings {

    const OPT_GLOBAL  = 'r3pt_global';
    const OPT_CONFIGS = 'r3pt_configs';

    // ── Global Defaults ────────────────────────────────────────────────────────

    /**
     * Returns the hard-coded fallback defaults (used when nothing is saved yet).
     */
    public static function base_defaults() {
        return [
            // Branding
            'logo_url'              => '',
            'logo_text'             => "Stem\nCell",

            // Header
            'location_name'         => 'TURKEY',
            'effective_label'       => 'Effective',
            'effective_date'        => 'June 1, 2026',

            // Column labels
            'col1_label'            => 'CELL COUNTS',
            'col2_label'            => 'USD PRICING',
            'col3_label'            => 'FREE EXOSOMES',
            'col4_label'            => 'FREE HOTEL?',

            // Column header icons  (cells | dollar | vial | hotel | custom)
            'col1_icon'             => 'cells',
            'col2_icon'             => 'dollar',
            'col3_icon'             => 'vial',
            'col4_icon'             => 'hotel',
            'col1_icon_custom_svg'  => '',
            'col2_icon_custom_svg'  => '',
            'col3_icon_custom_svg'  => '',
            'col4_icon_custom_svg'  => '',

            // Pricing rows (JSON-encoded array)
            'pricing_rows'          => json_encode( [
                [ 'cell_count' => '25M',  'price' => '$3,999.00',  'exosomes' => 'NO',      'hotel' => 'NO'       ],
                [ 'cell_count' => '50M',  'price' => '$6,499.00',  'exosomes' => '1 VIAL',  'hotel' => 'NO'       ],
                [ 'cell_count' => '75M',  'price' => '$7,999.00',  'exosomes' => '1 VIAL',  'hotel' => 'NO'       ],
                [ 'cell_count' => '100M', 'price' => '$9,499.00',  'exosomes' => '2 VIALS', 'hotel' => '2 NIGHTS' ],
                [ 'cell_count' => '125M', 'price' => '$10,999.00', 'exosomes' => '2 VIALS', 'hotel' => '2 NIGHTS' ],
                [ 'cell_count' => '150M', 'price' => '$12,499.00', 'exosomes' => '3 VIALS', 'hotel' => '2 NIGHTS' ],
                [ 'cell_count' => '175M', 'price' => '$13,999.00', 'exosomes' => '3 VIALS', 'hotel' => '2 NIGHTS' ],
                [ 'cell_count' => '200M', 'price' => '$15,499.00', 'exosomes' => '4 VIALS', 'hotel' => '3 NIGHTS' ],
                [ 'cell_count' => '225M', 'price' => '$16,999.00', 'exosomes' => '4 VIALS', 'hotel' => '3 NIGHTS' ],
                [ 'cell_count' => '250M', 'price' => '$18,499.00', 'exosomes' => '4 VIALS', 'hotel' => '3 NIGHTS' ],
            ] ),

            // ── Exosome Counts Section ────────────────────────────────────────
            'show_exosome_section'  => '0',
            'exosome_section_label' => 'EXOSOME COUNTS (BILLIONS)',
            'exosome_header_bg'     => '#1a2540',
            'exosome_rows'          => json_encode( [
                [ 'cell_count' => '100 billion', 'price' => '$4,999.00', 'exosomes' => 'no', 'hotel' => 'no'      ],
                [ 'cell_count' => '200 billion', 'price' => '$7,999.00', 'exosomes' => 'no', 'hotel' => '1 NIGHT' ],
            ] ),

            // Footer
            'footer_note_1'         => 'Free Hotel is 3 Star or 4 Star and up to the discretion of R3 Stem Cell concierge.',
            'footer_note_2'         => 'All credit card payments are subjected to a 3% bank charge.',
            'footer_warning'        => '',

            // ── Background ────────────────────────────────────────────────────
            // bg_type: 'color' | 'image' | 'video'
            'bg_type'               => 'color',
            'bg_image_url'          => '',
            'bg_video_url'          => '',
            'bg_overlay_color'      => '#000000',
            'bg_overlay_opacity'    => '40',

            // ── Visibility Toggles ────────────────────────────────────────────
            'show_logo'             => '1',
            'show_header_title'     => '1',
            'show_effective_date'   => '1',
            'show_table'            => '1',
            'show_footer'           => '1',
            'show_warning'          => '1',
            'show_background'       => '1',

            // Colors
            'bg_color'              => '#ffffff',
            'header_title_color'    => '#1a2540',
            'effective_date_color'  => '#e07040',
            'table_header_bg'       => '#dbe6f5',
            'price_col_bg'          => '#dbe6f5',
            'price_color'           => '#e07040',
            'table_text_color'      => '#1a2540',
            'warning_bg_color'      => '#1a2540',
            'warning_text_color'    => '#ffffff',
            'row_odd_bg'            => '#ffffff',
            'row_even_bg'           => '#f0f5fc',
            'table_border_radius'   => '8',
        ];
    }

    // ── Global saved defaults ──────────────────────────────────────────────────

    public static function get_global() {
        $saved = get_option( self::OPT_GLOBAL, [] );
        return wp_parse_args( $saved, self::base_defaults() );
    }

    public static function save_global( array $data ) {
        update_option( self::OPT_GLOBAL, $data );
    }

    // ── Named configs ──────────────────────────────────────────────────────────

    /**
     * Returns all named configs (array keyed by slug).
     */
    public static function get_all_configs() {
        return (array) get_option( self::OPT_CONFIGS, [] );
    }

    /**
     * Returns a single named config merged with global defaults.
     * If $slug is empty or 'default', returns global defaults only.
     */
    public static function get( $slug = '' ) {
        $global = self::get_global();

        if ( empty( $slug ) || $slug === 'default' ) {
            return $global;
        }

        $configs = self::get_all_configs();
        $config  = isset( $configs[ $slug ] ) ? (array) $configs[ $slug ] : [];

        return wp_parse_args( $config, $global );
    }

    /**
     * Save a named config (only stores the fields that differ from global).
     */
    public static function save( $slug, array $data ) {
        $configs         = self::get_all_configs();
        $configs[ $slug ] = $data;
        update_option( self::OPT_CONFIGS, $configs );
    }

    /**
     * Delete a named config.
     */
    public static function delete( $slug ) {
        $configs = self::get_all_configs();
        unset( $configs[ $slug ] );
        update_option( self::OPT_CONFIGS, $configs );
    }

    /**
     * Duplicate a config under a new slug.
     */
    public static function duplicate( $source_slug, $new_slug ) {
        $configs = self::get_all_configs();
        if ( isset( $configs[ $source_slug ] ) ) {
            $configs[ $new_slug ] = $configs[ $source_slug ];
            update_option( self::OPT_CONFIGS, $configs );
        }
    }

    // ── Sanitization ──────────────────────────────────────────────────────────

    /**
     * Sanitize a full config data array from $_POST.
     */
    public static function sanitize_config( array $raw ) {
        $data = [];

        // Text fields
        $text_fields = [
            'logo_url', 'logo_text', 'location_name', 'effective_label', 'effective_date',
            'col1_label', 'col2_label', 'col3_label', 'col4_label',
            'col1_icon', 'col2_icon', 'col3_icon', 'col4_icon',
            'footer_note_1', 'footer_note_2', 'footer_warning',
            'exosome_section_label',
            'bg_type', 'bg_image_url', 'bg_video_url',
        ];
        foreach ( $text_fields as $field ) {
            if ( isset( $raw[ $field ] ) ) {
                $data[ $field ] = sanitize_textarea_field( $raw[ $field ] );
            }
        }

        // URL fields
        $url_fields = [ 'bg_image_url', 'bg_video_url', 'logo_url' ];
        foreach ( $url_fields as $field ) {
            if ( isset( $raw[ $field ] ) ) {
                $data[ $field ] = esc_url_raw( $raw[ $field ] );
            }
        }

        // Custom SVG fields — allow SVG tags (strip PHP/scripts)
        $svg_fields = [ 'col1_icon_custom_svg', 'col2_icon_custom_svg', 'col3_icon_custom_svg', 'col4_icon_custom_svg' ];
        foreach ( $svg_fields as $field ) {
            if ( isset( $raw[ $field ] ) ) {
                $data[ $field ] = wp_kses( $raw[ $field ], self::allowed_svg_tags() );
            }
        }

        // Color fields
        $color_fields = [
            'bg_color', 'header_title_color', 'effective_date_color', 'table_header_bg',
            'price_col_bg', 'price_color', 'table_text_color', 'warning_bg_color',
            'warning_text_color', 'row_odd_bg', 'row_even_bg',
            'exosome_header_bg', 'bg_overlay_color',
        ];
        foreach ( $color_fields as $field ) {
            if ( isset( $raw[ $field ] ) ) {
                $data[ $field ] = sanitize_hex_color( $raw[ $field ] ) ?: ( self::base_defaults()[ $field ] ?? '#000000' );
            }
        }

        // Integer fields
        if ( isset( $raw['table_border_radius'] ) ) {
            $data['table_border_radius'] = absint( $raw['table_border_radius'] );
        }
        if ( isset( $raw['bg_overlay_opacity'] ) ) {
            $data['bg_overlay_opacity'] = min( 100, absint( $raw['bg_overlay_opacity'] ) );
        }

        // Toggle/checkbox fields — stored as '1' or '0'
        $toggle_fields = [
            'show_exosome_section',
            'show_logo', 'show_header_title', 'show_effective_date',
            'show_table', 'show_footer', 'show_warning', 'show_background',
        ];
        foreach ( $toggle_fields as $field ) {
            $data[ $field ] = ( ! empty( $raw[ $field ] ) && $raw[ $field ] === '1' ) ? '1' : '0';
        }

        // Pricing rows
        if ( isset( $raw['pricing_rows'] ) ) {
            $data['pricing_rows'] = self::sanitize_rows( $raw['pricing_rows'] );
        }

        // Exosome rows
        if ( isset( $raw['exosome_rows'] ) ) {
            $data['exosome_rows'] = self::sanitize_rows( $raw['exosome_rows'] );
        }

        return $data;
    }

    /**
     * Sanitize a rows array (pricing_rows or exosome_rows).
     */
    private static function sanitize_rows( $rows_raw ) {
        if ( ! is_array( $rows_raw ) ) {
            return json_encode( [] );
        }
        $clean_rows = [];
        foreach ( $rows_raw as $row ) {
            $clean_rows[] = [
                'cell_count' => sanitize_text_field( $row['cell_count'] ?? '' ),
                'price'      => sanitize_text_field( $row['price']      ?? '' ),
                'exosomes'   => sanitize_text_field( $row['exosomes']   ?? '' ),
                'hotel'      => sanitize_text_field( $row['hotel']      ?? '' ),
            ];
        }
        return json_encode( $clean_rows );
    }

    /**
     * Allowed HTML tags for custom SVG fields.
     */
    private static function allowed_svg_tags() {
        return [
            'svg'      => [ 'xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'class' => true, 'fill' => true, 'stroke' => true ],
            'circle'   => [ 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'opacity' => true ],
            'rect'     => [ 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'opacity' => true ],
            'path'     => [ 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true ],
            'polygon'  => [ 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ],
            'polyline' => [ 'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ],
            'line'     => [ 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true ],
            'ellipse'  => [ 'cx' => true, 'cy' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ],
            'text'     => [ 'x' => true, 'y' => true, 'text-anchor' => true, 'font-family' => true, 'font-weight' => true, 'font-size' => true, 'fill' => true ],
            'g'        => [ 'fill' => true, 'stroke' => true, 'transform' => true ],
            'defs'     => [],
            'title'    => [],
        ];
    }
}
