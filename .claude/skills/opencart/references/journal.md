# Journal 3.2.5 — Theme Customization Reference

## Ταυτότητα

- **Version**: 3.2.5
- **Author**: DigitalAtelier
- **OCMOD constant**: `JOURNAL3_INSTALLED` = `3.2.5`
- **OCMOD files**: `system/journal3.ocmod.xml`, `system/journal3_debug.ocmod.xml`

---

## Προσέγγιση Customization

Δεν χρησιμοποιούμε child theme. Αλλαγές γίνονται:

1. **Journal Admin** — layout, modules, colors, fonts, Custom CSS/JS
2. **Twig templates** — απευθείας σε `catalog/view/theme/journal3/template/`
3. **OCMOD** — για PHP-level αλλαγές σε controllers/models
4. **Custom CSS** — `catalog/view/theme/journal3/stylesheet/custom.css` ή Journal Admin → Custom CSS

---

## Δομή Directories

```
catalog/view/theme/journal3/
├── assets/                    ← custom assets (gitkeep, άδειο)
├── fonts_custom/              ← custom fonts
├── icons/                     ← icomoon icon font (svg, ttf, woff, woff2)
├── icons_old/                 ← legacy icon set
├── icons_custom/              ← placeholder για custom icons
├── image/                     ← theme images (journal3.png)
├── js/                        ← 20 JavaScript αρχεία
├── lib/                       ← 21 third-party libraries
├── stylesheet/
│   ├── style.min.css          ← compiled CSS (μη επεξεργάζεσαι απευθείας)
│   └── style.min.js           ← compiled JS
└── template/                  ← 150 Twig templates

catalog/controller/journal3/   ← 84 PHP controllers
catalog/model/journal3/        ← 14 PHP models
admin/controller/journal3/     ← 20 PHP controllers
admin/model/journal3/          ← 13 PHP models
admin/view/template/journal3/  ← 3 twig templates (error, journal3, js)
system/library/journal3/       ← core library classes + data
```

---

## Template Files

### Ιεραρχία Templates (150 αρχεία)

```
template/
├── account/           23 αρχεία   — login, register, orders, addresses κλπ
├── affiliate/          2 αρχεία
├── checkout/           9 αρχεία
├── common/            13 αρχεία   — header, footer, columns, home
├── error/              1 αρχείο
├── extension/total/    4 αρχεία
├── information/        3 αρχεία
├── product/            7 αρχεία   — category, product, compare, search
└── journal3/          70+ αρχεία
    ├── blog/           4 αρχεία   — feed, post, posts, comment_email
    ├── checkout/       7 αρχεία   — checkout overrides
    ├── headers/
    │   ├── desktop/   4 layouts: classic, compact, mega, slim
    │   └── mobile/    3 layouts: header_mobile_1/2/3
    └── module/        54 αρχεία   — ένα ανά Journal module
```

### Journal Module Templates (54 αρχεία σε `template/journal3/module/`)

| Template | Περιγραφή |
|----------|-----------|
| accordion_menu.twig | Accordion navigation |
| background_slider.twig | Full-screen background slider |
| banners.twig | Banner displays |
| banners_grid.twig | Banner grid layout |
| blocks.twig | Generic content blocks |
| blog_*.twig (5 files) | Blog posts, categories, search, tags, side posts |
| bottom_menu.twig | Footer navigation menu |
| button.twig | CTA button |
| catalog.twig / catalog_blocks.twig | Catalog displays |
| categories.twig | Category grid/list |
| countdown.twig | Countdown timer |
| faq.twig | FAQ accordion |
| filter.twig | Product filter panel |
| form.twig / form_email.twig | Contact forms |
| gallery.twig | Image gallery/lightbox |
| header_notice.twig / layout_notice.twig | Notification bars |
| icons_menu.twig | Menu with icons |
| image.twig | Single image block |
| info_blocks.twig | Info/feature blocks |
| links_menu.twig | Simple links menu |
| main_menu.twig | Primary navigation |
| manufacturers.twig | Manufacturers list |
| marquee.twig | Scrolling text |
| master_slider.twig | MasterSlider integration |
| newsletter.twig | Newsletter signup |
| notification.twig | Notifications |
| popup.twig / popup_content.twig / popup_page.twig | Popup modals |
| product_blocks.twig | Product grid blocks |
| product_blocks_attributes.twig | Product attributes display |
| product_blocks_reviews.twig | Product reviews |
| product_tabs.twig | Product tabbed content |
| products.twig | Products listing |
| side_menu.twig | Sidebar navigation |
| side_products.twig | Sidebar products |
| slider.twig | Swiper/carousel slider |
| spacer.twig | Layout spacer |
| testimonials.twig | Testimonials |
| text.twig | Text block |
| title.twig | Section title |
| top_menu.twig | Top bar menu |

---

## JavaScript Files (`catalog/view/theme/journal3/js/`)

| Αρχείο | Λειτουργία |
|--------|-----------|
| journal.js | Main theme script |
| common.js | Global/shared functionality |
| head.js | Header scripts |
| product.js | Product page logic |
| products.js | Product listing logic |
| checkout.js | Checkout process |
| account.js | Account pages |
| search.js | Search functionality |
| filter.js | Product filtering |
| carousel.js | Carousel behavior |
| slider.js | Slider/swiper logic |
| gallery.js | Gallery functionality |
| form.js | Form handling |
| newsletter.js | Newsletter |
| countdown.js | Countdown timer |
| countup.js | Counter animations |
| master_slider.js | MasterSlider |
| blog_search.js | Blog search |
| catalog.js | Catalog page |
| stepper.js | Step/quantity controls |

### Third-Party Libraries (`lib/`)
bootstrap, bootstrap-rtl, font-awesome, jquery, swiper, swiper-latest, masterslider, lightgallery, ion-rangeSlider, vue, typeahead, lozad (lazy load), ias (infinite scroll), imagezoom, countdown, countup, smoothscroll, hoverintent, loadjs, accounting, he

---

## System Library (`system/library/journal3/`)

### Core Classes (14 αρχεία)
| Αρχείο | Ρόλος |
|--------|-------|
| journal.php | Main Journal library class |
| assets.php | Asset management/loading |
| base.php | Base class |
| browser.php | Browser detection |
| build.php | Build/compilation |
| cache.php | Caching system |
| db.php | Database operations |
| document.php | Document management |
| image.php | Image manipulation + WebP |
| opencart.php | OpenCart integration |
| productextras.php | Extra product features |
| request.php | HTTP request handling |
| response.php | HTTP response handling |
| url.php | URL generation |

### Options System (`options/` — 30 αρχεία)
UI components για το admin panel: background, border, borderradius, color, colorscheme, divider, font, gap, icon, image, imagedimensions, imagelang, inputlang, inputpair, inputtriple, inputvalue, itemsperrow, link, margin, outline, padding, range, shadow, status, toggle + parser, parserold, postfilter, productfilter, option (base)

### Utils (`utils/` — 4 αρχεία)
arr.php, html.php, min.php, str.php

### OpenCart Integration (`opencart/` — 6 αρχεία)
autocomplete.php, bestseller.php, eventresult.php, menucontroller.php, modulecontroller.php, tables.php

### Settings Data (`data/settings/` — 302 JSON αρχεία)
Όλες οι default ρυθμίσεις ανά module/section:
```
data/settings/
├── blog/        — blog_category, blog_comment, blog_post
├── common/      — accordion, account, alerts, auto_carousel, κλπ
├── dashboard/
├── layout/      — layout configurations
├── module/      — 54 αρχεία (ένα ανά module)
├── settings/    — general, performance, active_skin, seo, custom_code, blog
├── skin/        — footer, page, products, product, blog, global, header
├── system/
└── variables/
```

---

## Admin Panel Structure

**Path**: Extensions → Journal 3

| Section | Λειτουργία |
|---------|-----------|
| Global Settings | Fonts, colors, general layout |
| Header | Logo, menu, search, cart |
| Footer | Columns, links, copyright |
| Modules | Journal-specific modules |
| Blog | Blog settings (αν ενεργό) |
| Custom CSS | Inject CSS χωρίς file edits |
| Custom JS | Inject JS χωρίς file edits |
| Skin | Design/skin management |
| Variables | CSS variables/theme tokens |
| Import/Export | Config backup/restore |
| Newsletter | Newsletter management |

### Admin Controllers (`admin/controller/journal3/`)
blog_category, blog_comment, blog_post, blog_setting, events, import_export, journal, layout, message, module_footer, module_header, module_layout, module_product, newsletter, setting, skin, startup, style, system, variable

### Admin Models (`admin/model/journal3/`)
blog_category, blog_comment, blog_post, import_export, journal, layout, message, module, newsletter, setting, skin, style, variable

---

## Database Tables

**Source of truth**: `system/library/journal3/opencart/tables.php` — class `Tables::TABLES`

Όλα τα tables χρησιμοποιούν `DB_PREFIX` (default: `oc_`), οπότε αποθηκεύονται ως `oc_journal3_*`.

### Blog Tables

| Table | Columns | Σκοπός |
|-------|---------|--------|
| `oc_journal3_blog_category` | category_id, parent_id, image, status, sort_order | Blog κατηγορίες |
| `oc_journal3_blog_category_description` | category_id, language_id, name, description, meta_title, meta_keywords, meta_robots, meta_description, keyword | Multilingual περιγραφές κατηγοριών |
| `oc_journal3_blog_category_to_layout` | category_id, store_id, layout_id | Layout ανά κατηγορία/store |
| `oc_journal3_blog_category_to_store` | category_id, store_id | Multi-store mapping |
| `oc_journal3_blog_post` | post_id, author_id, image, comments, status, sort_order, date_created, date_updated, views, **post_data** (MEDIUMTEXT JSON) | Blog posts |
| `oc_journal3_blog_post_description` | post_id, language_id, name, description, meta_title, meta_keywords, meta_robots, meta_description, keyword, tags | Multilingual post content |
| `oc_journal3_blog_post_to_category` | post_id, category_id | Post → κατηγορία mapping |
| `oc_journal3_blog_post_to_layout` | post_id, store_id, layout_id | Layout ανά post/store |
| `oc_journal3_blog_post_to_product` | post_id, product_id | Post → product σύνδεση |
| `oc_journal3_blog_post_to_store` | post_id, store_id | Multi-store mapping |
| `oc_journal3_blog_comments` | comment_id, parent_id, post_id, customer_id, author_id, name, email, website, comment, status, date | Comments (nested, parent_id για threads) |

### Layout & Modules

| Table | Columns | Σκοπός |
|-------|---------|--------|
| `oc_journal3_layout` | layout_id, **layout_data** (MEDIUMTEXT JSON) | Layout definitions — rows/columns/modules |
| `oc_journal3_module` | module_id, module_type, module_name, **module_data** (MEDIUMTEXT JSON) | Όλα τα Journal modules (banners, sliders, popups, κλπ) |

> **Σημαντικό**: Sliders, popups, banners, slideshows **δεν** έχουν ξεχωριστά tables — αποθηκεύονται ως records στο `oc_journal3_module` με `module_type` = `slider` / `popup` / κλπ. Το `module_data` είναι JSON με όλες τις ρυθμίσεις.

### Settings & Theming

| Table | Columns | Σκοπός |
|-------|---------|--------|
| `oc_journal3_setting` | store_id, setting_group, setting_name, setting_value (TEXT), serialized | Global theme settings (PK: store_id + group + name) |
| `oc_journal3_skin` | skin_id, skin_name | Skin definitions |
| `oc_journal3_skin_setting` | skin_id, setting_name, setting_value (TEXT), serialized | Ρυθμίσεις ανά skin |
| `oc_journal3_style` | style_name, style_label, style_type, style_value (MEDIUMTEXT), serialized | CSS style overrides (PK: style_name + style_type) |
| `oc_journal3_variable` | variable_name, variable_label, variable_type, variable_value (TEXT), serialized | CSS/theme variables (PK: variable_name + variable_type) |

### Forms & Newsletter

| Table | Columns | Σκοπός |
|-------|---------|--------|
| `oc_journal3_message` | message_id, name, email, fields (TEXT JSON), store_id, url, date | Form submissions από Journal forms |
| `oc_journal3_newsletter` | newsletter_id, name, email, ip, store_id | Newsletter subscriptions |

### Product Extensions

| Table | Columns | Σκοπός |
|-------|---------|--------|
| `oc_journal3_product_attribute` | product_id, attribute_id, language_id, text, sort_order | Custom product attributes |
| `oc_journal3_product_sales` | product_id, sales | Sales counter ανά product (για sorting) |

### Πλήρης λίστα (22 tables)
```
oc_journal3_blog_category
oc_journal3_blog_category_description
oc_journal3_blog_category_to_layout
oc_journal3_blog_category_to_store
oc_journal3_blog_comments
oc_journal3_blog_post
oc_journal3_blog_post_description
oc_journal3_blog_post_to_category
oc_journal3_blog_post_to_layout
oc_journal3_blog_post_to_product
oc_journal3_blog_post_to_store
oc_journal3_layout
oc_journal3_message
oc_journal3_module
oc_journal3_newsletter
oc_journal3_product_attribute
oc_journal3_product_sales
oc_journal3_setting
oc_journal3_skin
oc_journal3_skin_setting
oc_journal3_style
oc_journal3_variable
```

**Κανόνας**: Όταν κάνεις clone ένα site, πάντα κάνε dump/import αυτά τα tables.

### Export/Import Journal Config
```bash
# Export όλα τα Journal tables
docker compose exec db mariadb-dump \
  -u opencart -psecret opencart \
  oc_journal3_setting \
  oc_journal3_layout \
  oc_journal3_module \
  oc_journal3_skin \
  oc_journal3_skin_setting \
  oc_journal3_style \
  oc_journal3_variable \
  > journal_config.sql

# Import σε target environment
docker compose exec -T db mariadb \
  -u opencart -psecret opencart < journal_config.sql
```

> Για full site clone (με blog), πρόσθεσε και τα `oc_journal3_blog_*` tables στο dump.

---

## Event System

Το Journal hooks σε 17 OpenCart events (`catalog/controller/journal3/event/`):

account, cache, cart, category, footer, header, language, layout, maintenance, manufacturer, not_found, notification, performance, product, products, search, sitemap

---

## Custom CSS & Styling

### Preferred approach — Journal Admin
Extensions → Journal 3 → Global Settings → Custom CSS

Χρησιμοποίησέ το για:
- Color/font overrides
- Layout adjustments
- Mobile breakpoint fixes

### Αρχείο approach — για μεγαλύτερο CSS
```
catalog/view/theme/journal3/stylesheet/custom.css
```

**Σημείωση**: Μην επεξεργάζεσαι το `style.min.css` — είναι compiled.

---

## OCMOD για Journal

Για PHP-level αλλαγές σε Journal controllers/models:

```xml
<file path="catalog/controller/journal3/module/header.php">
  <operation>
    <search><![CDATA[
    // unique line στον journal controller
    ]]></search>
    <add position="after"><![CDATA[
    // η προσθήκη σου
    ]]></add>
  </operation>
</file>
```

Μετά: Admin → Extensions → Modifications → Refresh.

### Διαθέσιμοι Journal Controllers για OCMOD
- `catalog/controller/journal3/` — 84 controllers
- Modules: `catalog/controller/journal3/module/<module_name>.php`
- Events: `catalog/controller/journal3/event/<event_name>.php`
- Core: journal.php, assets.php, blog.php, checkout.php, events.php, grid.php, mail.php, price.php, product.php, product_extras.php, search.php, seo.php, settings.php, skin.php, startup.php

---

## Twig Variables στα Journal Templates

```twig
{{ journal.settings }}        {# All journal settings #}
{{ journal.module_data }}     {# Current module config #}
{{ journal.is_mobile }}       {# Boolean #}
{{ journal.layout }}          {# Current layout info #}
```

---

## Layout JSON Schema — Πλήρης Τεκμηρίωση

> Source: πραγματικά δεδομένα από `oc_journal3_layout` και `oc_journal3_module`

### layout_data — Top Level

```json
{
  "general": { ... },          // layout-level settings (boxed/fullwidth, header/footer overrides κλπ)
  "enabledPositions": ["top", "bottom"],   // μόνο τα active positions
  "positions": { ... }
}
```

#### `general` — σημαντικά keys

| Key | Τιμές | Σκοπός |
|-----|-------|--------|
| `headerDesktop` | `"module_id/module_type"` | Override desktop header για αυτό το layout |
| `headerMobile` | `"module_id/module_type"` | Override mobile header |
| `footerMenu` | module_id | Override footer menu |
| `pageStyle` | string | Page style override |
| `breadcrumbsStatus` | `"true"/"false"` | Show/hide breadcrumbs |
| `breadcrumbsVisibility` | `"true"/"false"` | Visibility |
| `columnLeftWidth` | px/% | Left column width |
| `columnRightWidth` | px/% | Right column width |
| `containerMaxWidth` | px | Max container width |
| `columnLeftSticky` / `columnRightSticky` | bool | Sticky columns |
| `bodyBackground` | background object | Page background |
| `containerBackground` | background object | Container background |
| `typographyStyle` | string | Typography override |

---

### positions — Layout Positions

**8 content positions + 1 absolute:**

```json
"positions": {
    "top":            { "rows": [...] },
    "bottom":         { "rows": [...] },
    "content_top":    { "rows": [...] },
    "content_bottom": { "rows": [...] },
    "column_left":    { "rows": [...] },
    "column_right":   { "rows": [...] },
    "footer_top":     { "rows": [...] },    // αν υπάρχει
    "header_top":     { "rows": [...] },    // αν υπάρχει
    "absolute": {
        "popup":              "module_id or ''",
        "notification":       "module_id or ''",
        "header_notice":      "module_id or ''",
        "bottom_menu":        "module_id or ''",
        "side_menu":          "module_id or ''",
        "background_slider":  "module_id or ''",
        "fullscreen_slider":  "module_id or ''"
    }
}
```

---

### Row Structure

```json
{
  "id": "uuid-v4",
  "options": {
    "status": {
      "status": "true",
      "device": ["phone", "tablet", "desktop"],
      "customer": "",
      "admin": "false",
      "params": "",
      "customer_group_1": "true",
      "store_0": "true"
    },
    "fullwidth": "true/false",
    "contentAlign": "false",
    "stackColumns": "true/false",
    "stackBase": "tablet/phone",
    "width": "",
    "minHeight": "",
    "padding": { "padding": "", "padding-top": "", ... },
    "background": { ... },
    "rowBackground": { ... },
    "colsBackground": { ... },
    "border": { ... },
    "backgroundBorderRadius": { ... },
    "shadow": { ... },
    "customClass": "",
    "customCss": "",
    "color_scheme": "",
    "zIndex": "",
    "waveStatus": "false",
    "videoBgStatus": "false",
    "overlayStatus": "false",
    "rowColsAlign": "",
    "rowColsAlignV": "",
    "autoGrowColumns": "false"
    // ... + 50+ ακόμα styling options
  },
  "columns": [ ... ]
}
```

**Row `status` object** — controls visibility:
- `status`: `"true"/"false"` — αν εμφανίζεται
- `device`: array `["phone","tablet","desktop"]` — σε ποιες συσκευές
- `customer`: `""`/`"logged_in"`/`"logged_out"` — customer state
- `admin`: `"true"/"false"` — εμφάνιση μόνο σε admins
- `store_0`: `"true"/"false"` — ανά store (multi-store)
- `customer_group_1`: `"true"/"false"` — ανά customer group

---

### Column Structure

```json
{
  "id": "uuid-v4",
  "options": {
    "width": "50",        // % — άθροισμα columns = 100
    "status": "true/false",
    "visibility": "true/false",
    "align": "",          // text-align
    "alignH": "",         // horizontal align items
    "padding": { ... },
    "background": { ... },
    "border": { ... },
    "borderRadius": { ... },
    "shadow": { ... },
    "outline": { ... },
    "customClass": "",
    "customCss": "",
    "color_scheme": "",
    "order": "",
    "zIndex": "",
    "minHeight": "",
    "autoHeight": "false",
    "autoGrowColumn": "false",
    "stickyCols": "false",
    "containerStyle": "",
    "itemsMargin": "",
    "itemsPadding": "",
    "itemsBackground": { ... },
    "colOverlayBackground": { ... },
    "colOverlayOpacity": ""
  },
  "items": [ ... ]
}
```

---

### Item (Module Wrapper) Structure

```json
{
  "id": "uuid-v4",
  "options": {
    "visibility": "true",
    "padding": { "padding": "", "padding-top": "", ... },
    "margin": { "margin": "", "margin-top": "", ... },
    "background": { ... },
    "border": { ... },
    "borderRadius": { ... },
    "shadow": { ... },
    "outline": { ... },
    "customClass": "",
    "customCss": "",
    "order": "",
    "zIndex": "",
    "gridItemMinHeight": "",
    "gridItemMaxWidth": "",
    "gridItemAlignSelf": "",
    "gridItemOffset": "",
    "gridItemContentWidth": "false",
    "containerStyle": "",
    "moduleTitleVisibility": ""
  },
  "item": {
    "id":   "947",           // module_id (string) από oc_journal3_module
    "name": "Slider / ...",  // module_name (για αναγνώριση)
    "type": "slider"         // module_type
  }
}
```

---

### Background Object (επαναχρησιμοποιείται παντού)

```json
{
  "background-color": { "color": "" },
  "background-image": "",
  "background-repeat": "",
  "background-position": "",
  "backgroundPositionX": "",
  "backgroundPositionY": "",
  "backgroundPositionUnit": "px",
  "background-attachment": "",
  "background-origin": "",
  "background-clip": "",
  "background-blend-mode": "",
  "backgroundSizeW": "",
  "backgroundSizeH": "",
  "backgroundSizeUnit": "px",
  "gradient": "",
  "overlay": "",
  "blur": "",
  "none": "false",
  "overwrite": "false"
}
```

---

### Global Layout (id = -1)

Φορτώνεται **πάντα** και merge-άρεται με το specific layout.

```json
{
  "popup":             "1052",
  "notification":      "137",
  "header_notice":     "926",
  "bottom_menu":       "266",
  "background_slider": "",
  "fullscreen_slider": "",
  "side_menu":         ""
}
```

Απλή flat δομή — μόνο module_ids για absolute modules.

---

### module_data — Δομή ανά module type

Κάθε module έχει: `{ "general": { ... }, "items": [ ... ] }`

#### Slider

```json
{
  "general": {
    // carousel settings, height, navigation, pagination,
    // autoplay, loop, effect, thumbs, breakpoints...
  },
  "items": [
    {
      "id": "uuid",
      "image": "path/to/image.jpg",
      "link": { "href": "", "target": "" },
      "alt": "",
      "type": "image/video",
      "status": "true",
      "align": "",
      "alignV": "",
      "overlay": { ... },
      "slideBackground": { ... },
      "items": [ /* layers */ ]
      // + 80+ styling options
    }
  ]
}
```

#### Banners Grid

```json
{
  "general": {
    "gridType": "",           // auto/fixed/masonry
    "heightType": "",
    "OverlayStatus": "",
    "autoGridContainerMarginOuter": "",
    // + carousel, buttons, image layer settings...
  },
  "items": [
    {
      "id": "uuid",
      "image": "path/to/image.jpg",
      "link": { ... },
      "alt": "",
      "status": "true",
      "items": [ /* overlay layers */ ]
      // + styling options
    }
  ]
}
```

#### Products

```json
{
  "general": {
    "gridType": "",
    "display": "",             // grid/list/carousel
    "carousel": { ... },
    "default": "",             // default tab
    "moduleProductList": "",
    "moduleProductCartTooltip": "",
    // + layout, pagination, filters...
  },
  "items": [
    {
      "id": "uuid",
      "title": "Latest",       // Tab title
      "name": "",
      "tabType": "",           // latest/bestseller/featured/special/category
      "filter": { ... },       // category_id, limit, sort, etc.
      "status": "true",
      "icon": "",
      "link": { ... }
    }
  ]
}
```

#### Categories

```json
{
  "general": {
    "gridType": "",
    "carousel": { ... },
    "default": ""
  },
  "items": [
    {
      "id": "uuid",
      "title": "Tab name",
      "type": "category/all",
      "category": "category_id",
      "categories": [],
      "limit": "8",
      "status": "true"
    }
  ]
}
```

#### Title

```json
{
  "general": {
    // font, size, color, align, padding, margin,
    // divider, accent title, buttons, container style...
    // (δεν έχει items)
  }
}
```

#### Info Blocks

```json
{
  "general": {
    "gridType": "",
    "align": "",
    "titleFont": { ... },
    "imageDimensions": { ... }
    // + carousel settings...
  },
  "items": [
    {
      "id": "uuid",
      "name": "",
      "status": "true",
      "icon": "",
      "align": "",
      "customClass": "",
      // title, text, button, image, counter settings...
    }
  ]
}
```

---

### CSS Classes που παράγονται αυτόματα

```
// Layout level
.layout-{layout_id}
.route-{route-with-dashes}         // π.χ. route-product-category
.category-{id} / .product-{id}     // page-specific

// Grid structure
.grid-rows                          // wrapper
.grid-row                           // κάθε row
.grid-row-{position}-{row_id}       // π.χ. grid-row-top-1
.grid-col                           // κάθε column
.grid-col-{position}-{row_id}-{col_id}
.grid-item                          // module wrapper
.grid-module-{position}-{row_id}-{col_id}-{module_id}

// Layout columns
.two-column / .one-column
.column-left / .column-right

// Device
.desktop / .mobile / .phone / .tablet
.desktop-header-active / .mobile-header-active

// Header type
.classic / .compact / .mega / .slim
.header-mobile-1/2/3
```

---

## Layout System — Runtime Flow

### Πώς φορτώνεται ένα layout σε κάθε request

```
Request
  └─ OCMOD → journal3/startup (πριν τον router)
       ├─ Δημιουργεί Journal object + όλα τα sub-objects
       ├─ Loads: settings, events, assets, skin
       └─ Registers OC events (μέσω journal3/events)
            └─ controller/common/column_left|right|content_top|bottom/before
                 └─ journal3/event/layout → controller_common_position_before
                      ├─ 1. Βρίσκει layout_id
                      ├─ 2. Φορτώνει layout data από DB (με cache)
                      ├─ 3. Κάνει parse τη δομή
                      └─ 4. Φορτώνει κάθε module
```

