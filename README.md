# R3 Pricing Table — WordPress Plugin

A standalone WordPress plugin that renders a beautiful, customizable pricing table anywhere on your site using a shortcode. **No Elementor required.**

---

## Installation

1. Upload the `r3-pricing-table` folder to `/wp-content/plugins/`
2. Activate the plugin in **WP Admin → Plugins**
3. Go to **WP Admin → R3 Pricing Table** to configure

---

## Usage

### Basic shortcode (uses Global Defaults)
```
[r3_pricing_table]
```

### Named configuration
```
[r3_pricing_table id="turkey"]
[r3_pricing_table id="mexico"]
[r3_pricing_table id="dubai"]
```

### Inline overrides
```
[r3_pricing_table id="turkey" location="DUBAI" effective_date="August 1, 2026"]
```

---

## Admin Panel

Go to **WP Admin → R3 Pricing Table**:

| Sub-page | Purpose |
|---|---|
| **Manage Tables** | List all saved configs with Edit / Duplicate / Delete |
| **Add / Edit Table** | Full tabbed form for one named config |
| **Global Defaults** | Fallback values for all tables |

### Edit Form Tabs
- **Branding** — Logo image upload, logo side text, table slug
- **Header** — Location name, effective label & date
- **Column Headers** — Label + icon picker per column (8 built-in SVGs + custom SVG paste)
- **Pricing Rows** — Drag-sortable repeater rows (Cell Count, Price, Exosomes, Hotel)
- **Footer** — Two notes + warning banner text
- **Colors & Style** — 11 color pickers + border radius slider

---

## Column Header Icons

Each of the 4 column headers has an independent icon picker:

| Option | Icon |
|---|---|
| `cells` | Cell cluster SVG (default for col 1) |
| `dollar` | Dollar sign in circle (default for col 2) |
| `vial` | Vial / exosome jar (default for col 3) |
| `hotel` | Hotel building with palm trees (default for col 4) |
| `syringe` | Syringe / injection icon |
| `star` | Star badge |
| `heart` | Heart / care icon |
| `dna` | DNA double helix |
| `custom` | Paste any custom SVG markup |

---

## Shortcode Attributes

All attributes are optional overrides:

| Attribute | Description | Example |
|---|---|---|
| `id` | Config slug to load | `id="turkey"` |
| `location_name` | Override location | `location_name="DUBAI"` |
| `effective_date` | Override date | `effective_date="Aug 1, 2026"` |
| `effective_label` | Override label | `effective_label="Prices as of"` |
| `footer_warning` | Override warning text | `footer_warning="Limited time only"` |

---

## File Structure

```
r3-pricing-table/
├── r3-pricing-table.php              ← Main plugin bootstrap
├── includes/
│   ├── class-r3-admin.php            ← Admin settings panel
│   ├── class-r3-settings.php         ← Config storage helpers
│   ├── class-r3-shortcode.php        ← Shortcode renderer
│   └── class-r3-svg-icons.php        ← SVG icon library
├── assets/
│   ├── css/
│   │   ├── r3-pricing-table-front.css  ← Frontend styles
│   │   └── r3-pricing-table-admin.css  ← Admin panel styles
│   └── js/
│       └── r3-pricing-table-admin.js   ← Admin interactivity
└── README.md
```

---

## Prefix Reference

All identifiers use the `r3pt_` / `r3pt-` / `R3PT_` prefix to avoid conflicts:

- **PHP Classes**: `R3PT_Admin`, `R3PT_Shortcode`, `R3PT_Settings`, `R3PT_Icons`
- **WordPress Options**: `r3pt_global`, `r3pt_configs`
- **Shortcode**: `r3_pricing_table`
- **CSS Classes**: `.r3pt-wrapper`, `.r3pt-table`, etc.
- **CSS Custom Properties**: `--r3pt-bg`, `--r3pt-title-color`, etc.
- **Admin Menu Slug**: `r3-pricing-table`
- **Script/Style Handles**: `r3-pricing-table`, `r3-pricing-table-admin`
- **Nonce Actions**: `r3pt_save_config`, `r3pt_delete_*`, `r3pt_duplicate_*`

---

## Version

`1.0.0` — September 2026
