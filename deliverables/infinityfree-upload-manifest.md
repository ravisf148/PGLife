# PGLife — InfinityFree Deployment Audit & Upload Manifest

> **Generated:** 2026-10-21
> **Local project root:** `/opt/lampp/htdocs/PGLife/`
> **Target hosting:** InfinityFree
> **Remote website root:** `htdocs/`
> **Target database name:** `if0_43234840_pglife`

---

## Task 1 — Complete Project Audit

### Application Architecture

PGLife is a **PHP + MySQL** web application with a single **React component** (PropertyList filtering) compiled to a standalone JS bundle via esbuild. There is **no framework router** — all pages are separate `.php` files.

### PHP Entry Points

| File | Purpose |
|---|---|
| `index.php` | Home page — city search + city cards |
| `property_list.php` | City property listing with filter/sort |
| `property_detail.php` | Individual property detail page |
| `dashboard.php` | Logged-in user dashboard (interested properties) |
| `logout.php` | Session destroy + redirect to `index.php` |

### PHP API Endpoints (AJAX)

| File | Called From | Method |
|---|---|---|
| `api/login_submit.php` | `js/common.js` via XHR | POST |
| `api/signup_submit.php` | `js/common.js` via XHR | POST |
| `api/toggle_interested.php` | `js/common.js` via XHR | GET |
| `api/fetch_properties.php` | React bundle | GET |

### PHP Includes

| File | Included By |
|---|---|
| `includes/database_connect.php` | All PHP pages and all API endpoints |
| `includes/head_links.php` | All pages |
| `includes/header.php` | All pages |
| `includes/footer.php` | All pages (loads jquery.js, bootstrap.min.js, common.js) |
| `includes/login_modal.php` | index.php, property_list.php, property_detail.php |
| `includes/signup_modal.php` | index.php, property_list.php, property_detail.php |

### CSS Files

| File | Referenced By |
|---|---|
| `css/bootstrap.min.css` | `includes/head_links.php` |
| `css/common.css` | `includes/head_links.php` |
| `css/home.css` | `index.php` |
| `css/property_list.css` | `property_list.php` |
| `css/property_detail.css` | `property_detail.php` |
| `css/dashboard.css` | `dashboard.php` |

### JavaScript Files

| File | Referenced By |
|---|---|
| `js/jquery.js` | `includes/footer.php` |
| `js/bootstrap.min.js` | `includes/footer.php` |
| `js/common.js` | `includes/footer.php` |
| `js/react-components/property_list.bundle.js` | `property_list.php` |

### Database Configuration — MUST CHANGE

| Setting | Current (local) | Required for InfinityFree |
|---|---|---|
| `$host` | `"localhost"` | Keep `"localhost"` (InfinityFree uses localhost internally) |
| `$username` | `"root"` | Change to `"if0_43234840"` |
| `$password` | `""` | Change to your InfinityFree database password |
| `$database` | `"pglife"` | Change to `"if0_43234840_pglife"` |

### React Build System

| File | Role |
|---|---|
| `src/PropertyList.jsx` | Source — React component source. NOT needed on server. |
| `js/react-components/property_list.bundle.js` | Built output — IS the runtime file. Must be uploaded. |
| `package.json` | Build config — dev/build only. Do NOT upload. |
| `package-lock.json` | Lockfile — dev only. Do NOT upload. |
| `node_modules/` | Build tools — dev only. NEVER upload. |

---

## Task 2 — Exact Upload Manifest

### Files to Upload to `htdocs/`