---

### Βήμα 1 — Εύρεση layout_id

Γίνεται μόνο μία φορά ανά request (static). Ιεραρχία:

| Route | Πηγή layout_id |
|-------|---------------|
| `product/category` | `category->getLayoutId($category_id)` |
| `product/product` | `product->getLayoutId($product_id)` |
| `information/information` | `information->getLayoutId($information_id)` |
| `product/manufacturer/info` | `manufacturer->getLayoutId($manufacturer_id)` |
| `journal3/blog` | `model_journal3_blog->getBlogCategoryLayoutId()` |
| `journal3/blog/post` | `model_journal3_blog->getBlogPostLayoutId()` |
| fallback | `model_design_layout->getLayout($route)` (OC default) |
| τελικό fallback | `config_layout_id` (default store layout) |

---

### Βήμα 2 — Φόρτωση layout data από DB

```php
// model_journal3_layout->get($layout_id)
SELECT layout_id, layout_data
FROM oc_journal3_layout
WHERE layout_id = '$id' OR layout_id = -1
ORDER BY layout_id DESC
```

**Κλειδί**: `layout_id = -1` είναι το **global layout** — φορτώνεται πάντα και merge-άρεται με το specific layout. Χρησιμοποιείται για global modules (popup, notification κλπ).

Το `layout_data` είναι **JSON** (MEDIUMTEXT). Αποκωδικοποιείται με `journal3_db->decode()`.

Αποτέλεσμα cache-άρεται: `journal3_cache->get('layout.' . $layout_id)`

---

### Βήμα 3 — Δομή layout (JSON)

```
layout_data (JSON)
├── general {}                    ← layout-level settings (boxed/fullwidth κλπ)
├── enabledPositions []           ← ποια positions είναι ενεργά
└── positions
    ├── column_left / column_right / content_top / content_bottom
    │   top / bottom / footer_top / footer_bottom
    │   └── rows[]
    │       └── options {}        ← row settings
    │       └── columns[]
    │           └── options {}    ← column settings
    │           └── items[]
    │               └── options {} ← module wrapper settings
    │               └── item
    │                   ├── module_id
    │                   └── module_type
    └── absolute
        ├── popup        → module_id
        ├── notification → module_id
        ├── header_notice→ module_id
        ├── bottom_menu  → module_id
        ├── side_menu    → module_id
        └── background_slider → module_id
    └── global (fallback για absolute modules αν δεν υπάρχουν στο absolute)
```

**CSS classes που παράγονται αυτόματα:**
```
grid-row-{position}-{row_id}
grid-col-{position}-{row_id}-{col_id}
grid-module-{position}-{row_id}-{col_id}-{module_id}
```

---

### Βήμα 4 — Φόρτωση κάθε module

```php
// Για κάθε item στο layout:
$this->load->controller('journal3/' . $module_type, [
    'module_id'   => $module_id,
    'module_type' => $module_type,
]);

// Module controller → model_journal3_module->get($id, $type)
SELECT * FROM oc_journal3_module
WHERE module_id = '$module_id'
// + check: module_type πρέπει να ταιριάζει

// Επιστρέφει: json_decode(module_data) → array
```

---

### Header Loading

Το header **δεν** φορτώνεται από το layout positions system — έχει δική του διαδικασία:

```
controller/common/header/before
  └─ journal3/event/layout → controller_common_header_before
       ├─ Διαβάζει setting 'headerDesktop' → "module_id/module_type"
       ├─ Διαβάζει setting 'headerMobile'  → "module_id/module_type"
       ├─ Φορτώνει journal3/header_desktop (controller)
       ├─ Φορτώνει journal3/header_mobile (controller)
       └─ Φορτώνει menus:
            desktop: main_menu, main_menu_2, top_menu (x3)
            mobile:  main_menu, top_menu (x2), secondary_menu, bottom_menu

view/common/header/before
  └─ journal3/event/layout → view_common_header_before
       ├─ Desktop: load view 'journal3/headers/desktop/{headerType}'
       │   headerType: classic | compact | mega | slim
       └─ Mobile:  load view 'journal3/headers/mobile/header_mobile_{mobileHeaderType}'
            mobileHeaderType: 1 | 2 | 3
```

---

### Footer Loading

```
controller/common/footer/before
  └─ Διαβάζει setting 'footerMenu' (ή 'footerMenuPhone' για mobile)
  └─ Φορτώνει journal3/footer_menu controller

view/common/footer/before
  └─ Inject: journal3_bottom, journal3_footer_menu στο footer template
```

---

### Absolute Modules (Global)

Αυτά τα modules φορτώνονται **πάντα**, ανεξάρτητα από layout position:

| Module | Πού εμφανίζεται |
|--------|----------------|
| popup | Inject στο header template |
| notification | Inject στο header template |
| header_notice | Inject στο header template |
| bottom_menu | Inject στο header template (mobile) |
| side_menu | Inject στο header template |
| background_slider | Inject στο header template |

Αποθηκεύονται είτε στο `positions.absolute` του specific layout είτε στο `positions.global` του global layout (id=-1). Το absolute έχει προτεραιότητα.

---

### Settings Flow

```
startup → journal3/settings controller
  └─ Φορτώνει oc_journal3_setting (store_id + active skin)
  └─ $this->journal3->load($settings)  ← merge στο static $settings array

// Κάθε module φορτώνει και τα δικά του settings:
$this->journal3->load($module_settings)

// Access σε templates:
{{ journal.settings }}      ← το static $settings array
{{ journal.get('key') }}    ← specific setting
```

---

### Cache System

Journal έχει δικό του cache layer (`journal3_cache`):
- `layout.{layout_id}` — parsed layout (CSS + PHP settings + module list)
- Invalidation: Extensions → Journal 3 → Settings → Clear Cache
- `system/storage/cache/` για OC cache (templates κλπ)

**Σημαντικό**: Αλλαγή στο Journal Admin → γράφει αμέσως στη DB, αλλά χρειάζεται Clear Cache για να φανεί.

---

### Κανόνας: Μην επεξεργάζεσαι layout DB rows manually

Το `layout_data` JSON είναι πολύπλοκο. Χρησιμοποίησε πάντα το Journal Admin UI.

---

## API Endpoints

```
catalog/controller/api/journal3/form.php       ← Form submission
catalog/controller/api/journal3/newsletter.php ← Newsletter subscription
```

---

## Cache Clearing

| Ενέργεια | Πού |
|----------|-----|
| Journal cache | Extensions → Journal 3 → Settings → Clear Cache |
| OC cache | `system/storage/cache/` (διαγραφή αρχείων) |
| OCMOD cache | Admin → Extensions → Modifications → Refresh |

---

## Troubleshooting

| Πρόβλημα | Αιτία | Λύση |
|----------|-------|------|
| Layout changes not showing | Journal cache | Extensions → Journal 3 → Clear Cache |
| Custom CSS not applying | Journal cache | Same |
| Module not appearing | Not assigned to layout | Journal admin → assign module to position |
| Template changes ignored | OC cache | Clear `system/storage/cache/` |
| OCMOD not applying | XML error ή no Refresh | Modifications → Refresh |
| Images not showing | `catalog/view/theme/journal3/image.php` handles resizing — check permissions |

---

## Header Layouts

### Desktop (4 layouts)
`catalog/view/theme/journal3/template/journal3/headers/desktop/`
- **classic.twig** — Κλασικό 2-row header
- **compact.twig** — Compact single-row
- **mega.twig** — Mega menu layout
- **slim.twig** — Slim/minimal

### Mobile (3 layouts)
`catalog/view/theme/journal3/template/journal3/headers/mobile/`
- header_mobile_1.twig
- header_mobile_2.twig
- header_mobile_3.twig

---

## Image Handling

### Path Format
Images στο `module_data` αποθηκεύονται ως **relative path χωρίς `image/` prefix**:
```
"image": {"lang_1": "catalog/products/my-image.jpg"}
```
Το πραγματικό αρχείο βρίσκεται στο: `image/catalog/products/my-image.jpg`

### Resize System
- `system/library/journal3/image.php` → `resize($filename, $width, $height, $resize_type, $convert_webp)`
- Input: path **χωρίς** `image/` prefix
- Cache output: `image/cache/{basename}-{width}x{height}{resize_type}.{ext}`
- Dev mode: routes μέσω `catalog/view/theme/journal3/image.php`
- Production: creates cached images directly
- Υποστηρίζει: WebP conversion, JPEG/PNG optimization, SVG passthrough, GIF passthrough, external URLs

### Frontend Image Handler
`catalog/view/theme/journal3/image.php` — dynamic resizer:
- URL format: `image.php/{md5_hash}.{ext}/{width}-{height}-{resize_type}/{filename}`
- Validates requests με MD5 hash
- Resizes on-the-fly αν δεν υπάρχει στο cache

---

## Variable / Color / Font System

### __VAR__ Prefix

Το `__VAR__` είναι το core mechanism για reusable design tokens. Αποθηκεύεται στη DB και αναλύεται runtime.

```
DB (oc_journal3_variable)
  variable_name: "ACCENT"
  variable_type: "color"
  variable_value: '{"color":"rgba(255,100,0,1)"}'

→ Φορτώνεται ως: $variables['color']['__VAR__ACCENT'] = {...}
→ CSS output: --j-color-color-accent: rgba(255,100,0,1)
→ Αναφορά σε settings: "__VAR__ACCENT"
```

**3 τύποι color references:**
| Format | Παράδειγμα | Σκοπός |
|--------|-----------|--------|
| Direct | `"rgba(255,100,0,1)"` | Hardcoded χρώμα |
| `__VAR__` | `"__VAR__ACCENT"` | Variable reference από DB |
| `__SCHEME__` | `"__SCHEME__foreground_primary"` | Color scheme token (HSL) |

### Color Scheme System (HSL)
Color schemes αποθηκεύονται ως HSL για δυναμικό manipulation:
```css
/* Κάθε scheme παράγει CSS variables: */
.color-scheme-light {
  --j-color-scheme-accent-h: 25;
  --j-color-scheme-accent-s: 100%;
  --j-color-scheme-accent-l: 50%;
  --j-color-scheme-accent-a: 1;
}
```
- Scheme name → CSS class: `LIGHT` → `.color-scheme-light`, `DARK` → `.color-scheme-dark`
- Εφαρμόζεται στο HTML element ή σε specific rows/columns/modules
- Επιτρέπει lightness adjustment: `calc(var(--j-color-scheme-accent-l) - 10%)`

### Font System

**Google Fonts**: `system/library/journal3/data/fonts/google.json`
- ~1000+ fonts, κάθε entry: `{family, variants[], subsets[], category}`
- Φορτώνονται dynamically based on skin settings

**System Fonts**: `system/library/journal3/data/fonts/system.json`
```json
{"fonts": ["Helvetica Neue, Helvetica, Arial, sans-serif", "Georgia, serif", ...]}
```

**Font object (20 keys):**
```json
{
  "font-family": "Roboto",
  "font-size": "16",
  "font-size-unit": "px",
  "font-weight": "400",
  "font-style": "normal",
  "color": {"color": "__VAR__ACCENT"},
  "text-align": "left",
  "text-transform": "uppercase",
  "text-decoration": "none",
  "text-decoration-color": {"color": ""},
  "text-underline-offset": "",
  "letter-spacing": "",
  "word-spacing": "",
  "line-height": "",
  "line-clamp": "",
  "style": "__VAR__TYPOGRAPHY",
  "textShadowOffsetX": "",
  "textShadowOffsetY": "",
  "textShadowBlur": "",
  "textShadowColor": {"color": ""}
}
```
- `style` field → references `__VAR__` style για base font merging

### Variable Types στα Settings JSON

| Type | Σκοπός | Output |
|------|--------|--------|
| `Variable` | Reference σε `common/{name}.json` | Expands σε πολλαπλά sub-settings |
| `Font` | Font styling | CSS font properties |
| `Color` | Single color | CSS color value |
| `ColorScheme` | Theme selector | CSS class name |
| `Background` | Background styling | CSS background properties |
| `Border` | Border styling | CSS border properties |
| `BorderRadius` | Border radius | CSS border-radius |
| `Shadow` | Box shadow | CSS box-shadow |
| `Padding` / `Margin` / `Gap` | Spacing | CSS spacing |
| `Toggle` | Boolean → CSS rules | On/off CSS class or value |
| `Radio` | Multiple choice → CSS | Maps value to CSS dict |
| `Icon` | Font icon | CSS ::before content |
| `Input` / `InputLang` | Text value | CSS custom property |

### Skin Settings Files

| File | Σκοπός |
|------|--------|
| `data/settings/skin/global/general.json` | 880+ global options (colors, typography, buttons, forms) |
| `data/settings/skin/header/general.json` | Header desktop/mobile modules, logo |
| `data/settings/skin/footer/general.json` | Footer layout |
| `data/settings/skin/page/general.json` | Page layout settings |
| `data/settings/skin/products/general.json` | Product listing config |
| `data/settings/skin/product/general.json` | Single product page |
| `data/settings/skin/blog/general.json` | Blog settings |

### DB Tables για Variables/Skin
```
oc_journal3_variable  → CSS/theme variables (name, type, value)
oc_journal3_style     → Style definitions (name, label, type, value)
oc_journal3_skin      → Skin records (id, name)
oc_journal3_skin_setting → Per-skin settings (skin_id, setting_name, setting_value)
oc_journal3_setting   → Global settings (store_id, group, name, value)
```

---

## Module Data — Πλήρης Schema

### Universal Patterns (ισχύουν σε ΟΛΑ τα modules)

```json
{
  "general": { ...module-wide settings... },
  "items": [ ...array of items... ]   // δεν υπάρχει σε title, text, header modules
}
```

**Multilingual values** — πάντα dict, ποτέ bare string:
```json
"title": {"lang_1": "Shop Now"},
"image": {"lang_1": "catalog/products/img.jpg"}
```

**Status dict** (controls visibility):
```json
"status": {
  "status": "true",
  "device": ["phone", "tablet", "desktop"],
  "customer": "",
  "admin": "false",
  "params": "",
  "customer_group_1": "true",
  "store_0": "true"
}
```

**Schedule dict:**
```json
"schedule": {"from": "", "to": "", "between": "true"}
```

**`_multi` keys** — responsive overrides array:
```json
"fontSize_multi": [{"min": "", "max": "__VAR__S", "value": "14"}]
```

**`-hold` keys** — deprecated, ignored (backward compat)

---

### Common Object Types

**Background dict (19 keys):**
```json
{
  "background-color": {"color": ""},
  "background-image": "",
  "background-repeat": "",
  "background-position": "",
  "backgroundPositionX": "",
  "backgroundPositionY": "",
  "backgroundPositionUnit": "px",
  "background-attachment": "",
  "background-origin": "",
  "background-clip": "",
  "background-blend-mode": "",
  "backgroundSizeW": "",
  "backgroundSizeH": "",
  "backgroundSizeUnit": "px",
  "gradient": "",
  "overlay": "",
  "blur": "",
  "none": "false",
  "overwrite": "false"
}
```

**Shadow dict (8 keys):**
```json
{"offsetX": "", "offsetY": "", "blur": "", "spread": "", "color": {"color": ""}, "inner": "false", "none": "false", "custom": ""}
```

**Border dict (8 keys):**
```json
{"border-width": "", "border-top-width": "", "border-right-width": "", "border-bottom-width": "", "border-left-width": "", "border-style": "", "border-color": {"color": ""}, "gradient": ""}
```

**Link dict (7 keys):**
```json
{"type": "", "id": "", "url": "", "scroll": "", "target": "", "rel": "", "page": ""}
```

**Dimensions dict (9 keys):**
```json
{"width": "", "height": "", "resize": "", "tablet_width": "", "tablet_height": "", "tablet_resize": "", "phone_width": "", "phone_height": "", "phone_resize": ""}
```

**Icon dict (10 keys):**
```json
{"size": "", "color": {"color": ""}, "offsetX": "", "offsetY": "", "flip": "", "margin": {}, "code": "", "name": "", "type": "", "image": ""}
```

**Size/offset dict (2 keys):**
```json
{"first": "", "second": ""}
```

---

### SLIDER module_data

```
general: 529 keys
items[]: slides
  items[].items[]: layers (polymorphic)
```

**`general` key groups:**
- Playback: `autoplay`, `autoplayDelay`, `loop`, `effect`, `speed`, `ease`, `parallax`, `parallax_bg`
- Dimensions: `imageDimensions` (dict), `slideHeight`, `slideMinHeight`
- Layout: `slidesPerView`, `slidesContentWidthType`, `align`, `alignV`, `centeredSlides`
- Navigation: `bulletsVisibility`, `bulletsType`, `buttonsVisibility`, `bulletsDirection`
- Thumbnails: `thumbnails`, `thumbnailsGap`, `thumbnailsDimensions`, `thumbnailsPosition`
- Buttons: `button1Style`, `button2Style`, `buttonFont`, `buttonRadius`, `buttonPadding`
- Background: `sliderBG`, `sliderBorder`, `imageOverlay`, `imageOverlayStatus`
- Layer defaults: `layersAlignV`, `layersBorderRadius`, `layersShadow`, `layersPadding`
- Column layout: `slidesColRightWidth`, `slidesColsGap`, `slidesColsStack`

**Slide object (82 keys):**
```json
{
  "id": "uuid-v4",
  "name": "Slide 1",
  "type": "image",
  "image": {"lang_1": "catalog/..."},
  "alt": {"lang_1": ""},
  "link": {...link dict...},
  "items": [...layers...],
  "slideBackground": {...background dict...},
  "overlay": {...background dict...},
  "overlayStatus2": "",
  "overlayBlend": "",
  "overlayOpacity": "",
  "align": "",
  "alignV": "",
  "thumbImage": {"lang_1": ""},
  "slidesLayersAlignH": "",
  "status": {...status dict...},
  "videoHtml5Url": {"lang_1": ""},
  "videoHtml5Poster": {"lang_1": ""}
}
```

**Layer object (polymorphic ~200 keys, `type` = text/image/icon/button/video/svg/shape):**

Common keys:
```json
{
  "id": "uuid-v4",
  "name": "Main Text",
  "type": "text",
  "position": "left",
  "layerZIndex": "",
  "layerAlignH": "",
  "layersAlignV": "",
  "layerOffset": {"first": "", "second": ""},
  "layerMaxWidth": "",
  "status": {...status dict...},
  "customClass": "",
  "customCss": "",
  "color_scheme": "",
  "layerParallaxScale": "",
  "layerParallaxOpacity": "",
  "layerParallaxOffsetX": "",
  "layerParallaxOffsetY": ""
}
```

**Text layer keys:** `text` (lang dict), `textFont`, `textFontHover`, `textSizeFactor`, `textDisplay`, `textBackground`, `textContainerStyle` (`__VAR__` ref), `caption` (lang dict), `captionPosition`, `textIconLeft` (icon), `textIconRight` (icon)

**Image layer keys:** `image` (lang dict), `alt`, `imageLink`, `imageDimensions`, `imageFill`, `imageLayerScale`, `imageClipPath`, `imageBorderRadius`, `imageShadow`

**Button layer keys:** `button_1_text` (lang dict), `button_1_link`, `button1Style`, `button1IconLeft`, `button1IconRight`, `button_2_text`, `button_2_link`, `button2Style`, `buttonFont`, `buttonRadius`, `buttonPadding`, `buttonsGap`, `buttonsDirection`

**Icon layer keys:** `icon` (icon dict), `iconHover`, `iconAnimateType`, `iconAnimateSpeed`, `iconSVGCode`, `iconSVGColor`

**Video layer keys:** `videoType` (html5/youtube/vimeo), `videoHtml5Url` (lang dict), `videoHtml5Poster`, `videoSize`

**Hotspot keys (3 per layer):** `hotspot1` (bool), `hotspot1Icon`, `hotspot1Offset`, `hotspot1Type` (link/product/content), `hotspot1Link`, `hotspot1Product`, `hotspot1Content`

---

### BANNERS GRID module_data

```
general: 213 keys
items[]: banners (same layer system as slider)
```

**Banner object (81 keys) — key additions vs slider:**
```json
{
  "id": "uuid-v4",
  "image": {"lang_1": "catalog/..."},
  "link": {...},
  "imageDimensions": {...},
  "items": [...layers...],
  "itemOrder": "",
  "itemSpanRows": "false",
  "flexItemCustom": {"first": "", "second": ""},
  "bannerColOverlayLeft*": "...",
  "bannerColOverlayRight*": "..."
}
```

Layer system: **ίδιο με slider** + επιπλέον:
- `imageLayerBorder`, `imageLayerBackground`, `imageLayerClipPath`
- `products`, `productsStyles*` (shop-the-look pins)
- `textInlineStyle*` (inline text override group)

---

### PRODUCTS module_data

```
general: ~80 keys
items[]: tabs
```

**`general` key groups:** `gridType`, `display` (grid/list/carousel), `carousel` dict, `default` (default tab ID), `moduleProductList`, `moduleProductCartTooltip`, `moduleProductDescriptionLimit`, `pagination`, `sort`, `filters`, `imageDimensions`, `CountdownVisibility`, `autoCarousel*`

**Tab item (15 keys):**
```json
{
  "id": "uuid-v4",
  "title": {"lang_1": "Latest"},
  "name": "",
  "tabType": "latest",   // latest|bestseller|featured|special|category|manufacturer
  "filter": {
    "category_id": "",
    "limit": "8",
    "sort": "p.date_added",
    "order": "DESC"
  },
  "status": "true",
  "icon": "",
  "link": {...}
}
```

---

### CATEGORIES module_data

```
general: ~70 keys (carousel, gridType, imageDimensions, TitleBackground, TitleMargin, TabsAlign)
items[]: tabs
```

**Tab item:**
```json
{
  "id": "uuid-v4",
  "title": {"lang_1": "All"},
  "type": "all",            // all|category
  "category": "42",         // category_id αν type=category
  "categories": [],
  "limit": "8",
  "status": "true"
}
```

---

### TITLE module_data

```
general: ~100 keys (no items)
```

Key groups: `name`, `title` (lang dict), `font` (font dict), `font2`, `align`, `accentTitleVisibility`, `accentTitleOffset`, `dividerHeight`, `MaxWidth`, `ContainerStyle`, `Shadow`, `Outline`, `ContainerShadow`, `ContainerBackground`, `ButtonsContainerDisplay`, + button 1/2 config

---

### TEXT module_data

```
general: 35 keys (no items)
```

```json
{
  "general": {
    "name": "",
    "title": {"lang_1": ""},
    "content": {"lang_1": "<p>HTML content here</p>"},
    "contentType": "html",   // "text" | "html"
    "titleStyle": "",
    "titleAlign": "",
    "Font": {...font dict...},
    "FontHover": {...},
    "contentAlign": "",
    "maxWidth": "",
    "maxHeight": "",
    "typographyStyle": "",
    "containerStyle": "",
    "dynamic": "false",
    "status": {...},
    "schedule": {...},
    "customClass": "",
    "customCss": ""
  }
}
```

---

### BLOCKS (CMS Blocks) module_data

```
general: ~400 keys
items[]: blocks
```

**`general` key groups:** `display` (grid/list/accordion/tabs), `gridType`, `carousel`, `autoCarousel*`, `flexItems`, `itemsPerRow`, `imageDimensions`, `imagePosition`, `titlePosition`, `accordionStyle`, `tabsStyle`, `FaderStatus`, + full even/odd style variants για κάθε element

**Block item (~80 keys):**
```json
{
  "id": "uuid-v4",
  "title": {"lang_1": "Feature"},
  "content": {"lang_1": "<p>...</p>"},
  "contentType": "text",     // text|html|map|product|module|form|gallery
  "header": "icon",          // icon|image|none
  "footer": "text",          // text|button|none
  "icon": {...icon dict...},
  "image": {"lang_1": ""},
  "imageLink": {...},
  "link": {...},
  "footerText": {"lang_1": ""},
  "footerButton": "false",
  "footerButtonStyle": "",
  "footerButtonLink": {...},
  "expandButton": "false",
  "expandHeight": "",
  "status": {...}
}
```

---

### MARQUEE module_data

```
general: 43 keys
items[]: marquee items
```

**Item (24 keys):**
```json
{
  "id": "uuid-v4",
  "title": {"lang_1": "FREE SHIPPING"},
  "label": {"lang_1": ""},
  "labelStyle": "__VAR__PILL",
  "icon": {...icon dict...},
  "link": {...},
  "status": {...}
}
```
`general` keys: `marqueeSpeed`, `Direction` (left/right), `repeat`, `pauseHover`, `FaderStatus`, `FaderColor`, `FaderWidth`, `itemFont`, `itemFontOdd`, `itemFontEven`, `itemGap`, `itemPadding`, `itemDivider`, `itemIcon`

---

### TESTIMONIALS module_data

```
general: ~160 keys (autoCarousel, autoGrid, display, gridType, itemsPerRow, icon, imageDimensions)
items[]: testimonials
```

**Item (~85 keys):**
```json
{
  "id": "uuid-v4",
  "content": {"lang_1": "Amazing product!"},
  "footerText": {"lang_1": "— John Doe, CEO"},
  "header": "icon",
  "footer": "text",
  "icon": {...},
  "image": {"lang_1": ""},
  "status": {...}
}
```

---

### INFO BLOCKS module_data

```
general: ~80 keys (gridType, align, titleFont, imageDimensions, carousel)
items[]: blocks
```

**Item:**
```json
{
  "id": "uuid-v4",
  "name": "",
  "icon": {...icon dict...},
  "align": "",
  "status": {...}
  // + title, text, counter, button, image settings
}
```

---

### HEADER DESKTOP modules

`module_type`: `header_desktop_classic` | `header_desktop_compact` | `header_desktop_mega` | `header_desktop_slim`

```
general: 371 keys (no items)
```

