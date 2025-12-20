# FreeScienceProject.com - AI Coding Agent Instructions

## Project Architecture Overview

This is a **modernized educational website** providing K-12 science experiments, transformed from mid-2000s table-based design to modern PHP/CSS/JS architecture. The project emphasizes **unified components**, **SEO/GEO optimization**, and **cross-browser compatibility**.

## Critical Architectural Patterns

### 1. Unified Header/Footer System (MANDATORY)
**All pages MUST use the centralized include system:**

```php
<?php
// Define page-specific variables BEFORE including header
$page_title = "Your Page Title";
$page_description = "Meta description";
$canonical_url = "https://freescienceproject.com/page.php";
$breadcrumbs = [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Category', 'url' => '/category.php'],
    ['name' => 'Current Page', 'url' => '']
];

include __DIR__ . '/includes/header.php';
?>
<!-- Page content -->
<?php include __DIR__ . '/includes/footer.php'; ?>
```

**Key Files:**
- `/includes/header.php` - Main site header with dynamic SEO/Schema.org
- `/includes/footer.php` - Site footer with scripts and links
- `/includes/project-header.php` - For individual project pages
- `/includes/project-footer.php` - For individual project pages

### 2. No Emojis Policy (STRICT RULE)
**NEVER use emojis anywhere in code** - they break compatibility with older browsers.

**Instead use:**
- CSS-generated icons: `.grade-icon.primary::before { content: 'K-4'; }`
- HTML entities: `&hearts;` instead of ❤️
- Pure CSS graphics with gradients and shapes

### 3. Schema.org Integration Pattern
Every page implements structured data via PHP variables:

```php
$schema_data = json_encode([
    "@context" => "https://schema.org",
    "@type" => "Course",
    "name" => $page_title,
    "provider" => ["@type" => "Organization", "name" => "Free Science Project"]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
```

## Development Workflows

### File Conversion Process (.htm → .php)
1. Convert HTML to PHP with includes system
2. Add dynamic variables for SEO
3. Update .htaccess with 301 redirects:
   ```apache
   RewriteRule ^old-page\.htm$ /new-page.php [R=301,L]
   ```

### CSS Architecture
Uses CSS custom properties with mobile-first responsive design:
```css
:root {
    --primary-color: #2c5aa0;
    --secondary-color: #ff6b35;
    --border-radius: 12px;
    --transition: all 0.3s ease;
}
```

**Grid Layout Rules:**
- Desktop: Force 4 columns for grade levels (`.grade-levels .projects-grid { grid-template-columns: repeat(4, 1fr); }`)
- Tablet: 2 columns at 1024px breakpoint
- Mobile: Single column at 768px breakpoint

### JavaScript Module Pattern
Two main JS files with specific purposes:
- `js/seo-geo-optimization.js` - Analytics, Schema.org, voice search optimization
- `js/interactive-features.js` - User interactions, FAQ accordions, search

## Project-Specific Conventions

### Page Types & Structure
1. **Category Pages** (`primary_projects.php`, etc.): Use main header/footer
2. **Individual Projects** (`/projects/*.html`): Use project-header/footer includes
3. **Homepage** (`index.php`): Special hero section with 4-column grade grid

### Grade Level System
Four distinct grade categories with color-coded CSS classes:
- **Primary** (K-4): Green gradient (`.grade-icon.primary`)
- **Elementary** (4-6): Blue gradient (`.grade-icon.elementary`) 
- **Intermediate** (7-8): Orange gradient (`.grade-icon.intermediate`)
- **Senior** (9-12): Purple gradient (`.grade-icon.senior`)

### SEO/GEO Implementation
**Every page must include:**
- Conversational intro sections for AI understanding
- FAQ structured data for voice search
- Educational context and practical applications
- Related topics cross-referencing

## Critical Dependencies & Integration Points

### External Dependencies
- **Google Fonts**: Inter font family with system fallbacks
- **Google Analytics 4**: Integrated via footer includes
- **Schema.org**: JSON-LD structured data in every page head

### Cross-Component Communication
- **Breadcrumbs**: Generated from `$breadcrumbs` array in header
- **Dynamic Meta Tags**: Populated from page-specific PHP variables
- **CSS Grid System**: Unified responsive breakpoints across all components

## Development Commands & Debugging

### Local Development
- Use PHP built-in server: `php -S localhost:8000` (if PHP installed)
- Files serve directly via Apache/web server
- No build process required - direct file editing

### Error Patterns to Watch
1. **HTTP 500 Errors**: Usually corrupted PHP variables or incorrect include paths
2. **Missing Emojis**: Replace with CSS alternatives immediately
3. **Grid Layout Issues**: Check responsive breakpoints in `modern-styles.css`

## File Priorities for AI Agents

**Always examine first:**
1. `includes/header.php` - Understand dynamic variable system
2. `css/modern-styles.css` - Master CSS architecture and responsive rules
3. `index.php` - Reference implementation of unified system
4. `proyecto.instructions.md` - Complete project documentation

**Key Pattern Files:**
- `primary_projects.php` - Example of category page implementation
- `js/seo-geo-optimization.js` - SEO/Schema.org patterns
- `.htaccess` - URL redirects and security configurations

## Next Steps Context

**Completed**: Homepage, unified header/footer system, primary projects modernization, emoji removal, responsive grid layout
**In Progress**: Modernizing remaining category pages (elementary, intermediate, senior)
**Pending**: 76+ individual project pages in `/projects/` folder modernization