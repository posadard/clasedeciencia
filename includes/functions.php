<?php
/**
 * The Green Almanac - Helper Functions
 */

/**
 * Sanitize output for HTML
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate UTM-tracked ChemicalStore URL
 */
function chemicalstore_url($path = '', $campaign = 'general') {
    $base = rtrim(CHEMICALSTORE_URL, '/');
    $path = ltrim($path, '/');
    $url = $base . '/' . $path;
    
    $params = [
        'utm_source' => UTM_SOURCE,
        'utm_medium' => UTM_MEDIUM,
        'utm_campaign' => $campaign
    ];
    
    return $url . '?' . http_build_query($params);
}

/**
 * Format date for display
 */
function format_date($date, $format = 'F j, Y') {
    if (!$date) return '';
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Generate excerpt from body text
 */
function generate_excerpt($text, $length = 160) {
    $text = strip_tags($text);
    if (strlen($text) <= $length) return $text;
    
    $text = substr($text, 0, $length);
    $last_space = strrpos($text, ' ');
    if ($last_space !== false) {
        $text = substr($text, 0, $last_space);
    }
    return $text . '...';
}

/**
 * Parse Markdown to HTML (basic)
 */
function parse_markdown($text) {
    // Headers
    $text = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $text);
    
    // Bold
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    
    // Lists
    $text = preg_replace('/^- (.+)$/m', '<li>$1</li>', $text);
    $text = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $text);
    
    // Paragraphs
    $text = '<p>' . preg_replace('/\n\n/', '</p><p>', $text) . '</p>';
    $text = str_replace('<p><h', '<h', $text);
    $text = str_replace('</h1></p>', '</h1>', $text);
    $text = str_replace('</h2></p>', '</h2>', $text);
    $text = str_replace('</h3></p>', '</h3>', $text);
    $text = str_replace('<p><ul>', '<ul>', $text);
    $text = str_replace('</ul></p>', '</ul>', $text);
    
    return $text;
}

/**
 * Get current page number from query string
 */
function get_current_page() {
    return isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
}

/**
 * Calculate pagination offset
 */
function get_offset($page = null) {
    $page = $page ?? get_current_page();
    return ($page - 1) * POSTS_PER_PAGE;
}

/**
 * Generate pagination HTML
 */
function pagination($total_items, $current_page = null, $base_url = '') {
    $current_page = $current_page ?? get_current_page();
    $total_pages = ceil($total_items / POSTS_PER_PAGE);
    
    if ($total_pages <= 1) return '';
    
    $html = '<nav class="pagination" aria-label="Pagination">';
    $html .= '<ul>';
    
    // Previous
    if ($current_page > 1) {
        $prev_url = $base_url . '?page=' . ($current_page - 1);
        $html .= '<li><a href="' . h($prev_url) . '" rel="prev">&laquo; Previous</a></li>';
    }
    
    // Page numbers
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $current_page) {
            $html .= '<li class="active"><span>' . $i . '</span></li>';
        } else {
            $page_url = $base_url . '?page=' . $i;
            $html .= '<li><a href="' . h($page_url) . '">' . $i . '</a></li>';
        }
    }
    
    // Next
    if ($current_page < $total_pages) {
        $next_url = $base_url . '?page=' . ($current_page + 1);
        $html .= '<li><a href="' . h($next_url) . '" rel="next">Next &raquo;</a></li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Build filter URL with parameters
 */
function build_filter_url($base, $params = []) {
    if (empty($params)) return $base;
    return $base . '?' . http_build_query($params);
}

/**
 * Get active filters from query string
 */
function get_active_filters() {
    $filters = [];
    
    if (isset($_GET['section'])) {
        $filters['section'] = $_GET['section'];
    }
    if (isset($_GET['tags'])) {
        $filters['tags'] = explode(',', $_GET['tags']);
    }
    if (isset($_GET['season'])) {
        $filters['season'] = $_GET['season'];
    }
    if (isset($_GET['chemicals'])) {
        $filters['chemicals'] = explode(',', $_GET['chemicals']);
    }
    if (isset($_GET['difficulty'])) {
        $filters['difficulty'] = $_GET['difficulty'];
    }
    if (isset($_GET['format'])) {
        $filters['format'] = $_GET['format'];
    }
    if (isset($_GET['issue'])) {
        $filters['issue'] = $_GET['issue'];
    }
    
    return $filters;
}