| # | Local Relative Path | Remote Path | Required | Reason | Config Change? |
|---|---|---|---|---|---|
| 1 | `index.php` | `index.php` | Required | Main home page | No |
| 2 | `property_list.php` | `property_list.php` | Required | City listing page | No |
| 3 | `property_detail.php` | `property_detail.php` | Required | Property detail page | No |
| 4 | `dashboard.php` | `dashboard.php` | Required | User dashboard | No |
| 5 | `logout.php` | `logout.php` | Required | Logout handler | No |
| 6 | `.htaccess` | `.htaccess` | Required | URL rewrite for index.php | No |
| 7 | `favicon.ico` | `favicon.ico` | Required | Browser tab icon | No |
| 8 | `includes/database_connect.php` | `includes/database_connect.php` | Required | DB connection | YES — update credentials first |
| 9 | `includes/head_links.php` | `includes/head_links.php` | Required | CSS/CDN links | No |
| 10 | `includes/header.php` | `includes/header.php` | Required | Navigation partial | No |
| 11 | `includes/footer.php` | `includes/footer.php` | Required | Footer + JS loading | No |
| 12 | `includes/login_modal.php` | `includes/login_modal.php` | Required | Login modal | No |
| 13 | `includes/signup_modal.php` | `includes/signup_modal.php` | Required | Signup modal | No |
| 14 | `api/login_submit.php` | `api/login_submit.php` | Required | Login AJAX endpoint | No |
| 15 | `api/signup_submit.php` | `api/signup_submit.php` | Required | Signup AJAX endpoint | No |
| 16 | `api/toggle_interested.php` | `api/toggle_interested.php` | Required | Interested toggle | No |
| 17 | `api/fetch_properties.php` | `api/fetch_properties.php` | Required | React component data API | No |
| 18 | `css/bootstrap.min.css` | `css/bootstrap.min.css` | Required | Bootstrap styles | No |
| 19 | `css/common.css` | `css/common.css` | Required | Shared styles | No |
| 20 | `css/home.css` | `css/home.css` | Required | Home page styles | No |
| 21 | `css/property_list.css` | `css/property_list.css` | Required | Property list styles | No |
| 22 | `css/property_detail.css` | `css/property_detail.css` | Required | Property detail styles | No |
| 23 | `css/dashboard.css` | `css/dashboard.css` | Required | Dashboard styles | No |
| 24 | `js/jquery.js` | `js/jquery.js` | Required | jQuery | No |
| 25 | `js/bootstrap.min.js` | `js/bootstrap.min.js` | Required | Bootstrap JS | No |
| 26 | `js/common.js` | `js/common.js` | Required | XHR handlers | No |
| 27 | `js/react-components/property_list.bundle.js` | `js/react-components/property_list.bundle.js` | Required | React bundle | No |
| 28 | `img/logo.png` | `img/logo.png` | Required | Navbar logo | No |
| 29 | `img/delhi.png` | `img/delhi.png` | Required | City card image | No |
| 30 | `img/mumbai.png` | `img/mumbai.png` | Required | City card image | No |
| 31 | `img/bangalore.png` | `img/bangalore.png` | Required | City card image | No |
| 32 | `img/hyderabad.png` | `img/hyderabad.png` | Required | City card image | No |
| 33 | `img/filter.png` | `img/filter.png` | Required | Filter icon | No |
| 34 | `img/asc.png` | `img/asc.png` | Required | Sort asc icon | No |
| 35 | `img/desc.png` | `img/desc.png` | Required | Sort desc icon | No |
| 36 | `img/male.png` | `img/male.png` | Required | Gender icon | No |
| 37 | `img/female.png` | `img/female.png` | Required | Gender icon | No |
| 38 | `img/unisex.png` | `img/unisex.png` | Required | Gender icon | No |
| 39 | `img/man.png` | `img/man.png` | Required | Testimonial avatar | No |
| 40 | `img/progress_spinner.gif` | `img/progress_spinner.gif` | Required | Loading indicator | No |
| 41 | `img/spinner_blue.gif` | `img/spinner_blue.gif` | Required | Loading indicator | No |
| 42 | `img/like.png` | `img/like.png` | Required | CSS reference | No |
| 43 | `img/about.jpg` | `img/about.jpg` | Required | About image | No |
| 44 | `img/bg.png` | `img/bg.png` | Required | CSS background | No |
| 45 | `img/bg2.png` | `img/bg2.png` | Required | CSS background | No |
| 46 | `img/bg3.png` | `img/bg3.png` | Required | CSS background | No |
| 47 | `img/fb.png` | `img/fb.png` | Required | Social icon | No |
| 48 | `img/linkedin.png` | `img/linkedin.png` | Required | Social icon | No |
| 49 | `img/twitter.png` | `img/twitter.png` | Required | Social icon | No |
| 50 | `img/Female_icon.png` | `img/Female_icon.png` | Optional | Upload anyway — no harm | No |
| 51 | `img/calendar.png` | `img/calendar.png` | Optional | Upload anyway — no harm | No |
| 52 | `img/rupee.png` | `img/rupee.png` | Optional | Upload anyway — no harm | No |
| 53 | `img/amenities/ac.svg` | `img/amenities/ac.svg` | Required | Amenity icon | No |
| 54 | `img/amenities/bed.svg` | `img/amenities/bed.svg` | Required | Amenity icon | No |
| 55 | `img/amenities/cctv.svg` | `img/amenities/cctv.svg` | Required | Amenity icon | No |
| 56 | `img/amenities/dining.svg` | `img/amenities/dining.svg` | Required | Amenity icon | No |
| 57 | `img/amenities/fireext.svg` | `img/amenities/fireext.svg` | Required | Amenity icon | No |
| 58 | `img/amenities/geyser.svg` | `img/amenities/geyser.svg` | Required | Amenity icon | No |
| 59 | `img/amenities/lift.svg` | `img/amenities/lift.svg` | Required | Amenity icon | No |
| 60 | `img/amenities/parking.svg` | `img/amenities/parking.svg` | Required | Amenity icon | No |
| 61 | `img/amenities/powerbackup.svg` | `img/amenities/powerbackup.svg` | Required | Amenity icon | No |
| 62 | `img/amenities/rowater.svg` | `img/amenities/rowater.svg` | Required | Amenity icon | No |
| 63 | `img/amenities/tv.svg` | `img/amenities/tv.svg` | Required | Amenity icon | No |
| 64 | `img/amenities/washingmachine.svg` | `img/amenities/washingmachine.svg` | Required | Amenity icon | No |
| 65 | `img/amenities/wifi.svg` | `img/amenities/wifi.svg` | Required | Amenity icon | No |
| 66 | `img/properties/1/` (3 jpg files) | `img/properties/1/` | Required | Property photos (glob()) | No |
| 67 | `img/properties/2/ ... 37/` | `img/properties/2/ ... 37/` | Required | 37 dirs, 42 total files | No |

