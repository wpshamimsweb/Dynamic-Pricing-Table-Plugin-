<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * R3PT_Admin — WP Admin settings panel for R3 Pricing Table.
 *
 * Menu structure:
 *   R3 Pricing Table  (top level)
 *     ├── Manage Tables
 *     ├── Add / Edit Table
 *     └── Global Defaults
 */
class R3PT_Admin {

    const PAGE_SLUG    = 'r3-pricing-table';
    const NONCE_ACTION = 'r3pt_save_config';

    public function __construct() {
        add_action( 'admin_menu',             [ $this, 'register_menus' ] );
        add_action( 'admin_enqueue_scripts',  [ $this, 'enqueue_assets' ] );
        add_action( 'admin_post_r3pt_save',   [ $this, 'handle_save' ] );
        add_action( 'admin_post_r3pt_delete', [ $this, 'handle_delete' ] );
        add_action( 'admin_post_r3pt_duplicate', [ $this, 'handle_duplicate' ] );
        add_action( 'admin_notices',          [ $this, 'admin_notices' ] );
    }

    // ── Menus ─────────────────────────────────────────────────────────────────

    public function register_menus() {
        add_menu_page(
            __( 'R3 Pricing Table', 'r3-pricing-table' ),
            __( 'R3 Pricing Table', 'r3-pricing-table' ),
            'manage_options',
            self::PAGE_SLUG,
            [ $this, 'page_manage' ],
            'dashicons-editor-table',
            58
        );

        add_submenu_page(
            self::PAGE_SLUG,
            __( 'Manage Tables', 'r3-pricing-table' ),
            __( 'Manage Tables', 'r3-pricing-table' ),
            'manage_options',
            self::PAGE_SLUG,
            [ $this, 'page_manage' ]
        );

        add_submenu_page(
            self::PAGE_SLUG,
            __( 'Add / Edit Table', 'r3-pricing-table' ),
            __( 'Add New Table', 'r3-pricing-table' ),
            'manage_options',
            self::PAGE_SLUG . '-edit',
            [ $this, 'page_edit' ]
        );

        add_submenu_page(
            self::PAGE_SLUG,
            __( 'Global Defaults', 'r3-pricing-table' ),
            __( 'Global Defaults', 'r3-pricing-table' ),
            'manage_options',
            self::PAGE_SLUG . '-global',
            [ $this, 'page_global' ]
        );
    }

    // ── Asset Enqueue ─────────────────────────────────────────────────────────

    public function enqueue_assets( $hook ) {
        $pages = [
            'toplevel_page_' . self::PAGE_SLUG,
            'r3-pricing-table_page_' . self::PAGE_SLUG . '-edit',
            'r3-pricing-table_page_' . self::PAGE_SLUG . '-global',
        ];

        if ( ! in_array( $hook, $pages, true ) ) {
            return;
        }

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style(
            'r3-pricing-table-admin',
            R3PT_URL . 'assets/css/r3-pricing-table-admin.css',
            [],
            R3PT_VERSION
        );

        wp_enqueue_media();
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script(
            'r3-pricing-table-admin',
            R3PT_URL . 'assets/js/r3-pricing-table-admin.js',
            [ 'jquery', 'wp-color-picker' ],
            R3PT_VERSION,
            true
        );
    }

    // ── Admin Notices ─────────────────────────────────────────────────────────

