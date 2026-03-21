<?php
/**
 * AI Chat Assistant Global Search Model - FIXED with Real DB Structure + SKU Search
 * Enhanced search functionality for AbanteCart with Content Tags support
 * 
 * @author posadard.com
 * @copyright Copyright (c) 2025 posadard.com
 * @license MIT License
 * @website https://posadard.com
 */

if (!defined('DIR_CORE') || IS_ADMIN) {
    header('Location: static_pages/');
}

class ModelToolGlobalSearch extends Model
{
    /**
     * registry to provide access to cart objects
     *
     * @var object Registry
     */
    public $registry;

    /**
     * array with descriptions of controller for search
     *
     * @var array
     */
    public $results_controllers = [
        'product_categories' => [
            'alias' => 'category',
            'id' => 'category_id',
            'page' => 'product/category',
            'response' => ''],
        'products' => [
            'alias' => 'product',
            'id' => 'product_id',
            'page' => 'product/product',
            'response' => ''],
        'reviews' => [
            'alias' => 'review',
            'id' => 'product_id',
            'page' => 'product/product',
            'response' => ''],
        'manufacturers' => [
            'alias' => 'brand',
            'id' => 'manufacturer_id',
            'page' => 'product/manufacturer',
            'response' => ''],
        'contents' => [
            'alias' => 'content',
            'id' => 'content_id',
            'page' => 'content/content',
            'response' => ''],
    ];

    /**
     * function returns list of accessible search categories
     *
     * @param string $keyword
     *
     * @return array
     */
    public function getSearchSources($keyword = '')
    {
        $search_categories = [];
        // limit of keyword length
        if (mb_strlen($keyword) >= 1) {
            foreach ($this->results_controllers as $k => $item) {
                if (
                    ($k == 'reviews' && $this->config->get('ai_chat_reviews'))
                    || ($k == 'manufacturers' && $this->config->get('ai_chat_brands'))
                    || ($k == 'product_categories' && $this->config->get('ai_chat_categories'))
                    || ($k == 'contents' && $this->config->get('ai_chat_pages'))
                    || ($k == 'products' && $this->config->get('ai_chat_products'))
                ) {
                    $search_categories[$k] = $item['alias'];
                }
            }
        }

        // $this->log->write(print_r($search_categories, true).' $search_categories');
        return $search_categories;
    }