**Key groups:**
- **Dimensions:** `headerHeight`, `stickyHeightNew`, `topBarHeight`
- **Background:** `headerBG`, `headerBGHome`, `stickyHeaderBG`, `headerTopBarBG`
- **Logo:** `headerLogoWidth`, `headerLogoMaxWidth`, `headerLogoImage`, `headerLogoPosition`, `desktopLogoType`
- **Main menu:** `headerMainMenu` (module_id), `headerMainMenu2`, `headerMainMenuHeight`, `headerMainMenuFont`, `headerMainMenuAlign`, `headerMainMenuDisplay`
- **Top menus:** `headerTopMenu`, `headerTopMenu2`, `headerTopMenu3`, `topBarStatus`
- **Search:** `searchStyle`, `searchPosition`, `headerSearchWidth`, `headerMiniSearchDisplay`, `headerMiniSearchBG`
- **Cart:** `cartStyle`, `cartVisibility`, `cartIcon`, `cartWidth`, `cartDropdown`
- **Sticky:** `stickyStatus`, `stickyLayout`, `stickyDistance`, `stickyHeaderBG`
- **Mega/Dropdown:** `megaMenu`, `headerDropdownStyle`, `mainMenuDropdownAlign`
- **Mobile trigger:** `mobileMenuOn`, `mobileMenu1`, `mobileMenuTrigger`, `mobileMenuDesktopBuilder`, `mobileMenuDesktopAccordion`
- **Language/Currency:** `languageStyle`, `languageDirection`, `currencyStyle`
- **Color schemes:** `color_scheme`, `header_color_scheme`, `header_home_color_scheme`, `sticky_color_scheme`, `header_dropdown_color_scheme`

---

### HEADER MOBILE modules

`module_type`: `header_mobile_1` | `header_mobile_2` | `header_mobile_3` | `header_mobile_4`

```
general: 49 keys (no items)
```

Key groups: `headerMobileHeight`, `headerMobileBG`, `headerMobileStickyStatus`, `headerMobileShadow`, `headerMobileMainMenu` (module_id), `headerMobileTopMenu`, `headerMobileMenuTitle`, `headerMobileCartIcon`, `headerMobileSearchIcon`, `headerMobileLogoPadding`, `headerMobileDropdownStyle`, `mobileLanguageStyle`, `mobileCurrencyStyle`

---

### MAIN MENU module_data

```
general: 7 keys
items[]: menu items
```

**`general`:** `name`, `status`, `color_scheme`, `dropdown_color_scheme`, `imageDimensions`, `customClass`, `customCss`

**Menu item (92 keys):**
```json
{
  "id": "uuid-v4",
  "title": {"lang_1": "Shop"},
  "type": "mega",         // flyout|mega|category|custom|page
  "link": {...},
  "rows": [...],          // mega menu rows (page-builder structure)
  "flyout": "module_id",  // αν type=flyout
  "subcategories": "true",
  "icon": {...icon dict...},
  "label": {"lang_1": "New"},
  "labelStyle": "__VAR__BLUE",
  "image": {"lang_1": ""},
  "dropdownWidth": "220",
  "dropdownAlign": "left",
  "megaMenuWidth": "",
  "megaMenuLayout": "full",
  "megaMenuBackground": {...},
  "multiLevelPosition": "right",
  "itemMinWidth": "220",
  "status": {...}
}
```

Mega menu `rows[]` → ίδια page-builder δομή με layout: `rows[].columns[].items[]`

---

### TOP MENU module_data

```
general: ~20 keys
items[]: menu items (22 keys each)
```

**Item:** `id`, `title` (lang dict), `link`, `icon`, `iconHover`, `font`, `fontHover`, `background`, `padding`, `order`, `items` (sub-items), `status`

---

### BOTTOM MENU module_data

```
general: ~20 keys
items[]: menu items (14 keys each)
```

**Item:** `id`, `title` (lang dict), `link`, `icon`, `font`, `fontHover`, `fontActive`, `background`, `backgroundHover`, `backgroundActive`, `status`

---

### FOOTER MENU module_data

```
general: 17 keys
rows[]: page-builder structure (rows → columns → items → module refs)
```

**`general`:** `color_scheme`, `footerType`, `width`, `background`, `border`, `padding`, `contentGutter`, `name`

`rows[]` → ίδια page-builder δομή: κάθε item έχει `item: {id, name, type}` που references άλλο module.

---

### GRID (Builder) module_data

Nested layout builder — περιέχει δική του row → column → item ιεραρχία.

```
general: ~20 keys
rows[]: page-builder structure
```

**Item types:**
- `journal3`: Φορτώνει Journal module (`load->controller('journal3/' . type)`)
- `opencart`: Φορτώνει OC extension module (`extension/module_name`)

**Δεν κάνει infinite nesting** — Grid → rows → columns → modules (τα modules δεν μπορούν να περιέχουν ξανά Grid).

---

## INSERT Logic — Δημιουργία νέων records

### Νέο Module (oc_journal3_module)
```php
// AUTO_INCREMENT id — δεν χρειάζεται UUID
INSERT INTO oc_journal3_module (module_name, module_type, module_data)
VALUES ('My Module', 'slider', '{...json...}')
// → getLastId() επιστρέφει το νέο module_id
```

### Νέο Layout (oc_journal3_layout)
```php
INSERT INTO oc_journal3_layout (layout_data)
VALUES ('{...json...}')
// → getLastId() = layout_id
```

### UUIDs για rows/columns/items
Τα UUIDs στα `id` fields μέσα στο layout_data/module_data **παράγονται client-side** (JavaScript UUID v4 library).

Αν δημιουργείς JSON server-side ή manually, χρησιμοποίησε:
```php
function generateUUID() {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
```

### encode() / decode()
```php
// Πριν αποθηκεύσεις στη DB:
$encoded = $journal3_db->encode($data, true);  // json_encode + escape special chars

// Μετά τη φόρτωση:
$decoded = $journal3_db->decode($row['data'], true);  // json_decode + unescape

// Special chars: \n → [~nl~], \r → [~nr~], \t → [~nt~]
```

**Άρα για manual INSERT:**
```sql
-- Encode το JSON σωστά (replace special chars) πριν το βάλεις στο query
INSERT INTO oc_journal3_module (module_name, module_type, module_data)
VALUES ('Name', 'type', '{"general":{...},"items":[...]}');

INSERT INTO oc_journal3_layout (layout_data)
VALUES ('{"general":{...},"enabledPositions":["top"],"positions":{...}}');
```

---

## Product Extras

3 τύποι modules που αφορούν τα products, διαχειρίζονται από Extensions → Journal 3 → Modules → Product.

### Product Labels (`module_type = product_label`)

Badges/tags πάνω στα products. 13 modules στη DB.

**Positions:**
- `default` → πάνω στην εικόνα (4 γωνίες: tl/tr/bl/br)
- `group_outside` → έξω από το image group
- `price` → δίπλα στην τιμή

**Core settings:**
```json
{
  "general": {
    "type": "special|outofstock|custom",
    "label": {"lang_1": "%"},
    "display": "default|diagonal",
    "positionDefault": "tl|tr|bl|br|group|group_outside|price",
    "vis": "always|hover|hidden",
    "hideOnSpecial": "false",
    "link": {...link dict...},
    "labelFont": {...font dict...},
    "labelBackground": {...background dict...},
    "labelBorder": {...border dict...},
    "labelBorderRadius": {...},
    "labelPadding": {...},
    "labelMargin": {...},
    "labelIcon": {...icon dict...},
    "labelShadow": {...shadow dict...},
    "scale": "",
    "labelSort": "",
    "quickviewVisibility": "true",
    "status": {...status dict...}
  }
}
```

**Special type**: αυτόματη υπολογισμός discount %: `'-' . round(($price - $special) / $price * 100) . '%'`
**Outofstock type**: χρησιμοποιεί `stock_status` field του product

**Twig variables:**
```twig
product.labels.default        // labels στην εικόνα
product.labels.group_outside  // labels έξω από group
product.labels.price          // labels στην τιμή
```

---

### Product Extra Buttons (`module_type = product_extra_button`)

Επιπλέον action buttons (max 2 ανά product). 2 modules στη DB.

```json
{
  "general": {
    "type": "custom|special|outofstock",
    "label": {"lang_1": "Buy Now"},
    "action": "link|quickbuy",
    "link": {...link dict...},
    "filter": {
      "preset": "all",
      "categories": [],
      "manufacturers": [],
      "products": [],
      "min_price": "",
      "max_price": "",
      "min_quantity": "",
      "max_quantity": "",
      "special": "false",
      "attributes": [],
      "options": []
    },
    "icon": {...icon dict...},
    "button": "",
    "buttonList": "",
    "buttonPage": "",
    "hideZeroPrice": "false",
    "status": {...status dict...}
  }
}
```

**`action` options:**
- `link` → απλό link με href/target
- `quickbuy` → add to cart + quick checkout popup

**Twig variable:** `product.extra_buttons` (array, max 2 items)

---

### Product Exclude Buttons (`module_type = product_exclude_button`)

Κρύβει cart/wishlist/compare buttons conditionally. 0 modules στη DB (feature διαθέσιμο).

```json
{
  "general": {
    "type": "custom",
    "filter": {...filter dict...},
    "excludeCart": "true|false",
    "excludeWishlist": "true|false",
    "excludeCompare": "true|false",
    "status": {...status dict...}
  }
}
```

**Mechanism**: Προσθέτει CSS classes στο product container:
```
.hide-cart      → κρύβει .cart-group
.hide-wishlist  → κρύβει .btn-wishlist
.hide-compare   → κρύβει .btn-compare
```

**Twig variable:** `product.classes` / `journal3_product_classes`

---

### Product Tabs & Blocks

Custom content sections στο product page.

| Type | Position options |
|------|-----------------|
| `product_tabs` | default, quickview, quickview_details, quickview_image, top, details, bottom, content_top |
| `product_blocks` | ίδιες + blockType (grid) |

**Twig variables:**
```twig
journal3_product_tabs_blocks_content_top
journal3_product_tabs_blocks_top
journal3_product_tabs_blocks_details
journal3_product_tabs_blocks_bottom
journal3_product_tabs_blocks_image
```

**DB stats:** product_tabs: 10 modules, product_blocks: 5 modules

---

### Product Extras — Data Flow

```
startup → product_extras controller loads + caches modules by type
  Cache keys: module.product_label, module.product_tabs, κλπ

event/product.php:
  controller_product_product_before → loads tabs/blocks
  view_product_product_before → injects into template:
    - journal3_product_labels (by position)
    - journal3_product_extra_buttons (max 2)
    - journal3_product_classes (exclusion CSS classes)
    - journal3_product_tabs_blocks_* (per position)

Filter logic (productextras.php):
  1. type matching (special/outofstock/custom)
  2. filter.preset = "all" → applies to all products
  3. filter.categories → applies only to products in those categories
  4. filter.products → applies to specific product IDs
  5. filter.special → only if product has special price
  6. filter.min_price / max_price / min_quantity / max_quantity
```

---

## Blog System

### DB Tables (11 tables)

Βλέπε πλήρη schema στην ενότητα **Database Tables** παραπάνω.

### Blog Post — πλήρη fields

**`oc_journal3_blog_post`:**
- `post_id`, `author_id` (→ oc_user.user_id), `image`, `comments` (0/1/2=inherit), `status`, `sort_order`, `date_created`, `date_updated`, `views`
- `post_data` JSON: `{"gallery_module": 123}` (optional gallery module_id)

**`oc_journal3_blog_post_description`:**
- `post_id`, `language_id`, `name`, `description` (HTML), `meta_title`, `meta_keywords`, `meta_robots`, `meta_description`, `keyword` (SEO slug), `tags` (comma-separated)

### Blog Category — πλήρη fields

**`oc_journal3_blog_category`:**
- `category_id`, `parent_id` (για ιεραρχία), `image`, `status`, `sort_order`

**`oc_journal3_blog_category_description`:**
- `category_id`, `language_id`, `name`, `description`, `meta_title`, `meta_keywords`, `meta_robots`, `meta_description`, `keyword`

### SEO Routing

```
/blog/              → journal3/blog (listing)
/blog/category-slug → journal3/blog?journal_blog_category_id=X
/blog/post-slug     → journal3/blog/post?journal_blog_post_id=X
/blog/?journal_blog_tag=mytag
/blog/?journal_blog_search=query
```

**SEO URL resolution**: keyword lookup στη `blog_category_description` και `blog_post_description` tables.

### Blog Routes

| Route | Method | Σκοπός |
|-------|--------|--------|
| `journal3/blog` | index | Post listing με pagination |
| `journal3/blog/post` | post | Single post + comments |
| `journal3/blog/comment` | comment | AJAX comment submission |
| `journal3/blog/feed` | feed | RSS/XML feed |
| `journal3/blog/seo_url` | seo_url | Internal SEO routing |

### Comments System

- Nested (parent_id για replies)
- Moderation: `status` field (0=pending, 1=approved)
- CAPTCHA optional (`blogPostCommentsCaptcha`)
- Email notification on new comment
- Gravatar από email hash

### Blog Settings (στο `oc_journal3_setting`)

| Setting | Default | Σκοπός |
|---------|---------|--------|
| `blogStatus` | true | Enable/disable blog |
| `blogPageKeyword` | "blog" | SEO route prefix |
| `blogPostsPerPage` | 6 | Posts ανά σελίδα |
| `blogPostsDescriptionLimit` | 250 | Excerpt length (chars) |
| `blogPostsSort` | newest | newest/oldest/comments/views |
| `blogAuthorName` | username | username/firstname/fullname |
| `blogPostComments` | true | Allow comments globally |
| `blogPostApproveComments` | true | Auto-approve |
| `blogPostCommentsCaptcha` | false | Require CAPTCHA |
| `blogPostCommentsNotifications` | false | Email admin |
| `blogDateFormat` | "d \<i\>M\</i\>" | Date format |
| `blogFeedStatus` | false | RSS feed |

### Blog + Products Relationship

```
oc_journal3_blog_post_to_product → many-to-many

Methods:
- getRelatedProducts($post_id)  → products που αναφέρει το post
- getRelatedPosts($product_id)  → posts που αναφέρουν το product
```

### Multi-store & Multilingual

- Visibility ανά store: `*_to_store` tables
- Layout ανά store: `*_to_layout` tables
- Text ανά language: `*_description` tables με `language_id`
- SEO slug ανά language: ξεχωριστό `keyword` per language

### Data για νέο Post (INSERT)

```php
$data = [
    'name'             => ['lang_1' => 'Title'],
    'description'      => ['lang_1' => '<p>HTML</p>'],
    'keyword'          => ['lang_1' => 'seo-slug'],
    'meta_title'       => ['lang_1' => ''],
    'meta_keywords'    => ['lang_1' => ''],
    'meta_robots'      => ['lang_1' => ''],
    'meta_description' => ['lang_1' => ''],
    'tags'             => ['lang_1' => 'tag1, tag2'],
    'image'            => 'catalog/blog/image.jpg',
    'comments'         => true,
    'status'           => true,
    'sort_order'       => 1,
    'date_created'     => '2025-01-01 12:00:00',
    'author_id'        => 1,
    'post_data'        => ['gallery_module' => null],
    'categories'       => [1, 2],
    'products'         => [],
    'layouts'          => ['store_0' => 0],
    'stores'           => ['store_0' => 'true'],
];
```

---

## System Settings

Διαχειρίζεται από Extensions → Journal 3 → System.
Αποθηκεύεται στο `oc_journal3_setting` table.

### Performance Options

| Setting | Default | Σκοπός |
|---------|---------|--------|
| `performanceSeoUrlEngine` | "all" | all/standard/fast |
| `performanceJQuery` | true | Include jQuery |
| `performanceBootstrap` | true | Include Bootstrap |
| `performanceFontAwesome` | true | Include Font Awesome |
| `performanceHTMLMinify` | false | Minify HTML output |
| `performanceCSSMinify` | false | Minify CSS |
| `performanceJSMinify` | false | Minify JS |
| `performanceCSSDefer` | false | Non-blocking CSS load |
| `performanceCSSInline` | false | Inline critical CSS |
| `performanceJSDefer` | false | Async JS loading |
| `performancePushCSS` | false | HTTP/2 PUSH CSS |
| `performancePushIcons` | false | HTTP/2 PUSH icons |
| `performanceLazyLoadImagesStatus` | true | Image lazy loading |
| `performanceCompressImagesWebpStatus` | false | WebP conversion |
| `performanceCompressImagesJpegStatus` | false | JPEG compression |
| `performanceCompressImagesPngStatus` | false | PNG compression |
| `performanceGoogleFontsDisplay` | "swap" | swap/block/fallback/optional/auto |
| `performanceCDNStatus` | false | CDN για static assets |
| `performanceCDNHttp` | "" | HTTP CDN URL |
| `performanceCDNHttps` | "" | HTTPS CDN URL |

**JS Defer mechanism**: αντικαθιστά `<script type="text/javascript"` με `<script type="text/javascript/defer"` στο HTML output.

**Defined constants** (runtime):
- `JOURNAL3_SEO_URL_ENGINE` ← performanceSeoUrlEngine
- `JOURNAL3_STATIC_URL` ← CDN base URL
- `JOURNAL3_STATIC_IMAGES_URL` ← image CDN URL

### SEO Options

| Setting | Default | Σκοπός |
|---------|---------|--------|
| `seoH1HomePage` | true | H1 tag στο homepage |
| `seoOpenGraphTagsStatus` | true | Open Graph meta tags |
| `seoOpenGraphTagsAppId` | "" | Facebook App ID |
| `seoOpenGraphTagsImageDimensions` | 600×315 | OG image size |
| `seoTwitterCardsStatus` | true | Twitter Card meta tags |
| `seoTwitterCardsTwitterUser` | "" | @twitter_handle |
| `seoTwitterCardsImageDimensions` | 200×200 | Twitter card image size |
| `seoGoogleRichSnippetsStatus` | true | JSON-LD rich snippets |

**Rich Snippets που παράγονται** (JSON-LD):
- `WebSite` + search action
- `Organization` + logo
- `BreadcrumbList`
- `Product` (product pages): SKU, MPN, price, availability, brand, aggregateRating, reviews
- `Article` (blog posts): headline, image, date, author

### Custom Code Injection

| Setting | Σκοπός |
|---------|--------|
| `customCSS` | `<style>` tag στο document |
| `customJS` | `<script>` tag στο document |
| `customCodeHeader` | Inject στο `<head>` (analytics, pixels, fonts) |
| `customCodeFooter` | Inject πριν `</body>` (chat widgets, tracking) |

**Τρέχουσες τιμές στη DB:** όλα κενά.

### System/Filter Options

| Setting | Default | Σκοπός |
|---------|---------|--------|
| `adminEditor` | false | Visual editor mode στο admin |
| `adminDimensions` | false | Dimension guides |
| `filterScrollTop` | false | Auto-scroll on filter apply |
| `filterAttributeValuesSeparator` | "," | Multi-value attribute separator |
| `filterUrlValuesSeparator` | "," | URL filter separator |
| `filterCheckQuantity` | false | Stock check on filter |
| `filterCheckQuantityRelated` | false | Stock check related products |
| `filterOrderByStock` | false | Order results by stock |
| `filterAddToCartStock` | true | Stock check on add-to-cart |
| `filterCheckOptionsQuantity` | false | Stock check για options |
| `filterTaxClassId` | "" | Tax class για calculations |

### Import / Export

**Exportable tables:**

| Category | Tables |
|----------|--------|
| variable | journal3_variable |
| setting | journal3_setting |
| skin | journal3_skin, journal3_skin_setting |
| style | journal3_style |
| module | journal3_module |
| layout | journal3_layout |
| blog | 9 blog tables |
| newsletter | journal3_newsletter |
| message | journal3_message |
| catalog | 27+ OC tables (products, categories, attributes, κλπ) |

**Formats:** `.sql` ή `.zip`
**Storage:** `system/library/journal3/data/import_export/`
**Smart features:**
- ON DUPLICATE KEY UPDATE για variables/styles/settings
- Auto-includes related tables (active_skin με skins, blog settings με blog)
- Individual item export ανά module_id/style_name
- OC2/OC3/OC4 compatibility handling on import

### Newsletter Management

Combines 2 sources:
```sql
(SELECT email, 1 as status FROM oc_customer WHERE newsletter = 1)
UNION
(SELECT email, 0 as status FROM oc_journal3_newsletter)
```

Export: CSV με Name, Customer status, Store.

### Form Messages (Contact Forms)

Αποθηκεύονται στο `oc_journal3_message`:
- `name`, `email`, `ip`, `date`, `url` (page που στάλθηκε), `store_id`
- `fields` JSON: array of `{type, name, value}` — dynamic form fields
- File uploads: `{type: "file", name, value, code, url}`

### Settings Loading Flow

```
startup → journal3/settings controller
  1. Load variables από cache ή DB (cache key: 'variables.all')
     → CSS custom properties: --j-variable-name
  2. Load 8 setting groups από cache ή DB (cache key: 'settings'):
     dashboard/dashboard, system/system, settings/active_skin,
     settings/blog, settings/custom_code, settings/general,
     settings/performance, settings/seo
  3. Image optimization capability check
     (disables WebP/JPEG/PNG if tools not available)
  4. CDN configuration → set constants
  5. Define: JOURNAL3_SEO_URL_ENGINE, JOURNAL3_STATIC_URL,
             JOURNAL3_STATIC_IMAGES_URL
```

Cache invalidation: on import_export, system edit, skin change.

---

## Remaining Module Schemas

### POPUP module_data

```
general: 69 keys
rows[]: page-builder structure (like footer_menu)
```

**`general` key groups:** `display` (exit_intent/delay/scroll/click), `cookie` (bool), `closeAfter` (seconds), `popupStyle` (`__VAR__`), `popupPadding`, `headerPadding`, `footerBG`, `footerBorder`, `button1`/`button2` (config), `icon1Right`/`icon2Right`, `color_scheme`, `doNotShowAgainText`/`doNotShowAgainBackground`/`doNotShowAgainPosition`/`doNotShowSize`, `imageDimensions`, `overlayBlend`, `titleMargin`, `customClass`, `status`, `schedule`

**Σημαντικό**: Popup χρησιμοποιεί `rows[]` (page-builder) αντί για `items[]` — οτιδήποτε μπορεί να μπει μέσα (modules, banners, forms).

---

### NOTIFICATION module_data

```
general: 44 keys (no items)
```

**Key groups:** `text` (lang dict — HTML content), `color_scheme`, `cookie` (bool), `notificationBorderRadius`, `notificationBorderRadius_multi`, `notificationPadding`, `notificationShadow`, `sideSpacing`, `sideSpacing_multi`, `notificationCloseIcon`, `notificationCloseOffset`, `notificationClosePadding`, `notificationCloseStyle`, `notificationCloseSize`, `closeButton`, `buttonBottomSpacing`, `linkFont`, `customClass`, `status`, `schedule`

---

### HEADER_NOTICE module_data

```
general: 73 keys
rows[]: page-builder structure
```

**Key groups:** `shadow`, `outline`, `foreground`, `color_scheme`, `cookie`, `height`, `maxWidth`, `closePosition`, `closeBorder`, `closeText`, `closeMargin`, `contentFont_multi`, `contentLinkFont`, `contentBorder`, `ctaButton`, `ctaButtonBorderRadius`, `iconAlign_multi`, `noticeMargin`, `overlayBlend`, `customClass`, `status`, `schedule`

---

### BACKGROUND_SLIDER module_data

```
general: 25 keys
items[]: slides (18 keys each)
```

**`general`:** `autoplay`, `autoplayDelay`, `speed`, `effect`, `loop`, `shuffle`, `parallax_bg`, `syncWith`, `slidesOpacity`, `sliderMaxHeight`, `background`, `icon`, `iconOpacity`, `iconEdgeMargin`, `overlayStatus`, `overlayBlend`, `imageDimensions`, `customClass`, `status`, `schedule`

**Slide item:** `id`, `type` (image/video/custom), `image` (lang dict), `alt`, `custom` (lang dict HTML), `icon`, `iconOpacity`, `iconEdgeMargin`, `overlayStatus`, `overlayBlend`, `slideOpacity`, `status`

---

### FILTER module_data

```
general: ~115 keys
items[]: filter option flags (short-code keys)
```

**`general` key groups:** `filterBackground`, `filterBackgroundMobile`, `filterAccordion`, `filterAccordionPadding`, `filterPadding`, `filterTitleMargin`, `filterCountBadge`, `filterCountBadgeVisibility`, `filterCountBadgeOffset`, `filterMobileWrapperStyle`, `mobileButtonStyle`, `mobileButtonAlign`, `mobileButtonWidth`, `mobileButtonPadding`, `mobileButtonBorderRadius`, `mobileButtonOffset`, `mobileText`, `mobileIcon`, `mobileFilterTitle`, `resetText`, `resetIcon`, `resetButtonStyle`, `resetButtonVisibility`, `resetButtonOffset`, `containerStyle`, `input`, `priceInput`, `priceSize`, `priceOffset`, `priceLineHeight`, `priceThumbBackground`, `priceThumbBorder`, `priceThumbBorderRadius`, `priceThumbShadow`, `pricePadding`, `priceLineBackground`, `priceLineForeground`, `currencyFont`, `itemFont`, `itemFontHover`, `itemFontActive`, `itemBackground`, `itemBackgroundActive`, `itemBorder`, `itemBorderHover`, `itemBorderActive`, `itemBorderRadius`, `itemShadow`, `itemShadowHover`, `itemSpacing`, `attributes`, `options`, `filters`, `collapsed`, `filtersCategoryCheck`, `imageDimensions`, `imageBackground`, `imageBorder`, `imageBorderRadius`, `imageShadow`, `sectionHeight`, `sectionHeightMobile`, `ScrollColor`, `ScrollColorHover`, `ScrollColorActive`, `TrackColor`, `TrackHeight`, `customClass`, `status`, `schedule`

**`items[]` short-code keys** — compact boolean/config flags per filter section:

| Key | Meaning |
|-----|---------|
| `o1..o11` | Options flags (show/hide specific option types) |
| `c` | Categories filter |
| `f1`, `f2` | Custom filter groups |
| `m` | Manufacturers filter |
| `p` | Price range filter |
| `a1..a5` | Attribute groups |
| `q` | Quantity/stock filter |
| `t` | Tag filter |

