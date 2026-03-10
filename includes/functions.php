<?php
/**
 * The Green Almanac - Helper Functions
 */

/**
 * Sanitize output for HTML
 */
function h($string) {
    // Coerce null to empty string to avoid deprecation warnings
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format safety warning text from plain string or JSON payload.
 */
function cdc_format_safety_warning($warning) {
    if ($warning === null) {
        return '';
    }

    $raw = is_string($warning) ? trim($warning) : $warning;
    if ($raw === '') {
        return '';
    }

    $sec = null;
    if (is_array($raw)) {
        $sec = $raw;
    } elseif (is_string($raw)) {
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $sec = $decoded;
        }
    }

    if (!is_array($sec)) {
        return trim((string)$raw);
    }

    $parts = [];
    $min = isset($sec['edad_min']) && $sec['edad_min'] !== '' ? (int)$sec['edad_min'] : null;
    $max = isset($sec['edad_max']) && $sec['edad_max'] !== '' ? (int)$sec['edad_max'] : null;
    if ($min !== null && $max !== null) {
        $parts[] = 'Edad segura: ' . $min . '-' . $max . ' años';
    } elseif ($min !== null) {
        $parts[] = 'Edad segura: ' . $min . '+ años';
    } elseif ($max !== null) {
        $parts[] = 'Edad segura: ≤' . $max . ' años';
    }

    if (isset($sec['notas'])) {
        if (is_array($sec['notas'])) {
            $notas = array_values(array_filter(array_map('trim', $sec['notas']), function ($v) {
                return $v !== '';
            }));
            if (!empty($notas)) {
                $parts[] = implode('. ', $notas);
            }
        } else {
            $nota = trim((string)$sec['notas']);
            if ($nota !== '') {
                $parts[] = $nota;
            }
        }
    }

    if (empty($parts)) {
        return trim((string)$raw);
    }

    return implode(' · ', $parts);
}

/**
 * Convert relative URL to absolute site URL.
 */
function cdc_absolute_url($url) {
    $url = trim((string)$url);
    if ($url === '') {
        return '';
    }
    if (preg_match('/^https?:\/\//i', $url)) {
        return $url;
    }
    return rtrim(SITE_URL, '/') . '/' . ltrim($url, '/');
}

/**
 * Encode schema graph safely for inline JSON-LD script output.
 */
function cdc_encode_schema_json($schema) {
    $json = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($json)) {
        return '';
    }
    return str_replace('</script>', '<\\/script>', $json);
}

/**
 * Build schema.org media nodes from recursos_multimedia rows.
 */
function cdc_build_media_schema_nodes($resources, $base_url) {
    if (!is_array($resources) || empty($resources)) {
        return [];
    }

    $nodes = [];
    $base_url = (string)$base_url;

    foreach ($resources as $r) {
        if (!is_array($r) || empty($r['url'])) {
            continue;
        }

        $tipo = strtolower((string)($r['tipo'] ?? ''));
        $mime = strtolower((string)($r['mime_type'] ?? ''));
        $id_suffix = isset($r['id']) ? (string)$r['id'] : md5((string)$r['url']);

        $schema_type = 'MediaObject';
        if (strpos($tipo, 'video') !== false || strpos($mime, 'video/') === 0) {
            $schema_type = 'VideoObject';
        } elseif (
            strpos($tipo, 'image') !== false ||
            strpos($tipo, 'imagen') !== false ||
            strpos($tipo, 'foto') !== false ||
            strpos($mime, 'image/') === 0
        ) {
            $schema_type = 'ImageObject';
        }

        $content_url = cdc_absolute_url((string)$r['url']);
        $node = [
            '@type' => $schema_type,
            '@id' => rtrim($base_url, '/') . '#media-' . $id_suffix,
            'name' => !empty($r['titulo']) ? (string)$r['titulo'] : 'Recurso multimedia',
            'contentUrl' => $content_url,
            'url' => $content_url
        ];

        if (!empty($r['descripcion'])) {
            $node['description'] = (string)$r['descripcion'];
        }
        if (!empty($r['mime_type'])) {
            $node['encodingFormat'] = (string)$r['mime_type'];
        }
        if (!empty($r['width'])) {
            $node['width'] = (int)$r['width'];
        }
        if (!empty($r['height'])) {
            $node['height'] = (int)$r['height'];
        }
        if (!empty($r['duration_iso8601']) && $schema_type === 'VideoObject') {
            $node['duration'] = (string)$r['duration_iso8601'];
        }
        if (!empty($r['upload_date'])) {
            $ts = strtotime((string)$r['upload_date']);
            if ($ts) {
                $node['uploadDate'] = date('c', $ts);
            }
        }
        if (!empty($r['thumbnail_url'])) {
            $node['thumbnailUrl'] = cdc_absolute_url((string)$r['thumbnail_url']);
        }
        if (!empty($r['embed_url']) && $schema_type === 'VideoObject') {
            $node['embedUrl'] = cdc_absolute_url((string)$r['embed_url']);
        }
        if (!empty($r['in_language'])) {
            $node['inLanguage'] = (string)$r['in_language'];
        }
        if (!empty($r['license_url'])) {
            $node['license'] = cdc_absolute_url((string)$r['license_url']);
        }
        if (!empty($r['creator_name'])) {
            $node['creator'] = [
                '@type' => 'Person',
                'name' => (string)$r['creator_name']
            ];
        }
        if (!empty($r['schema_role'])) {
            $node['additionalProperty'] = [
                '@type' => 'PropertyValue',
                'name' => 'schema_role',
                'value' => (string)$r['schema_role']
            ];
        }

        $nodes[] = $node;
    }

    return $nodes;
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
 * Build URL with query parameters
 */
function build_url($path, $params = []) {
    $url = $path;
    $clean_params = array_filter($params, function($v) {
        return $v !== '' && $v !== null;
    });
    if (!empty($clean_params)) {
        $url .= '?' . http_build_query($clean_params);
    }
    return $url;
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
