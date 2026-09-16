<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * R3PT_Icons — SVG icon library for R3 Pricing Table.
 */
class R3PT_Icons {

    // ── Column Header Icons ────────────────────────────────────────────────────

    public static function get_header_icon( $type, $custom_svg = '' ) {
        switch ( $type ) {
            // ── Cell Counts: blue atom/target ──
            case 'cells':
                return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="22" height="22" class="r3pt-th-icon" aria-hidden="true">
                    <circle cx="16" cy="16" r="10" fill="none" stroke="#2563a8" stroke-width="2.5"/>
                    <circle cx="16" cy="16" r="3" fill="#2563a8"/>
                    <ellipse cx="16" cy="16" rx="14" ry="5" fill="none" stroke="#2563a8" stroke-width="2" transform="rotate(45 16 16)"/>
                    <ellipse cx="16" cy="16" rx="14" ry="5" fill="none" stroke="#2563a8" stroke-width="2" transform="rotate(-45 16 16)"/>
                </svg>';

            // ── USD Pricing: bold blue dollar sign ──
            case 'dollar':
                return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="22" height="22" class="r3pt-th-icon" aria-hidden="true">
                    <text x="16" y="24" text-anchor="middle" font-family="Arial, sans-serif" font-weight="900" font-size="26" fill="#2563a8">$</text>
                </svg>';

            // ── Free Exosomes: blue IV bag / vial ──
            case 'vial':
                return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="22" height="22" class="r3pt-th-icon" aria-hidden="true">
                    <rect x="10" y="8" width="12" height="16" rx="2" fill="none" stroke="#2563a8" stroke-width="2.5"/>
                    <line x1="16" y1="2" x2="16" y2="8" stroke="#2563a8" stroke-width="2.5"/>
                    <line x1="12" y1="2" x2="20" y2="2" stroke="#2563a8" stroke-width="2.5"/>
                    <line x1="16" y1="24" x2="16" y2="30" stroke="#2563a8" stroke-width="2.5"/>
                    <rect x="13" y="13" width="6" height="6" fill="#2563a8"/>
                </svg>';

            // ── Free Hotel: blue bed ──
            case 'hotel':
                return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="24" height="24" class="r3pt-th-icon" aria-hidden="true">
                    <line x1="4" y1="6" x2="4" y2="26" stroke="#2563a8" stroke-width="2.5" stroke-linecap="round"/>
                    <line x1="28" y1="14" x2="28" y2="26" stroke="#2563a8" stroke-width="2.5" stroke-linecap="round"/>
                    <rect x="4" y="16" width="24" height="6" fill="none" stroke="#2563a8" stroke-width="2.5"/>
                    <rect x="9" y="12" width="8" height="4" rx="1" fill="none" stroke="#2563a8" stroke-width="2.5"/>
                </svg>';

            case 'syringe':
                return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="22" height="22" class="r3pt-th-icon" aria-hidden="true">
                    <line x1="7" y1="7" x2="25" y2="25" stroke="#2563a8" stroke-width="2" stroke-linecap="round"/>
                    <rect x="11" y="9" width="10" height="6" rx="1.5" fill="none" stroke="#2563a8" stroke-width="1.6" transform="rotate(45 16 12)"/>
                    <line x1="13" y1="19" x2="19" y2="13" stroke="#2563a8" stroke-width="1.3"/>
                    <line x1="11" y1="21" x2="7" y2="25" stroke="#2563a8" stroke-width="1.8" stroke-linecap="round"/>
                </svg>';

            case 'star':
                return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="22" height="22" class="r3pt-th-icon" aria-hidden="true">
                    <polygon points="16,4 19,11 27,11 21,16 23,24 16,19 9,24 11,16 5,11 13,11" fill="none" stroke="#2563a8" stroke-width="2.5" stroke-linejoin="round"/>
                </svg>';

            case 'heart':
                return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="22" height="22" class="r3pt-th-icon" aria-hidden="true">
                    <path d="M16 26 C16 26 6 19 6 12 C6 8.5 8.5 6 12 6 C14 6 15.5 7 16 8 C16.5 7 18 6 20 6 C23.5 6 26 8.5 26 12 C26 19 16 26 16 26Z" fill="none" stroke="#2563a8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>';

            case 'dna':
                return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="22" height="22" class="r3pt-th-icon" aria-hidden="true">
                    <path d="M12 5 C12 5 20 9 20 16 C20 23 12 27 12 27" fill="none" stroke="#2563a8" stroke-width="2" stroke-linecap="round"/>
                    <path d="M20 5 C20 5 12 9 12 16 C12 23 20 27 20 27" fill="none" stroke="#2563a8" stroke-width="2" stroke-linecap="round"/>
                    <line x1="12" y1="10" x2="20" y2="13" stroke="#2563a8" stroke-width="1.3"/>
                    <line x1="12" y1="19" x2="20" y2="22" stroke="#2563a8" stroke-width="1.3"/>
                    <line x1="12" y1="15" x2="20" y2="17" stroke="#2563a8" stroke-width="1.3"/>
                </svg>';

            case 'custom':
                return ! empty( $custom_svg ) ? $custom_svg : self::get_header_icon( 'cells' );

            default:
                return self::get_header_icon( 'cells' );
        }
    }

