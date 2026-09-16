<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * R3PT_Shortcode — registers and renders [r3_pricing_table] shortcode.
 *
 * Usage:
 *   [r3_pricing_table]
 *   [r3_pricing_table id="turkey"]
 *   [r3_pricing_table id="mexico" location="DUBAI" effective_date="Aug 1, 2026"]
 */
class R3PT_Shortcode {

    public function __construct() {
        add_shortcode( 'r3_pricing_table', [ $this, 'render' ] );
    }

    /**
     * Shortcode callback.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output.
     */
    public function render( $atts ) {

        $atts = shortcode_atts( [
            'id'              => 'default',
            'logo_url'        => null,
            'logo_text'       => null,
            'location_name'   => null,
            'effective_label' => null,
            'effective_date'  => null,
            'col1_label'      => null,
            'col2_label'      => null,
            'col3_label'      => null,
            'col4_label'      => null,
            'footer_note_1'   => null,
            'footer_note_2'   => null,
            'footer_warning'  => null,
        ], $atts, 'r3_pricing_table' );

        $config = R3PT_Settings::get( $atts['id'] );

        foreach ( $atts as $key => $value ) {
            if ( $key !== 'id' && $value !== null ) {
                $config[ $key ] = sanitize_text_field( $value );
            }
        }

        // ── Visibility toggles ────────────────────────────────────────────────
        $show_logo           = ( $config['show_logo']            ?? '1' ) === '1';
        $show_header_title   = ( $config['show_header_title']    ?? '1' ) === '1';
        $show_effective_date = ( $config['show_effective_date']  ?? '1' ) === '1';
        $show_table          = ( $config['show_table']           ?? '1' ) === '1';
        $show_exosome        = ( $config['show_exosome_section'] ?? '0' ) === '1';
        $show_footer         = ( $config['show_footer']          ?? '1' ) === '1';
        $show_warning        = ( $config['show_warning']         ?? '1' ) === '1';
        $show_background     = ( $config['show_background']      ?? '1' ) === '1';

        // ── Extract values ────────────────────────────────────────────────────
        $logo_url   = $config['logo_url']           ?? '';
        $logo_text  = $config['logo_text']           ?? 'Stem Cell';
        $location   = strtoupper( $config['location_name']   ?? 'TURKEY' );
        $eff_label  = $config['effective_label']     ?? 'Effective';
        $eff_date   = $config['effective_date']      ?? 'June 1, 2026';
        $col1       = $config['col1_label']          ?? 'CELL COUNTS';
        $col2       = $config['col2_label']          ?? 'USD PRICING';
        $col3       = $config['col3_label']          ?? 'FREE EXOSOMES';
        $col4       = $config['col4_label']          ?? 'FREE HOTEL?';
        $note1      = $config['footer_note_1']       ?? '';
        $note2      = $config['footer_note_2']       ?? '';
        $warning    = $config['footer_warning']      ?? '';

        // Exosome section
        $exo_label     = $config['exosome_section_label'] ?? 'EXOSOME COUNTS (BILLIONS)';
        $exo_header_bg = $config['exosome_header_bg']     ?? '#1a2540';

        // ── Pricing rows ──────────────────────────────────────────────────────
        $rows = json_decode( $config['pricing_rows'] ?? '[]', true );
        if ( ! is_array( $rows ) ) { $rows = []; }

        $exo_rows = json_decode( $config['exosome_rows'] ?? '[]', true );
        if ( ! is_array( $exo_rows ) ) { $exo_rows = []; }

        // ── Colors & style ────────────────────────────────────────────────────
        $bg_color            = $config['bg_color']             ?? '#ffffff';
        $header_title_color  = $config['header_title_color']   ?? '#1a2540';
        $effective_date_color= $config['effective_date_color'] ?? '#e07040';
        $table_header_bg     = $config['table_header_bg']      ?? '#dbe6f5';
        $price_col_bg        = $config['price_col_bg']         ?? '#e07040';
        $price_color         = $config['price_color']          ?? '#e07040';
        $table_text_color    = $config['table_text_color']     ?? '#1a2540';
        $warning_bg_color    = $config['warning_bg_color']     ?? '#1a2540';
        $warning_text_color  = $config['warning_text_color']   ?? '#ffffff';
        $row_odd_bg          = $config['row_odd_bg']           ?? '#ffffff';
        $row_even_bg         = $config['row_even_bg']          ?? '#f0f5fc';
        $border_radius       = intval( $config['table_border_radius'] ?? 8 );

        // ── Background ────────────────────────────────────────────────────────
        $bg_type            = $config['bg_type']            ?? 'color';
        $bg_image_url       = $config['bg_image_url']       ?? '';
        $bg_video_url       = $config['bg_video_url']       ?? '';
        $bg_overlay_color   = $config['bg_overlay_color']   ?? '#000000';
        $bg_overlay_opacity = intval( $config['bg_overlay_opacity'] ?? 40 );

        // Header icons
        $col1_icon     = $config['col1_icon']            ?? 'cells';
        $col2_icon     = $config['col2_icon']            ?? 'dollar';
        $col3_icon     = $config['col3_icon']            ?? 'vial';
        $col4_icon     = $config['col4_icon']            ?? 'hotel';
        $col1_icon_svg = $config['col1_icon_custom_svg'] ?? '';
        $col2_icon_svg = $config['col2_icon_custom_svg'] ?? '';
        $col3_icon_svg = $config['col3_icon_custom_svg'] ?? '';
        $col4_icon_svg = $config['col4_icon_custom_svg'] ?? '';

        // Unique wrapper ID
        $uid = 'r3pt-' . esc_attr( $atts['id'] ) . '-' . wp_unique_id();

        // ── Overlay RGBA ──────────────────────────────────────────────────────
        $overlay_alpha = round( $bg_overlay_opacity / 100, 2 );
        $overlay_rgb   = self::hex_to_rgb( $bg_overlay_color );
        $overlay_rgba  = "rgba({$overlay_rgb['r']},{$overlay_rgb['g']},{$overlay_rgb['b']},{$overlay_alpha})";

        // ── Wrapper inline styles ─────────────────────────────────────────────
        $wrapper_style = '';
        if ( $show_background ) {
            if ( $bg_type === 'color' ) {
                $wrapper_style = 'background-color:' . esc_attr( $bg_color ) . ';';
            } elseif ( $bg_type === 'image' && $bg_image_url ) {
                $wrapper_style = 'background-image:url(' . esc_url( $bg_image_url ) . ');background-size:cover;background-position:center;background-color:' . esc_attr( $bg_color ) . ';';
            } elseif ( $bg_type === 'video' ) {
                $wrapper_style = 'background-color:' . esc_attr( $bg_color ) . ';';
            }
        } else {
            $wrapper_style = 'background:transparent;box-shadow:none;';
        }

        ob_start();
        ?>
        <!-- R3 Pricing Table: <?php echo esc_html( $atts['id'] ); ?> -->
        <style>
        #<?php echo $uid; ?> {
            --r3pt-bg:             <?php echo esc_attr( $bg_color ); ?>;
            --r3pt-title-color:    <?php echo esc_attr( $header_title_color ); ?>;
            --r3pt-date-color:     <?php echo esc_attr( $effective_date_color ); ?>;
            --r3pt-thead-bg:       <?php echo esc_attr( $table_header_bg ); ?>;
            --r3pt-price-col-bg:   <?php echo esc_attr( $price_col_bg ); ?>;
            --r3pt-price-color:    <?php echo esc_attr( $price_color ); ?>;
            --r3pt-text-color:     <?php echo esc_attr( $table_text_color ); ?>;
            --r3pt-warning-bg:     <?php echo esc_attr( $warning_bg_color ); ?>;
            --r3pt-warning-text:   <?php echo esc_attr( $warning_text_color ); ?>;
            --r3pt-row-odd-bg:     <?php echo esc_attr( $row_odd_bg ); ?>;
            --r3pt-row-even-bg:    <?php echo esc_attr( $row_even_bg ); ?>;
            --r3pt-radius:         <?php echo esc_attr( $border_radius ); ?>px;
            --r3pt-exo-header-bg:  <?php echo esc_attr( $exo_header_bg ); ?>;
        }
        </style>

