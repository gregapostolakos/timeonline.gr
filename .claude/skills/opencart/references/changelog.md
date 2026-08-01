# Change Log

All changes made to project files are recorded here.  
Format: `YYYY-MM-DD | file(s) | description`

---

<!-- Log entries go here -->

2026-07-29 | stores/index.html, DB: oc_information_description (information_id=8) | Embedded the standalone `/stores/` store-locator widget inside Information page 8 via a self-sizing iframe. Made stores/index.html mount-independent (relative `widget.css`/`widget.js` instead of hardcoded `/oc/stores/…`) and added a postMessage height reporter so the embedding iframe auto-resizes. Replaced the page-8 description hacks (`.content{display:block}` + polling) with a clean iframe + adaptive path (prod `/oc/stores/`, local `/stores/`) + height listener. NOTE: page-8 description lives in the DB (not rsync-deployed) — the same snippet must be pasted into the production admin editor.

2026-07-29 | catalog/model/journal3/filter.php, catalog/controller/journal3/product_extras.php | Product Label "latest" preset now labels products added within the last 1 month with no count limit (true "NEW" badge). Added opt-in `date_added_months` cutoff in filter.php `getProducts()` (only fires when the param is passed, so other filters are unaffected) and a dedicated `case 'latest'` in product_extras.php that sets `date_added_months=1` and `limit=0`. Cleared cached `journal3.module.product_label` so assignments rebuild. Local sanity check: 132 products in last month vs 5796 total enabled.
