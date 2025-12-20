<?php
/**
 * Project Landing Page Helper Functions
 * Gets project data from the JavaScript data structure
 */

function getProjectData($projectCode) {
    // Read and parse the JavaScript data file
    $jsFilePath = __DIR__ . '/../js/science-projects-data.js';
    
    if (!file_exists($jsFilePath)) {
        error_log("Project data file not found: " . $jsFilePath);
        return null;
    }
    
    $jsContent = file_get_contents($jsFilePath);
    
    if ($jsContent === false) {
        error_log("Failed to read project data file: " . $jsFilePath);
        return null;
    }
    
    // Extract the project data using regex - handle .php, .asp, and new scienceproject URLs
    $patterns = [
        // For new scienceproject URLs
        '/{ title: "([^"]+)"[^}]*?url: "\/scienceproject\/' . preg_quote($projectCode) . '"[^}]*?link_url: "([^"]+)"[^}]*?subject: "([^"]+)"[^}]*?difficulty: "([^"]+)"[^}]*?materials: "([^"]+)"[^}]*?description: "([^"]+)"[^}]*?category: "([^"]+)"[^}]*?grade: "([^"]+)"/s',
        // For .php files (external projects) - legacy support
        '/{ title: "([^"]+)"[^}]*?url: "\/projects\/' . preg_quote($projectCode) . '\.php"[^}]*?link_url: "([^"]+)"[^}]*?subject: "([^"]+)"[^}]*?difficulty: "([^"]+)"[^}]*?materials: "([^"]+)"[^}]*?description: "([^"]+)"[^}]*?category: "([^"]+)"[^}]*?grade: "([^"]+)"/s',
        // For .asp files (external projects) - legacy support
        '/{ title: "([^"]+)"[^}]*?url: "\/projects\/' . preg_quote($projectCode) . '\.asp"[^}]*?link_url: "([^"]+)"[^}]*?subject: "([^"]+)"[^}]*?difficulty: "([^"]+)"[^}]*?materials: "([^"]+)"[^}]*?description: "([^"]+)"[^}]*?category: "([^"]+)"[^}]*?grade: "([^"]+)"/s'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $jsContent, $matches)) {
            return [
                'title' => $matches[1],
                'link_url' => $matches[2],
                'subject' => $matches[3],
                'difficulty' => $matches[4],
                'materials' => $matches[5],
                'description' => $matches[6],
                'category' => $matches[7],
                'grade' => $matches[8],
                'code' => $projectCode
            ];
        }
    }
    
    error_log("Project data not found for code: " . $projectCode);
    return null;
}

function getSubjectIcon($subject) {
    $icons = [
        'physics' => '⚡',
        'chemistry' => '⚗',
        'biology' => '♦',
        'earth-science' => '♦',
        'space' => '★',
        'engineering' => '⚙',
        'earth' => '♦'
    ];
    
    return $icons[$subject] ?? '♦';
}

function getDifficultyColor($difficulty) {
    $colors = [
        'easy' => '#28a745',
        'medium' => '#ffc107',
        'hard' => '#dc3545'
    ];
    
    return $colors[$difficulty] ?? '#6c757d';
}

function getProjectFAQ($subject, $difficulty) {
    $faqs = [
        'physics' => [
            'easy' => [
                'What materials do I need for this physics experiment?' => 'Most physics projects use common household items like batteries, wires, magnets, and everyday materials. Check the materials list for specific requirements.',
                'Is this project safe for kids?' => 'Yes, all our physics projects are designed with safety in mind. Always supervise young children and follow safety guidelines.'
            ],
            'medium' => [
                'How long does this physics project take?' => 'Most intermediate physics projects take 2-4 hours to complete, including setup and experimentation time.',
                'What physics concepts will I learn?' => 'This project teaches fundamental physics principles through hands-on experimentation and observation.'
            ],
            'hard' => [
                'Do I need advanced physics knowledge?' => 'Basic physics understanding is helpful, but our detailed instructions guide you through complex concepts step by step.',
                'Can this project be used for science fair?' => 'Absolutely! Advanced physics projects make excellent science fair entries with proper documentation and analysis.'
            ]
        ],
        'chemistry' => [
            'easy' => [
                'What chemical safety should I know?' => 'Always wear safety goggles, work in ventilated areas, and have an adult present. Use only recommended household chemicals.',
                'Are the chemicals safe?' => 'We only use safe, common household chemicals in our experiments. Always follow safety instructions carefully.'
            ],
            'medium' => [
                'What chemistry concepts does this teach?' => 'This project demonstrates important chemical reactions, properties, and processes through safe experimentation.',
                'How accurate are the results?' => 'Our chemistry experiments provide reliable, educational results when procedures are followed correctly.'
            ],
            'hard' => [
                'Do I need laboratory equipment?' => 'Most equipment can be improvised with household items, though some projects benefit from basic lab equipment.',
                'Can I modify the experiment?' => 'Advanced students can often extend experiments with additional variables, but always maintain safety protocols.'
            ]
        ],
        'biology' => [
            'easy' => [
                'How do I care for living specimens?' => 'Follow our care instructions carefully. Ensure proper environment, food, and handling for any living organisms.',
                'What if I have allergies?' => 'Check all materials and organisms used. Avoid projects involving allergens you\'re sensitive to.'
            ],
            'medium' => [
                'How long do biology experiments take?' => 'Biology projects often require observation over days or weeks. Plan accordingly for long-term studies.',
                'What biological concepts will I learn?' => 'Projects cover life processes, ecosystems, anatomy, and environmental interactions through direct observation.'
            ],
            'hard' => [
                'Do I need microscopy equipment?' => 'Some advanced projects benefit from microscopes, but many observations can be made with magnifying glasses.',
                'How do I document biological changes?' => 'Keep detailed logs with photos, measurements, and observations over time for scientific documentation.'
            ]
        ]
    ];
    
    return $faqs[$subject][$difficulty] ?? [
        'What makes this a good science project?' => 'This project combines hands-on learning with scientific principles, making abstract concepts tangible and understandable.',
        'Can I get help if I have problems?' => 'Yes! Our detailed instructions include troubleshooting tips and safety guidelines to help you succeed.'
    ];
}