**Σημαντικό**: Η βασική ρύθμιση γίνεται από skin settings (filterScrollTop, filterAttributeValuesSeparator, κλπ). Τα `items[]` ελέγχουν ποια filter sections εμφανίζονται.

---

### COUNTDOWN module_data

```
general: 122 keys (no items)
```

**Key groups:**
- Countdown display: `countdownStyle` (`__VAR__`), `countdownFont`, `countdownItemBorderRadius`, `countdownTextStylesSize`, `countdownTextStylesDisplay`, `countdownModuleStylesSize`, `countdownModuleStylesMaxWidth`, `countdownModuleStylesDisplay`
- Product display: `productsBorderRadius`, `productsShadow`, `productsStylesBorderRadius`, `productsStylesOutlineHover`, `moduleTextPosition`, `moduleTextStylesShadow`, `moduleTextStylesShadowHover`
- Timing: (date/time set in skin settings or via DB trigger)
- Border variants: `countdownModuleStylesBorder_multi` (responsive)

---

### GALLERY module_data

```
general: 132 keys
items[]: gallery items (22 keys each)
```

**`general` key groups:** `gridType`, `carousel`, `thumbDimensions`, `thumbBorder`, `iconLeft`, `iconRight`, `iconPosition`, `color_scheme`, `instagramPage`, `OverlayOpacityHover`, `autoGridContainerMarginOuter`, `autoGridContainerWidthOuter`, `AutoScrollbarTrackMargin`, `AutoCarouselPadding`, `autoCarouselStyle`, `moduleContainerBackground`, `moduleContainerBorder`, `moduleContainerMargin`, `customClass`, `status`

**Gallery item (22 keys):**
```json
{
  "id": "uuid-v4",
  "name": "",
  "image": {"lang_1": "catalog/gallery/img.jpg"},
  "videoType": "html5|youtube|vimeo",
  "videoHtml5Url": {"lang_1": ""},
  "videoYoutubeUrl": {"lang_1": ""},
  "videoVimeoUrl": {"lang_1": ""},
  "videoImage": {"lang_1": ""},
  "iconPosition": "",
  "iconAlways": "",
  "itemShadow": {...shadow dict...},
  "itemShadowHover": {...},
  "itemOffset": "",
  "grayscale": "",
  "customClass": "",
  "customCss": "",
  "status": {...status dict...}
}
```

---

### ACCORDION_MENU module_data

```
general: 36 keys
items[]: menu groups (22 keys each, with nested items[])
```

**`general`:** `menuModule` (`__VAR__DEFAULT`), `menuStyle` (`__VAR__`), `menuStyle2`, `shadow`, `iconWidth`, `subIconWidth`, `subIconPosition`, `subIconAlign`, `iconHover`, `subIconHover`, `iconMargin`, `imageDimensions`, `background`, `border`, `moduleShadow`, `ModuleBorderRadius`, `ModulePadding`, `ItemDivider`, `moduleTitle`, `customClass`, `status`

**Group item (22 keys):**
```json
{
  "id": "uuid-v4",
  "title": {"lang_1": "Category Name"},
  "icon": {...icon dict...},
  "isDivider": "false",
  "collapsed": "true",
  "collapsedPhone": "true",
  "subcategoriesImages": "false",
  "itemBackground": {...},
  "itemFont": {...font dict...},
  "iconHover": {"color": ""},
  "items": [...sub-items...],
  "status": {...}
}
```

---

### SIDE_MENU module_data

```
general: 36 keys
items[]: menu items (17 keys each)
```

**`general`:** `color_scheme`, `position` (left/right), `positionV` (top/center/bottom), `width`, `orientation` (vertical/horizontal), `background`, `backgroundHover`, `font`, `fontHover`, `itemGap`, `itemMargin`, `padding`, `Shadow`, `iconsHover`, `iconsActive`, `countBadgeVisibility`, `countBadgeOffset`, `iconMargin`, `customClass`, `status`

**Item (17 keys):**
```json
{
  "id": "uuid-v4",
  "title": {"lang_1": "Cart"},
  "type": "cart|wishlist|compare|search|link|custom",
  "link": {...},
  "icon": {...icon dict...},
  "font": {...font dict...},
  "fontHover": {...},
  "fontActive": {...},
  "background": {...},
  "backgroundHover": {...},
  "backgroundActive": {...},
  "itemGap": "",
  "orientation": "",
  "customClass": "",
  "customCss": "",
  "status": {...}
}
```

---

### FLYOUT_MENU module_data

```
general: 38 keys
items[]: menu items (45+ keys each, with nested dropdown structure)
```

**`general`:** `menuLayout` (dropdown/mega), `DropdownColumns`, `DropdownColumns2`, `DropdownColumns3`, `megaMenuPadding`, `megaMenuBorderRadius`, `megaMenuBorderRadiusFirst`, `flyoutMegaSublevelOffset`, `iconWidth`, `iconMargin`, `imageDimensions`, `globalIconHover`, `globalSubIconHover`, `dropdown_color_scheme`, `customClass`, `status`

**Item keys include:** `bg`, `bgHoverDesktop`, `font`, `fontDesktop`, `fontHover`, `fontHoverDesktop`, `iconHover`, `position` (flyout/mega), `flyoutMegaMenuShadow`, `DropdownColumns`, `DropdownColumns2`, `DropdownColumns3`, `megaMenuPadding`, `items` (sub-items recursively)

---

### ICONS_MENU module_data

```
general: 44 keys
items[]: icon items (19 keys each)
```

**`general`:** `gridType`, `iconsAlign`, `iconsAlign_multi`, `iconSpacing`, `iconSpacing_multi`, `iconColor`, `iconFont`, `iconBorder`, `iconBorderActive`, `iconBGActive`, `iconShadowHover`, `tooltip`, `tooltipPosition`, `containerBG`, `imageDimensions`, `iconsMenuCountBadge`, `iconsMenuCountBadgeOffset`, `moduleTitle`, `customClass`, `status`

**Item (19 keys):**
```json
{
  "id": "uuid-v4",
  "type": "cart|wishlist|compare|search|link|account|custom",
  "title": {"lang_1": "Cart"},
  "icon": {...icon dict...},
  "iconHover": {"color": ""},
  "iconFont": {...},
  "iconFontHover": {...},
  "iconMenuCountBadge": {"status": "true"},
  "iconBGActive": {...},
  "image": {"lang_1": ""},
  "link": {...},
  "customClass": "",
  "customCss": "",
  "status": {...}
}
```

---

### LINKS_MENU module_data

```
general: 68 keys
items[]: link items (28 keys each)
```

**`general`:** `itemFont`, `itemBackground`, `itemBackgroundActive`, `itemGap`, `itemFull`, `itemDivider`, `itemDividerWidth`, `align`, `linksColumns`, `linksColumnDividerStyle`, `linksColumnGap`, `linksPlusMobileClose`, `titleFont`, `titleMargin`, `titleDivider`, `countBadgeVisibility`, `countBadgeOffset`, `Shadow`, `MaxHeight_multi`, `customClass`, `status`

**Item (28 keys):**
```json
{
  "id": "uuid-v4",
  "title": {"lang_1": "Home"},
  "link": {...},
  "icon": {...},
  "iconHover": {"color": ""},
  "isTitle": "false",
  "columnBreak": "false",
  "LabelType": "",
  "LabelTriangleVisibility": "",
  "labelOffset": "",
  "itemFont": {...},
  "itemBackground": {...},
  "itemBorderRadius": "",
  "itemShadow": {...},
  "itemShadowHover": {...},
  "customClass": "",
  "customCss": "",
  "status": {...}
}
```

---

### NEWSLETTER module_data

```
general: 84 keys (no items)
```

**Key groups:** `position` (inline/popup), `titleFont`, `titleMargin`, `titleDivider`, `titleDivider`, `inputIcon`, `inputBorderRadius`, `inputStylesPadding`, `buttonFont`, `buttonRadius`, `buttonOffset`, `buttonMargin`, `agreeLink`, `agreePadding`, `subscribedEmail`, `subscribedEmailMessage`, `adminAlerts`, `emailLogo`, `textPadding`, `textIcon`, `shadow`, `background`, `padding`, `customClass`, `status`

---

### FORM module_data

```
general: 33 keys
items[]: form rows (each row has items[] = form fields)
```

**`general`:** `formStyle` (`__VAR__`), `titleFont`, `titleAlign`, `titleMargin`, `legendTitle`, `sendButtonText` (lang dict), `sentText` (lang dict), `sentEmailSubject`, `sentEmailFrom`, `sentEmailIPAddress`, `sentEmailUsingModule`, `sentEmailValue`, `sentEmailLogo`, `agree` (bool), `background`, `padding`, `buttonMargin`, `customClass`, `status`

**Form row item:**
```json
{
  "id": "uuid-v4",
  "name": "row 1",
  "items": [
    {
      "id": "uuid-v4",
      "type": "text|email|textarea|select|checkbox|radio|file|hidden",
      "label": {"lang_1": "Name"},
      "placeholder": {"lang_1": ""},
      "required": "true",
      "items": [...select options for select/checkbox/radio...]
    }
  ]
}
```

---

### BANNERS module_data

*(Simple banners — not banners_grid)*

```
general: 144 keys
items[]: banners (25+ keys each)
```

**`general` key groups:** `gridType`, `carousel`, `bannerStyle` (`__VAR__`), `titlePosition`, `title2Position`, `iconPosition`, `overlayBlend`, `imageShadowHover`, `titleBorder`, `titleS2Border`, `titleS2Background`, `AutoCarouselPadding`, `AutoCarouselScrollerType`, `AutoCarouselButtonsVisibility`, `autoCarouselStyle`, `autoGridContainerMarginOuter`, `autoGridContainerWidthOuter`, `autoGridContainerAlignToContent`, `customClass`, `status`

**Banner item (25 keys):**
```json
{
  "id": "uuid-v4",
  "name": "",
  "image": {"lang_1": "catalog/banners/img.jpg"},
  "alt": {"lang_1": ""},
  "background": {...background dict...},
  "backgroundHover": {...},
  "imageDimensions": {...dimensions dict...},
  "iconPosition": "",
  "iconDisplay": "",
  "iconMargin": {...},
  "itemIcon": {...icon dict...},
  "margin": {},
  "color_scheme": "",
  "customClass": "",
  "customCss": "",
  "status": {...}
}
```

*Δεν έχει layer system — banners_grid αντίθετα.*

---

### CATALOG module_data

```
general: 123 keys
items[]: catalog sections (34+ keys each, with nested items[])
```

**`general` key groups:** `gridType`, `carousel`, `imageDimensions`, `imagePosition`, `ImageFillNew`, `IncrementalCounter`, `AutoCarouselPadding`, `AutoScrollbarTrackMargin`, `autoCarouselStyle`, `autoCarouselRows_multi`, `itemsBoxBackgroundHover`, `itemsBoxFontHover`, `itemLinkFontHover`, `itemViewMoreFontHover`, `autoGridContainerPadding`, `autoGridContainerAlignToContent`, `customClass`, `status`

**Catalog section item (34+ keys):**
```json
{
  "id": "uuid-v4",
  "name": "",
  "font": {...font dict...},
  "imageVisibility": "",
  "ImageBackground": {...},
  "ImageOverlayHover": {...},
  "ImageOverlayOpacityHover": "",
  "subitemFont": {...},
  "subitemIcon": {...},
  "subitemIconHover": {...},
  "disableMaxHeight": "",
  "itemBackground": {...},
  "manufacturer": [...manufacturer_ids...],
  "items": [...sub-items or product IDs...]
}
```

---

### CATALOG_BLOCKS module_data

```
general: 475 keys
items[]: blocks (169 keys each)
```

Most complex module. Combines catalog listing + content blocks + product display. Key groups in `general`: full Even/Odd style variants for every element (background, font, shadow, border, padding, radius for both even/odd items).

**Use case:** Product category landing pages with alternating content+product blocks.

---

### MANUFACTURERS module_data

```
general: 79 keys
items[]: tabs (10 keys each)
```

**`general`:** `gridType`, `carousel`, `imageDimensions`, `brandNameVisibility`, `TitleBackground`, `TitleMargin`, `TitleStackBase`, `default`, `autoCarouselStyle`, `autoGridContainerPadding`, `autoGridContainerAlignToContent`, `customClass`, `status`

**Tab item (10 keys):**
```json
{
  "id": "uuid-v4",
  "title": {"lang_1": "All"},
  "type": "all|selected",
  "manufacturers": [...manufacturer_ids...],
  "tabType": "all",
  "limit": "20",
  "status": "true",
  "customClass": "",
  "customCss": "",
  "name": ""
}
```

---

### SIDE_PRODUCTS module_data

```
general: 91 keys
items[]: tabs (12 keys each)
```

**`general`:** `gridType`, `carousel`, `space`, `TabsAlign`, `flexItems`, `FaderStatus`, `modulePadding`, `autoCarouselStyle`, `autoGridContainerPadding`, `autoGridContainerAlignToContent`, `blocksItemsGrid`, `IconSpecial`, `customClass`, `status`

**Tab item (12 keys):**
```json
{
  "id": "uuid-v4",
  "title": {"lang_1": "Latest"},
  "tabType": "latest|bestseller|featured|special|category",
  "filter": {"category_id": "", "limit": "8"},
  "IconSpecial": {...},
  "CounterVisibility": "",
  "CounterFont": {...},
  "CounterFontHover": {...},
  "status": "true",
  "name": "",
  "customClass": "",
  "customCss": ""
}
```

---

### BUTTON module_data

```
general: 22 keys (no items)
```

```json
{
  "general": {
    "title": {"lang_1": "Shop Now"},
    "link": {...link dict...},
    "style": "__VAR__DEFAULT",
    "font": {...font dict...},
    "fontHover": {...},
    "iconLeft": {...icon dict...},
    "iconRight": {...},
    "iconRightHover": {...},
    "align": "center",
    "width": "",
    "width_multi": [],
    "scale": "",
    "scaleOrigin": "",
    "margin": {},
    "padding": {},
    "customClass": "",
    "customCss": "",
    "status": {...},
    "schedule": {...}
  }
}
```

---

### IMAGE module_data

```
general: 51 keys (no items)
```

**Key groups:** `image` (lang dict), `alt` (lang dict), `link`, `imageAlign`, `maxWidth`, `imageDimensions`, `imageBackground`, `caption` (lang dict), `iconHover`, `styleOverrideBorder`, `styleOverrideMargin`, `styleOverrideBackground`, `styleOverrideOutline`, `styleOverrideCaptionFont*`, `styleOverrideCaptionBackground*`, `styleOverrideCaptionBorder*`, `styleOverrideCaptionShadow`, `styleOverrideCaptionBorderRadius`, `styleOverrideCaptionPadding`, `customClass`, `status`, `schedule`

---

### BLOG_CATEGORIES module_data

```
general: 22 keys (no items)
```

**Key groups:** `title`, `linkFont`, `linkFontHover`, `linkBackground`, `linkBackgroundHover`, `linkSpacing`, `linkPadding`, `linkIcon`, `linkIconHover`, `linkTruncate`, `moduleBorder`, `moduleBorderRadius`, `moduleShadow`, `modulePadding`, `moduleTitle`, `customClass`, `status`, `schedule`

---

### BLOG_POSTS module_data

```
general: 69 keys
items[]: tabs (8 keys each)
```

**`general` key groups:** `gridType`, `display` (grid/list/carousel), `carousel`, `flexItems`, `FaderStatus`, `imageDimensions`, `titleMargin`, `autoGridContainerPadding`, `autoCarouselStyle`, `customClass`, `status`

**Tab item (8 keys):**
```json
{
  "id": "uuid-v4",
  "title": {"lang_1": "Latest"},
  "tabType": "latest|featured|category",
  "filter": {"category_id": "", "limit": "6"},
  "status": "true",
  "name": "",
  "customClass": "",
  "customCss": ""
}
```

---

### BLOG_SIDE_POSTS module_data

```
general: 66 keys
items[]: tabs (8 keys each, same as blog_posts)
```

Similar to `blog_posts` but designed for sidebar display. Key additions in `general`: `accordionStyle`, `accordionDefault`.

---

### BLOG_SEARCH module_data

```
general: 19 keys (no items)
```

**Key groups:** `title`, `titleStyle`, `placeholder` (lang dict), `inputStyle` (`__VAR__`), `buttonStyle` (`__VAR__`), `buttonHeight`, `buttonPadding`, `buttonMargin`, `buttonOffset`, `buttonRadius`, `Background`, `Padding`, `Shadow`, `moduleTitle`, `customClass`, `status`, `schedule`

---

### BLOG_TAGS module_data

```
general: 12 keys (no items)
```

**Keys:** `title`, `titleStyle`, `limit`, `tagsStyle` (`__VAR__`), `tagsGap`, `display` (cloud/list), `moduleTitle`, `customClass`, `status`, `schedule`

---

### BLOG_COMMENTS module_data

```
general: 22 keys (no items)
```

**Key groups:** `spacing`, `ImagePosition`, `imageBorder`, `imageBorderRadius`, `imageMargin`, `imageMargin2`, `grayscale`, `commentFont`, `commentFontHover`, `commentBackground`, `commentPadding`, `commentDivider`, `authorFont`, `moduleTitle`, `title`, `customClass`, `status`, `schedule`

---

### FAQ module_data

```
general: 19 keys
items[]: Q&A items (10 keys each)
```

**`general`:** `accordionStyle` (`__VAR__`), `default` (default open item ID), `fontQ`, `fontQHover`, `fontQActive`, `fontA`, `counterStatus`, `counterFont`, `counterFontHover`, `counterFontActive`, `titleAlign`, `moduleMaxWidth`, `moduleTitle`, `title`, `customClass`, `status`, `schedule`

**FAQ item (10 keys):**
```json
{
  "id": "uuid-v4",
  "title": {"lang_1": "Question?"},
  "content": {"lang_1": "<p>Answer</p>"},
  "contentType": "html",
  "icon": {...icon dict...},
  "iconHover": {"color": ""},
  "name": "",
  "customClass": "",
  "customCss": "",
  "status": {...}
}
```

---

### SPACER module_data

```
general: 10 keys (no items)
```

```json
{
  "general": {
    "name": "",
    "height": "40",
    "maxWidth": "",
    "contentWidth": "",
    "divider": "false",
    "icon": {...icon dict...},
    "customClass": "",
    "customCss": "",
    "status": {...},
    "schedule": {...}
  }
}
```

---

### PRODUCT_BLOCKS module_data

```
general: 77 keys
rows[]: page-builder structure (like popup/header_notice)
```

Used on product page. Supports `display` (grid/list/accordion), `carousel`, `imagePosition`, `typographyStyle`, icon styles, hover styles, `boxPadding`, `titlePosition`, `titleMargin`, `positionPhone`. Complex cross-section with rows[] so content is defined via page-builder sub-modules.

---

### PRODUCT_TABS module_data

```
general: 123 keys
rows[]: page-builder structure
moduleBoxStylesBackground, moduleBoxStylesBorder (top-level extra keys)
```

Controls the product description/attributes/reviews tabs layout. Key settings: `display` (tabs/accordion), `expandHeight`, `moduleBoxStyles*` (size, shadow, outline, maxWidth), `reviewBoxStyles*`, `reviewTableBackground`, `reviewTableBorderColor`, `blocksTitleVisibility`, `headingIcon*`.

---

## Event System — Full Registration List

All registrations in `catalog/controller/journal3/events.php` → `index()` method.
Handler files are in `catalog/controller/journal3/event/`.

### Always-On Events

| Trigger | Action | Purpose |
|---------|--------|---------|
| `view/*/before` | `journal3/events/view` | Inject `$journal3` object into every view |
| `view/*/before` | `journal3/event/layout/view_before` | Inject `journal3_top` position into any view |
| `view/*/before` | `journal3/seo/view_before` | SEO meta tags (OG, Twitter, JSON-LD) |

### Header & Footer

| Trigger | Action |
|---------|--------|
| `controller/common/header/before` | `journal3/event/header/controller_common_header_before` |
| `view/common/header/before` | `journal3/event/header/view_common_header_before` |
| `controller/common/header/before` | `journal3/event/layout/controller_common_header_before` |
| `view/common/header/before` | `journal3/event/layout/view_common_header_before` |
| `controller/common/footer/before` | `journal3/event/footer/controller_common_footer_before` |
| `view/common/footer/before` | `journal3/event/footer/view_common_footer_before` |
| `controller/common/footer/before` | `journal3/event/layout/controller_common_footer_before` |
| `view/common/footer/before` | `journal3/event/layout/view_common_footer_before` |

### Layout Positions

| Trigger | Action |
|---------|--------|
| `controller/common/column_left/before` | `journal3/event/layout/controller_common_position_before` |
| `controller/common/column_right/before` | `journal3/event/layout/controller_common_position_before` |
| `controller/common/content_top/before` | `journal3/event/layout/controller_common_position_before` |
| `controller/common/content_bottom/before` | `journal3/event/layout/controller_common_position_before` |
| `view/common/column_left/before` *(OC 4.1+)* | `journal3/event/layout/view_common_position_before` |
| `view/common/column_right/before` *(OC 4.1+)* | `journal3/event/layout/view_common_position_before` |
| `view/common/content_top/before` *(OC 4.1+)* | `journal3/event/layout/view_common_position_before` |
| `view/common/content_bottom/before` *(OC 4.1+)* | `journal3/event/layout/view_common_position_before` |
| `model/design/layout/getModules/after` *(OC 4.1+)* | `journal3/event/layout/model_design_layout_getModules_after` |

### Product Listing Pages

| Trigger | Action |
|---------|--------|
| `controller/product/catalog/before` | `journal3/event/products/controller_products_before` |
| `controller/product/category/before` | `journal3/event/products/controller_products_before` |
| `controller/product/manufacturer/info/before` | `journal3/event/products/controller_products_before` |
| `controller/product/search/before` | `journal3/event/products/controller_products_before` |
| `controller/product/special/before` | `journal3/event/products/controller_products_before` |
| `view/product/category/before` | `journal3/event/products/view_products_before` |
| `view/product/manufacturer_info/before` | `journal3/event/products/view_products_before` |
| `view/product/search/before` | `journal3/event/products/view_products_before` |
| `view/product/special/before` | `journal3/event/products/view_products_before` |
| `view/product/thumb/after` | `journal3/event/products/view_product_thumb_after` |

**Lazy registrations** (registered inside `controller_products_before`, fire only on listing pages):

| Trigger | Action |
|---------|--------|
| `model/catalog/product/getProducts/after` | `journal3/event/products/model_catalog_product_getProducts_after` |
| `model/catalog/product/getTotalProducts/after` | `journal3/event/products/model_catalog_product_getTotalProducts_after` |
| `model/catalog/product/getProductSpecials/after` | `journal3/event/products/model_catalog_product_getProductSpecials_after` |
| `model/catalog/product/getSpecials/after` | `journal3/event/products/model_catalog_product_getProductSpecials_after` |
| `model/catalog/product/getTotalProductSpecials/after` | `journal3/event/products/model_catalog_product_getTotalProductSpecials_after` |
| `model/catalog/product/getTotalSpecials/after` | `journal3/event/products/model_catalog_product_getTotalProductSpecials_after` |

### Category Page

| Trigger | Action |
|---------|--------|
| `controller/product/category/before` | `journal3/event/category/controller_product_category_before` |
| `model/catalog/category/getCategory/after` | `journal3/event/category/model_catalog_category_getCategory_after` |
| `model/catalog/category/getCategories/after` | `journal3/event/category/model_catalog_category_getCategories_after` |
| `view/product/category/before` | `journal3/event/category/view_product_category_before` |

### Product Page

| Trigger | Action |
|---------|--------|
| `controller/product/product/before` | `journal3/event/product/controller_product_product_before` |
| `model/catalog/product/getProduct/after` | `journal3/event/product/model_catalog_product_getProduct_after` |
| `view/product/product/before` | `journal3/event/product/view_product_product_before` |
| `view/product/compare/before` | `journal3/event/product/view_product_compare_before` |
| `model/catalog/product/getProductImages/after` | `journal3/event/product/model_catalog_product_getProductImages_after` |
| `model/catalog/product/getProductRelated/before` | `journal3/event/product/model_catalog_product_getProductRelated_before` |
| `model/catalog/product/getProductOptions/after` | `journal3/event/product/model_catalog_product_getProductOptions_after` |
| `controller/product/review/before` *(OC4)* | `journal3/event/product/controller_product_review_before` |
| `controller/product/review/after` *(OC4)* | `journal3/event/product/controller_product_review_after` |

### Notifications (Cart/Wishlist/Compare)

| Trigger | Action |
|---------|--------|
| `controller/checkout/cart/add/after` | `journal3/event/notification/controller_checkout_cart_add_after` |
| `controller/checkout/cart/edit/after` | `journal3/event/notification/controller_checkout_cart_edit_after` |
| `controller/checkout/cart/remove/after` | `journal3/event/notification/controller_checkout_cart_remove_after` |
| `controller/account/wishlist/add/after` | `journal3/event/notification/controller_account_wishlist_add_after` |
| `controller/product/compare/add/after` | `journal3/event/notification/controller_product_compare_add_after` |

### Account

| Trigger | Action |
|---------|--------|
| `controller/account/login/before` | `journal3/event/account/controller_account_before` |
| `controller/account/register/before` | `journal3/event/account/controller_account_before` |
| `controller/account/register/before` | `journal3/event/account/controller_account_account_before` |
| `controller/account/edit/before` | `journal3/event/account/controller_account_account_before` |
| `model/account/customer/addCustomer/before` | `journal3/event/account/model_account_customer_addCustomer_before` |
| `model/account/customer/editCustomer/before` | `journal3/event/account/model_account_customer_editCustomer_before` |
| `view/account/register/before` | `journal3/event/account/view_account_account_before` |
| `view/account/edit/before` | `journal3/event/account/view_account_account_before` |
| `view/account/account/after` | `journal3/event/account/view_account_account_after` |
| `controller/account/address/add/before` | `journal3/event/account/controller_account_address_before` |
| `controller/account/address/edit/before` | `journal3/event/account/controller_account_address_before` |
| `model/account/address/addAddress/before` | `journal3/event/account/model_account_address_addAddress_before` |
| `model/account/address/editAddress/before` | `journal3/event/account/model_account_address_editAddress_before` |
| `view/account/address_form/before` | `journal3/event/account/view_account_address_before` |
| `view/account/wishlist/before` | `journal3/event/account/view_account_wishlist_before` |
| `view/account/wishlist_list/before` | `journal3/event/account/view_account_wishlist_before` |
| `view/account/order_info/before` | `journal3/event/account/view_account_order_info_before` |