    public static function header_icon_options() {
        return [
            'cells'   => __( 'Cells / Atom (Blue)', 'r3-pricing-table' ),
            'dollar'  => __( 'Dollar Sign (Blue)', 'r3-pricing-table' ),
            'vial'    => __( 'Gas Pump / IV Bag (Blue)', 'r3-pricing-table' ),
            'hotel'   => __( 'Bed / Hotel (Blue)', 'r3-pricing-table' ),
            'syringe' => __( 'Syringe (Blue)', 'r3-pricing-table' ),
            'star'    => __( 'Star (Blue)', 'r3-pricing-table' ),
            'heart'   => __( 'Heart (Blue)', 'r3-pricing-table' ),
            'dna'     => __( 'DNA Helix (Blue)', 'r3-pricing-table' ),
            'custom'  => __( 'Custom SVG (paste below)', 'r3-pricing-table' ),
        ];
    }

    // ── Row Cell Icons ────────────────────────────────────────────────────────

    public static function get_row_svg( $index = 0 ) {
        // Exact match to reference image: Blue circle outline with solid blue inner dot
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 22" width="18" height="18" aria-hidden="true">
            <circle cx="11" cy="11" r="8" fill="none" stroke="#2563a8" stroke-width="3"/>
            <circle cx="11" cy="11" r="4" fill="#2563a8"/>
        </svg>';
    }

    public static function get_default_logo_svg() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 70 70" width="60" height="60">
            <ellipse cx="35" cy="35" rx="33" ry="33" fill="none" stroke="#2563a8" stroke-width="2.5"/>
            <ellipse cx="22" cy="25" rx="10" ry="13" fill="none" stroke="#2563a8" stroke-width="2"/>
            <ellipse cx="48" cy="25" rx="10" ry="13" fill="none" stroke="#2563a8" stroke-width="2"/>
            <ellipse cx="22" cy="48" rx="10" ry="13" fill="none" stroke="#2563a8" stroke-width="2"/>
            <ellipse cx="48" cy="48" rx="10" ry="13" fill="none" stroke="#2563a8" stroke-width="2"/>
            <text x="35" y="40" text-anchor="middle" font-family="Arial" font-weight="bold" font-size="12" fill="#2563a8">R3</text>
        </svg>';
    }

    public static function get_pin_icon() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 30" width="32" height="40" aria-hidden="true">
            <path d="M12 0 C6.48 0 2 4.48 2 10 C2 17.5 12 30 12 30 C12 30 22 17.5 22 10 C22 4.48 17.52 0 12 0Z" fill="#e07040"/>
            <circle cx="12" cy="10" r="4.5" fill="#ffffff"/>
            <circle cx="12" cy="10" r="2" fill="#e07040"/>
        </svg>';
    }

    public static function get_exosome_header_icon() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 28 28" width="26" height="26" aria-hidden="true">
            <polygon points="14,2 26,8 26,20 14,26 2,20 2,8" fill="#e07040"/>
            <circle cx="14" cy="14" r="5" fill="none" stroke="#fff" stroke-width="2"/>
            <circle cx="14" cy="14" r="2" fill="#fff"/>
        </svg>';
    }

    public static function get_info_icon() {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" aria-hidden="true">
            <circle cx="12" cy="12" r="11" fill="#2563a8"/>
            <text x="12" y="17" text-anchor="middle" font-family="Georgia, serif" font-style="italic" font-weight="bold" font-size="14" fill="#ffffff">i</text>
        </svg>';
    }
}