    public function admin_notices() {
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, self::PAGE_SLUG ) === false ) {
            return;
        }

        if ( isset( $_GET['r3pt_saved'] ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Pricing table saved successfully.', 'r3-pricing-table' ) . '</p></div>';
        }
        if ( isset( $_GET['r3pt_deleted'] ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Pricing table deleted.', 'r3-pricing-table' ) . '</p></div>';
        }
        if ( isset( $_GET['r3pt_duplicated'] ) ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Pricing table duplicated.', 'r3-pricing-table' ) . '</p></div>';
        }
        if ( isset( $_GET['r3pt_error'] ) ) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Error: Please check the form and try again.', 'r3-pricing-table' ) . '</p></div>';
        }
    }

    // ── Page: Manage Tables ───────────────────────────────────────────────────

    public function page_manage() {
        $configs = R3PT_Settings::get_all_configs();
        ?>
        <div class="wrap r3pt-admin-wrap">
            <h1 class="wp-heading-inline"><?php esc_html_e( 'R3 Pricing Tables', 'r3-pricing-table' ); ?></h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG . '-edit' ) ); ?>" class="page-title-action">
                <?php esc_html_e( '+ Add New Table', 'r3-pricing-table' ); ?>
            </a>
            <hr class="wp-header-end">

            <?php if ( empty( $configs ) ) : ?>
                <div class="r3pt-empty-state">
                    <div class="r3pt-empty-icon">📋</div>
                    <h2><?php esc_html_e( 'No pricing tables yet', 'r3-pricing-table' ); ?></h2>
                    <p><?php esc_html_e( 'Create your first pricing table to get started. You can place it anywhere using a shortcode.', 'r3-pricing-table' ); ?></p>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG . '-edit' ) ); ?>" class="button button-primary button-hero">
                        <?php esc_html_e( 'Create Your First Table', 'r3-pricing-table' ); ?>
                    </a>
                </div>
            <?php else : ?>
                <div class="r3pt-table-list">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e( 'Table Name / Slug', 'r3-pricing-table' ); ?></th>
                                <th><?php esc_html_e( 'Location', 'r3-pricing-table' ); ?></th>
                                <th><?php esc_html_e( 'Effective Date', 'r3-pricing-table' ); ?></th>
                                <th><?php esc_html_e( 'Shortcode', 'r3-pricing-table' ); ?></th>
                                <th><?php esc_html_e( 'Actions', 'r3-pricing-table' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ( $configs as $slug => $config ) :
                            $merged = R3PT_Settings::get( $slug );
                            $edit_url = admin_url( 'admin.php?page=' . self::PAGE_SLUG . '-edit&slug=' . urlencode( $slug ) );
                            $delete_url = wp_nonce_url(
                                admin_url( 'admin-post.php?action=r3pt_delete&slug=' . urlencode( $slug ) ),
                                'r3pt_delete_' . $slug
                            );
                            $dup_url = wp_nonce_url(
                                admin_url( 'admin-post.php?action=r3pt_duplicate&slug=' . urlencode( $slug ) ),
                                'r3pt_duplicate_' . $slug
                            );
                            ?>
                            <tr>
                                <td>
                                    <strong><a href="<?php echo esc_url( $edit_url ); ?>"><?php echo esc_html( $slug ); ?></a></strong>
                                </td>
                                <td><?php echo esc_html( $merged['location_name'] ?? '—' ); ?></td>
                                <td><?php echo esc_html( $merged['effective_date'] ?? '—' ); ?></td>
                                <td>
                                    <code class="r3pt-shortcode-snippet">[r3_pricing_table id="<?php echo esc_attr( $slug ); ?>"]</code>
                                    <button class="r3pt-copy-btn" data-clipboard="[r3_pricing_table id=&quot;<?php echo esc_attr( $slug ); ?>&quot;]">
                                        <?php esc_html_e( 'Copy', 'r3-pricing-table' ); ?>
                                    </button>
                                </td>
                                <td class="r3pt-row-actions">
                                    <a href="<?php echo esc_url( $edit_url ); ?>" class="button button-small"><?php esc_html_e( 'Edit', 'r3-pricing-table' ); ?></a>
                                    <a href="<?php echo esc_url( $dup_url ); ?>" class="button button-small"><?php esc_html_e( 'Duplicate', 'r3-pricing-table' ); ?></a>
                                    <a href="<?php echo esc_url( $delete_url ); ?>" class="button button-small r3pt-btn-danger"
                                       onclick="return confirm('<?php esc_attr_e( 'Delete this pricing table? This cannot be undone.', 'r3-pricing-table' ); ?>')">
                                       <?php esc_html_e( 'Delete', 'r3-pricing-table' ); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    // ── Page: Edit / Add Table ────────────────────────────────────────────────

    public function page_edit() {
        $slug        = isset( $_GET['slug'] ) ? sanitize_key( $_GET['slug'] ) : '';
        $is_new      = empty( $slug );
        $config      = $slug ? R3PT_Settings::get( $slug ) : R3PT_Settings::get_global();
        $page_title  = $is_new
            ? __( 'Add New Pricing Table', 'r3-pricing-table' )
            : sprintf( __( 'Edit Table: %s', 'r3-pricing-table' ), esc_html( $slug ) );

        $rows = [];
        if ( ! empty( $config['pricing_rows'] ) ) {
            $rows = json_decode( $config['pricing_rows'], true );
            if ( ! is_array( $rows ) ) {
                $rows = [];
            }
        }

        $exo_rows = [];
        if ( ! empty( $config['exosome_rows'] ) ) {
            $exo_rows = json_decode( $config['exosome_rows'], true );
            if ( ! is_array( $exo_rows ) ) {
                $exo_rows = [];
            }
        }

        $this->render_edit_form( $slug, $config, $rows, $exo_rows, $page_title, $is_new, false );
    }

    // ── Page: Global Defaults ─────────────────────────────────────────────────

    public function page_global() {
        $config   = R3PT_Settings::get_global();
        $rows     = json_decode( $config['pricing_rows'] ?? '[]', true );
        $exo_rows = json_decode( $config['exosome_rows'] ?? '[]', true );
        if ( ! is_array( $rows ) ) { $rows = []; }
        if ( ! is_array( $exo_rows ) ) { $exo_rows = []; }

        $this->render_edit_form( 'global', $config, $rows, $exo_rows, __( 'Global Defaults', 'r3-pricing-table' ), false, true );
    }

    // ── Shared Edit Form ──────────────────────────────────────────────────────

    private function render_edit_form( $slug, $config, $rows, $exo_rows, $page_title, $is_new, $is_global ) {
        $icon_options = R3PT_Icons::header_icon_options();
        $defaults     = R3PT_Settings::base_defaults();
        ?>
        <div class="wrap r3pt-admin-wrap">
            <h1><?php echo esc_html( $page_title ); ?></h1>

            <?php if ( ! $is_new && ! $is_global ) : ?>
            <div class="r3pt-shortcode-banner">
                <strong><?php esc_html_e( 'Shortcode:', 'r3-pricing-table' ); ?></strong>
                <code>[r3_pricing_table id="<?php echo esc_attr( $slug ); ?>"]</code>
                <button class="r3pt-copy-btn" data-clipboard="[r3_pricing_table id=&quot;<?php echo esc_attr( $slug ); ?>&quot;]">
                    <?php esc_html_e( 'Copy', 'r3-pricing-table' ); ?>
                </button>
            </div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                <?php wp_nonce_field( self::NONCE_ACTION, 'r3pt_nonce' ); ?>
                <input type="hidden" name="action" value="r3pt_save">
                <input type="hidden" name="r3pt_is_global" value="<?php echo $is_global ? '1' : '0'; ?>">
                <input type="hidden" name="r3pt_original_slug" value="<?php echo esc_attr( $slug ); ?>">

                <!-- ── Tabs ── -->
                <div class="r3pt-tabs">
                    <nav class="r3pt-tab-nav">
                        <button type="button" class="r3pt-tab-btn active" data-tab="branding">🎨 <?php esc_html_e( 'Branding', 'r3-pricing-table' ); ?></button>
                        <button type="button" class="r3pt-tab-btn" data-tab="header">📍 <?php esc_html_e( 'Header', 'r3-pricing-table' ); ?></button>
                        <button type="button" class="r3pt-tab-btn" data-tab="columns">📊 <?php esc_html_e( 'Columns', 'r3-pricing-table' ); ?></button>
                        <button type="button" class="r3pt-tab-btn" data-tab="rows">💲 <?php esc_html_e( 'Pricing Rows', 'r3-pricing-table' ); ?></button>
                        <button type="button" class="r3pt-tab-btn" data-tab="exosome">🧬 <?php esc_html_e( 'Exosome Counts', 'r3-pricing-table' ); ?></button>
                        <button type="button" class="r3pt-tab-btn" data-tab="footer">📝 <?php esc_html_e( 'Footer', 'r3-pricing-table' ); ?></button>
                        <button type="button" class="r3pt-tab-btn" data-tab="background">🖼 <?php esc_html_e( 'Background', 'r3-pricing-table' ); ?></button>
                        <button type="button" class="r3pt-tab-btn" data-tab="style">🎨 <?php esc_html_e( 'Colors & Style', 'r3-pricing-table' ); ?></button>
                        <button type="button" class="r3pt-tab-btn" data-tab="visibility">👁 <?php esc_html_e( 'Visibility', 'r3-pricing-table' ); ?></button>
                    </nav>

                    <!-- ── Tab: Branding ── -->
                    <div class="r3pt-tab-panel active" id="r3pt-tab-branding">
                        <div class="r3pt-form-section">
                            <?php if ( $is_new ) : ?>
                            <div class="r3pt-field">
                                <label for="r3pt_slug"><?php esc_html_e( 'Table ID / Slug', 'r3-pricing-table' ); ?> <span class="required">*</span></label>
                                <input type="text" id="r3pt_slug" name="r3pt_slug" value=""
                                       placeholder="e.g. turkey, mexico, dubai"
                                       pattern="[a-z0-9\-_]+" required class="regular-text">
                                <p class="description"><?php esc_html_e( 'Lowercase letters, numbers, hyphens only. Used in the shortcode: [r3_pricing_table id="your-slug"]', 'r3-pricing-table' ); ?></p>
                            </div>
                            <?php elseif ( ! $is_global ) : ?>
                            <div class="r3pt-field">
                                <label><?php esc_html_e( 'Table Slug', 'r3-pricing-table' ); ?></label>
                                <code class="r3pt-slug-display"><?php echo esc_html( $slug ); ?></code>
                                <input type="hidden" name="r3pt_slug" value="<?php echo esc_attr( $slug ); ?>">
                            </div>
                            <?php endif; ?>

                            <div class="r3pt-field">
                                <label><?php esc_html_e( 'Logo Image', 'r3-pricing-table' ); ?></label>
                                <div class="r3pt-media-uploader">
                                    <div class="r3pt-logo-preview">
                                        <?php if ( ! empty( $config['logo_url'] ) ) : ?>
                                            <img src="<?php echo esc_url( $config['logo_url'] ); ?>" alt="Logo">
                                        <?php else : ?>
                                            <div class="r3pt-logo-placeholder"><?php esc_html_e( 'No logo set — default R3 SVG will be used', 'r3-pricing-table' ); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" name="logo_url" id="r3pt_logo_url" value="<?php echo esc_attr( $config['logo_url'] ?? '' ); ?>">
                                    <button type="button" class="button r3pt-upload-logo-btn"><?php esc_html_e( 'Upload / Choose Logo', 'r3-pricing-table' ); ?></button>
                                    <button type="button" class="button r3pt-remove-logo-btn" <?php echo empty( $config['logo_url'] ) ? 'style="display:none"' : ''; ?>>
                                        <?php esc_html_e( 'Remove Logo', 'r3-pricing-table' ); ?>
                                    </button>
                                </div>
                            </div>

                            <div class="r3pt-field">
                                <label for="r3pt_logo_text"><?php esc_html_e( 'Logo Side Text', 'r3-pricing-table' ); ?></label>
                                <textarea id="r3pt_logo_text" name="logo_text" rows="2" class="regular-text"><?php echo esc_textarea( $config['logo_text'] ?? "Stem\nCell" ); ?></textarea>
                                <p class="description"><?php esc_html_e( 'Text displayed next to the logo. Use line breaks for multi-line.', 'r3-pricing-table' ); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- ── Tab: Header ── -->
                    <div class="r3pt-tab-panel" id="r3pt-tab-header">
                        <div class="r3pt-form-section">
                            <div class="r3pt-field">
                                <label for="r3pt_location_name"><?php esc_html_e( 'Location Name', 'r3-pricing-table' ); ?></label>
                                <input type="text" id="r3pt_location_name" name="location_name"
                                       value="<?php echo esc_attr( $config['location_name'] ?? 'TURKEY' ); ?>" class="regular-text"
                                       placeholder="e.g. TURKEY, MEXICO, DUBAI">
                                <p class="description"><?php esc_html_e( 'Displays as "[LOCATION] PRICING" in the table header.', 'r3-pricing-table' ); ?></p>
                            </div>
                            <div class="r3pt-field">
                                <label for="r3pt_effective_label"><?php esc_html_e( 'Effective Label', 'r3-pricing-table' ); ?></label>
                                <input type="text" id="r3pt_effective_label" name="effective_label"
                                       value="<?php echo esc_attr( $config['effective_label'] ?? 'Effective' ); ?>" class="regular-text">
                            </div>
                            <div class="r3pt-field">
                                <label for="r3pt_effective_date"><?php esc_html_e( 'Effective Date', 'r3-pricing-table' ); ?></label>
                                <input type="text" id="r3pt_effective_date" name="effective_date"
                                       value="<?php echo esc_attr( $config['effective_date'] ?? 'June 1, 2026' ); ?>" class="regular-text"
                                       placeholder="e.g. June 1, 2026">
                            </div>
                        </div>
                    </div>

                    <!-- ── Tab: Column Headers ── -->
                    <div class="r3pt-tab-panel" id="r3pt-tab-columns">
                        <div class="r3pt-form-section">
                            <?php for ( $col = 1; $col <= 4; $col++ ) :
                                $col_label    = $config[ "col{$col}_label" ]      ?? '';
                                $col_icon     = $config[ "col{$col}_icon" ]       ?? 'cells';
                                $col_icon_svg = $config[ "col{$col}_icon_custom_svg" ] ?? '';
                            ?>
                            <div class="r3pt-column-header-row">
                                <h3><?php printf( esc_html__( 'Column %d', 'r3-pricing-table' ), $col ); ?></h3>
                                <div class="r3pt-field-row">
                                    <div class="r3pt-field r3pt-field-half">
                                        <label><?php esc_html_e( 'Label Text', 'r3-pricing-table' ); ?></label>
                                        <input type="text" name="col<?php echo $col; ?>_label"
                                               value="<?php echo esc_attr( $col_label ); ?>" class="regular-text">
                                    </div>
                                    <div class="r3pt-field r3pt-field-half">
                                        <label><?php esc_html_e( 'Header Icon', 'r3-pricing-table' ); ?></label>
                                        <select name="col<?php echo $col; ?>_icon" class="r3pt-icon-select" data-col="<?php echo $col; ?>">
                                            <?php foreach ( $icon_options as $icon_key => $icon_label ) : ?>
                                                <option value="<?php echo esc_attr( $icon_key ); ?>"
                                                    <?php selected( $col_icon, $icon_key ); ?>>
                                                    <?php echo esc_html( $icon_label ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <!-- Icon preview -->
                                <div class="r3pt-icon-preview-wrap">
                                    <div class="r3pt-icon-preview-label"><?php esc_html_e( 'Preview:', 'r3-pricing-table' ); ?></div>
                                    <div class="r3pt-icon-preview" data-col-preview="<?php echo $col; ?>">
                                        <?php echo R3PT_Icons::get_header_icon( $col_icon, $col_icon_svg ); ?>
                                    </div>
                                </div>
                                <!-- Custom SVG area -->
                                <div class="r3pt-field r3pt-custom-svg-field" id="r3pt-custom-svg-<?php echo $col; ?>"
                                     style="<?php echo $col_icon === 'custom' ? '' : 'display:none'; ?>">
                                    <label><?php esc_html_e( 'Custom SVG Code', 'r3-pricing-table' ); ?></label>
                                    <textarea name="col<?php echo $col; ?>_icon_custom_svg" rows="5" class="large-text code r3pt-custom-svg-input"
                                              placeholder='<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 36 36" width="28" height="28">...</svg>'><?php echo esc_textarea( $col_icon_svg ); ?></textarea>
                                    <p class="description"><?php esc_html_e( 'Paste full SVG markup. Use fill="#fff" for white icons on dark headers.', 'r3-pricing-table' ); ?></p>
                                </div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- ── Tab: Pricing Rows ── -->
                    <div class="r3pt-tab-panel" id="r3pt-tab-rows">
                        <div class="r3pt-form-section">
                            <p class="description r3pt-rows-note">
                                <?php esc_html_e( 'Each row represents one pricing tier. The cell icon cycles through progressive SVG designs automatically based on row order.', 'r3-pricing-table' ); ?>
                            </p>
                            <div class="r3pt-rows-table-wrap">
                                <table class="r3pt-rows-table" id="r3pt-rows-table">
                                    <thead>
                                        <tr>
                                            <th class="r3pt-col-handle">↕</th>
                                            <th><?php esc_html_e( 'Cell Count', 'r3-pricing-table' ); ?></th>
                                            <th><?php esc_html_e( 'Price (USD)', 'r3-pricing-table' ); ?></th>
                                            <th><?php esc_html_e( 'Free Exosomes', 'r3-pricing-table' ); ?></th>
                                            <th><?php esc_html_e( 'Free Hotel', 'r3-pricing-table' ); ?></th>
                                            <th class="r3pt-col-remove"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="r3pt-rows-body">
                                        <?php foreach ( $rows as $i => $row ) : ?>
                                        <tr class="r3pt-row-item">
                                            <td class="r3pt-col-handle r3pt-drag-handle" title="Drag to reorder">⠿</td>
                                            <td><input type="text" name="pricing_rows[<?php echo $i; ?>][cell_count]"
                                                        value="<?php echo esc_attr( $row['cell_count'] ?? '' ); ?>"
                                                        placeholder="e.g. 25M" class="small-text"></td>
                                            <td><input type="text" name="pricing_rows[<?php echo $i; ?>][price]"
                                                        value="<?php echo esc_attr( $row['price'] ?? '' ); ?>"
                                                        placeholder="e.g. $4,950.00" class="small-text"></td>
                                            <td><input type="text" name="pricing_rows[<?php echo $i; ?>][exosomes]"
                                                        value="<?php echo esc_attr( $row['exosomes'] ?? 'NO' ); ?>"
                                                        placeholder="NO or 1 VIAL" class="small-text"></td>
                                            <td><input type="text" name="pricing_rows[<?php echo $i; ?>][hotel]"
                                                        value="<?php echo esc_attr( $row['hotel'] ?? 'NO' ); ?>"
                                                        placeholder="NO or 2 NIGHTS" class="small-text"></td>
                                            <td><button type="button" class="button r3pt-remove-row">✕</button></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" id="r3pt-add-row" class="button button-secondary">
                                + <?php esc_html_e( 'Add Row', 'r3-pricing-table' ); ?>
                            </button>
                        </div>
                    </div>

                    <!-- ── Tab: Exosome Counts ── -->
                    <div class="r3pt-tab-panel" id="r3pt-tab-exosome">
                        <div class="r3pt-form-section">

                            <!-- Enable/Disable toggle -->
                            <div class="r3pt-field r3pt-toggle-field">
                                <label class="r3pt-toggle-label" for="r3pt_show_exosome">
                                    <?php esc_html_e( 'Enable Exosome Counts Section', 'r3-pricing-table' ); ?>
                                </label>
                                <label class="r3pt-switch">
                                    <input type="hidden" name="show_exosome_section" value="0">
                                    <input type="checkbox" id="r3pt_show_exosome" name="show_exosome_section"
                                           value="1" <?php checked( ( $config['show_exosome_section'] ?? '0' ), '1' ); ?>>
                                    <span class="r3pt-switch-slider"></span>
                                </label>
                                <p class="description"><?php esc_html_e( 'When enabled, a second table section will appear below the main pricing rows.', 'r3-pricing-table' ); ?></p>
                            </div>

                            <div class="r3pt-field">
                                <label for="r3pt_exosome_label"><?php esc_html_e( 'Section Label', 'r3-pricing-table' ); ?></label>
                                <input type="text" id="r3pt_exosome_label" name="exosome_section_label"
                                       value="<?php echo esc_attr( $config['exosome_section_label'] ?? 'EXOSOME COUNTS (BILLIONS)' ); ?>"
                                       class="regular-text">
                            </div>

                            <div class="r3pt-field r3pt-color-field">
                                <label><?php esc_html_e( 'Section Header Background', 'r3-pricing-table' ); ?></label>
                                <?php $exo_bg = $config['exosome_header_bg'] ?? '#1a2540'; ?>
                                <input type="text" name="exosome_header_bg"
                                       value="<?php echo esc_attr( $exo_bg ); ?>"
                                       class="r3pt-color-picker" data-default-color="<?php echo esc_attr( $exo_bg ); ?>">
                            </div>

                            <div class="r3pt-rows-table-wrap">
                                <table class="r3pt-rows-table" id="r3pt-exosome-rows-table">
                                    <thead>
                                        <tr>
                                            <th class="r3pt-col-handle">↕</th>
                                            <th><?php esc_html_e( 'Count Label', 'r3-pricing-table' ); ?></th>
                                            <th><?php esc_html_e( 'Price (USD)', 'r3-pricing-table' ); ?></th>
                                            <th><?php esc_html_e( 'Exosomes', 'r3-pricing-table' ); ?></th>
                                            <th><?php esc_html_e( 'Hotel', 'r3-pricing-table' ); ?></th>
                                            <th class="r3pt-col-remove"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="r3pt-exosome-rows-body">
                                        <?php foreach ( $exo_rows as $ei => $exo_row ) : ?>
                                        <tr class="r3pt-exosome-row-item">
                                            <td class="r3pt-col-handle r3pt-drag-handle" title="Drag to reorder">⠿</td>
                                            <td><input type="text" name="exosome_rows[<?php echo $ei; ?>][cell_count]"
                                                        value="<?php echo esc_attr( $exo_row['cell_count'] ?? '' ); ?>"
                                                        placeholder="e.g. 100 billion" class="small-text"></td>
                                            <td><input type="text" name="exosome_rows[<?php echo $ei; ?>][price]"
                                                        value="<?php echo esc_attr( $exo_row['price'] ?? '' ); ?>"
                                                        placeholder="e.g. $4,999.00" class="small-text"></td>
                                            <td><input type="text" name="exosome_rows[<?php echo $ei; ?>][exosomes]"
                                                        value="<?php echo esc_attr( $exo_row['exosomes'] ?? 'no' ); ?>"
                                                        class="small-text"></td>
                                            <td><input type="text" name="exosome_rows[<?php echo $ei; ?>][hotel]"
                                                        value="<?php echo esc_attr( $exo_row['hotel'] ?? 'no' ); ?>"
                                                        class="small-text"></td>
                                            <td><button type="button" class="button r3pt-remove-exosome-row">✕</button></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" id="r3pt-add-exosome-row" class="button button-secondary">
                                + <?php esc_html_e( 'Add Exosome Row', 'r3-pricing-table' ); ?>
                            </button>
                        </div>
                    </div>

                    <!-- ── Tab: Footer ── -->
                    <div class="r3pt-tab-panel" id="r3pt-tab-footer">
                        <div class="r3pt-form-section">
                            <div class="r3pt-field">
                                <label for="r3pt_footer_note_1"><?php esc_html_e( 'Footer Note 1', 'r3-pricing-table' ); ?></label>
                                <textarea id="r3pt_footer_note_1" name="footer_note_1" rows="2" class="large-text"><?php echo esc_textarea( $config['footer_note_1'] ?? '' ); ?></textarea>
                            </div>
                            <div class="r3pt-field">
                                <label for="r3pt_footer_note_2"><?php esc_html_e( 'Footer Note 2', 'r3-pricing-table' ); ?></label>
                                <textarea id="r3pt_footer_note_2" name="footer_note_2" rows="2" class="large-text"><?php echo esc_textarea( $config['footer_note_2'] ?? '' ); ?></textarea>
                            </div>
                            <div class="r3pt-field">
                                <label for="r3pt_footer_warning"><?php esc_html_e( 'Warning Banner Text', 'r3-pricing-table' ); ?></label>
                                <input type="text" id="r3pt_footer_warning" name="footer_warning"
                                       value="<?php echo esc_attr( $config['footer_warning'] ?? '' ); ?>" class="large-text"
                                       placeholder="e.g. ALL PROMOTIONAL OFFERS CANNOT BE COMBINED.">
                                <p class="description"><?php esc_html_e( 'Leave empty to hide the warning banner.', 'r3-pricing-table' ); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- ── Tab: Background ── -->
                    <div class="r3pt-tab-panel" id="r3pt-tab-background">
                        <div class="r3pt-form-section">

                            <?php $bg_type = $config['bg_type'] ?? 'color'; ?>

                            <div class="r3pt-field">
                                <label><?php esc_html_e( 'Background Type', 'r3-pricing-table' ); ?></label>
                                <div class="r3pt-bg-type-radios">
                                    <label class="r3pt-radio-card <?php echo $bg_type === 'color' ? 'active' : ''; ?>">
                                        <input type="radio" name="bg_type" value="color" <?php checked( $bg_type, 'color' ); ?>>
                                        <span class="r3pt-radio-icon">🎨</span>
                                        <span><?php esc_html_e( 'Solid Color', 'r3-pricing-table' ); ?></span>
                                    </label>
                                    <label class="r3pt-radio-card <?php echo $bg_type === 'image' ? 'active' : ''; ?>">
                                        <input type="radio" name="bg_type" value="image" <?php checked( $bg_type, 'image' ); ?>>
                                        <span class="r3pt-radio-icon">🖼</span>
                                        <span><?php esc_html_e( 'Image', 'r3-pricing-table' ); ?></span>
                                    </label>
                                    <label class="r3pt-radio-card <?php echo $bg_type === 'video' ? 'active' : ''; ?>">
                                        <input type="radio" name="bg_type" value="video" <?php checked( $bg_type, 'video' ); ?>>
                                        <span class="r3pt-radio-icon">🎬</span>
                                        <span><?php esc_html_e( 'Video', 'r3-pricing-table' ); ?></span>
                                    </label>
                                </div>
                            </div>

                            <!-- Color -->
                            <div class="r3pt-bg-section r3pt-bg-color-section" style="<?php echo $bg_type !== 'color' ? 'display:none;' : ''; ?>">
                                <div class="r3pt-field r3pt-color-field">
                                    <label><?php esc_html_e( 'Background Color', 'r3-pricing-table' ); ?></label>
                                    <?php $bg_color_val = $config['bg_color'] ?? '#f0ece6'; ?>
                                    <input type="text" name="bg_color"
                                           value="<?php echo esc_attr( $bg_color_val ); ?>"
                                           class="r3pt-color-picker" data-default-color="<?php echo esc_attr( $bg_color_val ); ?>">
                                </div>
                            </div>

                            <!-- Image -->
                            <div class="r3pt-bg-section r3pt-bg-image-section" style="<?php echo $bg_type !== 'image' ? 'display:none;' : ''; ?>">
                                <div class="r3pt-field">
                                    <label><?php esc_html_e( 'Background Image', 'r3-pricing-table' ); ?></label>
                                    <div class="r3pt-bg-image-preview">
                                        <?php if ( ! empty( $config['bg_image_url'] ) ) : ?>
                                            <img src="<?php echo esc_url( $config['bg_image_url'] ); ?>" alt="Background" style="max-height:120px;border-radius:6px;">
                                        <?php else : ?>
                                            <div class="r3pt-logo-placeholder"><?php esc_html_e( 'No background image set', 'r3-pricing-table' ); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" name="bg_image_url" id="r3pt_bg_image_url" value="<?php echo esc_attr( $config['bg_image_url'] ?? '' ); ?>">
                                    <button type="button" class="button r3pt-upload-bg-btn"><?php esc_html_e( 'Upload / Choose Image', 'r3-pricing-table' ); ?></button>
                                    <button type="button" class="button r3pt-remove-bg-btn" <?php echo empty( $config['bg_image_url'] ) ? 'style="display:none"' : ''; ?>>
                                        <?php esc_html_e( 'Remove Image', 'r3-pricing-table' ); ?>
                                    </button>
                                </div>
                                <div class="r3pt-field r3pt-color-field">
                                    <label><?php esc_html_e( 'Fallback Background Color', 'r3-pricing-table' ); ?></label>
                                    <?php $bg_color_val = $config['bg_color'] ?? '#f0ece6'; ?>
                                    <input type="text" name="bg_color"
                                           value="<?php echo esc_attr( $bg_color_val ); ?>"
                                           class="r3pt-color-picker" data-default-color="<?php echo esc_attr( $bg_color_val ); ?>">
                                </div>
                            </div>

                            <!-- Video -->
                            <div class="r3pt-bg-section r3pt-bg-video-section" style="<?php echo $bg_type !== 'video' ? 'display:none;' : ''; ?>">
                                <div class="r3pt-field">
                                    <label for="r3pt_bg_video_url"><?php esc_html_e( 'Video URL (.mp4)', 'r3-pricing-table' ); ?></label>
                                    <input type="url" id="r3pt_bg_video_url" name="bg_video_url"
                                           value="<?php echo esc_attr( $config['bg_video_url'] ?? '' ); ?>"
                                           class="large-text" placeholder="https://example.com/video.mp4">
                                    <p class="description"><?php esc_html_e( 'Paste a direct .mp4 URL. The video will autoplay, loop, and be muted.', 'r3-pricing-table' ); ?></p>
                                </div>
                                <div class="r3pt-field r3pt-color-field">
                                    <label><?php esc_html_e( 'Fallback Background Color', 'r3-pricing-table' ); ?></label>
                                    <?php $bg_color_val = $config['bg_color'] ?? '#f0ece6'; ?>
                                    <input type="text" name="bg_color"
                                           value="<?php echo esc_attr( $bg_color_val ); ?>"
                                           class="r3pt-color-picker" data-default-color="<?php echo esc_attr( $bg_color_val ); ?>">
                                </div>
                            </div>

                            <!-- Overlay (for image & video) -->
                            <div class="r3pt-bg-section r3pt-bg-overlay-section" style="<?php echo $bg_type === 'color' ? 'display:none;' : ''; ?>">
                                <h3><?php esc_html_e( 'Background Overlay', 'r3-pricing-table' ); ?></h3>
                                <div class="r3pt-field r3pt-color-field">
                                    <label><?php esc_html_e( 'Overlay Color', 'r3-pricing-table' ); ?></label>
                                    <?php $overlay_color = $config['bg_overlay_color'] ?? '#000000'; ?>
                                    <input type="text" name="bg_overlay_color"
                                           value="<?php echo esc_attr( $overlay_color ); ?>"
                                           class="r3pt-color-picker" data-default-color="<?php echo esc_attr( $overlay_color ); ?>">
                                </div>
                                <div class="r3pt-field">
                                    <label for="r3pt_overlay_opacity"><?php esc_html_e( 'Overlay Opacity (%)', 'r3-pricing-table' ); ?></label>
                                    <div class="r3pt-slider-wrap">
                                        <input type="range" id="r3pt_overlay_opacity_range" min="0" max="100" step="1"
                                               value="<?php echo esc_attr( $config['bg_overlay_opacity'] ?? 40 ); ?>">
                                        <input type="number" id="r3pt_overlay_opacity" name="bg_overlay_opacity"
                                               min="0" max="100"
                                               value="<?php echo esc_attr( $config['bg_overlay_opacity'] ?? 40 ); ?>"
                                               class="small-text">
                                        <span>%</span>
                                    </div>
                                    <p class="description"><?php esc_html_e( 'Controls how opaque the overlay is (0 = transparent, 100 = fully opaque).', 'r3-pricing-table' ); ?></p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ── Tab: Colors & Style ── -->
                    <div class="r3pt-tab-panel" id="r3pt-tab-style">
                        <div class="r3pt-form-section r3pt-colors-grid">

                            <?php
                            $color_fields = [
                                'header_title_color'   => __( 'Location Title Color', 'r3-pricing-table' ),
                                'effective_date_color' => __( 'Effective Date Color', 'r3-pricing-table' ),
                                'table_header_bg'      => __( 'Table Header Background', 'r3-pricing-table' ),
                                'price_col_bg'         => __( 'Price Column Header Bg', 'r3-pricing-table' ),
                                'price_color'          => __( 'Price Text Color', 'r3-pricing-table' ),
                                'table_text_color'     => __( 'Table Body Text Color', 'r3-pricing-table' ),
                                'warning_bg_color'     => __( 'Warning Banner Background', 'r3-pricing-table' ),
                                'warning_text_color'   => __( 'Warning Banner Text Color', 'r3-pricing-table' ),
                                'row_odd_bg'           => __( 'Row Odd Background', 'r3-pricing-table' ),
                                'row_even_bg'          => __( 'Row Even Background', 'r3-pricing-table' ),
                            ];
                            foreach ( $color_fields as $field => $label ) :
                                $value = $config[ $field ] ?? $defaults[ $field ];
                            ?>
                            <div class="r3pt-field r3pt-color-field">
                                <label><?php echo esc_html( $label ); ?></label>
                                <input type="text" name="<?php echo esc_attr( $field ); ?>"
                                       value="<?php echo esc_attr( $value ); ?>"
                                       class="r3pt-color-picker" data-default-color="<?php echo esc_attr( $value ); ?>">
                            </div>
                            <?php endforeach; ?>

                            <div class="r3pt-field">
                                <label for="r3pt_border_radius"><?php esc_html_e( 'Table Border Radius (px)', 'r3-pricing-table' ); ?></label>
                                <div class="r3pt-slider-wrap">
                                    <input type="range" id="r3pt_border_radius_range" min="0" max="30" step="1"
                                           value="<?php echo esc_attr( $config['table_border_radius'] ?? 12 ); ?>">
                                    <input type="number" id="r3pt_border_radius" name="table_border_radius"
                                           min="0" max="30"
                                           value="<?php echo esc_attr( $config['table_border_radius'] ?? 12 ); ?>"
                                           class="small-text">
                                    <span>px</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ── Tab: Visibility ── -->
                    <div class="r3pt-tab-panel" id="r3pt-tab-visibility">
                        <div class="r3pt-form-section">
                            <p class="description r3pt-rows-note">
                                <?php esc_html_e( 'Control which elements are visible on the frontend. Toggle off any section you don\'t need.', 'r3-pricing-table' ); ?>
                            </p>

                            <?php
                            $visibility_fields = [
                                'show_background'     => [ 'label' => __( 'Background (Color / Image / Video)', 'r3-pricing-table' ),
                                                           'desc'  => __( 'Hides the background entirely (transparent wrapper).', 'r3-pricing-table' ) ],
                                'show_logo'           => [ 'label' => __( 'Logo / Branding', 'r3-pricing-table' ),
                                                           'desc'  => __( 'Shows the logo image and text area.', 'r3-pricing-table' ) ],
                                'show_header_title'   => [ 'label' => __( 'Location Title (e.g. MEXICO PRICING)', 'r3-pricing-table' ),
                                                           'desc'  => __( 'Shows the large location heading.', 'r3-pricing-table' ) ],
                                'show_effective_date' => [ 'label' => __( 'Effective Date Line', 'r3-pricing-table' ),
                                                           'desc'  => __( 'Shows the "Effective July, 2026" line.', 'r3-pricing-table' ) ],
                                'show_table'          => [ 'label' => __( 'Main Pricing Table', 'r3-pricing-table' ),
                                                           'desc'  => __( 'Shows the main pricing rows table.', 'r3-pricing-table' ) ],
                                'show_footer'         => [ 'label' => __( 'Footer Notes', 'r3-pricing-table' ),
                                                           'desc'  => __( 'Shows the info box with footer notes.', 'r3-pricing-table' ) ],
                                'show_warning'        => [ 'label' => __( 'Warning Banner', 'r3-pricing-table' ),
                                                           'desc'  => __( 'Shows the warning/disclaimer banner at the bottom.', 'r3-pricing-table' ) ],
                            ];
                            foreach ( $visibility_fields as $field => $info ) :
                                $checked = ( $config[ $field ] ?? '1' ) === '1';
                            ?>
                            <div class="r3pt-visibility-row">
                                <div class="r3pt-visibility-info">
                                    <strong><?php echo esc_html( $info['label'] ); ?></strong>
                                    <span class="description"><?php echo esc_html( $info['desc'] ); ?></span>
                                </div>
                                <label class="r3pt-switch">
                                    <input type="hidden" name="<?php echo esc_attr( $field ); ?>" value="0">
                                    <input type="checkbox" name="<?php echo esc_attr( $field ); ?>"
                                           value="1" <?php checked( $checked ); ?>>
                                    <span class="r3pt-switch-slider"></span>
                                </label>
                            </div>
                            <?php endforeach; ?>

                        </div>
                    </div>

                </div><!-- .r3pt-tabs -->

                <!-- ── Submit ── -->
                <div class="r3pt-form-footer">
                    <?php submit_button( $is_global ? __( 'Save Global Defaults', 'r3-pricing-table' ) : __( 'Save Pricing Table', 'r3-pricing-table' ), 'primary large', 'submit', false ); ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=' . self::PAGE_SLUG ) ); ?>" class="button button-large">
                        <?php esc_html_e( '← Back to Tables', 'r3-pricing-table' ); ?>
                    </a>
                </div>
            </form>
        </div>
        <?php
    }

    // ── Handlers ──────────────────────────────────────────────────────────────

    public function handle_save() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized.', 'r3-pricing-table' ) );
        }
        if ( ! isset( $_POST['r3pt_nonce'] ) || ! wp_verify_nonce( $_POST['r3pt_nonce'], self::NONCE_ACTION ) ) {
            wp_die( esc_html__( 'Nonce verification failed.', 'r3-pricing-table' ) );
        }

        $is_global = ! empty( $_POST['r3pt_is_global'] ) && $_POST['r3pt_is_global'] === '1';
        $data      = R3PT_Settings::sanitize_config( $_POST );

        if ( $is_global ) {
            R3PT_Settings::save_global( $data );
            wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_SLUG . '-global&r3pt_saved=1' ) );
            exit;
        }

        // Named config
        $slug = sanitize_key( $_POST['r3pt_slug'] ?? '' );
        if ( empty( $slug ) ) {
            wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_SLUG . '-edit&r3pt_error=1' ) );
            exit;
        }

        R3PT_Settings::save( $slug, $data );
        wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_SLUG . '-edit&slug=' . urlencode( $slug ) . '&r3pt_saved=1' ) );
        exit;
    }

    public function handle_delete() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized.', 'r3-pricing-table' ) );
        }

        $slug = sanitize_key( $_GET['slug'] ?? '' );
        if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'r3pt_delete_' . $slug ) ) {
            wp_die( esc_html__( 'Nonce verification failed.', 'r3-pricing-table' ) );
        }

        R3PT_Settings::delete( $slug );
        wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_SLUG . '&r3pt_deleted=1' ) );
        exit;
    }

    public function handle_duplicate() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'Unauthorized.', 'r3-pricing-table' ) );
        }

        $slug = sanitize_key( $_GET['slug'] ?? '' );
        if ( ! wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'r3pt_duplicate_' . $slug ) ) {
            wp_die( esc_html__( 'Nonce verification failed.', 'r3-pricing-table' ) );
        }

        $new_slug = $slug . '-copy';
        // Ensure unique
        $configs = R3PT_Settings::get_all_configs();
        $counter = 1;
        while ( isset( $configs[ $new_slug ] ) ) {
            $new_slug = $slug . '-copy-' . $counter;
            $counter++;
        }

        R3PT_Settings::duplicate( $slug, $new_slug );
        wp_safe_redirect( admin_url( 'admin.php?page=' . self::PAGE_SLUG . '-edit&slug=' . urlencode( $new_slug ) . '&r3pt_duplicated=1' ) );
        exit;
    }
}