### Checkout

| Trigger | Action |
|---------|--------|
| `controller/checkout/checkout/before` | `journal3/events/controller_checkout_checkout_before` |
| `controller/checkout/cart/add/before` | `journal3/events/controller_checkout_cart_add_before` |

### Performance Optimizations

| Trigger | Action | Purpose |
|---------|--------|---------|
| `model/catalog/category/getCategories/after` | `journal3/event/performance/model_catalog_category_getCategories_after` | Disable unused OC categories menu |
| `controller/common/menu/before` | `journal3/event/performance/controller_common_menu_before` | Disable unused OC main menu (OC3) |
| `model/catalog/information/getInformations/before` | `journal3/event/performance/model_catalog_information_getInformations_before` | Disable unused footer getInformations |

### JS Defer & HTML Minify *(conditional on settings)*

Applied to routes: `common/home`, `information/contact`, `information/information`, `product/category`, `product/manufacturer_info`, `product/manufacturer_list`, `product/product`, `product/search`, `product/special`

| Trigger | Action | Condition |
|---------|--------|-----------|
| `controller/{route}/before` | `journal3/events/controller_js_defer_before` | `performanceJSDefer` = true |
| `controller/{route}/after` | `journal3/events/controller_js_defer_after` | `performanceJSDefer` = true |
| `view/{route}/after` | `journal3/events/html_minify` | `performanceHTMLMinify` = true |

### Other

| Trigger | Action | Purpose |
|---------|--------|---------|
| `controller/journal3/search/before` | `journal3/event/layout/controller_common_header_before` | Search AJAX header data |
| `model/catalog/manufacturer/getManufacturers/after` | `journal3/event/manufacturer/model_catalog_manufacturer_getManufacturers_after` | Manufacturer images |
| `view/product/manufacturer_list/before` | `journal3/event/manufacturer/view_product_manufacturer_list_before` | Manufacturer list page |
| `view/common/cart/before` | `journal3/event/cart/view_common_cart_before` | Mini cart |
| `view/checkout/cart/before` | `journal3/event/cart/view_checkout_cart_before` | Cart page |
| `view/common/maintenance/before` | `journal3/event/maintenance/view_common_maintenance_before` | Maintenance page |
| `view/common/search/before` | `journal3/event/search/view_common_search_before` | Search bar |
| `view/error/not_found/before` | `journal3/event/not_found/view_error_not_found_before` | 404 page |
| `view/information/sitemap/before` | `journal3/event/sitemap/sitemap` | Sitemap page |
| `controller/extension/feed/google_sitemap/after` | `journal3/event/sitemap/google_sitemap` | XML sitemap |
| `model/localisation/language/getLanguages/after` | `journal3/event/language/model_localisation_language_after` | Language list |
| `view/common/language/before` | `journal3/event/language/view_common_language_before` | Language switcher |
| `model/checkout/order/addOrderHistory/after` | `journal3/event/cache/model_checkout_order_addOrderHistory_after` | Cache invalidation on order |

### Profiler Events *(only when `JOURNAL3_PROFILER` = true)*

| Trigger | Action | Priority |
|---------|--------|----------|
| `controller/*/before` | `journal3/profiler/before_controller` | **-9999** (first) |
| `controller/*/after` | `journal3/profiler/after_controller` | **9999** (last) |
| `view/*/before` | `journal3/profiler/before_view` | default |
| `view/*/after` | `journal3/profiler/after_view` | default |

### Event Handler Files Map

| File | Handles |
|------|---------|
| `event/layout.php` | Header/footer/position injection, layout resolution |
| `event/header.php` | Header data preparation |
| `event/footer.php` | Footer data preparation |
| `event/product.php` | Product page data (images, labels, stats, tabs) |
| `event/products.php` | Product listing pages (category/search/special) |
| `event/category.php` | Category page (subcategories, images) |
| `event/cart.php` | Mini cart + cart page |
| `event/notification.php` | Add-to-cart/wishlist/compare notifications |
| `event/account.php` | Account/register/login/address pages |
| `event/manufacturer.php` | Manufacturer pages |
| `event/performance.php` | Disables unused OC controllers/models |
| `event/cache.php` | Journal cache invalidation |
| `event/sitemap.php` | Sitemap pages |
| `event/search.php` | Search bar |
| `event/language.php` | Language switcher |
| `event/maintenance.php` | Maintenance page |
| `event/not_found.php` | 404 page |
| `seo.php` | SEO meta tags |
| `events.php` | JS defer + HTML minify + checkout |

---

## Actual Project Variables (oc_journal3_variable)

### Breakpoints (5)

| Variable | Value (px) |
|----------|-----------|
| `CONTENT_MAX_WIDTH` | 1540 |
| `L` | 1200 |
| `M` | 900 |
| `S` | 500 |
| `XS` | 360 |

---

### Colors (34 + separators)

| Variable | Value |
|----------|-------|
| `BRAND_PRIMARY` | rgba(231, 216, 203, 1) — warm beige |
| `BRAND_PRIMARY_FG` | rgba(25, 25, 25, 1) — near black |
| `BRAND_SECONDARY` | rgba(226, 230, 237, 1) — light blue-gray |
| `BRAND_SECONDARY_FG` | rgba(33, 32, 32, 1) — near black |
| `GRAYSCALE_1` | rgba(255, 255, 255, 1) — white |
| `GRAYSCALE_10` | rgba(245, 244, 243, 1) — off-white |
| `GRAYSCALE_30` | rgba(232, 230, 227, 1) — light gray |
| `GRAYSCALE_60` | rgba(110, 114, 117, 1) — medium gray |
| `GRAYSCALE_90` | rgba(28, 32, 33, 1) — dark near-black |
| `GRAYSCALE_FULL` | rgba(0, 0, 0, 1) — black |
| `UTILITY_OVERLAY` | rgba(0, 0, 0, 0.8) |
| `UTILITY_TRANSPARENT` | rgba(0, 0, 0, 0) |
| `X_ACCENT_1` | rgba(180, 33, 39, 1) — red |
| `X_ACCENT_2` | rgba(64, 148, 99, 1) — green |
| `X_ACCENT_3` | rgba(13, 82, 214, 1) — blue |
| `X_ACCENT_4` | rgba(242, 199, 81, 1) — yellow |
| `X_ACCENT_5` | rgba(190, 139, 222, 1) — purple |
| `X_ACCENT_6` | rgba(242, 119, 99, 1) — orange |
| `X_PASTEL_1` | rgba(255, 235, 235, 1) — pastel red |
| `X_PASTEL_2` | rgba(237, 245, 234, 1) — pastel green |
| `X_PASTEL_3` | rgba(215, 228, 254, 1) — pastel blue |
| `X_PASTEL_4` | rgba(250, 245, 217, 1) — pastel yellow |
| `X_PASTEL_5` | rgba(246, 240, 254, 1) — pastel purple |
| `X_PASTEL_DARK_1` | rgba(117, 15, 11, 1) — dark red |
| `X_PASTEL_DARK_2` | rgba(20, 68, 66, 1) — dark teal |
| `X_PASTEL_DARK_3` | rgba(46, 63, 117, 1) — dark blue |
| `X_PASTEL_DARK_4` | rgba(157, 126, 37, 1) — dark gold |
| `X_PASTEL_DARK_5` | rgba(77, 54, 89, 1) — dark purple |

*Separator entries (0_CUSTOM_SEPARATOR, GRAYSCALE_0_SEPARATOR, etc.) have empty color values — visual dividers in Journal admin.*

---

### Color Schemes (4)

| Variable | Description |
|----------|-------------|
| `SCHEME_1` | **Light** — white background, GRAYSCALE_90 text, BRAND_PRIMARY accents, X_ACCENT_3 links |
| `SCHEME_2` | **Dark** — GRAYSCALE_90 background, GRAYSCALE_10 text, inverted colors |
| `SCHEME_3` | **Brand Primary** — BRAND_PRIMARY background, BRAND_PRIMARY_FG text |
| `SCHEME_4` | **Brand Secondary** — BRAND_SECONDARY background, BRAND_SECONDARY_FG text |

**Color scheme token keys** (each scheme defines these ~43 tokens):
`background_primary`, `background_secondary`, `background_tertiary`, `background_primary_hover`, `background_secondary_hover`, `background_tertiary_hover`, `background_shade`, `background_shade_hover`, `background_border`, `background_border_hover`, `body_background`, `site_background`, `image_background`, `foreground_primary`, `foreground_secondary`, `foreground_tertiary`, `foreground_primary_hover`, `foreground_secondary_hover`, `brand_primary`, `brand_primary_hover`, `brand_primary_foreground`, `brand_primary_foreground_hover`, `brand_secondary`, `brand_secondary_hover`, `brand_secondary_foreground`, `brand_secondary_foreground_hover`, `brand_accent`, `button_background`, `button_foreground`, `neutral_background`, `neutral_foreground`, `text_link`, `text_link_hover`, `overlay`, `pastel_primary`, `pastel_secondary`, `pastel_tertiary`, `pastel_red`, `pastel_green`, `pastel_blue`, `pastel_yellow`, `pastel_purple`, `pastel_foreground`

Each token supports `lightness` and `alpha` adjustments:
```json
{"color": "__VAR__GRAYSCALE_90", "lightness": "-5", "alpha": "0.1"}
```

---

### Fonts (6)

| Variable | Family | Type | Weight |
|----------|--------|------|--------|
| `DEFAULT` | Helvetica Neue, Helvetica, Arial, sans-serif | system | regular |
| `SYSTEM` | Helvetica Neue, Helvetica, Arial, sans-serif | system | regular |
| `DEFAULT_HEADING_BOLD` | Libre Baskerville | google | regular |
| `DEFAULT_HEADING_BOLDER` | Libre Baskerville | google | 700 |
| `DEFAULT_HEADING_BOLD_SEMI` | Libre Baskerville | google | regular |
| `ACCENT` | Ephesis | google | regular |

---

### Font Sizes (24 + separators)

**Scale (px):**

| Variable | px |
|----------|-----|
| `FONT_SIZE_100` | 11 |
| `FONT_SIZE_200` | 12 |
| `FONT_SIZE_300` | 13 |
| `FONT_SIZE_400` | 14 |
| `FONT_SIZE_500` | 15 |
| `FONT_SIZE_600` | 16 |
| `FONT_SIZE_700` | 18 |
| `FONT_SIZE_800` | 20 |
| `FONT_SIZE_900` | 24 |
| `FONT_SIZE__1000` | 28 |
| `FONT_SIZE__1100` | 32 |
| `FONT_SIZE__1200` | 42 |
| `FONT_SIZE__1300` | 56 |
| `FONT_SIZE__1400` | 80 |

**Semantic sizes with responsive multi:**

| Variable | Desktop | Tablet/Mobile |
|----------|---------|---------------|
| `FONT_SIZE__TITLE_DISPLAY_L` | 64px | 48@1540, 36@900, 32@500 |
| `FONT_SIZE__TITLE_DISPLAY_M` | 42px | 36@1200, 28@500 |
| `FONT_SIZE__TITLE_SECTION` | 28px | 24@900 |
| `FONT_SIZE__TITLE_MODULE` | 20px | 18@900 |
| `FONT_SIZE__TITLE_ITEM` | 16px | — |
| `FONT_SIZE__TITLE_ITEM_LIST` | 14px | 15@500 |
| `FONT_SIZE__TITLE_MENU` | 13px | 14@500 |
| `FONT_SIZE___PRICE_LARGER` | 26px | 22@900 |

---

### Font Styles (28)

Typography presets (font_style type = full font object with all 20 keys):

| Variable | Use case |
|----------|----------|
| `PARAGRAPH_DEFAULT` | line-height 1.7, DEFAULT font |
| `PARAGRAPH_LARGE` | line-height 1.7, larger |
| `PARAGRAPH_SMALL` | line-height 1.5 |
| `TITLE_SECTION` | line-height 1.3, HEADING_BOLDER, foreground_primary |
| `TITLE_MODULE` | line-height 1.4, HEADING_BOLD, foreground_primary |
| `TITLE_ITEM` | line-height 1.3, HEADING_BOLD, transform:none |
| `TITLE_LIST` | line-height 1.3, HEADING_BOLD_SEMI |
| `TITLE_LIST_BOLD` | line-height 1.3, foreground_secondary |
| `TITLE_LIST_ITEM` | line-height 1.3, DEFAULT |
| `TITLE_DISPLAY_M` | word-spacing 1, line-height 1.2, HEADING_BOLDER |
| `UI_DEFAULT` | line-height 1.4, DEFAULT |
| `UI_LARGE` | line-height 1.5 |
| `UI_SMALL` | line-height 1.3, text-decoration:none |
| `UI_SMALLER` | line-height 1.25, DEFAULT |
| `UI_VERY_SMALL` | line-height 1.1 |
| `BUTTON_DEFAULT` | line-height 1, transform:none |
| `BUTTON_LARGE` | line-height 1, transform:none |
| `BUTTON_SMALL` | line-height 1 |
| `PRICE_XL` | line-height 1, HEADING_BOLD |
| `PRICE_L` | line-height 1, HEADING_BOLD |
| `PRICE_M` | line-height 1, HEADING_BOLD, foreground_primary |
| `PRICE_S` | line-height 1, HEADING_BOLD_SEMI, foreground_primary |
| `PRICE_OLD` | line-height 1, DEFAULT, foreground_tertiary |
| `LINK_HOVER` | text-decoration:dotted, color:text_link |
| `MENU_LIST` | DEFAULT font, color via __SCHEME__ |
| `MEDIA_LABEL` | line-height 1.4, HEADING_BOLD_SEMI, uppercase |
| `MEDIA_LABEL_ACCENT` | line-height 1, ACCENT font |
| `STROKE` | line-height 1.1, SYSTEM, color:UTILITY_TRANSPARENT (for text-stroke effect) |

---

### Gaps / Spacing (55)

**Base spacing scale (px):**

| Variable | Value |
|----------|-------|
| `SPACING___1` | 2 |
| `SPACING___2` | 4 |
| `SPACING___3` | 6 |
| `SPACING___4` | 8 |
| `SPACING___5` | 12 |
| `SPACING___6` | 16 |
| `SPACING___7` | 20 |
| `SPACING___8` | 24 |
| `SPACING___9` | 32 |
| `SPACING____10` | 40 |
| `SPACING____11` | 48 |
| `SPACING____12` | 56 |
| `SPACING____13` | 64 |
| `SPACING____14` | 72 |
| `SPACING____15` | 88 |
| `SPACING____16` | 112 |
| `SPACING____17` | 144 |
| `SPACING____18` | 184 |
| `SPACING____19` | 224 |
| `SPACING____20` | 256 |

**Semantic gaps:**

| Variable | Desktop | Responsive |
|----------|---------|-----------|
| `SPACING_GAP_1` | 8px | — |
| `SPACING_GAP_2` | 12px | — |
| `SPACING_GAP_3` | 20px | 16@900 |
| `SPACING_GAP_4` | 32px | 24@1200 |
| `SPACING_GAP_5` | 48px | 32@1200 |
| `SPACING_GAP_6` | 60px | 48@1200, 32@500 |
| `SPACING_GAP_GENERAL_1` | 24px | 20@900 |
| `SPACING_GAP_GENERAL_2` | 32px | 24@900 |
| `SPACING_GAP_GENERAL_3` | 40px | 32@1200, 24@500 |
| `SPACING_GUTTER_1` | 32px | 24@1200 |
| `SPACING_GUTTER_2` | 40px | 24@1200 |
| `SPACING_GUTTER_4` | 36px | 28@1540 |
| `SPACING_PADDING_S` | 24px | 20@900, 16@500 |
| `SPACING_PADDING_M` | 32px | 24@500 |
| `SPACING_PADDING_L` | 40px | 32@1200, 24@900 |
| `SPACING_PADDING_XL` | 48px | 32@900 |
| `SPACING_VERTICAL_1` | 24px | — |
| `SPACING_VERTICAL_L` | 32px | — |
| `SPACING_VERTICAL_XL` | 48px | 32@1200 |
| `SPACING_VERTICAL_XXL` | 60px | 40@1200 |
| `SPACING_VERTICAL_XXXL` | 120px | 90@1200, 60@900 |
| `SPACING_VR_ROW_S` | 32px | — |
| `SPACING_VR_ROW_M` | 60px | — |
| `SPACING_VR_ROW_L` | 90px | 60@900 |
| `SPACING_VR_ROW_K` | 120px | 90@1200, 60@900 |
| `SPACING_VR_ROW_I` | 144px | 120@1200, 90@900, 60@500 |
| `SPACING_VR_ROW_H` | 150px | 120@1200, 90@900 |

---

### Radius (14)

| Variable | Value (px) |
|----------|-----------|
| `DEFAULT` | 6 |
| `LARGE` | 8 |
| `INPUT` | 4 |
| `INPUT_2` | 4 |
| `LABELS` | 3 |
| `LIST_IMAGE` | 6 |
| `LIST_ITEMS` | 6 |
| `BUTTON` | 99 |
| `BUTTON_CAROUSEL` | 99 |
| `CHIPS` | 99 |
| `COUNT_BADGE` | 99 |
| `HEADER_ELEMENTS` | 99 |
| `AVATAR_IMAGE` | 99 |
| `ROUND` | 9999 |

---

### Shadows (16)

| Variable | Description |
|----------|-------------|
| `DEFAULT` | Subtle multi-layer shadow |
| `SMALL` | Minimal shadow |
| `SMALL_SOFT` | Soft small shadow |
| `LARGE` | Prominent multi-layer |
| `LARGE_SOFT` | Large but soft |
| `LARGE_UP` | Upward shadow |
| `ELEVATED` | Material-style elevation |
| `ELEVATED_2` | Higher elevation |
| `DROPDOWN` | For dropdowns/popovers |
| `INSET` | Inner shadow |
| `INSET_UP_SOFT` | Inner upward soft |
| `MEDIUM_SOFT` | Medium soft shadow |
| `MEDIUM_UP` | Upward medium |
| `MEDIUM_NW` | Northwest direction |
| `BORDER` | Box shadow border effect |
| `NONE` | Explicit none |

---

### Gradients (31)

| Variable | Type |
|----------|------|
| `BRAND_1` | Brand primary to transparent (top) |
| `BRAND_1_2` | Brand primary to secondary (90deg) |
| `BRAND_1_END` | Brand primary to transparent (right) |
| `BRAND_2` | Brand secondary to transparent |
| `BRAND_2_END` | Brand secondary to transparent (right) |
| `ACCENT_1..6` | Accent colors to transparent (top) |
| `OVERLAY` | Black smooth gradient bottom-to-top |
| `OVERLAY_2` | Black 80% to 30% (top) |
| `FULL_OVERLAY` | 50% flat black |
| `DARK_2/3/4` | Dark gradients |
| `DIAGONAL_ACCENT/BRAND_1/PASTEL` | 135deg corner gradients |
| `DUAL_ACCENT` | Accent_2 to Accent_3 (90deg) |
| `SCHEME_PRIMARY` | HSL scheme variable-based |
| `SCHEME_PRIMARY_SECONDARY` | Primary to secondary |
| `SCHEME_PRIMARY_SOFT` | Soft scheme gradient |
| `SCHEME_PRIMARY_TOP` | Top-fading scheme |
| `SCHEME_SECONDARY_TERTIARY` | Secondary to tertiary |
| `MESH_ACCENT` | Radial mesh with accent colors |
| `MESH_PASTEL` | Radial mesh with pastel colors |
| `PASTEL_B_COPY` | Pastel_4 fade from bottom |
| `PASTEL_T` | Pastel_4 fade from top |
| `NOISE` | SVG fractal noise texture |
| `Mail` | Email striped pattern |

---

### Other Variables

| Variable | Type | Value |
|----------|------|-------|
| `DEFAULT` | items_per_row | Responsive grid: 4 desktop, 3@L, 2@M, 1@S |
| `DEFAULT` | value | 20 (generic numeric value token) |

---

## Style Catalog (oc_journal3_style)

322 styles organized by `style_type`. Reference names via `__VAR__` prefix. Full list by type:

| style_type | Count | Names |
|------------|-------|-------|
| `accordion` | 5 | DEFAULT, DEFAULT_SMALL, FILL, FILL_L, LARGE |
| `accordion_menu` | 2 | DEFAULT, OFF_CANVAS |
| `auto_carousel` | 7 | DEFAULT, ARROWS_TOP, BUTTONS_TOP, BUTTONS_TOP_SMALL, LARGE, LARGE_TOP, SMALL |
| `auto_grid` | 6 | DEFAULT, LARGE, LIST, MEDIUM, SMALL, SMALLER |
| `auto_scrollbar` | 2 | DEFAULT, DEFAULT_THIN |
| `banner` | 4 | DEFAULT, BOTTOM, BOTTOM_FLOAT, LARGE_TEXT |
| `blocks` | 2 | DEFAULT, ZIGZAG |
| `breadcrumbs` | 4 | DEFAULT, DEFAULT_BORDER, DEFAULT_BORDER_COPY, DEFAULT_TITLE |
| `button` | 22 | BORDER, BRAND_PRIMARY, BRAND_SECONDARY, DEFAULT, GRAY, GREEN, ICON, ICON_ONLY, LARGE, LIGHT, LINK, LINK_UNDERLINE, MEDIUM, OUTLINE, OUTLINE_FILL, OVERLAY_DARK, OVERLAY_PRIMARY, POPUP_CLOSE, QUICKVIEW, RED, SMALL, TEXT |
| `carousel` | 5 | DEFAULT, PRODUCT_IMAGE, PRODUCT_PAGE_VERTICAL_IMAGES, QUICKVIEW, SLIDER |
| `cart` | 13 | BUTTON, BUTTON_BRAND, BUTTON_BRAND_SECONDARY, DEFAULT, DEFAULT_BORDER, DEFAULT_FILL, DEFAULT_FILL_BORDER, DEFAULT_FILL_BORDER_SECONDARY, ICON, ICON_TOP, LARGE_TITLE, OUTLINE_FILL, PRIMARY |
| `category` | 9 | CHIPS, DEFAULT, DEFAULT_FILL, LARGE, LIST, LIST_FILL, NAME_OVER, ROUND, SIMPLE |
| `container` | 19 | BUILDER_GRID_ITEM, BUILDER_GRID_ITEM_BORDER, MEDIA_LABEL_ACCENT, MEDIA_LABEL_CAPS, MEDIA_LABEL_DIVIDER, MEDIA_LABEL_FILL, MEDIA_LABEL_FILL_BRAND, MEDIA_LABEL_FILL_BRAND_2, MEDIA_LABEL_FILL_BUTTON, MEDIA_LABEL_FILL_PASTEL, MEDIA_LABEL_FILL_S, MEDIA_LABEL_FILL_SMALL, MEDIA_PRICE, MEDIA_PRICE_BOX, MEDIA_TEXT_FILL, SLIDER_MAIN_TEXT, SLIDER_MAIN_TEXT_LARGE_COPY, SLIDER_MAIN_TEXT_MEDIUM, SLIDER_MAIN_TEXT_XL |
| `count_badge` | 8 | BRAND_PRIMARY, BRAND_SECONDARY, BUTTON, DARK_BLUE, DEFAULT, LIGHT, OUTLINE, SIMPLE |
| `countdown` | 3 | DEFAULT, DEFAULT_2, SEPARATED |
| `form_input` | 7 | DEFAULT, DEFAULT_OUTLINE, SELECT, SIMPLE, SMALL, STEPPER, TEXTAREA |
| `gallery_module` | 1 | DEFAULT |
| `info_block` | 15 | BOX, BOX_NEGATIVE, BOX_SIDE, BOX_SIMPLE, CHIPS, COUNTERS, COUNTERS_SIMPLE, DEFAULT, DEFAULT_OUTLINE, DEFAULT_VERTICAL, LARGE_CON_TOP, SIMPLE_BOXED, SIMPLE_LIST, SMALL, TOP_START |
| `label` | 13 | BLUE, BRAND_PRIMARY, BRAND_SECONDARY, DEFAULT, GREEN, ICON_ONLY, ORANGE, OUTLINE, PURPLE, RED, TEXT, TEXT_ACCENT, YELLOW |
| `links_menu` | 4 | CHIPS, DEFAULT, HORIZONTAL_SCROLL, MULTI_COLUMN |
| `manufacturers` | 7 | DEFAULT, DEFAULT_FILL, GRAYSCALE, LIST, LIST_FILL, NO_IMAGE, ROUND |
| `menu` | 23 | BUTTON_MENU, BUTTON_MENU_BRAND, BUTTON_MENU_PRIMARY, BUTTON_PRIMARY, BUTTON_PRIMARY_SHADOW, DEFAULT, DEFAULT_DIVIDER, DROPDOWN, ICON_ONLY, ICON_TOP, LARGE_ICON, MAIN_MENU, MAIN_MENU_BG, MAIN_MENU_BOLD, MENU_CAPS, MENU_CAPS_BG, MENU_CAPS_S, MENU_TABS, OFF_CANVAS, OFF_CANVAS_BOLD, TOP_MENU, TOP_MENU_MOBILE, VERTICAL |
| `menu_label` | 9 | BLUE, BRAND_PRIMARY, BRAND_SECONDARY, DEFAULT, GREEN, LIGHT, PURPLE, RED, YELLOW |
| `notification` | 1 | DEFAULT |
| `popup` | 1 | DEFAULT |
| `post_grid` | 8 | CHIPS, CONTENT_FILL, CONTENT_FILL_CENTER, DEFAULT, DEFAULT_CENTER, DEFAULT_FILLED, DEFAULT_FILLED_SIMPLE, LIST |
| `post_list` | 2 | DEFAULT, DEFAULT_FILL |
| `product_grid` | 18 | DEFAULT, DEFAULT_2, DEFAULT_QTY, FULL_1, FULL_2, FULL_3, HOVER_1, HOVER_2, HOVER_3, LARGE_1, LARGE_2, LARGE_3, LARGE_4, SIMPLE, SIMPLE_2, SIMPLE_3, SIMPLE_4, SIMPLE_LIST |
| `product_list` | 2 | DEFAULT, SIMPLE |
| `search` | 9 | DEFAULT, DEFAULT_BRAND, DEFAULT_BUTTON, DROPDOWN_BUTTON, DROPDOWN_PAGE, OFFSET_BORDER, OFFSET_BORDER_BRAND_1_COPY, SIMPLE, SIMPLE_OUTLINE |
| `side_posts` | 2 | DEFAULT, DEFAULT_FILL |
| `side_products` | 6 | CHIPS, DEFAULT, SIMPLE, SIMPLE_BORDER, SMALL, SMALL_DIVIDER |
| `stepper` | 4 | COMPACT_DEFAULT, DEFAULT, DEFAULT_FLAT, STEPPER_COMPACT |
| `tabs` | 10 | DEFAULT, DEFAULT_LARGE, DEFAULT_SMALL, PAGE_TABS_BUTTON, PAGE_TABS_SHADOW, TABS_SHADOW, TAB_BUTTON, TAB_BUTTON_FILLED, VERTICAL_BUTTONS, VERTICAL_HORIZONTAL |
| `title` | 18 | DEFAULT, DEFAULT_DIVIDER, DEFAULT_DIVIDER_BORDER, LARGE, LARGER, LARGER_DIVIDER, LARGER_DIVIDER_BORDER, LARGE_DIVIDER, LARGE_DIVIDER_BORDER, MEDIUM, MEDIUM_DIVIDER, MEDIUM_DIVIDER_BORDER, OUTLINE, PAGE_TITLE, PAGE_TITLE_BORDER, PAGE_TITLE_FILL, PAGE_TITLE_FILL_BG, VERY_SMALL |
| `title_module` | 3 | DEFAULT, DEFAULT_DIVIDER, DEFAULT_DIVIDER_ICON |
| `tooltip` | 6 | DEFAULT, DEFAULT_BRAND_PRIMARY, GREEN, HOTSPOT, RED_COPY, DEFAULT_BG_PRIMARY_COPY_COPY |
| `typography` | 1 | DEFAULT |