        <div class="r3pt-wrapper<?php echo ( $bg_type === 'video' && $show_background ) ? ' r3pt-has-video-bg' : ''; ?>" id="<?php echo $uid; ?>" style="<?php echo esc_attr( $wrapper_style ); ?>">

            <?php // ── Video Background ─────────────────────────────── ?>
            <?php if ( $show_background && $bg_type === 'video' && $bg_video_url ) : ?>
            <div class="r3pt-video-bg-wrap">
                <video class="r3pt-bg-video" autoplay muted loop playsinline>
                    <source src="<?php echo esc_url( $bg_video_url ); ?>">
                </video>
                <div class="r3pt-bg-overlay" style="background:<?php echo esc_attr( $overlay_rgba ); ?>;"></div>
            </div>
            <?php endif; ?>

            <?php // ── Image overlay ────────────────────────────────── ?>
            <?php if ( $show_background && $bg_type === 'image' && $bg_image_url ) : ?>
            <div class="r3pt-bg-overlay" style="background:<?php echo esc_attr( $overlay_rgba ); ?>;"></div>
            <?php endif; ?>

            <div class="r3pt-content">

                <?php // ── Logo ─────────────────────────────────────── ?>
                <?php if ( $show_logo ) : ?>
                <div class="r3pt-logo-wrap">
                    <?php if ( $logo_url ) : ?>
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Logo', 'r3-pricing-table' ); ?>" class="r3pt-logo-img" />
                    <?php else : ?>
                        <div class="r3pt-logo-default">
                            <?php echo R3PT_Icons::get_default_logo_svg(); ?>
                        </div>
                    <?php endif; ?>
                    <div class="r3pt-logo-text"><?php echo nl2br( esc_html( $logo_text ) ); ?></div>
                </div>
                <?php endif; ?>