> IMPORTANT: PHP uses glob("img/properties/{id}/*") — entire img/properties/ tree must be uploaded.

---

### Expected Remote Directory Tree

```
htdocs/
├── .htaccess
├── favicon.ico
├── index.php
├── property_list.php
├── property_detail.php
├── dashboard.php
├── logout.php
├── includes/
│   ├── database_connect.php  ← UPDATED CREDENTIALS
│   ├── head_links.php
│   ├── header.php
│   ├── footer.php
│   ├── login_modal.php
│   └── signup_modal.php
├── api/
│   ├── login_submit.php
│   ├── signup_submit.php
│   ├── toggle_interested.php
│   └── fetch_properties.php
├── css/
│   ├── bootstrap.min.css
│   ├── common.css
│   ├── home.css
│   ├── property_list.css
│   ├── property_detail.css
│   └── dashboard.css
├── js/
│   ├── jquery.js
│   ├── bootstrap.min.js
│   ├── common.js
│   └── react-components/
│       └── property_list.bundle.js
└── img/
    ├── logo.png, delhi.png, mumbai.png, bangalore.png, hyderabad.png
    ├── filter.png, asc.png, desc.png
    ├── male.png, female.png, unisex.png, man.png
    ├── about.jpg, bg.png, bg2.png, bg3.png
    ├── progress_spinner.gif, spinner_blue.gif, like.png
    ├── fb.png, linkedin.png, twitter.png
    ├── Female_icon.png, calendar.png, rupee.png
    ├── amenities/
    │   └── ac.svg, bed.svg, cctv.svg, dining.svg, fireext.svg,
    │       geyser.svg, lift.svg, parking.svg, powerbackup.svg,
    │       rowater.svg, tv.svg, washingmachine.svg, wifi.svg
    └── properties/
        ├── 1/ (3 jpg)
        ├── 2/ ... 37/ (42 total jpg files)
```

