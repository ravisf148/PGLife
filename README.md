# PGLife — Student Accommodation Portal

A full-stack student accommodation web application built using **PHP**, **MySQL**, **HTML5**, **Vanilla CSS**, **Bootstrap**, **JavaScript**, **AJAX**, and **React**.

---

## Overview

PGLife is an online platform for students and young professionals seeking paying guest (PG) accommodations across major cities in India (Delhi, Mumbai, Bengaluru, and Hyderabad). It provides dynamic property search, asynchronous multi-criteria filtering, property details with image carousels and amenities, and a personalized user dashboard with an interested/shortlist system.

---

## Technologies Used

- **Frontend:** HTML5, CSS3, Bootstrap 4, JavaScript (ES6+), FontAwesome
- **Dynamic UI / Interactive Component:** React 19 (mounted on property listing for seamless AJAX filtering and sorting)
- **Backend:** PHP 8.2 (Session management, prepared statements, bcrypt password hashing)
- **Database:** MySQL / MariaDB (InnoDB engine, foreign key constraints, unique indexing)
- **Asynchronous Communication:** Vanilla AJAX (XMLHttpRequest / Fetch API) for filter updates and interest toggling without full-page reloads
- **Build Tooling:** ESBuild for lightweight React JSX compilation to standalone browser bundle

---

## Features

1. **Homepage (`index.php`)**
   - Search bar for quick city lookups
   - Quick navigation cards for major cities (Delhi, Mumbai, Bengaluru, Hyderabad)
   - Dynamic header reflecting user authentication status

2. **Property Listing & Interactive Filtering (`property_list.php`)**
   - Server-side fallback rendering for SEO and fast initial page load
   - **React Integration:** Mounted on `#react-property-list-root`
   - **AJAX Multi-Criteria Filtering:**
     - **City Filter:** Filter by specific city or view all cities
     - **Gender Filter:** No Filter, Unisex, Male, Female
     - **Budget Filter:** No Filter, Under ₹6,000, ₹6,000–₹8,500, Above ₹8,500
   - **AJAX Sorting:** Sort properties by highest or lowest monthly rent
   - **Loading & Empty States:** Displays loading indicator during requests and a clean empty state when no PGs match criteria
   - **Interactive Heart/Interest Button:** Toggle interest state asynchronously with automatic login prompt for unauthenticated visitors

3. **Property Details (`property_detail.php`)**
   - Multi-image Bootstrap carousel
   - Breadcrumb navigation
   - Amenities categorized by Building, Common Area, Bedroom, and Washroom
   - Rating breakdown (Cleanliness, Food, Safety) and student testimonials
   - Graceful 404 handling for invalid or nonexistent property IDs

4. **User Authentication & Dashboard (`dashboard.php`)**
   - Modal-based signup with client and server validation (10-digit phone, min 6-character password, unique email check)
   - Modal-based login with secure `password_verify()`
   - Protected dashboard displaying user profile details and all currently interested properties
   - Session-managed logout (`logout.php`)

---

## Local Setup Instructions (Linux / XAMPP)

### 1. Prerequisites
- Linux with XAMPP installed at `/opt/lampp`
- PHP 8.2+ (`/opt/lampp/bin/php`)
- MySQL / MariaDB (`/opt/lampp/bin/mysql`)
- Node.js & npm (for building the React component if modified)

### 2. Project Location
Place the project directory in your XAMPP web root:
```bash
/opt/lampp/htdocs/PGLife
```

### 3. Start Apache & MySQL
```bash
sudo /opt/lampp/lampp startapache
sudo /opt/lampp/lampp startmysql
```

### 4. Database Setup
Create the `pglife` database and import the schema deliverable:
```bash
/opt/lampp/bin/mysql -u root -e "CREATE DATABASE IF NOT EXISTS pglife;"
/opt/lampp/bin/mysql -u root pglife < /opt/lampp/htdocs/PGLife/pglife_schema.sql
```
*Database configuration can be found in `includes/database_connect.php`.*

### 5. Access the Website
Open your browser and navigate to:
```
http://localhost/PGLife/index.php
```

---

## React Component Build Instructions

The compiled React bundle is pre-built and included in `js/react-components/property_list.bundle.js`, so you do not need to run a build to use the site.

If you edit the React source code in `src/PropertyList.jsx`, you can rebuild the bundle using npm:

```bash
# Install dependencies
npm install

# Build production bundle
npm run build

# Build with source maps for development
npm run build:dev

# Watch for live source changes
npm run watch
```

---

## Default Test Credentials

For quick evaluation, pre-seeded accounts are available in `pglife_schema.sql`:

- **Email:** `anuj.kalbalia@gmail.com` | **Password:** `qwerty`
- **Email:** `radhika@gmail.com` | **Password:** `qwerty`
- **Email:** `aditya@gmail.com` | **Password:** `qwerty`
- **Email:** `shadab@gmail.com` | **Password:** `qwerty`