---

## Master Skin Settings Catalog

26 JSON definition files → **~650+ universal setting keys** (ίδια σε ΟΛΑ τα Journal themes — μόνο οι τιμές αλλάζουν).

**Legend:** `php` = available in PHP via `getPhp()`, `js` = available in JS via `getJs()`

---

### Setting Types Quick Reference

| Type | Output | Example key |
|------|--------|-------------|
| `Toggle` | bool CSS/PHP | `scrollTop`, `quickviewStatus` |
| `Radio` | string PHP/CSS | `pageTitlePosition`, `globalProductView` |
| `Select` | string PHP | `productSort`, `activeCheckout` |
| `Font` | CSS font properties | `postCommentFont`, `labelFont` |
| `Color` | CSS color | `postStatsScrollColor` |
| `Background` | CSS background | `globalProductGridBackground` |
| `Border` | CSS border | `postCommentBorder` |
| `BorderRadius` | CSS border-radius | `scrollTopRadius` |
| `Shadow` | CSS box-shadow | `postStatsShadow` |
| `Margin` / `Padding` / `Gap` | CSS spacing | `postStatsMargin` |
| `Icon` | icon code + config | `globalCartIcon`, `authorIcon` |
| `Variable` | references a style variant | `globalButton`, `globalProductGrid` |
| `InputLang` | multilingual string | `quickviewText`, `sectionTitleLogin` |
| `InputNumber` / `Input` | numeric/text PHP | `productLimit`, `globalExpandCharactersLimit` |
| `ImageDimensions` | `{width, height, resize}` php | `image_dimensions_thumb` |
| `ImageLang` | multilingual image path php | `logo`, `logo2x` |
| `Module` | module_id php | `headerDesktop`, `footerMenu` |
| `ColorScheme` | scheme name → CSS class php | `color_scheme`, `title_color_scheme` |
| `ItemsPerRow` | responsive grid config php | `globalItemsPerRow` |

---

### `image_dimensions.json` — 19 Image Dimension Settings (all `php`)

| Key | Default (WxH, resize) |
|-----|-----------------------|
| `image_dimensions_category` | 200×200, fit |
| `image_dimensions_subcategory` | 200×200, fit |
| `image_dimensions_thumb` | 500×500, fit |
| `image_dimensions_popup` | 1000×1000, fit |
| `image_dimensions_popup_thumb` | 80×80, fill |
| `image_dimensions_product` | 250×250, fit |
| `image_dimensions_additional` | 100×100, fit |
| `image_dimensions_manufacturer_logo` | 100×100, fit |
| `image_dimensions_options` | 40×40, fill |
| `image_dimensions_related` | 250×250, fit |
| `image_dimensions_manufacturer` | 100×100, fit |
| `image_dimensions_compare` | 100×100, fit |
| `image_dimensions_wishlist` | 50×50, fit |
| `image_dimensions_cart` | 50×50, fit |
| `image_dimensions_location` | 120×120, fit |
| `image_dimensions_autosuggest` | 50×50, fit |
| `image_dimensions_notification` | 50×50, fit |
| `image_dimensions_blog` | 335×200, fill |
| `image_dimensions_blog_post` | 1060×400, fill |

---

### `global/general.json` — Core Global Settings

**Color Schemes (php):**
- `color_scheme` — Global site scheme
- `title_color_scheme` — Page title area scheme
- `breadcrumbs_color_scheme` — Breadcrumbs scheme

**Global Style Variables** (all `Variable` type):
`globalPage`, `globalTypography`, `globalDropdown`, `globalMenu`, `globalLegend`, `globalTitle`, `globalCarousel`, `globalAutoCarousel`, `globalAutoGrid`, `globalPageTitle`, `globalPageTitleTop`, `globalModuleTitle`, `globalSideColumnTitle`, `globalMenuLabel`, `globalButton`, `primaryButton`, `largeButton`, `smallButton`, `secondaryButton`, `successButton`, `dangerButton`, `defaultButton`, `warningButton`, `infoButton`, `lightButton`, `darkButton`, `globalButtons`, `globalTags`, `globalTabs`, `globalAlerts`, `globalBreadcrumbs`, `globalAccordion`, `globalCountBadge`, `globalLabel`, `globalTooltip`, `hotspotTooltip`, `globalTable`, `globalForms`, `globalPagination`, `globalStars`, `globalPopup`, `IconButtonsStyle`, `globalExpandButtonStyle`

**Page Title:**
- `pageTitlePosition` (php): `default|top|content_top`
- `pageTitleGutterSubtract`, `titleBeforeBreadcrumbs`, `titleTopOverlay*`, `titleTopBackground`, `titleTopMargin/Padding`, `breadcrumbsBackground`, `breadcrumbsPaddingTop/Bottom`

**Scroll to Top:**
- `scrollTop` (php, js): bool
- `scrollToTop` (php, js): bool
- `scrollTopAlign`: left/right
- `scrollTopOffset`, `scrollTopSize`, `scrollTopRadius`, `scrollTopIconNew`, `scrollTopBackground/BackgroundHover/Border/BorderHover/Shadow/ShadowHover/PaddingNew/IconHover`

**Global Icons:**
`globalMenuIcon`, `globalSearchIcon`, `globalCartIcon`, `globalWishlistIcon` (heart2 eb67), `globalCompareIcon` (iconmonstr-compare eab6), `globalUpdateIcon` (refresh f021), `globalRemoveIcon` (times-circle f057), `globalCloseIcon`, `globalViewIcon`, `globalReturnIcon`, `authorIcon` (eadc), `dateIcon` (f133), `timeIcon` (eb29), `commentIcon` (f27a), `viewIcon` (f06e), `categoryIcon` (f022), `websiteIcon` (e321)

**Loader:**
- `loaderStatus` (php): off/on
- `loaderText`, `loaderFont`, `loaderMargin`, `loaderIcon`, `loaderBackground`
- `loadingIconNew`, `loadingTypeNew` (spin/pulse), `loadingSpeedNew`

**Expand ("Show More"):**
- `globalExpandHeight` (px): default 70
- `globalExpandCharactersLimit` (php): default 200
- `globalExpandButtonText/TextLess` (php)
- `globalExpandButtonStyle`, `globalExpandButtonFont`, `globalExpandButtonPadding`
- `globalExpandIcon/IconHover/IconUp/IconHoverUp/IconPosition/IconSpace`
- `globalExpandOverlayColor`

**Popup defaults:** `defaultPopupSticky`, `defaultPopupClose/CloseHover/CloseOffset`, `defaultPopupTitleFont`, `defaultPopupPadding/Font/Divider/BorderRadius/Border/Background/TitleBackground/Shadow`

**Misc:** `responsiveLayout` (php), `addToCartAction` (php): default/popup/cart, `placeholder` (image path), `theme_color` (php), `oldBrowserStatus/Title/Text/Color/Background`, `GlobalScrollbarStyle`, `BodyScrollbarStyle`

---

### `header/general.json`

| Key | Type | php | Notes |
|-----|------|-----|-------|
| `headerDesktop` | Module | ✓ | Module ID of active desktop header |
| `headerMobile` | Module | ✓ | Module ID of active mobile header |
| `headerDesktopLogoImage` | Radio | | default/alternate |
| `headerMobileLogoImage` | Radio | | default/alternate |
| `mobileHeaderAt` | InputValue | js | Breakpoint px (default 1024) |
| `mobileHeaderTablet` | Toggle | ✓ | Show mobile header on tablet |
| `logo` | ImageLang | ✓ | Main logo |
| `logo2x` | ImageLang | ✓ | Retina logo |
| `logoAlternate` | ImageLang | ✓ | Alternate (sticky/scroll) logo |
| `logo2xAlternate` | ImageLang | ✓ | Alternate retina logo |
| `logoMobile2x` | ImageLang | ✓ | Mobile logo 2x |
| `logoMobile2xAlternate` | ImageLang | ✓ | Mobile alternate logo 2x |
| `logoSocialShare` | ImageLang | ✓ | OG/social share logo |

---

### `footer/general.json`

| Key | Type | php | Notes |
|-----|------|-----|-------|
| `footerMenu` | Module | ✓ | Desktop footer menu module ID |
| `footerMenuPhone` | Module | ✓ | Mobile footer menu module ID |

---

### `products/general.json` — Product Listing Settings

**PHP settings (affect server-side behavior):**
- `productDescriptionLimit` (php): 150 — excerpt chars
- `productLimit` (php): 15 — products per page
- `productSort` (php): `p.sort_order` — default sort field
- `productOrder` (php): `ASC`
- `sortBarStatus` (php): show/hide sort bar
- `globalProductView` (php): `grid|list`
- `globalProductViewTablet/Phone` (php)
- `globalProductGridType` (php): `auto|ipr`
- `globalHideZeroPrice/globalCartZeroPrice` (php)
- `globalProductStat1/Stat2` (php): product card stats (`none|sold|views|model|sku|...`)
- `globalOptionsPopupStatus` (php): options popup enable
- `infiniteScrollStatus` (php, js): infinite scroll
- `searchPageCategories` (php)
- `allProductsPageTitle/MetaTitle/MetaKeywords/MetaRobots/MetaDescription` (php)

**JS settings:**
- `infiniteScrollOffset`, `infiniteScrollLoadPrev/Next`, `infiniteScrollLoading`, `infiniteScrollNoneLeft`

**Style variables:**
`globalProductGrid`, `globalProductList`, `globalProductGridBackground/Border/Padding`, `globalProductGridGutter`, `globalItemsPerRow`, `globalAutoGridStyle`, `globalProductAutoGrid`, `globalProductFlexItems/ItemGap/Grow/Align`, `OptionsFormStyle`, `globalOptionsPopupStyle`, `globalOptionsButtonStyle`, `globalOptionsStepperStyle`, `globalProductQuickviewTooltip`, `globalProductCartTooltip`, `globalProductWishlistTooltip`, `globalProductCompareTooltip`, `globalProductExtraTooltip`, `globalPagePaginationStyle`

**Sort bar:** `gridVisibility`, `gridIcon`, `listIcon`, `gridBackground/BackgroundHover`, `listBackground/BackgroundHover`, `compareVisibility`, `compareIcon`, `compareWrap`, `countBadgeVisibility`, `sortVisibility`, `showVisibility`, `inputStyle`

**Infinite scroll styles:** `infiniteFont`, `infiniteBox`, `infiniteLoader`, `infiniteLoaderColor`, `infiniteButtonMargin/Style/Width/Icon/IconHover`

---

### `product/general.json`

| Key | Type | Notes |
|-----|------|-------|
| `productPageStyle` | Variable (product_page) | Full product page style variant |