### Folder Names That MUST Be Preserved

| Folder | Reason |
|---|---|
| `includes/` | Hardcoded in `require "includes/database_connect.php"` etc. |
| `api/` | Hardcoded in `common.js`: `"api/login_submit.php"`, `"api/toggle_interested.php"` |
| `css/` | Hardcoded in PHP: `<link href="css/home.css">` etc. |
| `js/` | Hardcoded in PHP: `<script src="js/jquery.js">` |
| `js/react-components/` | Hardcoded in PHP: `<script src="js/react-components/property_list.bundle.js">` |
| `img/` | Hardcoded in all `src="img/..."` attributes |
| `img/amenities/` | Dynamic PHP: `"img/amenities/" . $amenity['icon'] . ".svg"` |
| `img/properties/` | Dynamic PHP: `glob("img/properties/" . $id . "/*")` |

---

## Task 3 — File Classification

| File/Folder | Classification | Impact if Missing on Server |
|---|---|---|
| `index.php` | Required at runtime | Home page 404 — site inaccessible |
| `property_list.php` | Required at runtime | City listing 404 |
| `property_detail.php` | Required at runtime | Property detail 404 |
| `dashboard.php` | Required at runtime | Dashboard 404 |
| `logout.php` | Required at runtime | Logout breaks, session stuck |
| `.htaccess` | Required at runtime | URL rewrites broken |
| `favicon.ico` | Required at runtime | Console 404 (no visual breakage) |
| `includes/database_connect.php` | Required at runtime | ALL pages crash with fatal PHP error |
| `includes/head_links.php` | Required at runtime | No Bootstrap CSS — site unstyled |
| `includes/header.php` | Required at runtime | No navigation |
| `includes/footer.php` | Required at runtime | jQuery/Bootstrap JS missing — all modals/XHR broken |
| `includes/login_modal.php` | Required at runtime | Login impossible |
| `includes/signup_modal.php` | Required at runtime | Signup impossible |
| `api/login_submit.php` | Required at runtime | Login returns 404 |
| `api/signup_submit.php` | Required at runtime | Signup returns 404 |
| `api/toggle_interested.php` | Required at runtime | Heart toggle broken |
| `api/fetch_properties.php` | Required at runtime | React component cannot load data |
| `css/bootstrap.min.css` | Required at runtime | All layout/styling broken |
| `css/common.css` | Required at runtime | Shared styles missing |
| `css/home.css` | Required at runtime | Home page unstyled |
| `css/property_list.css` | Required at runtime | Property list unstyled |
| `css/property_detail.css` | Required at runtime | Property detail unstyled |
| `css/dashboard.css` | Required at runtime | Dashboard unstyled |
| `js/jquery.js` | Required at runtime | Bootstrap modals broken |
| `js/bootstrap.min.js` | Required at runtime | Bootstrap modals broken |
| `js/common.js` | Required at runtime | All XHR calls broken |
| `js/react-components/property_list.bundle.js` | Required at runtime | Filter/sort UI missing |
| `img/logo.png` | Required at runtime | Navbar logo broken |
| `img/delhi.png` etc. | Required at runtime | City card images broken |
| `img/male.png, female.png, unisex.png` | Required at runtime | Gender icons missing |
| `img/filter.png, asc.png, desc.png` | Required at runtime | Sort/filter icons missing |
| `img/man.png` | Required at runtime | Testimonial avatar missing |
| `img/amenities/*.svg` (13 files) | Required at runtime | Amenity icons missing |
| `img/properties/*/` (37 dirs, 42 files) | Required at runtime | All property photos missing |
| `img/bg*.png` | Required at runtime | CSS background missing |
| `img/progress_spinner.gif` | Required at runtime | Loading spinner missing |
| `img/spinner_blue.gif` | Required at runtime | Spinner missing |
| `img/like.png` | Required at runtime | CSS reference |
| `img/about.jpg` | Required at runtime | About image missing |
| `img/fb.png, linkedin.png, twitter.png` | Required at runtime | Social icons missing |
| `img/Female_icon.png, calendar.png, rupee.png` | Possibly unused — upload anyway | No impact |
| `src/PropertyList.jsx` | Build/Dev only | Not needed on server |
| `package.json` | Build/Dev only | Not needed on server |
| `package-lock.json` | Build/Dev only | Not needed on server |
| `node_modules/` | Build/Dev only — 20 MB | NEVER upload |
| `pglife_schema.sql` | Assignment deliverable | Import via phpMyAdmin, not served |
| `dummy_data.sql` | Assignment deliverable | Import via phpMyAdmin, not served |
| `deliverables/pglife_schema.sql` | Assignment deliverable | Not served |
| `deliverables/project-explanation.md` | Assignment deliverable | Not served |
| `deliverables/project-explanation.pdf` | Assignment deliverable | Not served |
| `deliverables/screenshots/` | Assignment deliverable | Not served |
| `README.md` | Dev documentation | Not needed on server |
| `VID20261011135156.mp4` | Assignment deliverable | 52 MB — NEVER upload (bandwidth/storage) |
| `.gitignore` | Dev only | Not needed on server |
| `.git/` | Dev only | NEVER upload — exposes source history |
| `.vscode/` | Dev only | Not needed on server |
| `.backup_audit/` | Dev/Audit only | Not needed on server |
| `dashboard.html` | Unnecessary — static prototype | Superseded by dashboard.php |
| `index.html` | Unnecessary — DANGER on server | Apache serves .html before .php — NEVER upload |
| `index.html.bak` | Unnecessary | Backup |
| `index.html.bak2` | Unnecessary | Backup |
| `property_detail.html` | Unnecessary | Superseded by .php |
| `property_list.html` | Unnecessary | Superseded by .php |
| `css/home.css.bak` | Unnecessary | Backup |
| `css/home.css.bak2` | Unnecessary | Backup |
| `db_test.php` | SENSITIVE — do NOT upload | Exposes DB table names publicly |