    /**
     * function returns total counts of search results
     *
     * @param string $search_category
     * @param string $keyword
     *
     * @return int
     */
    public function getTotal($search_category, $keyword)
    {
        // two variants of needles for search: with and without html-entities
        $needle = $this->db->escape(mb_strtolower(htmlentities($keyword, ENT_QUOTES)), true);
        $needle2 = $this->db->escape(mb_strtolower($keyword), true);

        $language_id = (int) $this->config->get('storefront_language_id');

        $all_languages = $this->language->getActiveLanguages();
        $current_store_id = !isset($this->session->data['current_store_id']) ? 0 : $this->session->data['current_store_id'];
        $search_languages = [];
        if ($this->config->get('ai_chat_all_lang')) {
            foreach ($all_languages as $l) {
                $search_languages[] = (int) $l['language_id'];
            }
        } else {
            $search_languages[] = (int) $language_id;
        }

        $output = 0;

        switch ($search_category) {
            case 'product_categories':
                $sql = 'SELECT count(*) as total
						FROM ' . $this->db->table('category_descriptions') . " c
						WHERE (LOWER(c.name) like '%" . $needle . "%'
								OR LOWER(c.name) like '%" . $needle2 . "%' )
						AND c.language_id IN (" . implode(',', $search_languages) . ');';
                try {
                    $result = $this->db->query($sql);
                    $output = (int)$result->row['total'];
                } catch (Exception $e) {
                    if ($this->config->get('ai_chat_debug_logging')) {
                        error_log('AI Chat Categories Count Error: ' . $e->getMessage());
                    }
                    $output = 0;
                }
                break;

            case 'products':
                // ✅ MODIFICADO: Incluir búsqueda en SKU para conteo
                $sql = 'SELECT a.product_id
						FROM ' . $this->db->table('products') . ' a
						LEFT JOIN ' . $this->db->table('product_descriptions') . ' b
							ON (b.product_id = a.product_id AND b.language_id IN (' . implode(',', $search_languages) . "))
						WHERE (
                            LOWER(a.model) like '%" . $needle . "%' 
                            OR LOWER(a.sku) like '%" . $needle . "%'
                            OR LOWER(a.model) like '%" . $needle2 . "%' 
                            OR LOWER(a.sku) like '%" . $needle2 . "%'
                        )";

                // slower
                if ($this->config->get('ai_chat_pdesc')) {
                    $sql .= " OR LOWER(b.description) like '%" . $needle . "%' OR LOWER(b.description) like '%" . $needle2 . "%'";
                }

                $sql .= ' UNION
						SELECT product_id
						FROM ' . $this->db->table('product_descriptions') . " pd1
						WHERE ( LOWER(pd1.name) like '%" . $needle . "%' OR LOWER(pd1.name) like '%" . $needle2 . "%' )
							AND pd1.language_id	IN (" . implode(',', $search_languages) . ')';

                $sql .= ' UNION
						SELECT DISTINCT a.product_id
						FROM ' . $this->db->table('product_option_value_descriptions') . ' a
						LEFT JOIN ' . $this->db->table('product_descriptions') . ' b
							ON (b.product_id = a.product_id AND b.language_id	IN (' . implode(',', $search_languages) . "))
						WHERE ( LOWER(a.name) like '%" . $needle . "%' OR LOWER(a.name) like '%" . $needle2 . "%' )
							AND a.language_id IN (" . implode(',', $search_languages) . ')';

                if ($this->config->get('ai_chat_ptags')) {
                    $sql .= ' UNION
						SELECT DISTINCT a.product_id
						FROM ' . $this->db->table('product_tags') . ' a
						LEFT JOIN ' . $this->db->table('product_descriptions') . ' b
							ON (b.product_id = a.product_id AND b.language_id	IN (' . implode(',', $search_languages) . "))
						WHERE ( LOWER(a.tag) like '%" . $needle . "%' OR LOWER(a.tag) like '%" . $needle2 . "%' )
							AND a.language_id = " . $language_id;
                }

                try {
                    $result = $this->db->query($sql);
                    if ($result->num_rows) {
                        $unique_products = [];
                        foreach ($result->rows as $row) {
                            $unique_products[$row['product_id']] = 1;
                        }
                        $output = count($unique_products);
                    } else {
                        $output = 0;
                    }
                } catch (Exception $e) {
                    if ($this->config->get('ai_chat_debug_logging')) {
                        error_log('AI Chat Products Count Error: ' . $e->getMessage());
                    }
                    $output = 0;
                }
                break;

            case 'reviews':
                if ($this->config->get('ai_chat_reviews')) {
                    $sql = 'SELECT COUNT(DISTINCT r.review_id) as total
							FROM ' . $this->db->table('reviews') . ' r
							LEFT JOIN ' . $this->db->table('products') . ' p ON (p.product_id = r.product_id)
							WHERE p.status = 1 AND r.status = 1 AND (
								LOWER(r.text) LIKE \'%' . $needle . '%\'
								OR LOWER(r.author) LIKE \'%' . $needle . '%\'';
                    
                    if ($needle != $needle2) {
                        $sql .= ' OR LOWER(r.text) LIKE \'%' . $needle2 . '%\'
								 OR LOWER(r.author) LIKE \'%' . $needle2 . '%\'';
                    }
                    $sql .= ')';
                    
                    try {
                        $result = $this->db->query($sql);
                        $output = (int)$result->row['total'];
                    } catch (Exception $e) {
                        if ($this->config->get('ai_chat_debug_logging')) {
                            error_log('AI Chat Reviews Count Error: ' . $e->getMessage());
                        }
                        $output = 0;
                    }
                } else {
                    $output = 0;
                }
                break;

            case 'manufacturers':
                $sql = 'SELECT count(*) as total
						FROM ' . $this->db->table('manufacturers') . "
						WHERE (LOWER(name) like '%" . $needle . "%' OR LOWER(name) like '%" . $needle2 . "%')";

                try {
                    $result = $this->db->query($sql);
                    $output = (int)$result->row['total'];
                } catch (Exception $e) {
                    if ($this->config->get('ai_chat_debug_logging')) {
                        error_log('AI Chat Manufacturers Count Error: ' . $e->getMessage());
                    }
                    $output = 0;
                }
                break;

            case 'contents':
                // FIXED: Use correct field names and include content_tags
                try {
                    // Count from content_descriptions using CORRECT field names
                    $sql1 = 'SELECT COUNT(DISTINCT c.content_id) as total
							 FROM ' . $this->db->table('contents') . ' c
							 INNER JOIN ' . $this->db->table('content_descriptions') . ' cd
								 ON (c.content_id = cd.content_id AND cd.language_id IN (' . implode(',', $search_languages) . '))
							 WHERE c.status = 1 AND (
								 LOWER(cd.title) LIKE \'%' . $needle . '%\'
								 OR LOWER(cd.description) LIKE \'%' . $needle . '%\'
								 OR LOWER(cd.meta_keywords) LIKE \'%' . $needle . '%\'
								 OR LOWER(cd.meta_description) LIKE \'%' . $needle . '%\'
								 OR LOWER(cd.content) LIKE \'%' . $needle . '%\'';
                    
                    if ($needle != $needle2) {
                        $sql1 .= ' OR LOWER(cd.title) LIKE \'%' . $needle2 . '%\'
								  OR LOWER(cd.description) LIKE \'%' . $needle2 . '%\'
								  OR LOWER(cd.meta_keywords) LIKE \'%' . $needle2 . '%\'
								  OR LOWER(cd.meta_description) LIKE \'%' . $needle2 . '%\'
								  OR LOWER(cd.content) LIKE \'%' . $needle2 . '%\'';
                    }
                    $sql1 .= ')';
                    
                    // Count from content_tags
                    $sql2 = 'SELECT COUNT(DISTINCT c.content_id) as total
							 FROM ' . $this->db->table('contents') . ' c
							 INNER JOIN ' . $this->db->table('content_tags') . ' ct
								 ON (c.content_id = ct.content_id AND ct.language_id IN (' . implode(',', $search_languages) . '))
							 WHERE c.status = 1 AND (
								 LOWER(ct.tag) LIKE \'%' . $needle . '%\'';
                    
                    if ($needle != $needle2) {
                        $sql2 .= ' OR LOWER(ct.tag) LIKE \'%' . $needle2 . '%\'';
                    }
                    $sql2 .= ')';
                    
                    // Use UNION to get exact count without duplicates
                    $sqlUnion = 'SELECT DISTINCT c.content_id
								 FROM ' . $this->db->table('contents') . ' c
								 INNER JOIN ' . $this->db->table('content_descriptions') . ' cd
									 ON (c.content_id = cd.content_id AND cd.language_id IN (' . implode(',', $search_languages) . '))
								 WHERE c.status = 1 AND (
									 LOWER(cd.title) LIKE \'%' . $needle . '%\'
									 OR LOWER(cd.description) LIKE \'%' . $needle . '%\'
									 OR LOWER(cd.meta_keywords) LIKE \'%' . $needle . '%\'
									 OR LOWER(cd.meta_description) LIKE \'%' . $needle . '%\'
									 OR LOWER(cd.content) LIKE \'%' . $needle . '%\'';
                    
                    if ($needle != $needle2) {
                        $sqlUnion .= ' OR LOWER(cd.title) LIKE \'%' . $needle2 . '%\'
									  OR LOWER(cd.description) LIKE \'%' . $needle2 . '%\'
									  OR LOWER(cd.meta_keywords) LIKE \'%' . $needle2 . '%\'
									  OR LOWER(cd.meta_description) LIKE \'%' . $needle2 . '%\'
									  OR LOWER(cd.content) LIKE \'%' . $needle2 . '%\'';
                    }
                    $sqlUnion .= ')
                    
                    UNION
                    
                    SELECT DISTINCT c.content_id
                    FROM ' . $this->db->table('contents') . ' c
                    INNER JOIN ' . $this->db->table('content_tags') . ' ct
                        ON (c.content_id = ct.content_id AND ct.language_id IN (' . implode(',', $search_languages) . '))
                    WHERE c.status = 1 AND (
                        LOWER(ct.tag) LIKE \'%' . $needle . '%\'';
                    
                    if ($needle != $needle2) {
                        $sqlUnion .= ' OR LOWER(ct.tag) LIKE \'%' . $needle2 . '%\'';
                    }
                    $sqlUnion .= ')';
                    
                    $resultUnion = $this->db->query($sqlUnion);
                    $output = $resultUnion->num_rows;
                    
                    // Debug logging
                    if ($this->config->get('ai_chat_debug_logging')) {
                        error_log("AI Chat Content Count: Total=$output for keyword='$keyword'");
                        error_log("AI Chat Content Count SQL: " . $sqlUnion);
                    }
                    
                } catch (Exception $e) {
                    if ($this->config->get('ai_chat_debug_logging')) {
                        error_log('AI Chat Content Count Error: ' . $e->getMessage());
                        error_log('AI Chat Content Count SQL: ' . (isset($sqlUnion) ? $sqlUnion : 'SQL not set'));
                    }
                    $output = 0;
                }
                break;
            default:
                $output = 0;
                break;
        }

        return $output;
    }

    /**
     * function returns search results in JSON format
     *
     * @param string $search_category
     * @param string $keyword
     * @param string $mode
     *
     * @return array
     */
    public function getResult($search_category, $keyword, $mode = 'listing')
    {
        $language_id = (int) $this->config->get('storefront_language_id');
        $itemslimit = (int) $this->config->get('ai_chat_items_limit');
        if ($itemslimit < 1) {
            $itemslimit = 3;
        }

        // two variants of needles for search: with and without html-entities
        $needle = $this->db->escape(mb_strtolower(htmlentities($keyword, ENT_QUOTES)));
        $needle2 = $this->db->escape(mb_strtolower($keyword));

        $page = (int) $this->request->get_or_post('page');
        $rows = (int) $this->request->get_or_post('rows');

        if ($page) {
            $page = !$page ? 1 : $page;
            $offset = ($page - 1) * $rows;
            $rows_count = $rows;
        } else {
            $offset = 0;
            $rows_count = $mode == 'listing' ? 10 : $itemslimit;
        }

        $all_languages = $this->language->getActiveLanguages();
        $current_store_id = (int) $this->session->data['current_store_id'];
        $search_languages = [];
        if ($this->config->get('ai_chat_all_lang')) {
            foreach ($all_languages as $l) {
                $search_languages[] = (int) $l['language_id'];
            }
        } else {
            $search_languages[] = (int) $language_id;
        }

        $result = [];

        switch ($search_category) {
            case $search_category == 'product_categories' and $this->config->get('ai_chat_categories'):
                $sql = 'SELECT
							a.category_id, a.status,
							c.category_id,
							c.name as title,
							c.name as text,
							c.meta_keywords as text2,
							c.meta_description as text3,
							c.description as text4
						FROM ' . $this->db->table('category_descriptions') . ' c
						LEFT JOIN ' . $this->db->table('categories') . " a
							ON (c.category_id = a.category_id)
						WHERE (LOWER(c.name) like '%" . $needle . "%'
								OR LOWER(c.name) like '%" . $needle2 . "%' )
							AND c.language_id IN (" . implode(',', $search_languages) . ')
							AND a.status=1
						LIMIT ' . $offset . ',' . $rows_count;
                try {
                    $queryResult = $this->db->query($sql);
                    $result = $queryResult->rows;
                } catch (Exception $e) {
                    if ($this->config->get('ai_chat_debug_logging')) {
                        error_log('AI Chat Categories Search Error: ' . $e->getMessage());
                    }
                    $result = [];
                }
                break;

            case $search_category == 'products' and $this->config->get('ai_chat_products'):
                // ✅ COMPLETAMENTE REESCRITO: Siempre incluir model, sku, blurb, description
                $sql = 'SELECT a.product_id, a.status, b.name as title, a.model, a.sku, b.blurb, b.description, a.model as text';
                
                $sql .= ' FROM ' . $this->db->table('products') . ' a
                        LEFT JOIN ' . $this->db->table('product_descriptions') . ' b
                            ON (b.product_id = a.product_id AND a.status IN (1) AND b.language_id IN (' . implode(',', $search_languages) . "))
                        WHERE a.status=1 AND (
                            LOWER(a.model) like '%" . $needle . "%'
                            OR LOWER(a.sku) like '%" . $needle . "%'
                        )";
                if ($needle != $needle2) {
                    $sql .= " OR (
                            LOWER(a.model) like '%" . $needle2 . "%'
                            OR LOWER(a.sku) like '%" . $needle2 . "%'
                        )";
                }
                $sql .= '
                        UNION
                        SELECT pd1.product_id, pd1.language_id, pd1.name as title, a.model, a.sku, pd1.blurb, pd1.description, pd1.name as text';
                
                $sql .= ' FROM ' . $this->db->table('product_descriptions') . ' pd1
                        LEFT JOIN ' . $this->db->table('products') . " a
                        ON a.product_id = pd1.product_id
                        WHERE ( LOWER(pd1.name) like '%" . $needle . "%'
                        ";
                if ($needle != $needle2) {
                    $sql .= " OR LOWER(pd1.name) like '%" . $needle2 . "%' ";
                }

                // Solo agregar búsqueda en descripción si está habilitada
                if ($this->config->get('ai_chat_pdesc')) {
                    $sql .= " OR LOWER(pd1.description) like '%" . $needle . "%' OR LOWER(pd1.description) like '%" . $needle2 . "%'";
                }

                $sql .= ' )
                            AND a.status=1
                            AND pd1.language_id IN (' . implode(',', $search_languages) . ')
                        UNION
                        SELECT a.product_id, a.language_id, b.name as title, p.model, p.sku, b.blurb, b.description, a.name as text';
                
                $sql .= ' FROM ' . $this->db->table('product_option_descriptions') . ' a
                        LEFT JOIN ' . $this->db->table('product_descriptions') . ' b
                            ON (b.product_id = a.product_id AND b.language_id IN (' . implode(',', $search_languages) . "))
                        LEFT JOIN " . $this->db->table('products') . " p
                            ON (p.product_id = a.product_id)
                        WHERE ( LOWER(a.name) like '%" . $needle . "%'
                        ";
                if ($needle != $needle2) {
                    $sql .= " OR LOWER(a.name) like '%" . $needle2 . "%' ";
                }
                $sql .= ')
                            AND a.language_id IN (' . implode(',', $search_languages) . ')
                        UNION
                        SELECT a.product_id, a.language_id, b.name as title, p.model, p.sku, b.blurb, b.description, a.name as text';
                
                $sql .= ' FROM ' . $this->db->table('product_option_value_descriptions') . ' a
                        LEFT JOIN ' . $this->db->table('product_descriptions') . ' b
                            ON (b.product_id = a.product_id AND b.language_id IN (' . implode(',', $search_languages) . "))
                        LEFT JOIN " . $this->db->table('products') . " p
                            ON (p.product_id = a.product_id)
                        WHERE ( LOWER(a.name) like '%" . $needle . "%'
                        ";
                if ($needle != $needle2) {
                    $sql .= " OR LOWER(a.name) like '%" . $needle2 . "%' ";
                }
                $sql .= ' )
                            AND a.language_id IN (' . implode(',', $search_languages) . ')';

                if ($this->config->get('ai_chat_ptags')) {
                    $sql .= '
                        UNION
                        SELECT a.product_id, a.language_id, b.name as title, p.model, p.sku, b.blurb, b.description, a.tag as text';
                    
                    $sql .= ' FROM ' . $this->db->table('product_tags') . ' a
                        LEFT JOIN ' . $this->db->table('product_descriptions') . ' b
                            ON (b.product_id = a.product_id AND b.language_id IN (' . implode(',', $search_languages) . '))
                        LEFT JOIN ' . $this->db->table('products') . " p
                            ON (p.product_id = a.product_id)
                        WHERE ( LOWER(a.tag) like '%" . $needle . "%'
                        ";
                    if ($needle != $needle2) {
                        $sql .= " OR LOWER(a.tag) like '%" . $needle2 . "%' ";
                    }
                    $sql .= ' )
                            AND p.status=1
                            AND a.language_id IN (' . implode(',', $search_languages) . ')';
                }
                
                $sql .= ' LIMIT ' . $offset . ',' . $rows_count;

                try {
                    $queryResult = $this->db->query($sql);
                    $table = [];
                    if ($queryResult->num_rows) {
                        foreach ($queryResult->rows as $row) {
                            if (!isset($table[$row['product_id']])) {
                                $table[$row['product_id']] = $row;
                            }
                        }
                    }
                    $result = $table;
                } catch (Exception $e) {
                    if ($this->config->get('ai_chat_debug_logging')) {
                        error_log('AI Chat Products Search Error: ' . $e->getMessage());
                    }
                    $result = [];
                }
                break;

            case $search_category == 'reviews' and $this->config->get('ai_chat_reviews'):
                $sql = 'SELECT DISTINCT r.review_id, r.text, r.product_id, pd.name as title, r.author
						FROM ' . $this->db->table('reviews') . ' r
						LEFT JOIN ' . $this->db->table('product_descriptions') . ' pd
							ON (pd.product_id = r.product_id AND pd.language_id IN (' . implode(',', $search_languages) . '))
						LEFT JOIN ' . $this->db->table('products') . ' p
							ON (p.product_id = r.product_id)
						WHERE p.status = 1 AND r.status = 1 AND (
							LOWER(r.text) LIKE \'%' . $needle . '%\'
							OR LOWER(r.author) LIKE \'%' . $needle . '%\'';
                
                if ($needle != $needle2) {
                    $sql .= ' OR LOWER(r.text) LIKE \'%' . $needle2 . '%\'
							 OR LOWER(r.author) LIKE \'%' . $needle2 . '%\'';
                }
                
                $sql .= ') ORDER BY r.date_added DESC 
						 LIMIT ' . (int)$offset . ',' . (int)$rows_count;
                
                try {
                    $queryResult = $this->db->query($sql);
                    $result = $queryResult->rows;
                } catch (Exception $e) {
                    if ($this->config->get('ai_chat_debug_logging')) {
                        error_log('AI Chat Reviews Search Error: ' . $e->getMessage());
                    }
                    $result = [];
                }
                break;

            case $search_category == 'manufacturers' and $this->config->get('ai_chat_brands'):
                $sql = 'SELECT manufacturer_id, `name` as text, `name` as title
						FROM ' . $this->db->table('manufacturers') . "
						WHERE (LOWER(name) like '%" . $needle . "%' OR LOWER(name) like '%" . $needle2 . "%' )
						LIMIT " . $offset . ',' . $rows_count;
                try {
                    $queryResult = $this->db->query($sql);
                    $result = $queryResult->rows;
                } catch (Exception $e) {
                    if ($this->config->get('ai_chat_debug_logging')) {
                        error_log('AI Chat Manufacturers Search Error: ' . $e->getMessage());
                    }
                    $result = [];
                }
                break;

            case $search_category == 'contents' and $this->config->get('ai_chat_pages'):
                // FIXED: Use correct field names and include content_tags search with UNION
                $sql = 'SELECT DISTINCT c.content_id, c.status, cd.title as title, cd.title as text,
                               cd.description, cd.content, "description" as source_type
                        FROM ' . $this->db->table('contents') . ' c
                        INNER JOIN ' . $this->db->table('content_descriptions') . ' cd
                            ON (c.content_id = cd.content_id AND cd.language_id IN (' . implode(',', $search_languages) . '))
                        WHERE c.status = 1 AND (
                            LOWER(cd.title) LIKE \'%' . $needle . '%\'
                            OR LOWER(cd.description) LIKE \'%' . $needle . '%\'
                            OR LOWER(cd.meta_keywords) LIKE \'%' . $needle . '%\'
                            OR LOWER(cd.meta_description) LIKE \'%' . $needle . '%\'
                            OR LOWER(cd.content) LIKE \'%' . $needle . '%\'';
                
                if ($needle != $needle2) {
                    $sql .= ' OR LOWER(cd.title) LIKE \'%' . $needle2 . '%\'
                             OR LOWER(cd.description) LIKE \'%' . $needle2 . '%\'
                             OR LOWER(cd.meta_keywords) LIKE \'%' . $needle2 . '%\'
                             OR LOWER(cd.meta_description) LIKE \'%' . $needle2 . '%\'
                             OR LOWER(cd.content) LIKE \'%' . $needle2 . '%\'';
                }
                
                $sql .= ')
                
                UNION
                
                SELECT DISTINCT c.content_id, c.status, cd.title as title, 
                               CONCAT("Tagged as: ", ct.tag) as text,
                               cd.description, cd.content, "tag" as source_type
                FROM ' . $this->db->table('contents') . ' c
                INNER JOIN ' . $this->db->table('content_descriptions') . ' cd
                    ON (c.content_id = cd.content_id AND cd.language_id IN (' . implode(',', $search_languages) . '))
                INNER JOIN ' . $this->db->table('content_tags') . ' ct
                    ON (c.content_id = ct.content_id AND ct.language_id IN (' . implode(',', $search_languages) . '))
                WHERE c.status = 1 AND (
                    LOWER(ct.tag) LIKE \'%' . $needle . '%\'';
                
                if ($needle != $needle2) {
                    $sql .= ' OR LOWER(ct.tag) LIKE \'%' . $needle2 . '%\'';
                }
                
                $sql .= ')
                
                ORDER BY title ASC 
                LIMIT ' . (int)$offset . ',' . (int)$rows_count;
                
                try {
                    $queryResult = $this->db->query($sql);
                    $result = $queryResult->rows;
                    
                    // Debug logging
                    if ($this->config->get('ai_chat_debug_logging')) {
                        error_log("AI Chat Content Search: Found " . count($result) . " results for '$keyword'");
                        error_log("AI Chat Content Search SQL: " . $sql);
                        if (count($result) > 0) {
                            error_log("AI Chat Content Search: First result - " . json_encode($result[0]));
                        }
                    }
                    
                } catch (Exception $e) {
                    if ($this->config->get('ai_chat_debug_logging')) {
                        error_log('AI Chat Content Search Error: ' . $e->getMessage());
                        error_log('AI Chat Content Search SQL: ' . $sql);
                    }
                    $result = [];
                }
                break;

            default:
                $result = [];
                break;
        }

        if ($mode == 'listing') {
            $result = $this->_prepareResponse($keyword,
                $this->results_controllers[$search_category]['page'],
                $this->results_controllers[$search_category]['id'],
                $result);
        }
        
        if (is_array($result)) {
            foreach ($result as &$row) {
                $row['controller'] = $this->results_controllers[$search_category]['page'];

                // shorten text for suggestion
                if ($mode != 'listing') {
                    if (isset($row['text'])) {
                        $dec_text = htmlentities($row['text'], ENT_QUOTES);
                        $len = mb_strlen($dec_text);
                        if ($len > 100) {
                            $ellipsis = '...';
                            $row['text'] = mb_substr($dec_text, 0, 100) . $ellipsis;
                        }
                    }
                }
            }
        }
        
        $output['result'] = $result;
        $output['search_category'] = $search_category;

        return $output;
    }

    /**
     * function prepares array with search results for json encoding
     *
     * @param string $keyword
     * @param string $rt
     * @param string|array $key_field(s)
     * @param array $table
     *
     * @return array
     */
    private function _prepareResponse($keyword = '', $rt = '', $key_field = '', $table = [])
    {
        $output = [];
        if (!$rt || !$key_field || !$keyword) {
            return [];
        }

        $tmp = [];
        $text = '';
        if ($table && is_array($table)) {
            foreach ($table as $row) {
                // let's extract  and colorize keyword in row
                foreach ($row as $key => $field) {
                    $field_decoded = htmlentities($field, ENT_QUOTES);

                    // if keyword found
                    $pos = mb_stripos($field_decoded, $keyword);
                    if (is_int($pos) && $key != 'title') {
                        $row['title'] = '<span class="search_res_title">' . strip_tags($row['title']) . '</span>';
                        $start = $pos < 50 ? 0 : ($pos - 50);
                        $keyword_len = mb_strlen($keyword);
                        $field_len = mb_strlen($field_decoded);
                        $ellipsis = ($field_len - $keyword_len > 10) ? '...' : '';
                        // before founded word
                        $text .= $ellipsis . mb_substr($field_decoded, $start, $pos);
                        // founded word
                        $len = ($field_len - ($pos + $keyword_len)) > 50 ? 50 : $field_len;
                        // after founded word
                        $text .= mb_substr($field_decoded, $pos + $keyword_len, $len) . $ellipsis;

                        $row['text'] = $text;
                        break;
                    }
                }

                // exception for extension settings
                $temp_key_field = $key_field;
                $url = $rt;

                if ($rt == 'setting/setting' && !empty($row['extension'])) {
                    $temp_key_field = $this->results_controllers['extensions']['id'];
                    if ($row['type'] == 'total') { // for order total extensions
                        $url = sprintf($this->results_controllers['extensions']['page2'], $row['extension']);
                    } else {
                        $url = $this->results_controllers['extensions']['page'];
                    }
                }

                if (is_array($temp_key_field)) {
                    foreach ($temp_key_field as $var) {
                        if (isset($row[$var])) {
                            $url .= '&' . $var . '=' . $row[$var];
                        }
                    }
                } else {
                    if (isset($row[$temp_key_field])) {
                        $url .= '&' . $temp_key_field . '=' . $row[$temp_key_field];
                    }
                }
                
                $tmp['type'] = isset($row['type']) ? $row['type'] : 'content';
                $tmp['href'] = $this->html->getSecureURL($url);
                $tmp['text'] = '<a href="' . $tmp['href'] . '" target="_blank" title="' . (isset($row['text']) ? $row['text'] : '') . '">' . (isset($row['title']) ? $row['title'] : 'Untitled') . '</a>';
                $output[] = $tmp;
            }
        } else {
            if (method_exists($this, 'language')) {
                $this->load->language('tool/global_search');
                $output[0] = ['text' => $this->language->get('no_results_message')];
            } else {
                $output[0] = ['text' => 'No results found'];
            }
        }

        return $output;
    }
}