function getProjectShopCategories($project) {
    // Base categories for all projects
    $baseCategories = [
        ['id' => 112, 'name' => 'Lab Supplies > Plasticware'],
        ['id' => 108, 'name' => 'Lab Supplies > Glassware'],
        ['id' => 110, 'name' => 'Lab Supplies > Magnifiers'],
        ['id' => 144, 'name' => 'Tools > Scales']
    ];
    
    // Subject-specific categories (will be added to base)
    $subjectCategories = [
        'physics' => [
            ['id' => 132, 'name' => 'Physics > Magnetism > Magnets'],
            ['id' => 87, 'name' => 'Electricity'],
            ['id' => 145, 'name' => 'Tools > Thermometers'],
            ['id' => 131, 'name' => 'Physics > Magnetism > Compass']
        ],
        'chemistry' => [
            ['id' => 75, 'name' => 'Chemicals'],
            ['id' => 116, 'name' => 'Lab Supplies > Test Tubes'],
            ['id' => 108, 'name' => 'Lab Supplies > Glassware'],
            ['id' => 117, 'name' => 'Metals']
        ],
        'biology' => [
            ['id' => 74, 'name' => 'Biology > Specimen samples'],
            ['id' => 110, 'name' => 'Lab Supplies > Magnifiers'],
            ['id' => 104, 'name' => 'KITS > Science Kits'],
            ['id' => 116, 'name' => 'Lab Supplies > Test Tubes']
        ],
        'earth-science' => [
            ['id' => 75, 'name' => 'Chemicals'],
            ['id' => 108, 'name' => 'Lab Supplies > Glassware'],
            ['id' => 144, 'name' => 'Tools > Scales'],
            ['id' => 145, 'name' => 'Tools > Thermometers']
        ],
        'earth' => [
            ['id' => 75, 'name' => 'Chemicals'],
            ['id' => 108, 'name' => 'Lab Supplies > Glassware'],
            ['id' => 144, 'name' => 'Tools > Scales'],
            ['id' => 145, 'name' => 'Tools > Thermometers']
        ],
        'space' => [
            ['id' => 110, 'name' => 'Lab Supplies > Magnifiers'],
            ['id' => 144, 'name' => 'Tools > Scales'],
            ['id' => 132, 'name' => 'Physics > Magnetism > Magnets'],
            ['id' => 104, 'name' => 'KITS > Science Kits']
        ]
    ];

    $categories = [];
    $projectTitle = strtolower($project['title']);
    
    // Special project mappings (priority)
    if (strpos($projectTitle, 'volcano') !== false) {
        $categories = [
            ['id' => 75, 'name' => 'Chemicals'],
            ['id' => 144, 'name' => 'Tools > Scales'],
            ['id' => 108, 'name' => 'Lab Supplies > Glassware'],
            ['id' => 145, 'name' => 'Tools > Thermometers']
        ];
    }
    elseif (strpos($projectTitle, 'electric') !== false || strpos($projectTitle, 'battery') !== false) {
        $categories = [
            ['id' => 87, 'name' => 'Electricity'],
            ['id' => 102, 'name' => 'Electricity > Wires'],
            ['id' => 98, 'name' => 'Electricity > Multimeters'],
            ['id' => 132, 'name' => 'Physics > Magnetism > Magnets']
        ];
    }
    elseif (strpos($projectTitle, 'magnet') !== false || strpos($projectTitle, 'compass') !== false) {
        $categories = [
            ['id' => 132, 'name' => 'Physics > Magnetism > Magnets'],
            ['id' => 131, 'name' => 'Physics > Magnetism > Compass'],
            ['id' => 87, 'name' => 'Electricity'],
            ['id' => 117, 'name' => 'Metals']
        ];
    }
    elseif (strpos($projectTitle, 'motor') !== false) {
        $categories = [
            ['id' => 97, 'name' => 'Electricity > Motors'],
            ['id' => 132, 'name' => 'Physics > Magnetism > Magnets'],
            ['id' => 87, 'name' => 'Electricity'],
            ['id' => 102, 'name' => 'Electricity > Wires']
        ];
    }
    elseif (strpos($projectTitle, 'dna') !== false) {
        $categories = [
            ['id' => 74, 'name' => 'Biology > Specimen samples'],
            ['id' => 104, 'name' => 'KITS > Science Kits'],
            ['id' => 116, 'name' => 'Lab Supplies > Test Tubes'],
            ['id' => 110, 'name' => 'Lab Supplies > Magnifiers']
        ];
    }
    elseif (strpos($projectTitle, 'solar') !== false) {
        $categories = [
            ['id' => 93, 'name' => 'Electricity > Energy'],
            ['id' => 145, 'name' => 'Tools > Thermometers'],
            ['id' => 87, 'name' => 'Electricity'],
            ['id' => 98, 'name' => 'Electricity > Multimeters']
        ];
    }
    elseif (strpos($projectTitle, 'crystal') !== false || strpos($projectTitle, 'salt') !== false) {
        $categories = [
            ['id' => 75, 'name' => 'Chemicals'],
            ['id' => 110, 'name' => 'Lab Supplies > Magnifiers'],
            ['id' => 108, 'name' => 'Lab Supplies > Glassware'],
            ['id' => 144, 'name' => 'Tools > Scales']
        ];
    }
    // Subject-based categories
    elseif (isset($subjectCategories[$project['subject']])) {
        $categories = $subjectCategories[$project['subject']];
    }
    // Grade-level defaults
    else {
        $gradeDefaults = [
            'primary' => [
                ['id' => 132, 'name' => 'Physics > Magnetism > Magnets'],
                ['id' => 112, 'name' => 'Lab Supplies > Plasticware'],
                ['id' => 104, 'name' => 'KITS > Science Kits'],
                ['id' => 110, 'name' => 'Lab Supplies > Magnifiers']
            ],
            'elementary' => [
                ['id' => 110, 'name' => 'Lab Supplies > Magnifiers'],
                ['id' => 108, 'name' => 'Lab Supplies > Glassware'],
                ['id' => 75, 'name' => 'Chemicals'],
                ['id' => 132, 'name' => 'Physics > Magnetism > Magnets']
            ],
            'intermediate' => [
                ['id' => 87, 'name' => 'Electricity'],
                ['id' => 145, 'name' => 'Tools > Thermometers'],
                ['id' => 98, 'name' => 'Electricity > Multimeters'],
                ['id' => 75, 'name' => 'Chemicals']
            ],
            'senior' => [
                ['id' => 98, 'name' => 'Electricity > Multimeters'],
                ['id' => 117, 'name' => 'Metals'],
                ['id' => 75, 'name' => 'Chemicals'],
                ['id' => 87, 'name' => 'Electricity']
            ]
        ];
        $categories = $gradeDefaults[$project['category']] ?? $gradeDefaults['primary'];
    }

    // Ensure we always have at least 4 categories
    if (count($categories) < 4) {
        // Add from base categories to reach minimum of 4
        $needed = 4 - count($categories);
        $existingIds = array_column($categories, 'id');
        
        foreach ($baseCategories as $baseCat) {
            if ($needed <= 0) break;
            if (!in_array($baseCat['id'], $existingIds)) {
                $categories[] = $baseCat;
                $needed--;
            }
        }
    }

    // Return exactly 4 categories (trim if more than 4)
    return array_slice($categories, 0, 4);
}

function generateShopWidget($categories) {
    $uniqueId = uniqid();
    $categoryButtons = '';
    
    foreach ($categories as $index => $cat) {
        $categoryButtons .= sprintf(
            '<a href="https://shop.miniscience.com/index.php?rt=product/category&path=%d" 
               class="supply-category-button" 
               target="_blank" 
               rel="noopener">
                %s
            </a>',
            $cat['id'],
            htmlspecialchars($cat['name'])
        );
    }

    return sprintf(
        '<div class="project-shop-widget">
            <h3>Related Supplies</h3>
            <div class="shop-widget-container">
                <div class="supply-categories-grid">
                    %s
                </div>
                <p class="shop-note">
                    <small>Click any category to browse related supplies on our partner store.</small>
                </p>
            </div>
        </div>',
        $categoryButtons
    );
}

?>