---

## Task 4 — Verify Current Uploaded Website

**The live InfinityFree URL was not found in any project configuration file.**

Please provide your live URL (e.g., `https://yoursite.rf.gd/` or `https://yoursite.42web.io/`) so I can:
- Check for 404 errors on assets
- Test PHP error outputs
- Verify AJAX API responses (login, signup, toggle interested)
- Confirm database connectivity
- Identify any missing files on the remote server

---

## Task 5 — Safe Final Upload Plan

### Pre-Upload Checklist

**Do these BEFORE uploading any file:**

- [ ] Step 1: Update `includes/database_connect.php` locally:
  - `$username = "if0_43234840"`
  - `$password = "YOUR_INFINITYFREE_DB_PASSWORD"`
  - `$database = "if0_43234840_pglife"`
  - Do NOT commit this to Git

- [ ] Step 2: Import the database via phpMyAdmin:
  - Login to InfinityFree control panel → phpMyAdmin
  - Select database `if0_43234840_pglife`
  - Import → choose `pglife_schema.sql`
  - (This inserts all tables + seed data in one step)

### Upload Strategy

- Root PHP files: upload **individually** (7 files)
- `includes/`, `api/`, `css/`, `js/`: upload as **folders** (small, few files each)
- `img/`: **ZIP and extract** (41 MB, 37 subdirectories)

### Step A — Root Files