                <?php // ── Header ────────────────────────────────────── ?>
                <?php if ( $show_header_title || $show_effective_date ) : ?>
                <div class="r3pt-header">
                    <?php if ( $show_header_title ) : ?>
                    <div class="r3pt-header-icon-col">
                        <span class="r3pt-pin-icon"><?php echo R3PT_Icons::get_pin_icon(); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="r3pt-header-text-col">
                        <?php if ( $show_header_title ) : ?>
                        <h2 class="r3pt-location-title" style="color:var(--r3pt-title-color);"><?php echo esc_html( $location ); ?> PRICING</h2>
                        <?php endif; ?>
                        <?php if ( $show_effective_date ) : ?>
                        <p class="r3pt-effective-line">
                            <?php echo esc_html( $eff_label ); ?>
                            <span class="r3pt-effective-date"><?php echo esc_html( $eff_date ); ?></span>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php // ── Table ─────────────────────────────────────── ?>
                <?php if ( $show_table ) : ?>
                <div class="r3pt-table-wrapper">
                    <table class="r3pt-table">
                        <thead>
                            <tr>
                                <th class="r3pt-th-cell">
                                    <div class="r3pt-th-inner">
                                        <?php echo R3PT_Icons::get_header_icon( $col1_icon, $col1_icon_svg ); ?>
                                        <span class="r3pt-th-label"><?php echo esc_html( $col1 ); ?></span>
                                    </div>
                                </th>
                                <th class="r3pt-th-cell r3pt-th-price">
                                    <div class="r3pt-th-inner">
                                        <?php echo R3PT_Icons::get_header_icon( $col2_icon, $col2_icon_svg ); ?>
                                        <span class="r3pt-th-label"><?php echo esc_html( $col2 ); ?></span>
                                    </div>
                                </th>
                                <th class="r3pt-th-cell">
                                    <div class="r3pt-th-inner">
                                        <?php echo R3PT_Icons::get_header_icon( $col3_icon, $col3_icon_svg ); ?>
                                        <span class="r3pt-th-label"><?php echo esc_html( $col3 ); ?></span>
                                    </div>
                                </th>
                                <th class="r3pt-th-cell">
                                    <div class="r3pt-th-inner">
                                        <?php echo R3PT_Icons::get_header_icon( $col4_icon, $col4_icon_svg ); ?>
                                        <span class="r3pt-th-label"><?php echo esc_html( $col4 ); ?></span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $rows as $index => $row ) :
                                $cell_count = $row['cell_count'] ?? '';
                                $price      = $row['price']      ?? '';
                                $exosomes   = $row['exosomes']   ?? 'NO';
                                $hotel      = $row['hotel']      ?? 'NO';
                            ?>
                            <tr>
                                <td class="r3pt-td-cell-count">
                                    <div class="r3pt-cell-count-inner">
                                        <span class="r3pt-row-svg-icon"><?php echo R3PT_Icons::get_row_svg( $index ); ?></span>
                                        <span class="r3pt-cell-count-label"><?php echo esc_html( $cell_count ); ?></span>
                                    </div>
                                </td>
                                <td class="r3pt-price-cell"><?php echo esc_html( $price ); ?></td>
                                <td class="r3pt-exosomes-cell"><?php echo esc_html( $exosomes ); ?></td>
                                <td class="r3pt-hotel-cell"><?php echo esc_html( $hotel ); ?></td>
                            </tr>
                            <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <?php // ── Exosome Counts Section (Separate Table) ─────────── ?>
                <?php if ( $show_exosome && ! empty( $exo_rows ) ) : ?>
                <div class="r3pt-table-wrapper r3pt-exosome-wrapper">
                    <table class="r3pt-table">
                        <thead>
                            <tr class="r3pt-exosome-header-row" style="background-color:<?php echo esc_attr( $exo_header_bg ); ?>;">
                                <td colspan="4" class="r3pt-exosome-header-cell">
                                    <div class="r3pt-exosome-header-inner">
                                        <span class="r3pt-exosome-header-icon">
                                            <?php echo R3PT_Icons::get_exosome_header_icon(); ?>
                                        </span>
                                        <span class="r3pt-exosome-header-label"><?php echo esc_html( $exo_label ); ?></span>
                                    </div>
                                </td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $exo_rows as $exo_index => $exo_row ) :
                                $exo_cell  = $exo_row['cell_count'] ?? '';
                                $exo_price = $exo_row['price']      ?? '';
                                $exo_exo   = $exo_row['exosomes']   ?? 'no';
                                $exo_hotel = $exo_row['hotel']      ?? 'no';
                            ?>
                            <tr class="r3pt-exosome-row">
                                <td class="r3pt-td-cell-count">
                                    <div class="r3pt-cell-count-inner">
                                        <span class="r3pt-row-svg-icon"><?php echo R3PT_Icons::get_row_svg( $exo_index ); ?></span>
                                        <span class="r3pt-cell-count-label"><?php echo esc_html( $exo_cell ); ?></span>
                                    </div>
                                </td>
                                <td class="r3pt-price-cell"><?php echo esc_html( $exo_price ); ?></td>
                                <td class="r3pt-exosomes-cell"><?php echo esc_html( $exo_exo ); ?></td>
                                <td class="r3pt-hotel-cell"><?php echo esc_html( $exo_hotel ); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

                <?php // ── Footer ────────────────────────────────────── ?>
                <?php if ( $show_footer && ( $note1 || $note2 ) ) : ?>
                <div class="r3pt-footer-box">
                    <div class="r3pt-footer-icon-wrap">
                        <?php echo R3PT_Icons::get_info_icon(); ?>
                    </div>
                    <div class="r3pt-footer-notes">
                        <?php if ( $note1 ) : ?><p><?php echo esc_html( $note1 ); ?></p><?php endif; ?>
                        <?php if ( $note2 ) : ?><p><?php echo esc_html( $note2 ); ?></p><?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php // ── Warning Banner ────────────────────────────── ?>
                <?php if ( $show_warning && $warning ) : ?>
                <div class="r3pt-warning-row">
                    <span class="r3pt-warning-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22 22" width="18" height="18" aria-hidden="true">
                            <circle cx="11" cy="11" r="10" fill="#e07040"/>
                            <text x="11" y="16" text-anchor="middle" font-family="Arial" font-weight="bold" font-size="14" fill="#fff">!</text>
                        </svg>
                    </span>
                    <span class="r3pt-warning-badge"><?php echo esc_html( $warning ); ?></span>
                </div>
                <?php endif; ?>

            </div><!-- .r3pt-content -->

        </div><!-- .r3pt-wrapper -->
        <?php
        return ob_get_clean();
    }

    /**
     * Convert hex color to RGB array.
     */
    private static function hex_to_rgb( $hex ) {
        $hex = ltrim( $hex, '#' );
        if ( strlen( $hex ) === 3 ) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return [
            'r' => hexdec( substr( $hex, 0, 2 ) ),
            'g' => hexdec( substr( $hex, 2, 2 ) ),
            'b' => hexdec( substr( $hex, 4, 2 ) ),
        ];
    }
}
