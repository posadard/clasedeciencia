# The Green Almanac - AI Coding Agent Instructions

## AI Agent Permissions & Scope
**IMPORTANT**: Copilot is a code-writing assistant only. It does not have permission to edit, delete, or modify the project's database or any files outside the working directory. Its sole function is to assist with code generation, syntax suggestions, and example snippets.

Whenever Copilot needs to verify data, test behavior, or display debug information, it must do so only by printing messages to the Chrome Developer Console (using console.log, console.warn, or console.error). I, the user, will manually copy, paste, and execute these messages to verify results.

All deployment, database uploads, and server updates are handled manually by the user. Copilot should never attempt to connect to the live server or modify any persistent data. It operates in a local coding environment and must treat itself as a developer terminal with no execution privileges beyond console output.

## Documentation Policy
**DO NOT** create summary documents (*.md files) or documentation after every change unless explicitly requested by the user. Focus on making the code changes requested. The user will ask for documentation when needed.

## Project Overview
The Green Almanac is a chemistry-focused online magazine targeting homestead/farming communities (including Amish/Mennonite) with low-bandwidth, printable content. This is a **content-first, SEO-optimized static site** that drives traffic to ChemicalStore.com.

## Architecture & Tech Stack

### Recommended Stack (Per Specification)
- **Static Site Generator**: Eleventy, Astro, or Next.js (SSG mode)
- **Content**: Markdown/MDX with front-matter
- **Styling**: Monochrome, high-contrast theme with print CSS
- **Performance**: HTML-first, minimal JS, optimized for low bandwidth

### Directory Structure (Follow This Pattern)
```
src/
├── content/           # All content in Markdown with front-matter
│   ├── articles/      # Main content (format: howto|reference|story|recipe)
│   ├── dictionary/    # Chemical entries (slug, formula, CAS, uses)
│   ├── essentials/    # Product references linking to ChemicalStore
│   └── issues/        # Editorial groupings by season/year
├── pages/             # Route templates
│   ├── library/       # Master listing with server-side filters
│   ├── section/[slug] # Pre-filtered by section
│   ├── articles/[slug], dictionary/[slug], essentials/[slug]
│   └── safety/ → external redirect to sds.chemicalstore.com
├── components/
│   ├── SidebarFilters # GET param-based filtering UI
│   ├── Card          # Article/content cards
│   └── SEO           # JSON-LD and meta tag generation
└── lib/
    ├── filters.js    # Server-side filtering logic (see SQL pseudo-code)
    ├── seo.js        # Schema.org builders
    └── utm.js        # ChemicalStore link tracking
```

## Content Model & Front-Matter Patterns

### Article Front-Matter (Required Fields)
```yaml
type: article
title: "Restoring Rusty Tools with Washing Soda"
slug: restoring-rusty-tools-washing-soda
excerpt: "A simple, safe method using sodium carbonate." # ≤160 chars
section: home-workshop  # calendar-seasons|farming-garden|home-workshop
tags: [rust, cleaning]
season: [Spring, Fall]
chemicals: [sodium-carbonate]  # Reference to dictionary entries
format: howto  # howto|reference|story|recipe
difficulty: basic  # basic|intermediate|advanced
read_time_min: 8
author: Staff
published_at: 2025-10-06
chemicalstore_cta:
  - label: "Buy Washing Soda at ChemicalStore"
    url: "https://chemicalstore.com/...?utm_source=thegreenalmanac&utm_medium=referral&utm_campaign=article_footer"
```

### Dictionary Entry Pattern
```yaml
type: dictionary
slug: sodium-carbonate
common_names: ["washing soda", "soda ash"]
chemical_name: Sodium Carbonate
formula: Na2CO3
CAS: "497-19-8"
aliases: ["carbonate of soda"]
short_uses: "Laundry booster, descaling, degreasing."
safety_note: "Irritant; avoid eye contact; keep dry."
```

## Critical Filtering Logic

### URL Structure for Filters
All filters via GET parameters: `/library/?section=home-workshop&tags=rust,cleaning&season=Spring&chemicals=sodium-carbonate&difficulty=basic&format=howto&issue=fall-2025&page=1`

### Server-Side Filter Implementation
Use AND logic for multiple values within same parameter. Reference the SQL pseudo-code in `generalidades.txt` for exact filtering requirements:
- Multiple tags/chemicals require ALL to match (use HAVING COUNT)
- Section is single-select
- Pagination with shareable URLs

## SEO/Schema.org Requirements

### Required JSON-LD Types
- **Article** schema for all articles
- **HowTo** schema when format=howto
- **ItemList** for listing pages (/library/, /section/)
- **Product** (minimal) for essentials linking to ChemicalStore

### Essential Meta Tags
- Unique title/description per page
- rel="canonical" on articles and listings
- OpenGraph/Twitter cards for sharing
- GEO meta for local content when applicable

## External Integrations

### ChemicalStore Links (Critical)
ALL outbound product links must include UTM parameters:
```
utm_source=thegreenalmanac&utm_medium=referral&utm_campaign={context}
```

### Safety Data Sheets
Link to `https://sds.chemicalstore.com/` - **never mirror, always external link**

### Contact/Issues
Email integration to `office@chemicalstore.com` via mailto or serverless function

## Performance & Accessibility Standards

### Load Performance
- Static generation preferred (no client-side framework requirements)
- Images pre-scaled and lazy-loaded
- Minimal JavaScript footprint
- Fast loading on low bandwidth connections

### Print Optimization
**Critical**: High-quality print CSS for articles, dictionary entries, and reference tables. Target audience often prints content for offline use.

### Accessibility
- High contrast monochrome theme
- Semantic HTML structure
- Keyboard navigation support
- Screen reader optimization

## Development Workflows

### Content Creation
1. Add Markdown files to appropriate `/content/` subdirectory
2. Use exact front-matter schema (validate required fields)
3. Reference chemicals by slug (creates automatic cross-linking)
4. Include ChemicalStore CTAs with proper UTM tracking

### Filter Testing
Test filtering combinations thoroughly:
- Multiple tags + single section + season
- Chemical cross-references working
- Pagination maintaining filter state
- URLs shareable and crawlable

### SEO Validation
- Validate JSON-LD with Google's Rich Results Test
- Check sitemap generation includes all content types
- Verify canonical URLs and meta descriptions
- Test print CSS on various browsers

## Key Files to Reference
- `generalidades.txt` - Complete project specification
- Focus on the filtering SQL pseudo-code for accurate implementation
- Follow the exact front-matter schemas provided
- Maintain the suggested repository structure

## Common Pitfalls to Avoid
- Don't use client-side filtering (must be server-side for SEO)
- Don't mirror SDS content (always external links)
- Don't forget UTM parameters on ChemicalStore links
- Don't skip print CSS optimization
- Avoid heavy JavaScript frameworks (HTML-first approach)

This project prioritizes content discoverability, SEO optimization, and seamless integration with ChemicalStore's e-commerce ecosystem while serving a specific low-bandwidth, print-friendly audience.