Open `htdocs/` in InfinityFree File Manager and upload:
- `index.php`
- `property_list.php`
- `property_detail.php`
- `dashboard.php`
- `logout.php`
- `favicon.ico`
- `.htaccess` (enable hidden files in File Manager to see it)

**DO NOT upload:** `db_test.php`, `index.html`, `index.html.bak`, `index.html.bak2`, `property_list.html`, `property_detail.html`, `dashboard.html`

### Step B — `includes/` Folder

1. Create folder `htdocs/includes/`
2. Upload all 6 files (with updated `database_connect.php`)

### Step C — `api/` Folder

1. Create folder `htdocs/api/`
2. Upload all 4 files

### Step D — `css/` Folder

1. Create folder `htdocs/css/`
2. Upload 6 CSS files
3. Do NOT upload: `home.css.bak`, `home.css.bak2`

### Step E — `js/` Folder

1. Create folder `htdocs/js/`
2. Upload: `jquery.js`, `bootstrap.min.js`, `common.js`
3. Create subfolder `htdocs/js/react-components/`
4. Upload: `property_list.bundle.js`

### Step F — `img/` Folder (ZIP method)

**On your local machine:**
```bash
cd /opt/lampp/htdocs/PGLife
zip -r img_upload.zip img/
```

In InfinityFree File Manager:
1. Navigate to `htdocs/`
2. Upload `img_upload.zip`
3. Click Extract on the ZIP file
4. Verify `img/` and `img/properties/` exist
5. Delete `img_upload.zip` from the server

### Post-Upload Verification Checklist

- [ ] Home page loads with city images
- [ ] Click a city — property list loads
- [ ] Click a property — detail page with amenity icons loads
- [ ] Heart icon clicked — login modal appears (if not logged in)
- [ ] Sign up — success alert, page reloads
- [ ] Login — navbar shows "Hi, [Name]" and Dashboard link
- [ ] Dashboard — shows interested properties
- [ ] Filter/sort on property list — React component updates results
- [ ] Logout — redirected to home

---

## Cleanup Candidates (Local Only — Do NOT Delete Without Approval)

| File | Classification | Safe to Delete Locally? | Notes |
|---|---|---|---|
| `index.html` | Static prototype | Yes | Never upload to server |
| `index.html.bak` | Backup | Yes | In .gitignore |
| `index.html.bak2` | Backup | Yes | In .gitignore |
| `property_list.html` | Static prototype | Yes | Superseded |
| `property_detail.html` | Static prototype | Yes | Superseded |
| `dashboard.html` | Static prototype | Yes | Superseded |
| `css/home.css.bak` | Backup | Yes | In .gitignore |
| `css/home.css.bak2` | Backup | Yes | In .gitignore |
| `db_test.php` | Debug tool | Yes | In .gitignore — never upload |
| `.backup_audit/` | Audit artifacts | Yes | In .gitignore |
| `VID20261011135156.mp4` | Demo video | Only if backed up elsewhere | 52 MB, in .gitignore |

**Do NOT delete locally:**
- `src/PropertyList.jsx` — needed to rebuild the React bundle
- `package.json / package-lock.json` — needed for `npm run build`
- `node_modules/` — needed for builds
- `pglife_schema.sql / dummy_data.sql` — assignment deliverables
- `deliverables/` — assignment submission folder
- `README.md` — project documentation
- `.git/` — version history

---

## Critical Pre-Flight Warnings

**`index.html` must NOT be uploaded.** Apache serves `index.html` before `index.php` — visitors will see the old static HTML prototype instead of the PHP home page.

**`db_test.php` must NOT be uploaded** — exposes database table names to anyone at `/db_test.php`.

**`node_modules/` must NEVER be uploaded** — 20 MB of dev tools with no runtime use.

**`.git/` must NEVER be uploaded** — exposes your complete source code history publicly.

**Update `database_connect.php` credentials BEFORE uploading** — otherwise the entire site will crash with a fatal PHP error on first load.