*(Product page settings mostly come from the skin's `productPageStyle` variable which resolves to a `product_page` style variant: DEFAULT, DEFAULT_FILL, DEFAULT_SIDE, LARGE_ADDITIONAL)*

---

### `page/category.json` — Category Page

**PHP:** `categoryPageDescStatus`, `categoryPageCategoryDescriptionStatus`, `categoryPageCategoryImageStatus`, `subcategoriesStatus` (show/hide), `subcategoriesDisplay` (carousel/grid/list/links), `subcategoriesItemsPerRow`, `subcategoriesCarousel`, `refineTitle`, `refineTitleText`

**Subcategory display:**
`subcategoriesGridType` (auto/ipr), `subcategoriesFlexItems/ItemGap/Grow/Align`, `autoGrid`, `autoGridStyle`, `autoCarousel*`, `FaderStatus/Color/Width`, `AutoScrollbarStyle`

**Subcategory styling:**
`subcategoriesNameFont/FontHover`, `subcategoriesNamePadding/AlignH/AlignV/Truncate`, `subcategoriesImageStatus/Scale/Position`, `subcategoriesItemBoxStyles`, `subcategoriesNameContainer`, `subcategoriesImage`, `subcategoriesMarginTop/Margin/Order/MaxWidth`, `categoryCountBadge*`

**Category description:**
`categoryDescriptionDisplay`, `categoryDescriptionText`, `categoryPageTypography`, `categoryImageFloat`, `descriptionMaxWidth`

---

### `page/checkout.json` — Checkout

**PHP:** `activeCheckout` (opencart/journal), `quickCheckoutAuthentication` (register/guest/login), `quickCheckoutSameAddress`, `quickCheckoutAutoSaveFields`, `sectionShippingVisibility/PaymentVisibility`, `sectionShippingAutoSelect/PaymentAutoSelect`, `quickCheckoutConfirmNewsletter`, `quickCheckoutComments` (visible/hidden)

**All form field visibility settings (php):** `quickCheckoutAccount*Field`, `quickCheckoutAddress*Field` — each: `required|visible|hidden`

**Checkout style variables:**
`checkoutAccordionStyle`, `checkoutFormStyle`, `checkoutPageButtonsStyle`, `checkoutPageTableStyle`, `quickCheckoutFormStyle`, `quickCheckoutTitles`, `CheckoutButtonStyle`, `CheckoutButtonStyleJournal`, `checkoutPopupStyle`, `checkoutPageStepperStyle`, `oc4ConfirmButtonStyle`, `oc4ConfirmTableStyle`

**Section title texts (php, InputLang):**
`sectionTitleLogin`, `sectionTitlePersonal`, `sectionTitlePaymentAddress`, `sectionTitleShippingAddress`, `sectionTitleShippingMethod`, `sectionTitlePaymentMethod`, `sectionTitleCouponVoucherReward`, `sectionTitlePaymentDetails`, `sectionTitleShoppingCart`, `sectionTitleConfirm`, `confirmOrderLanguage`

**Layout:** `quickCheckoutColumnsSplit` (% split), `quickCheckoutSpaceColumn/Section`, `oc4CheckoutSectionsSplit/SectionsSplitGap`, `oc4CheckoutSticky`, `cartMaxHeight`, `checkoutMaxWidth`

**Payment/Shipping icons:** `qcShipIcon1..10`, `qcPayIcon1..10`

---

### `global/quickview.json`

| Key | php/js | Default |
|-----|--------|---------|
| `quickviewStatus` | php | true |
| `quickviewPopupWidth` | | 760 |
| `quickviewText` | php, js | "Quickview" |
| `quickviewDescription` | php | true |
| `quickviewDescriptionPosition` | php | image |
| `quickviewExtraText` | php | "More Details" |
| `quickviewExpandButton` | php | true |
| `quickviewExpandHeight` | | — |
| `quickviewPageStyle` | | Variable(quickview) |
| `quickviewPopupStyle` | | Variable(popup) |

---

### `global/countdown.json`

| Key | php/js | Default |
|-----|--------|---------|
| `countdownStatus` | php | true |
| `countdownStyle` | | Variable(countdown) |
| `countdownDay/Hour/Min/Sec` | php, js | Day/Hour/Min/Sec |

---

### `global/notification.json`

| Key | php/js | Notes |
|-----|--------|-------|
| `notificationStatus` | php | Enable notification system |
| `notificationHideAfter` | js | 2000ms auto-hide |
| `cartNotificationStyle` | | Variable(notification) |
| `wishlistNotificationStyle` | | Variable(notification) |
| `compareNotificationStyle` | | Variable(notification) |
| `alertNotificationStyle` | | Variable(notification) |

---

### `global/stepper.json`

| Key | php/js | Default |
|-----|--------|---------|
| `stepperStatus` | php, js | true |
| `stepperDecimals` | js | 0 |
| `globalStepper` | | Variable(stepper) |

---

### `global/ripple.json`

| Key | js | Default |
|-----|-----|---------|
| `rippleStatus` | ✓ | false |
| `rippleSelectors` | ✓ | ".btn" |

---

### `catalog_mode.json`

All `php`, all Toggle — enable/disable UI elements in catalog mode:
`catalogLanguageStatus`, `catalogCurrencyStatus`, `catalogSearchStatus`, `catalogMiniCartStatus`, `catalogCartStatus`, `catalogWishlistStatus`, `catalogCompareStatus`, `catalogQuickviewTabletStatus`, `catalogQuickviewPhoneStatus`

---

### `blog/posts.json` — Blog Listing

**PHP:** `globalPostView` (grid/list), `globalPostGridType` (auto/ipr), `rssIconOnly`, `rssVisibility`

**Style vars:** `globalPostGrid`, `globalPostList`, `globalPostAutoGridStyle`, `globalPostItemsPerRow`, `globalPostGridGutter`, `globalPostAutoGrid`, `globalPostFlexItems/ItemGap/Grow/Align`

**RSS:** `rssIcon`, `rssIconHover`, `rssFont`, `rssFontHover`, `rssOffset`

---

### `blog/post.json` — Single Post Page

**Visibility toggles:** `postStatVisibility`, `postAuthorVisibility`, `postDateVisibility`, `postCommentsVisibility`, `postViewsVisibility`, `postCategoriesVisibility`, `postTimeVisibility`, `postImageVisibility`, `postCommentsWebsite`

**Date display:**
- `datePosition` (php): image/inline
- `dateDayFont`, `dateMonthFont`, `dateBackground/Margin/Padding/Border/BorderRadius/Shadow`

**Post stats bar:**
`postStatsFont`, `postStatsLinkFont/FontHover`, `postStatsIconsColor`, `postStatsBackground/Color/Margin/Padding/Border/BorderRadius/Shadow`, `postStatsScroll`, `postStatsScrollColor`, `DefaultScrollbarStyle`

**Post icons:** `postAuthorIcon`, `postDateIcon`, `postCommentsIcon`, `postViewsIcon`, `postCategoriesIcon`

**Content:**
`postTypography`, `postFont`, `postParagraphSpacing`, `postDetailsBackground/Padding`, `blogPostMaxWidth`
`postColumns` (initial/2/3), `postColumnGap/DividerColor/DividerWidth/DividerStyle`
`postImageAlign`, `postImageMargin/Border/BorderRadius/Shadow`
`postTags`, `postTagsAlign/Margin`

**Comments:**
`postCommentsBackground/Margin/Padding/Border/BorderRadius/Shadow`, `postCommentsTitle`
`postCommentBackground/Space/Padding/Border/BorderRadius/Shadow`
`postReplyBackground/Margin/Padding/Border/BorderRadius/Shadow`
`postCommentImageSize` (php: 50px), `postCommentImageVisibility/AvatarGrayscale/Border/Margin/BorderRadius/Shadow`
`postCommentUserFont`, `postCommentDataFont`, `postCommentDateIcon/TimeIcon/SiteIcon`
`postCommentFont`, `postCommentUserFont`, `postReplyFont`
`formStyle`, `replyStyle`, `pageButtons`, `postReply`

---

### `page/cart.json` — Cart Page

**Style vars:** `cartPageTableStyle`, `cartPageTableTotalsStyle`, `cartPageStepperStyle`, `cartPageUpdateButtonStyle`, `cartPageRemoveButtonStyle`, `cartPageTotalTableStyle`, `CartGetQuoteStyle`, `ContinueShoppingButtonStyle`, `CartCheckoutButtonStyleNew`

**Layout:** `cartPageBottomPosition` (bottom/side), `cartPageBottomStickyStatus`, `cartPageBottomWidthNew`, `cartPageBottomSpacing`, `cartPageBottomBoxMarginNew`, `cartTotalPosition`, `cartTableMaxWidth/Align`

**Panels (coupons/comments):** `cartPanelsStatus` (php), `cartPagePanelsTitle`, `cartPagePanelsAccordion/Form/PageButtons`, `cartPagePanel0-3Visibility/Open`

**Column visibility:** `cartPageImageVisibility`, `cartPageQuantityVisibility`, `cartPageNameVisibility`, `cartPageModelVisibility`, `cartPageUnitVisibility`

**Fonts:** `cartPageTableProductFont/FontHover`, `cartPageTableOptionsFont`, `cartPageTableModelFont`, `cartPageTablePriceFont`, `cartPageTableTotalFont`, `cartPageTotalLabelTableFont`, `cartPageTotalValueTableFont`, `cartPageStockFont/Background`

---

### `page/wishlist.json` — Wishlist Page

**Style vars:** `wishlistPageTableStyle`, `wishlistPageCartButtonStyle`, `wishlistPageRemoveButtonStyle`, `wishlistPageButtonsStyle`

**Column visibility:** `wishlistPageImageVisibility`, `wishlistPageNameVisibility`, `wishlistPageModelVisibility`, `wishlistPageStockVisibility`, `wishlistPageUnitVisibility`

**Fonts:** `wishlistPageNameFont/FontHover`, `wishlistPageModelFont`, `wishlistPageStockFont/Background`, `wishlistPageOutStockFont/Background`, `wishlistPageNewPriceFont`, `wishlistPageOldPriceFont`

**Icons:** `wishlistPageCartIcon`, `wishlistPageRemoveIcon`

---

### `page/account.json` — Account Pages

**Login/Register popups:**
- `accountLoginPopup/Width/HeightNew` → popup config
- `accountRegisterPopup/Width/HeightNew`
- `accountLoginPopupTitle`, `accountLoginPopupTitleMargin`
- `accountLoginColumnsNew` (php): 1|2 columns
- `accountRegisterBefore` (php): position of register relative to login

**Field visibility (php):** Each field: `required|visible|hidden`
Account fields: firstName, lastName, telephone, fax, customerGroup
Address fields: firstName, lastName, company, address1, address2, city, country, region, postcode

**Field sort order:** `account*FieldSort` — controls display order

**Page display:** `accountPageStyle` (Variable account), `accountCustomerGroup` (php: visible)

**Buttons:** `accountReorderButtonStyle/Icon/Padding/Visibility`, `accountViewOrderButtonStyle/Icon/Padding`, `accountReturnButtonStyle/Icon/Padding/Visibility`, `accountAddressButtonStyle/Icon/Padding`, `accountAddressDeleteButtonStyle/Icon/Padding`

**Account page sections toggle:** `accountPageAddress`, `accountPageAffiliates`, `accountPageCards`, `accountPageDownloads`, `accountPageEdit`, `accountPageHistory`, `accountPageNewsletter`, `accountPagePassword`, `accountPageRecurring`, `accountPageReturns`, `accountPageRewards`, `accountPageTransactions`, `accountPageWishlist`

**Account page icons:** `accountPageIcon`, `accountPageIconAddress`, `accountPageIconEdit`, `accountPageIconPassword`, + icon per section

---

### `page/compare.json` — Compare Page

**Column visibility toggles:** `comparePageName`, `comparePageImage`, `comparePagePrice`, `comparePageModel`, `comparePageBrand`, `comparePageAvailability`, `comparePageRating`, `comparePageSummary`, `comparePageWeight`, `comparePageDimensions`

**Style vars:** `comparePageTableStyle`, `comparePageCartButtonStyle`, `comparePageRemoveButtonStyle`

---

### `page/contact.json` — Contact Page

**Store locations:** `contactLocationTitle/TitleVisibility`, `contactLocationImage/Address/Phone/Fax/Hours`, `contactLocationFont/StrongFont/FontMap`, `contactLocationMapStyle`

**Multiple stores:** `contactStoresTitle/TitleVisibility`, `contactOtherStoresVisibility`, `contactStoresAccordion`

**Contact form:** `contactFormStatus` (php), `contactFormStyle`, `contactPageButtons`

---

### `page/search.json` — Search Page

`searchPageCriteriaTitleVisibility`, `searchPageSubcategoriesVisibility`, `searchPageDescriptionVisibility`, `searchPageProductsVisibility`, `searchPageSubcategoriesMargin`, `searchPageButtonsMargin`, `searchPageTitleStyle`, `searchPageForm`, `searchPageButtons`, `searchPageCategories` (php), `searchPageProductsStyle`, `searchPageProductsTitleVisibility`

---

### `page/information.json` — Info Pages

`informationTypography`, `informationColumns` (initial/2/3), `informationColumnGap`, `informationColumnDividerColor/Width/Style`, `informationHideLoneP` (removes lone `<p>` tags)

---

### `page/sitemap.json` — Sitemap

`sitemapBox`, `sitemapBox2`, `sitemapFont/FontHover`, `sitemapTopFont/TopFontHover`, `sitemapCategoryIcon/IconHover`, `sitemapSubCategoryIcon/IconHover`, `sitemapGap`, `sitemapColumns` (2/3/4)

---

### `page/manufacturers.json` — Manufacturers Listing

`manufacturersImageStatus`, `manufacturerItemsGrid` (width×gap), `manufacturerItemFont/FontHover`, `manufacturerBrandBG/Shadow/Border/BorderRadius/Padding/Font`, `manufacturerLetterFont/FontHover`, `manufacturerBox`, `manufacturerTitle/TitleFont`, `manufacturerLinkFont/FontHover`

---

### `page/maintenance.json` — Maintenance Page

`maintenanceHeader/Footer`, `fullwidth`, `maintenanceAlign`, `maintenanceFont`, `maintenanceBackground`, `maintenancePadding`, `maintenanceMetaTitle/Content` (php), `maintenanceTypography`, `maintenanceGridModule` (php), `maintenanceContentVisibility`

---

### Summary: Settings by Section

| File | Key count | Key php settings |
|------|-----------|-----------------|
| `image_dimensions.json` | 19 | all 19 |
| `global/general.json` | ~130 | pageTitlePosition, scrollTop, addToCartAction, theme_color, loaderStatus, oldBrowserStatus, responsiveLayout |
| `products/general.json` | ~90 | productLimit, productSort, productOrder, globalProductView, globalProductGridType, globalHideZeroPrice, infiniteScrollStatus, globalOptionsPopupStatus |
| `page/checkout.json` | ~120 | activeCheckout, quickCheckoutAuthentication, sectionShipping/PaymentVisibility, all field visibility settings |
| `page/category.json` | ~70 | categoryPageDescStatus, subcategoriesStatus, subcategoriesDisplay, refineTitle |
| `page/account.json` | ~80 | accountLoginColumnsNew, accountRegisterBefore, all field visibility settings |
| `blog/post.json` | ~110 | datePosition, postCommentImageSize |
| `blog/posts.json` | ~20 | globalPostView |
| `header/general.json` | 13 | all 13 |
| `global/quickview.json` | ~25 | quickviewStatus, quickviewDescription, quickviewDescriptionPosition |
| `global/countdown.json` | 6 | countdownStatus, countdownDay/Hour/Min/Sec |
| `global/notification.json` | 6 | notificationStatus |
| `global/stepper.json` | 3 | stepperStatus, stepperDecimals |
| `global/ripple.json` | 2 | (js only) |
| `catalog_mode.json` | 9 | all 9 |
| `footer/general.json` | 2 | both |
| `page/cart.json` | ~55 | cartPanelsStatus |
| `page/wishlist.json` | ~25 | — |
| `page/compare.json` | ~15 | — |
| `page/contact.json` | ~20 | contactFormStatus |
| `page/search.json` | ~13 | searchPageCategories |
| `page/information.json` | 7 | — |
| `page/manufacturers.json` | ~18 | — |
| `page/sitemap.json` | ~12 | — |
| `page/maintenance.json` | ~17 | maintenanceMetaTitle, maintenanceContent, maintenanceGridModule |
| `product/general.json` | 1 | — |

---

## Parser Class — Full Documentation

### `Journal3\Options\Parser`

Location: `system/library/journal3/options/parser.php`

**Purpose**: Takes JSON setting definition files + DB-stored values → compiles CSS, JS config, PHP config, and font lists.

### Constructor

```php
public function __construct(
    $files,            // string|array — relative paths under data/settings/ (e.g. 'skin/global/general')
    $db_settings,      // array — raw key→value from oc_journal3_skin_setting or module_data
    $selector_prefix = null,   // CSS selector prefix (e.g. '.module-slider-123')
    $selector_params = null    // array — layout_id, row_id, col_id for scoped CSS
)
```

Calls `$this->parse()` immediately on construction.

### Static Config (set before instantiation)

```php
Parser::setConfig('language_id', $language_id);
Parser::setConfig('currency_id', $currency_id);
Parser::setConfig('device', 'desktop|tablet|phone');
Parser::setConfig('rtl', true|false);
Parser::setCache($cache_object);
```

### Output Methods

```php
$parser->getPhp(): array
// All settings with 'php' => true + variable-type settings
// Used for controller logic (counts, limits, display modes)

$parser->getJs(): array
// All settings with 'js' => true
// Numeric values auto-cast to int/float
// Used for Swiper options, carousel config, etc.

$parser->getCss(): ?string
// Compiled minified CSS
// Handles: media queries (min/max-width, range), device variants,
// :hover stripping on non-desktop, RTL variants
// Returns null if no CSS rules generated

$parser->getFonts(): array
// Structure: {
//   'fonts'        => [family => [weight => weight]],
//   'subsets'      => [subset => subset],
//   'fonts_custom' => [family => ['woff2' => hash, 'woff' => hash]]
// }
// Used for Google Fonts loading

$parser->getSettings(): array
// All resolved settings key→value

$parser->getSetting($key, $default = null)
// Dot-notation access via Arr::get()

$parser->addJs($name, $value)
// Manually inject a JS setting after parsing
```

### Setting Definition Format (in JSON files)

```json
{
  "name": "setting_key",
  "type": "Font|Color|Background|Border|BorderRadius|Shadow|Padding|Margin|Gap|Toggle|Radio|Input|InputLang|Variable|Include|ColorScheme|Icon",
  "php": true,        // → include in getPhp()
  "js": true,         // → include in getJs()
  "selector": ".my-element",   // CSS selector (combined with prefix)
  "property": "font-size",     // CSS property
  "device": true,     // → generate device-specific variants
  "hover": true,      // → generate :hover variant
  "default": "value"  // fallback if DB value missing
}
```

### __VAR__ Resolution

```
1. Setting value = "__VAR__ACCENT"
2. Parser calls Option::getVariable('__VAR__ACCENT', $type)
3. Looks up $variables[$type]['__VAR__ACCENT']
4. Variables loaded from oc_journal3_variable (cache key: 'variables.all')
5. Returns actual value: {"color": "rgba(...)"}
```

### __SCHEME__ Resolution

```
1. Setting value = "__SCHEME__foreground_primary"
2. Parser calls Option::getVariable('__SCHEME__foreground_primary', 'color')
3. Looks up active skin's color_scheme setting
4. Extracts HSL values → CSS var reference:
   var(--j-color-scheme-foreground-primary-h),
   var(--j-color-scheme-foreground-primary-s),
   calc(var(--j-color-scheme-foreground-primary-l) - 0%)
5. Outputs as HSL CSS variable usage
```

### Responsive `_multi` Processing

```
setting "fontSize_multi": [
  {"min": "", "max": "__VAR__M", "value": "14"},
  {"min": "__VAR__S", "max": "__VAR__L", "value": "12"}
]
→ Generates @media queries:
  @media (max-width: 900px) { ...font-size: 14px... }
  @media (min-width: 500px) and (max-width: 1200px) { ...font-size: 12px... }
```

---

## Asset Loading System

Location: `catalog/controller/journal3/assets.php`

### Load Order

1. **jQuery** — if `performanceJQuery` = true
2. **Font Awesome** — if `performanceFontAwesome` = true
3. **Bootstrap** — if `performanceBootstrap` = true
4. **Icomoon icon font** — always loaded
   - Version hash from MD5 of icon files
   - Preload link for HTTP/2 push if `performancePushIcons` = true
5. **Common JS files** (in order):
   - `hoverintent.js` — hover delay detection
   - `smoothscroll.js` — smooth scroll polyfill
   - `common.js` — Journal core utilities
   - `journal.js` — main Journal JS
   - `stepper.js` — quantity stepper
   - `countdown.js` — countdown timer
   - `search.js` — search functionality
   - `typeahead.js` — search autocomplete
   - `lozad.js` — lazy loading (IntersectionObserver)
   - `loadjs.js` — async JS loader

### CSS Defer Mechanism

When `performanceCSSDefer` = true:
```html
<!-- Standard -->
<link rel="stylesheet" href="...">

<!-- Deferred (non-blocking) -->
<link rel="preload" href="..." as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="..."></noscript>
```

### JS Defer Mechanism

When `performanceJSDefer` = true, the event `view/*/after` replaces:
```
type="text/javascript"  →  type="text/javascript/defer"
```
Then a small inline script converts them back after page load.

### CDN Asset URLs

When `performanceCDNStatus` = true:
- Static assets (JS/CSS) use `JOURNAL3_STATIC_URL`
- Images use `JOURNAL3_STATIC_IMAGES_URL`
- Both set as constants at startup from `performanceCDNHttp/Https` settings

---

## Twig Template Variables

### header.twig (`template/common/header.twig`)

| Variable | Type | Content |
|----------|------|---------|
| `journal3` | object | Global Journal object (settings, device, popups) |
| `direction` | string | `"ltr"` or `"rtl"` |
| `lang` | string | Language code (e.g. `"el"`) |
| `journal3_classes` | array | CSS classes for `<html>` element |
| `journal3_oc_version` | string | OpenCart version |
| `journal3_version` | string | Journal version (3.2.5) |
| `title` | string | Page `<title>` |
| `base` | string | `<base href>` URL |
| `description` | string | Meta description |
| `keywords` | string | Meta keywords |
| `journal3_metas` | array | `[{attr, name, content}]` — OG/Twitter meta tags |
| `journal3_meta_tags` | array | `[name => {type, content}]` — additional meta |
| `journal3_links` | array | `[{href, rel, attrs}]` — preload/canonical links |
| `links` | array | Standard OC link tags |
| `journal3_sentry_dsn_loader` | string\|null | Sentry CDN loader URL |
| `journal3_js` | object | `window['Journal']` JS config object |
| `journal3_inline_scripts` | array | Inline `<script>` blocks |
| `styles` | array | `[{href, rel, media}]` stylesheet refs |
| `journal3_css` | array | `[{id, css}]` inline `<style>` blocks |
| `analytics` | array | Raw analytics HTML |
| `journal3_is_oc4` | bool | OC4 compatibility flag |
| `journal3_background_slider` | string | Rendered background slider HTML |
| `journal3_home_h1` | string | Hidden H1 for SEO (homepage only) |
| `journal_admin_bar_links` | array | Admin bar links (admin users) |
| `scripts` | array | `<script>` tags |
| `scripts_defer` | array | Deferred `<script>` tags |
| `journal3_popup` | string | Rendered popup HTML |
| `journal3_header_notice` | string | Rendered header notice HTML |
| `journal3_bottom_menu` | string | Rendered bottom menu HTML |
| `journal3_side_menu` | string | Rendered side menu HTML |
| `journal3_notification` | string | Rendered notification HTML |
| `journal3_header_desktop` | string\|false | Rendered desktop header HTML |
| `journal3_header_mobile` | string\|false | Rendered mobile header HTML |

### layout position views (column_left, column_right, content_top, content_bottom, top, bottom, etc.)

| Variable | Type | Content |
|----------|------|---------|
| `rows` | array | `[row_id => ['classes' => [...], 'columns' => [col_id => ['classes' => [...], 'items' => [module_id => ['classes' => [...], 'item' => rendered_html]]]]]]` |
| `grid_classes` | array | CSS classes for the grid wrapper |
| `modules` | array | Flat `[module_id => rendered_html]` |

### slider.twig (`template/journal3/module/slider.twig`)

| Variable | Content |
|----------|---------|
| `classes` | CSS class array → `journal3.classes(classes)` |
| `edit` | Admin edit URL |
| `name` | Module name for admin tooltip |
| `options` | JSON object → `data-options` (Swiper config) |
| `syncWith` | CSS selector for synced slider |
| `parallax_bg` | bool — show parallax background div |
| `lazyLoad` | bool — use lozad lazy loading |
| `parallaxValue` | px value for parallax depth |
| `width`, `height` | Image dimensions (int) |
| `lazyload_placeholder` | transparent GIF data-URI |
| `items` | Array of slide objects |
| `item.classes` | Per-slide CSS classes |
| `item.type` | `image|video|category|product|custom` |
| `item.image`, `item.image2x` | Resized image URLs |
| `item.alt` | Image alt text |
| `item.link.href` | Slide link URL |
| `item.videoSrc` | Video URL/ID |
| `item.videoPoster` | Video poster image |
| `item.items` | Sub-items (layers) per slide |
| `item.items_left`, `item.items_right`, `item.items_absolute` | Layer groups by position |
| `subitem.type` | `text|image|button|icon|svg|video` |
| `subitem.text` | Text content |
| `subitem.image`, `subitem.image2x` | Layer image URLs |
| `subitem.button_1_text`, `subitem.button_2_text` | Button labels |
| `subitem.hotspot1/2/3` | Hotspot present (bool) |
| `subitem.hotspot1Type` | `link|product|content` |
| `subitem.hotspot1Content` | Hotspot popup HTML |

### product.twig (`template/product/product.twig`)

Journal injects these variables via `catalog/controller/journal3/event/product.php`:

**Images:**
| Variable | Content |
|----------|---------|
| `images` | Array of `{thumb, thumb2x, popup, popup2x}` per image |
| `journal3_image_thumb_width/height/resize` | Main image dimensions |
| `journal3_image_popup_width/height/resize` | Lightbox image dimensions |
| `journal3_image_popup_thumb_width/height/resize` | Lightbox thumbnail dimensions |
| `journal3_image_additional_width/height/resize` | Additional images dimensions |
| `journal3_images_additional` | Array of `{additional, additional2x}` |
| `journal3_images_additional_position` | left/right/bottom/top |
| `journal3_images_additional_direction` | `'vertical'` or `'horizontal'` |
| `journal3_images_additional_carousel` | bool |
| `journal3_images_additional_carousel_options` | Swiper options array |
| `journal3_images_style` | Inline style string for image wrapper |
| `journal3_images_additional_style` | Inline style string for additional images |
| `journal3_image_placeholder` | Transparent placeholder data-URI (lazy load) or null |
| `journal3_images_carousel` | Carousel options object |
| `journal3_images_gallery` | Array of `{type, src, srcset, thumb, subHtml}` for lightgallery |
| `journal3_images_gallery_options` | Lightgallery config array |

**Product Extras:**
| Variable | Content |
|----------|---------|
| `journal3_product_labels` | Array of label data by position |
| `journal3_product_classes` | Associative CSS class flags |
| `journal3_product_extra_buttons` | Extra button HTML |
| `journal3_product_countdown` | `special_date_end` string or null |

**CSS class flags in `journal3_product_classes`:**
- `out-of-stock` — quantity ≤ 0
- `has-zero-price` — price ≤ 0
- `has-countdown` — countdown enabled
- `has-special` — has special price
- `has-extra-button` — extra button assigned
- + exclude button CSS classes (hide-cart, hide-wishlist, etc.)

**Tabs/Blocks (per position):**
| Variable | Content |
|----------|---------|
| `journal3_product_tabs_blocks_content_top` | HTML or null |
| `journal3_product_tabs_blocks_top` | HTML or null |
| `journal3_product_tabs_blocks_details` | HTML or null |
| `journal3_product_tabs_blocks_bottom` | HTML or null |
| `journal3_product_tabs_blocks_image` | HTML or null |
| `journal3_product_tabs_blocks_default` | HTML or null |

**Product Stats (when enabled):**
Each stat follows pattern: `journal3_product_{stat}` (bool) + `journal3_product_{stat}_text` (label) + `journal3_product_{stat}_value` (value).

Stats: `stock`, `manufacturer`, `model`, `weight`, `dimensions`, `reward`, `sku`, `upc`, `ean`, `jan`, `isbn`, `mpn`, `location`, `sold`, `views`

Additional manufacturer vars: `journal3_product_manufacturer_display` (text/image), `journal3_product_manufacturer_href`, `journal3_product_manufacturer_image/image2x`

**Other:**
| Variable | Content |
|----------|---------|
| `journal3_button_cart` | Add-to-cart button text |
| `journal3_product_quantity` | Pre-fill quantity (from GET param) |
| `journal3_view_more_url` | URL to full product page |
| `stylePrefix` | `'quickviewPageStyle'` or `'productPageStyle'` |
| `optionPrice` | Option price display setting |
| `description` | Trimmed; `''` if empty tags only |
| `quickviewExpand` | `'no-expand'` or `''` |

**Per option value (radio/checkbox):** `image` + `image2x` added to each product_option_value entry.

**journal3.get() calls used in product.twig:**
```twig
journal3.get('pageTitlePosition')
journal3.get('title_color_scheme')
journal3.get('breadcrumbs_color_scheme')
journal3.get('image_dimensions_additional.width/height')
journal3.get('image_dimensions_manufacturer_logo.width/height')
journal3.is_rtl
journal3.is_quickview_popup
journal3.is_options_popup
journal3.classes(journal3_product_classes)
journal3.jsonAttrs(journal3_images_carousel)
```

---

### category.twig (`template/product/category.twig`)

Journal injects via `catalog/controller/journal3/event/category.php`:

**Category image:**
| Variable | Content |
|----------|---------|
| `thumb` | Resized category image URL (1x) |
| `thumb2x` | Resized category image URL (2x) |
| `journal3_image_category_width/height/resize` | Category image dimensions |

**Subcategories:**
| Variable | Content |
|----------|---------|
| `categories` | Each entry extended with `image`, `image2x`, `alt`, `name` (with count badge) |
| `journal3_image_subcategory_width/height/resize` | Subcategory image dimensions |
| `journal3_images_carousel` | Carousel options object |

**Standard OC vars used in template:**
`heading_title`, `description`, `products` (rendered HTML), `pagination`, `results`, `text_empty`, `breadcrumbs`, `sorts`, `limits`, `compare`, `text_sort`, `text_limit`

**Journal-added vars:**
| Variable | Content |
|----------|---------|
| `journal3_top` | Top position HTML |
| `journal3_filter_sort` | Sort URL param |
| `journal3_filter_order` | Order URL param |
| `journal3_filter_limit` | Limit URL param |
| `journal3_products_count` | Total product count |
| `journal3_text_compare` | Compare button text |

**journal3.get() calls in category.twig:**
```twig
journal3.get('pageTitlePosition')
journal3.get('title_color_scheme')
journal3.get('breadcrumbs_color_scheme')
journal3.get('categoryPageDescStatus')
journal3.get('categoryPageCategoryImageStatus')
journal3.get('categoryPageCategoryDescriptionStatus')
journal3.get('subcategoriesStatus')
journal3.get('subcategoriesDisplay')
journal3.get('subcategoriesGridType')
journal3.get('subcategoriesItemsPerRow')
journal3.get('refineTitle')
journal3.get('refineTitleText')
journal3.get('sortBarStatus')
journal3.get('globalProductView')
journal3.get('globalProductGridType')
journal3.get('image_dimensions_category.width/height')
journal3.get('image_dimensions_subcategory.width/height')
journal3.is_desktop
journal3.is_rtl
journal3.jsonAttrs(journal3_images_carousel)
```

---

### products.twig (`template/journal3/module/products.twig`)

Set by `catalog/controller/journal3/products.php` via `$this->settings`:

| Variable | Content |
|----------|---------|
| `edit` | Admin edit URL |
| `name` | Module name |
| `title` | Module title |
| `classes` | CSS class array for wrapper |
| `products_classes` | CSS class array for product grid |
| `swiper_carousel` | bool — carousel mode |
| `gridType` | `'ipr'`, `'auto'`, etc. |
| `image_width/height/resize` | Product image dimensions |
| `carouselOptions` | Swiper options |
| `dummy_image` | Transparent placeholder or null |
| `default_index` | Active tab index |
| `sectionsDisplay` | `'tabs'`, `'accordion'`, or `'blocks'` |
| `inline_button_text` | View more button text |
| `inline_button_link` | View more link dict |
| `id` | Module ID |
| `items` | Array of tab/section objects |
| `item.active` | bool |
| `item.tab_classes` | CSS classes for tab |
| `item.panel_classes` | CSS classes for panel |
| `item.classes` | CSS classes |
| `item.products` | Pre-rendered product cards HTML |
| `item.title` | Tab title |
| `item.tabType` | `'link'` or tab type |
| `item.link.href` | Tab link URL |
| `item.index` | Tab index |
| `itemsPerRow` | Items-per-row config (JSON) |

**Each product in the products array:**
`product_id`, `name`, `description`, `price`, `special`, `tax`, `minimum`, `rating`, `href`, `thumb`, `thumb2x`, `second_thumb`, `second_thumb2x`, `classes`, `quantity`, `stock_status`, `labels`, `extra_buttons`, `date_end`, `price_value`, `stat1`, `stat2`, `qid`, `button_cart`

---

### journal3 object (available in ALL templates)

```twig
{{ journal3.get('settingKey') }}           {# get a skin setting #}
{{ journal3.get('settingKey', 'default') }} {# with default #}
{{ journal3.is_desktop }}                   {# bool #}
{{ journal3.is_mobile }}                    {# bool #}
{{ journal3.is_tablet }}                    {# bool #}
{{ journal3.is_phone }}                     {# bool #}
{{ journal3.is_popup }}                     {# bool #}
{{ journal3.is_login_popup }}               {# bool #}
{{ journal3.is_rtl }}                       {# bool #}
{{ journal3.classes(array) }}               {# join array as CSS classes #}
{{ journal3.linkAttrs(link) }}              {# render link attributes #}
{{ journal3.countBadge(text, count) }}      {# count badge HTML #}
{{ journal3.blog_date(date) }}              {# format blog date #}
{{ journal3.carousel(options, key) }}       {# carousel config array #}
{{ journal3.version_compare(v1, v2, op) }}  {# PHP version_compare #}
{{ journal3.uniqueId(prefix) }}             {# unique ID string #}
```

---

## Module Template Variables — Remaining Modules

### Common Variables (present in virtually every module)

| Variable | Description |
|----------|-------------|
| `journal3` | Global Journal3 helper object |
| `classes` | CSS class array for the outer wrapper div |
| `id` | Unique module ID (DOM `id` attrs, tab/accordion anchors) |
| `edit` | Admin edit link (`module_layout/{type}/edit/{id}`) |
| `name` | Module name (shown in admin edit tooltip) |
| `title` | Optional module heading (`<h3 class="title module-title">`) |
| `items` | Array of item objects |
| `gridType` | `'ipr'`, `'auto'`, or fixed-column type |
| `carousel` / `swiper_carousel` | Boolean controlling Swiper carousel rendering |
| `carouselOptions` | JSON-encoded Swiper options |
| `itemsPerRow` | JSON object for per-breakpoint items (`data-items-per-row`) |
| `sectionsDisplay` | `'blocks'`, `'tabs'`, or `'accordion'` |
| `dummy_image` | Transparent placeholder for lazy-load |

---

### banners.twig + banners_grid.twig

**banners.twig** top-level:
- `id`, `classes`, `edit`, `name`, `title`
- `gridType`, `carousel`, `carouselOptions`, `itemsPerRow`
- `lazyLoad` — boolean
- `dummy_image`

Per-item (`item.*`):
- `item.classes`, `item.link.href`, `item.link.*`
- `item.image`, `item.image2x`, `item.image_width`, `item.image_height`, `item.alt`
- `item.title`, `item.title2`, `item.title3` — overlay captions

**banners_grid.twig** top-level:
- `classes`, `edit`, `name`, `title`, `gridType`, `lazyLoad`, `lazyload_placeholder`

Per-item (`item.*`):
- `item.classes`, `item.type` — `'image'`, `'category'`, `'product'`, `'video'`
- `item.image`, `item.image2x`, `item.image_width`, `item.image_height`, `item.alt`
- `item.videoSrc`, `item.videoPoster`
- `item.link.href`, `item.link.*`
- `item.items`, `item.items_left`, `item.items_right` — nested sub-items

Per-subitem (`subitem.*`):
- `subitem.type` — `'text'`, `'svg'`, `'button'`, `'image'`
- `subitem.classes`, `subitem.text`, `subitem.svgCode`, `subitem.caption`
- `subitem.button_1_text`, `subitem.button_1_link.href`
- `subitem.button_2_text`, `subitem.button_2_link.href`
- `subitem.image`, `subitem.image2x`, `subitem.width`, `subitem.height`, `subitem.alt`

**Controller (`banners.php`) settings keys:**
`edit`, `name`, `swiper_carousel`, `classes`, `carouselOptions`, `width`, `height`, `dummy_image`, `imageDimensions.resize`
Per item: `classes`, `image`, `image2x`, `image_width`, `image_height`, `text`

---

### catalog.twig + catalog_blocks.twig

**catalog.twig** top-level:
- `classes`, `edit`, `name`, `title`
- `gridType`, `carousel`, `carouselOptions`, `itemsPerRow`
- `dummy_image`, `lazyLoad`
- `imageDimensions.width`, `imageDimensions.height`
- `viewMoreText` — "View More" link label

Per-item (`item.*`):
- `item.classes`, `item.href`, `item.name`, `item.link.*`
- `item.image`, `item.image2x`
- `item.items` — sub-category/product list
- `item.total` — total count (triggers "View More" when `total > items|length`)

Per-subitem (`sub_item.*`):
- `sub_item.classes`, `sub_item.href`, `sub_item.name`, `sub_item.link.*`
- `sub_item.image`, `sub_item.image2x`

**catalog_blocks.twig** top-level:
- `display`, `titlePosition`, `allLinkText`, `lazyLoad`, `dummy_image`
- `imageDimensionsFeatured.width/.height`

Per-item (`item.*`):
- `item.blockLink.href`, `item.blockLink.*`
- `item.title`, `item.href`, `item.description`, `item.text`, `item.alt`
- `item.header` — `'image'`, `'icon'`, `'text'`, `'none'`
- `item.image`, `item.image2x`
- `item.blockHeaderLink.*`
- `item.footer`, `item.footerButton`, `item.footerButtonLink.*`
- `item.subtype`, `item.items`

**Controller (`catalog.php`) settings keys:**
`edit`, `name`, `swiper_carousel`, `classes`, `carouselOptions`, `is_demos`, `dummy_image`, `images`, `imageDimensions.width/.height/.resize`
Per item: `classes`, `items[]`, `image`, `image2x`, `name`, `href`, `total`

---

### blog_posts.twig + blog_side_posts.twig

Both are structurally identical (only CSS class names differ).

Top-level:
- `classes`, `edit`, `name`, `title`, `id`
- `sectionsDisplay` — `'blocks'`, `'tabs'`, `'accordion'`
- `gridType`, `swiper_carousel`, `carouselOptions`, `itemsPerRow`
- `display` — `'grid'`, `'list'`, etc.

Per-item (`item.*`):
- `item.classes`, `item.tab_classes`, `item.panel_classes`
- `item.title`, `item.index`, `item.tabType`, `item.link.href`
- `item.posts` — pre-rendered HTML of blog posts

**Controller (`blog_posts.php`) settings keys:**
`edit`, `name`, `swiper_carousel`, `classes`, `carouselOptions`, `image_width`, `image_height`, `image_resize`, `dummy_image`, `text_tax`, `button_cart`, `button_wishlist`, `button_compare`, `default_index`
Per item: `active`, `tab_classes`, `panel_classes`, `classes`, `posts` (rendered HTML)

---

### countdown.twig

Top-level:
- `classes`, `edit`, `name`
- `title`, `text` — optional heading and body
- `date` — target date string for `data-date`
- `products` — pre-rendered products HTML (from `beforeRender`)
- `countdownDay`, `countdownHour`, `countdownMin`, `countdownSec` — label strings (via `journal3.get()`)

**Controller (`countdown.php`) settings keys:**
`edit`, `name`, `date` (formatted via `date('D M d Y H:i:s O', strtotime(...))`), `products` (set in `beforeRender()` if `productsModule` is configured)

---

### gallery.twig

Top-level:
- `id`, `classes`, `edit`, `name`, `title`
- `gridType`, `carousel`, `carouselOptions`, `itemsPerRow`
- `button` — boolean; shows "Open Gallery" button instead of grid
- `buttonText` — label for open button
- `dummy_image`
- `thumbsLimit` — max thumbnails to render
- `thumbDimensions.width/.height`
- `images` — JSON array of lightgallery image/video objects (`data-images`)
- `options` — JSON object for lightgallery options (`data-options`)

Per-item (`item.*`):
- `item.classes`, `item.type`
- `item.popup` — lightbox src or link href
- `item.thumb`, `item.thumb2x`
- `item.alt` — caption text

**Controller (`gallery.php`) settings keys:**
`edit`, `name`, `swiper_carousel`, `classes`, `carouselOptions`, `dummy_image`, `dynamic`, `dynamicPath`
`images[]`: `type`, `src`, `srcset`, `width`, `height`, `thumb`, `subHtml`
`options`: `addClass`, `colorSchemeClass`, `thumbWidth`, `thumbHeight`, `allowMediaOverlap`
Per item: `classes`, `alt`, `thumb`, `thumb2x`, `popup`, `popup2x`, `popupThumb`

---

### newsletter.twig

Top-level:
- `classes`, `edit`, `name`, `title`
- `color_scheme_module`, `color_scheme_input` — CSS class names for color schemes
- `text` — optional intro text block
- `action` — form POST URL
- `id` — for unique `for`/`id` attribute pairs
- `placeholder` — email input placeholder
- `buttonType` — `'icon'` or other
- `buttonText` — submit button label
- `tooltipStatus`, `tooltipPosition`, `tooltipText`
- `module_id` — for tooltip CSS class scoping
- `captcha` — rendered captcha HTML
- `agree_data.text` — agree/terms checkbox text

**Controller (`newsletter.php`) settings keys:**
`edit`, `name`, `agree_data`, `action` (OC4+: `journal3/newsletter|send`, else `api/journal3/newsletter`), `captcha` (rendered in `beforeRender()`)

---

### form.twig

Top-level:
- `classes`, `edit`, `name`, `title`
- `action` — form POST URL
- `datepicker` — locale identifier for date picker
- `module_id`, `journal3_is_oc4`, `journal3_is_oc41` — OC version flags
- `text_loading`, `button_upload` — i18n strings

Per-item (`item.*`) — item types: `legend`, `select`, `radio`, `checkbox`, `text`, `name`, `email`, `tel`, `textarea`, `file`, `date`:
- `item.type`, `item.classes`, `item.label`, `item.required`, `item.placeholder`
- `item.items[]` — sub-options for select/radio/checkbox (each with `.label`)

---

### faq.twig

Top-level:
- `classes`, `edit`, `name`, `title`
- `id` — accordion collapse IDs (`{id}-collapse`, `{id}-collapse-{loop.index}`)
- `items`
- `faq_schema` — JSON-LD schema markup (raw output at bottom)

Per-item (`item.*`):
- `item.classes`, `item.panel_classes`
- `item.title` — question text
- `item.has_icon` — boolean for icon display
- `item.content` — answer HTML

---

### accordion_menu.twig

Top-level:
- `classes`, `edit`, `name`, `title`
- `items`
- `image_width`, `image_height` — thumbnail dimensions

Per-item (`item.*`) — recursive via `renderAccordionMenu` macro:
- `item.classes`, `item.link.href`, `item.link.total`, `item.link.classes`
- `item.title` — link text (via `journal3.countBadge()`)
- `item.thumb`, `item.thumb2x` — optional image
- `item.isOpen` — default open state
- `item.isDivider` — divider styling
- `item.items` — nested children (recursive)

---

### manufacturers.twig

Top-level:
- `classes`, `edit`, `name`, `title`, `id`
- `sectionsDisplay`, `gridType`, `swiper_carousel`, `carouselOptions`, `itemsPerRow`

Per-item (`item.*`):
- `item.classes`, `item.tab_classes`
- `item.title`, `item.index`, `item.tabType`, `item.link.href`
- `item.manufacturers` — pre-rendered manufacturers HTML

---

### side_products.twig

Top-level:
- `classes`, `edit`, `name`, `title`, `id`
- `sectionsDisplay`, `gridType`, `swiper_carousel`, `carouselOptions`, `itemsPerRow`

Per-item (`item.*`):
- `item.classes`, `item.tab_classes`
- `item.title`, `item.index`, `item.tabType`, `item.link.href`
- `item.products` — pre-rendered products HTML

---

### links_menu.twig

Top-level:
- `classes`, `edit`, `name`, `title`
- `items` — module skipped entirely if empty

Per-item (`item.*`):
- `item.classes`, `item.link.href`, `item.link.total`, `item.link.classes`
- `item.title` — via `journal3.countBadge()`
- `item.label` — optional badge/label span
- `item.isTitle` — boolean for title-only item styling

---

### icons_menu.twig

Top-level:
- `classes`, `edit`, `name`, `title`
- `items` — module skipped entirely if empty
- `tooltipStatus`, `tooltipPosition`
- `module_id` — CSS class scoping
- `imageDimensions.width/.height`

Per-item (`item.*`):
- `item.classes`, `item.link.href`, `item.link.total`, `item.link.classes`
- `item.title` — tooltip text and alt
- `item.type` — `'image'` (renders `<img>`) or icon
- `item.image`, `item.image2x`

---

### notification.twig

Top-level:
- `classes`, `edit`, `name`
- `options` — JSON-encoded notification behaviour (`data-options`)
- `title` — notification heading
- `text` — notification body
- `notificationCloseText` — close button label

---

### popup.twig

Top-level:
- `iframe` — boolean; renders bare content fragment (inside iframe)
- `contentType` — `'image'`, `'text'`, `'grid'`, or other
- `classes`, `edit`, `name`
- `options` — JSON-encoded popup behaviour (`data-options`)
- `closeButton` — boolean; shows close button + `popup-bg-closable` class
- `headerText` — optional popup header
- `ajax` — boolean; if true + `contentType == 'grid'`, renders an `<iframe>`
- `iframe_src` — URL for iframe source
- `image` — image URL for `contentType == 'image'`
- `imageDimensions.width/.height`
- `text` — HTML content for `contentType == 'text'`
- `content` — rendered HTML for other/grid content
- `footer` — boolean; shows footer area
- `button1`, `button1Text`, `button1Link.href`
- `button2`, `button2Text`, `button2Link.href`
- `doNotShowAgain` — boolean; "don't show again" checkbox
- `doNotShowAgainChecked` — pre-checked state
- `doNotShowAgainText` — checkbox label
- `id` — checkbox `id`/`for` pair

---

## Skin Settings JSON Structure & Default Values

### File Architecture

Settings definitions live in `system/library/journal3/data/settings/` — **302 JSON files** total, organized into:

| Folder | Files | Content |
|--------|-------|---------|
| `common/` | ~46 | Shared component definitions (banner, button, carousel, menu, popup, …) |
| `variables/` | ~46 | Style variable definitions (button, title, typography, form, …) |
| `module/` | ~130 | Per-module config (products, header_desktop, header_mobile, footer_menu, popup, …) |
| `skin/` | ~50 | Page & global skin settings (page/*, global/*, blog/*, header/, footer/) |
| `layout/` | 4 | Layout-level settings (general, column, module, row) |
| `settings/` | 6 | System-wide (active_skin, blog, custom_code, general, performance, seo) |
| `blog/` | 3 | Blog (category, comment, post) |
| `system/` | 1 | Core filter + admin settings |

### JSON Definition Schema

Each setting key follows this structure:

```json
{
  "settingKey": {
    "type": "InputType",
    "value": "defaultValue",
    "php": true,
    "js": true,
    "selector": ".css-selector",
    "variable": "varName",
    "property": "css-property",
    "rules": {}
  }
}
```

| Field | Description |
|-------|-------------|
| `type` | Setting input type (see types below) |
| `value` | Default value — present on most settings; absent on style settings that rely on CSS defaults |
| `php` | `true` → available in PHP/Twig context |
| `js` | `true` → included in `data-options` JSON via `$parser->getJs()` |
| `selector` | CSS selector used by Parser to scope generated CSS rules |
| `variable` | References a `__VAR__` variable name |
| `property` | CSS property this setting maps to |
| `rules` | Conditional visibility/dependency rules |

### Default Value Patterns by Type

| Type | Default pattern | Example |
|------|----------------|---------|
| `Toggle` | `"false"` or `"true"` | `adminEditor: "false"` |
| `Input` / `InputNumber` | Numeric string or `""` | `accountLoginBoxSpacingNew: "25"` |
| `InputLang` / `EditorLang` | Text string or `""` | `name: "New Testimonials"` |
| `Radio` / `Select` | Option key string | `pageTitlePosition: "default"` |
| `Color` / `Font` / `Background` | Usually absent (relies on CSS) | — |
| `ImageDimensions` (complex) | `{"first": "40", "second": "40"}` | `AutoCarouselButtonSize` |
| `Variable` | References another variable | `title`, `button`, `form` |

### Sample Defaults by File

**`system/system.json`**
- `adminEditor`: `"false"` — enable WYSIWYG in Journal admin
- `filterUrlValuesSeparator`: `","` — separator for multi-value filter URLs
- `filterScrollTop`: `"false"` — scroll to top after filter
- `filterCheckQuantity`: `"false"` — hide out-of-stock filter options

**`skin/global/general.json`**
- `pageTitlePosition`: `"default"`
- `pageTitleGutterSubtract`: `"true"`
- `titleBeforeBreadcrumbs`: `"false"`
- `AutoScrollbarThumbVisibility`: `"true"`
- `AutoScrollbarFillVisibility`: `"false"`

**`skin/page/account.json`**
- `accountLoginColumnsNew`: `"2"`
- `accountLoginBoxSpacingNew`: `"25"`
- `accountRegisterBefore`: `"-1"`

**`module/testimonials/general.json`** (~100 settings)
- `name`: `"New Testimonials"`
- `DisplayLayout`: `"grid"`
- `itemsPerSlide`: `"auto"`
- `autoplay`: `"false"`
- `animationType`: `"ipr"`

**`common/auto_carousel.json`**
- `AutoCarouselButtonSize`: `{"first": "40", "second": "40"}`
- `AutoScrollbarThumbVisibility`: `"true"`

### Key Rules

- **Style settings** (Color, Font, Background, Border, Shadow) mostly have no `value` → they rely on CSS or parent variable defaults
- **Boolean settings** default almost universally to `"false"`
- **Only keys with `"js": true`** are included in `data-options` JSON output
- **Only keys with `"php": true`** are available as Twig variables directly
- The `value` field stores the *factory default* — the DB value for the active skin overrides it

---

## Popup & Notification data-options Schema

Options are collected via `$parser->getJs()` — only settings marked `"js": true` in the JSON definition files end up in `data-options`.

### Popup Module

```html
<div class="popup-wrapper ..." data-options='{"showAfter":5000,"hideAfter":"","cookie":"f39fd35a","doNotShowAgain":true,"doNotShowAgainChecked":false}'>
```

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `showAfter` | number (ms) | `5000` | Delay before popup appears on page load |
| `hideAfter` | number or `""` | `""` | Auto-close after N ms; empty = never auto-close |
| `cookie` | string | auto-generated | Cookie name for tracking if user dismissed popup |
| `doNotShowAgain` | boolean | `false` | Enable "Don't show again" checkbox |
| `doNotShowAgainChecked` | boolean | `false` | Pre-check the "Don't show again" checkbox |

The controller also sends popup metadata to JS via `journal3_document->addJs()`:
```php
$this->journal3_document->addJs(['popup' => [
    'm' => $this->module_id,         // module instance ID
    'c' => $this->settings['cookie'],
    'o' => $this->settings['options'],
]]);
```

### Notification Module

```html
<div class="notification ..." data-options='{"cookie":"60febeba"}'>
```

| Key | Type | Description |
|-----|------|-------------|
| `cookie` | string | Cookie name for tracking notification dismissal |

Same JS metadata pattern via `journal3_document->addJs(['notification' => ['m', 'c', 'o']])`.

### Parser Type Conversions

The Parser class (lines 406–412) converts `"js": true` values:
- String with no decimal → cast to `int`
- String with decimal → cast to `float`
- All other values → remain as string

---

## OC4 vs OC3 Behavioral Differences

### Version Detection

**File:** `system/library/journal3/opencart.php`

```php
$this->ver    = (int)explode('.', VERSION)[0];  // 3 or 4
$this->is_oc4 = $this->ver === 4;
$this->is_oc3 = $this->ver === 3;
// Sub-version:
version_compare(VERSION, '4.1.0.0', '>=')  // OC 4.1+
```

Twig receives: `journal3_is_oc3`, `journal3_is_oc4`, `journal3_is_oc41`

### Model Method Renames (OC3 → OC4)

| Feature | OC3 event name | OC4 event name |
|---------|---------------|---------------|
| Product images | `getProductImages/after` | `getImages/after` |
| Related products | `getProductRelated/before` | `getRelated/before` |
| Product options | `getProductOptions/after` | `getOptions/after` |
| Product attributes | `getProductAttributes` | `getAttributes` |

Journal branches on `is_oc4` in `events.php` to register the correct event names.

### Route / URL Changes

| OC3 route | OC4 route |
|-----------|-----------|
| `affiliate/account` | `account/affiliate` |
| `account/voucher` | `checkout/voucher` |
| `account/return` | `account/returns` |
| `account/return/insert` | `account/returns/add` |

OC4 always requires `language=<code>` in all generated URLs.
OC4 account links require `customer_token=__customer_token__` in URL.

### Form Submission Endpoints

| Version | Form endpoint | Newsletter endpoint |
|---------|--------------|-------------------|
| OC3 | `api/journal3/form` | `api/journal3/newsletter` |
| OC4.0 | `api/journal3/form` | `api/journal3/newsletter` |
| OC4.1+ | `journal3/form/send` | `journal3/newsletter/send` |

Cart remove method: **GET** (OC4.1+) vs **POST** (OC3 / OC4.0).

### Database Schema

| Table | OC2/OC3 | OC4 |
|-------|---------|-----|
| SEO URLs | `url_alias` | `seo_url` (with `store_id` + `language_id` filters) |

OC4 `seo_url` uses `$row['key']` / `$row['value']` instead of parsed query strings.

### Class Namespace

```php
// OC3
$image = new \Image(DIR_IMAGE . $file);

// OC4
$image = new \Opencart\System\Library\Image(DIR_IMAGE . $file);
```

### OC4-Only Features

- **Review events** — `controller/product/review/before` and `/after` registered only for OC4; review token fetched from `$this->session->data['review_token']`
- **API session ID** — OC4 popup form submissions require `session->data['api_id']`; auto-generated as `journal3_popup_<hash>` if absent
- **Layout events (OC4.1+)** — `view/common/column_left/before`, `view/common/column_right/before`, `model/design/layout/getModules/after`
- **Wishlist count (OC4.1+)** — `getTotalWishlist($customer_id)` requires explicit ID; OC3/OC4.0 takes no parameter

### OC4 Limitations

- **Journal custom checkout disabled** — `activeCheckout = 'journal'` is ignored on OC4; standard OC4 checkout is used
- **Datetimepicker** — required on OC4.0, not on OC4.1+ (native HTML5 inputs used instead)

### Twig Template Differences

| Feature | OC3 | OC4 |
|---------|-----|-----|
| Empty cart text | `text_empty` | `text_no_results` |
| Product reviews label | `reviews` (data var) | `text_reviews` (lang var) |
| Form error feedback | Custom markup | Bootstrap 5 `invalid-feedback` class |
| Account forms | Standard | Customer token required |
| Toast alerts | Not present | Container in `header.twig` |

### Icon Folder Path Resolution

```php
// OC4
is_file(DIR_APPLICATION . 'view/theme/journal3/icons_custom/selection.json')

// OC3
is_file(DIR_TEMPLATE . 'journal3/icons_custom/selection.json')
```

### Captcha Config Paths

```php
// OC2
$this->config->get($this->config->get('config_captcha') . '_status')

// OC3
$this->config->get('captcha_' . $this->config->get('config_captcha') . '_status')

// OC4
$this->model_setting_extension->getExtensionByCode('captcha', ...)
```

---

## Journal JS Frontend API

### JS File Map

```
catalog/view/theme/journal3/js/
├── head.js          Core API (Journal.load, Journal.lazy, Journal.template), responsive header
├── common.js        Dropdowns, tooltips, popups, cart/wishlist/compare, notifications
├── journal.js       UI interactions, menu handling, off-canvas
├── carousel.js      Swiper carousel initialization
├── slider.js        Hero slider (Swiper-latest)
├── product.js       Product page (gallery sync, auto price update)
├── products.js      Product list (grid/list toggle, infinite scroll)
├── gallery.js       Light Gallery integration
├── filter.js        Filter/search AJAX functionality
├── form.js          Form interactions (date picker, file upload)
├── checkout.js      Quick checkout
├── catalog.js       Catalog hover effects
├── search.js        Search autocomplete
├── newsletter.js    Newsletter signup
├── countdown.js     Countdown timers
├── countup.js       Number count-ups
├── stepper.js       Quantity steppers
└── master_slider.js Master slider integration
```

### Journal Global Object

```javascript
Journal['isDesktop']                // bool
Journal['isTouch']                  // bool
Journal['isAdmin']                  // bool — show admin edit buttons
Journal['isPopup']                  // bool
Journal['isRTL']                    // bool
Journal['mobile_header_active']     // bool
Journal['columnsCount']             // int — product grid columns
Journal['ocv']                      // int — OpenCart major version (3 or 4)
Journal['assets']                   // object — URLs to lazy-loaded JS assets
Journal['add_cart_url']             // string
Journal['remove_cart_url']          // string
Journal['edit_cart_url']            // string
Journal['add_wishlist_url']         // string
Journal['add_compare_url']          // string
Journal['info_cart_url']            // string
Journal['filterBase']               // string — filter URL base
Journal['filterUrlValuesSeparator'] // string — default ","
Journal['infiniteScrollStatus']     // bool
Journal['infiniteScrollOffset']     // int (px)
Journal['notificationHideAfter']    // int (ms)
Journal['scrollToTop']              // bool — scroll top on add-to-cart
Journal['scrollTop']                // bool — scroll-to-top button
Journal['flyoutStickyOffset']       // int — sticky header trigger offset
Journal['stickyStatus']             // bool
Journal['popup']                    // object — popup config
Journal['performanceJSDefer']       // bool
Journal['admin_url']                // string
```

### Core Methods

```javascript
// Dynamic asset loader (uses loadjs internally)
Journal.load(urls, bundleName, successCallback)

// Lazy loader — uses Lozad, stores instance in window['__journal_lazy'][name]
Journal.lazy(name, selector, { load: fn, loaded: fn })

// Manually trigger lazy load on a specific element
window['__journal_lazy']['image'].triggerLoad(element)

// Process <template> elements into DOM (used for menus, modals)
Journal.template(el)

// Dropdown positioning helpers
Journal.dropdownOffset()
Journal.mobileDropdownOffset()
Journal.mobileOffCanvasDropdownOffset()
Journal.mobileSearch()

// Table overflow detection
Journal.tableScroll()
```

### Global Functions

```javascript
// Cart operations
cart.add(product_id, quantity, quick_buy = false)
cart.remove(key)
cart.update(key, quantity)

// Wishlist & compare
wishlist.add(product_id)
compare.add(product_id)
voucher.remove(key)

// Quickview — opens iframe popup
quickview(product_id)

// Custom popup
open_popup(module_id)

// Notification toast
show_notification({
    position: 'center',  // center | top | bottom
    className: '',
    title: '',
    image: '',
    message: '',
    buttons: [{ href, className, name }],
    timeout: 5000
})

// Loading overlay
loader('.selector', true)   // show
loader('.selector', false)  // hide

// Popup iframe auto-resize
update_popup_height(iframeElement)
```

### Custom Events

| Event | Where fired | Payload |
|-------|------------|---------|
| `jcarousel:init` | `carousel.js:106` — Swiper carousel init | `{ swiper: swiperInstance }` |
| `JournalMobileHeaderActive` | `head.js:129` — header switches to mobile | — |
| `JournalDesktopHeaderActive` | `head.js:135` — header switches to desktop | — |

Bootstrap events used: `show.bs.dropdown`, `shown.bs.dropdown`, `hide.bs.dropdown`, `hidden.bs.dropdown`, `show.bs.collapse`, `hide.bs.collapse`, `hidden.bs.collapse`, `show.bs.tab`, `show.bs.tooltip`, `show.bs.popover`

### Swiper Initialization Pattern

```javascript
// data-options on .swiper element is merged with defaults
const options = $.extend({
    init: false,
    slidesPerView: 1,
    slidesPerGroup: 1,
    spaceBetween: 0,
    observer: true,
    observeParents: true,
    watchSlidesProgress: true,
    navigation: { nextEl: ..., prevEl: ... },
    pagination: { el: ..., type: 'bullets', clickable: true },
    scrollbar: { el: ..., draggable: true }
}, $this.data('options'));

const swiper = new Swiper($('.swiper-container'), options);
swiper.on('init', checkPages);
swiper.on('resize', checkPages);
swiper.init();

// Instance stored on element for later access
$this.data('swiper', swiper);
```

**Swiper events used:** `init`, `resize`, `slideChange`, `observerUpdate`, `snapIndexChange`, `slideChangeTransitionStart`, `autoplayStart`, `autoplayStop`

**Responsive grid:** `data-items-per-row='{"c0":4,"c1":3,"sc":2}'` — keys: `c0` full-width, `c<n>` content-width, `sc` sidebar-width

**Swiper-latest** (hero/background sliders): separate `SwiperLatest` constructor, same events

### Module Data Attributes

| Attribute | Description |
|-----------|-------------|
| `data-options='{...}'` | JSON options merged with JS defaults |
| `data-items-per-row='{...}'` | Responsive column config per breakpoint |
| `data-sync-with='<selector>'` | Sync multiple carousels |
| `data-gallery='<selector>'` | Link gallery button to container |
| `data-images='[...]'` | Dynamic lightgallery images array |
| `data-off-canvas='<className>'` | Opens an off-canvas panel |
| `data-filter-trigger` | Marks filter input elements |
| `data-tooltipClass` / `data-popoverClass` | Custom classes on Bootstrap tooltips/popovers |
| `data-pickerClass` | Custom class on datetimepicker |

### Module Lazy-Load Registry

| Module name | Selector | Asset loaded |
|-------------|----------|-------------|
| `carousel_swiper` | `.swiper` | `swiper` |
| `slider` | `.module-slider` | `swiper-latest` |
| `background_slider` | `.module-background_slider` | `swiper-latest` |
| `image` | `.lazyload` | native (Lozad) |
| `countdown` | `.countdown` | `countdown` |
| `countup` | `.module-info_blocks.has-countup` | `countup` |
| `gallery` | `[data-gallery]` | `lightgallery` |
| `form` | `.module-form` | `datetimepicker` |
| `master_slider` | `.module-master_slider` | `masterslider` |

### Filter API

```javascript
// Build filter URL from current selections
journal_filter_url()  // → string URL

// Apply filter (AJAX, replaces .module-filter + .main-products-wrapper)
journal_filter(url, { source: 'pagination', updateHistory: true })

// Ion Range Slider for price filter
journal_filter_price_slider()
```

Filter state stored in: `Journal['filterCollapsed']`, `Journal['infiniteScrollInstance']`

### Product Page: Auto Price Update

```javascript
// Fires on option/quantity change
$('.product-options input, #product-quantity').on('change', autoUpdatePrice);
// AJAX POST to: index.php?route=journal3/price
// Updates: .product-price, .product-stock, .product-tax, .product-discount, .product-points
```

### Popup Infrastructure

```html
<!-- Standard popup markup -->
<div class="popup-wrapper popup-<type>">
  <div class="journal-loading"><em class="fa fa-spinner fa-spin"></em></div>
  <div class="popup-container">
    <div class="popup-body">
      <div class="popup-inner-body">
        <button class="btn popup-close"></button>
        <iframe onload="update_popup_height(this)"></iframe>
      </div>
    </div>
  </div>
  <div class="popup-bg popup-bg-closable"></div>
</div>
```

**Popup types:** `.popup-options`, `.popup-quickview`, `.popup-login`, `.popup-register`, `.popup-module`

**Lifecycle:** append to `<body>` → add `popup-open popup-center` (after 10ms) → close: remove classes → remove DOM (after 500ms transition)

**Quickview iframe URL:** `index.php?route=journal3/product&product_id=<id>&popup=quickview`
