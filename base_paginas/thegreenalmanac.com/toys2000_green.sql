-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 18, 2025 at 04:09 PM
-- Server version: 10.5.25-MariaDB-cll-lve
-- PHP Version: 8.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `toys2000_green`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$YourHashHere', 'office@chemicalstore.com', '2025-10-06 20:39:35');

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` varchar(160) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `section_id` int(10) UNSIGNED DEFAULT NULL,
  `format` enum('howto','reference','story','recipe') DEFAULT 'howto',
  `difficulty` enum('basic','intermediate','advanced') DEFAULT 'basic',
  `read_time_min` int(11) DEFAULT NULL,
  `author` varchar(100) DEFAULT 'Staff',
  `body` mediumtext DEFAULT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` varchar(255) DEFAULT NULL,
  `canonical_url` varchar(255) DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `featured` tinyint(1) DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `issue_id` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title`, `slug`, `excerpt`, `cover_image`, `section_id`, `format`, `difficulty`, `read_time_min`, `author`, `body`, `seo_title`, `seo_description`, `canonical_url`, `status`, `featured`, `published_at`, `issue_id`, `created_at`, `updated_at`) VALUES
(1, 'Restoring Rusty Tools with Washing Soda', 'restoring-rusty-tools-washing-soda', 'A simple, safe method using sodium carbonate to remove rust from hand tools and garden equipment.', NULL, 3, 'howto', 'basic', 8, 'Staff', '<h2>Restoring Rusty Tools with Washing Soda</h2>\r\n\r\n<p>Rust is the enemy of metal tools, but you don&#39;t need harsh chemicals to remove it. Sodium carbonate (washing soda) provides a safe, effective method for electrolytic rust removal.</p>\r\n\r\n<h3>What You&#39;ll Need</h3>\r\n\r\n<ul>\r\n	<li>Washing soda (sodium carbonate)</li>\r\n	<li>Large plastic container</li>\r\n	<li>12V battery charger or power supply</li>\r\n	<li>Steel electrode (rebar or scrap steel)</li>\r\n	<li>Wire brush</li>\r\n	<li>Protective gloves and eyewear</li>\r\n</ul>\r\n\r\n<h3>The Process</h3>\r\n\r\n<ol>\r\n	<li><strong>Prepare the solution</strong>: Mix 1 tablespoon of washing soda per gallon of water in your container.</li>\r\n	<li><strong>Set up electrodes</strong>: Connect the positive lead to a steel electrode and the negative to your rusty tool.</li>\r\n	<li><strong>Submerge</strong>: Place both in the solution without touching each other.</li>\r\n	<li><strong>Run the current</strong>: Leave for 2&ndash;12 hours depending on rust severity.</li>\r\n	<li><strong>Clean and protect</strong>: Scrub with a wire brush, rinse, dry, and oil immediately.</li>\r\n</ol>\r\n\r\n<h3>Safety Notes</h3>\r\n\r\n<ul>\r\n	<li>Work in a ventilated area</li>\r\n	<li>Hydrogen gas is produced &mdash; keep away from sparks</li>\r\n	<li>Wear eye protection</li>\r\n	<li>Don&#39;t use stainless steel as the anode</li>\r\n</ul>\r\n\r\n<p>This method removes rust without damaging the underlying metal, making it perfect for restoring vintage tools and implements.</p>\r\n', NULL, NULL, NULL, 'published', 1, '2025-10-06 14:00:00', 1, '2025-10-06 18:53:22', '2025-10-08 17:09:08'),
(19, 'Natural Mouse Repellent Using Menthol Crystals', 'natural-mouse-repellent-using-menthol-crystals', 'Create a safe, natural mouse repellent using menthol crystals to deter rodents without toxic chemicals.', NULL, 3, 'howto', 'basic', 10, 'Staff', '<h1>Natural Mouse Repellent Using Menthol Crystals</h1>\r\n\r\n<p>Menthol crystals, derived from peppermint oil, provide a natural, non-toxic method to deter mice from entering your home or storage spaces. Their intense aroma overwhelms rodents&rsquo; sensitive noses, encouraging them to avoid treated areas.</p>\r\n\r\n<h2>What You&#39;ll Need</h2>\r\n\r\n<ul>\r\n	<li>Menthol crystals (100&ndash;200g)</li>\r\n	<li>Small breathable fabric pouches or cotton balls</li>\r\n	<li>Perforated glass or plastic containers</li>\r\n	<li>Scissors and string or twist ties</li>\r\n	<li>Gloves (optional)</li>\r\n	<li>Optional: Peppermint or eucalyptus essential oils</li>\r\n</ul>\r\n\r\n<h2>The Science Behind Menthol Repellents</h2>\r\n\r\n<p>Menthol crystals disrupt mice&rsquo;s olfactory system, masking food and predator scents. The strong odor triggers sensory overload, causing discomfort and avoidance. As a biodegradable compound, menthol offers an eco-friendly alternative to chemical rodenticides.</p>\r\n\r\n<h2>The Process</h2>\r\n\r\n<h3>Method 1: Menthol Sachet Repellents</h3>\r\n\r\n<ol>\r\n	<li>Place 5&ndash;10 menthol crystals in each small fabric pouch.</li>\r\n	<li>Tie securely and position sachets near corners, entry points, and storage areas.</li>\r\n	<li>Replace or refresh every 2&ndash;4 weeks as the scent fades.</li>\r\n</ol>\r\n\r\n<h3>Method 2: Cotton Ball Dispensers</h3>\r\n\r\n<ol>\r\n	<li>Crush menthol crystals into smaller pieces.</li>\r\n	<li>Place crushed crystals on cotton balls and store in perforated containers.</li>\r\n	<li>Set containers in problem areas and refresh weekly.</li>\r\n</ol>\r\n\r\n<h3>Method 3: Menthol Solution Spray</h3>\r\n\r\n<ol>\r\n	<li>Dissolve 1 tablespoon of menthol crystals per cup of rubbing alcohol.</li>\r\n	<li>Let the mixture sit for 24 hours, stirring occasionally.</li>\r\n	<li>Transfer to a spray bottle and apply along entry points and baseboards.</li>\r\n	<li>Reapply every 3&ndash;5 days for best results.</li>\r\n</ol>\r\n\r\n<h2>Strategic Placement Locations</h2>\r\n\r\n<ul>\r\n	<li>Entry points like doors, windows, and cracks</li>\r\n	<li>Under sinks and behind kitchen appliances</li>\r\n	<li>Basements, attics, and garages</li>\r\n	<li>Along walls and in corners where mice travel</li>\r\n	<li>Near pet food and garbage storage</li>\r\n</ul>\r\n\r\n<h2>Maintenance Schedule</h2>\r\n\r\n<ul>\r\n	<li><strong>Week 1:</strong> Deploy repellents throughout target areas.</li>\r\n	<li><strong>Weeks 2&ndash;3:</strong> Monitor effectiveness and increase placements if needed.</li>\r\n	<li><strong>Week 4:</strong> Refresh or replace all sachets and cotton balls.</li>\r\n	<li><strong>Monthly:</strong> Adjust placement based on observed activity.</li>\r\n</ul>\r\n\r\n<h2>Expected Results</h2>\r\n\r\n<p>Reduction in mouse activity should be noticeable within 3&ndash;7 days. Maximum effectiveness is achieved when combined with good sanitation and sealed entry points.</p>\r\n\r\n<h2>Enhancing Effectiveness</h2>\r\n\r\n<ul>\r\n	<li>Seal holes larger than &frac14; inch with steel wool or caulk.</li>\r\n	<li>Keep food sealed and areas free of crumbs.</li>\r\n	<li>Combine with peppermint essential oil for a stronger scent barrier.</li>\r\n</ul>\r\n\r\n<h2>Cost and Comparison</h2>\r\n\r\n<ul>\r\n	<li>Menthol crystals: $10&ndash;15 for 100g (enough for one home)</li>\r\n	<li>Fabric pouches: $5&ndash;10 per pack</li>\r\n	<li>Total project cost: $15&ndash;25 (vs. $20&ndash;40 for commercial repellents)</li>\r\n</ul>\r\n\r\n<h2>Troubleshooting</h2>\r\n\r\n<ul>\r\n	<li><strong>If mice remain:</strong> Increase concentration or add more placements.</li>\r\n	<li><strong>If scent is too strong:</strong> Use fewer crystals or improve ventilation.</li>\r\n	<li><strong>If scent fades quickly:</strong> Store crystals in airtight containers and refresh often in humid conditions.</li>\r\n</ul>\r\n\r\n<h2>Safety Notes</h2>\r\n\r\n<ul>\r\n	<li>Keep menthol crystals away from children and pets.</li>\r\n	<li>Avoid direct skin contact&mdash;may cause irritation.</li>\r\n	<li>Ensure ventilation in enclosed spaces.</li>\r\n	<li>Do not apply directly to food-contact surfaces.</li>\r\n</ul>\r\n\r\n<h2>Environmental Benefits</h2>\r\n\r\n<ul>\r\n	<li>Non-toxic and biodegradable.</li>\r\n	<li>Safe for pets and beneficial wildlife.</li>\r\n	<li>Reduces reliance on poison-based pest control methods.</li>\r\n</ul>\r\n\r\n<h2>Conclusion</h2>\r\n\r\n<p>Using menthol crystals as a natural mouse repellent provides an effective, safe, and eco-friendly alternative to chemical deterrents. With proper placement and upkeep, this method helps maintain a rodent-free home without harming the environment.</p>\r\n', NULL, NULL, NULL, 'published', 1, '2025-10-08 20:04:00', NULL, '2025-10-08 20:07:53', '2025-10-08 20:45:20'),
(20, 'Shower Steamers with Menthol Crystals', 'shower-steamers-with-menthol-crystals', 'Create effervescent shower tablets with menthol and essential oils for a spa-like aromatherapy and respiratory relief experience.', NULL, 3, 'howto', 'intermediate', 12, 'Staff', '<h1>Shower Steamers with Menthol Crystals</h1>\r\n\r\n<p>Shower steamers are effervescent aromatherapy tablets that release menthol vapors in hot shower steam. These vapors provide respiratory relief and a refreshing spa-like experience. This project combines simple chemistry with cosmetic formulation to create a customizable wellness product.</p>\r\n\r\n<h2>What You&#39;ll Need</h2>\r\n\r\n<ul>\r\n	<li>1 cup baking soda (sodium bicarbonate)</li>\r\n	<li>&frac12; cup citric acid</li>\r\n	<li>&frac14; cup cornstarch</li>\r\n	<li>2&ndash;4 tbsp menthol crystals (adjust for intensity)</li>\r\n	<li>1&ndash;2 tbsp carrier oil (coconut, jojoba, or almond)</li>\r\n	<li>Essential oils (optional: eucalyptus, lavender, peppermint, etc.)</li>\r\n	<li>Water in spray bottle (minimal amount)</li>\r\n	<li>Silicone molds, mixing bowl, gloves, spatula</li>\r\n	<li>Mortar and pestle or ziplock bag (to crush menthol)</li>\r\n</ul>\r\n\r\n<h2>The Process</h2>\r\n\r\n<ol>\r\n	<li><strong>Prepare workspace:</strong> Clean and dry all tools and wear gloves to handle menthol crystals safely.</li>\r\n	<li><strong>Crush menthol crystals:</strong> Break into smaller granules for even distribution.</li>\r\n	<li><strong>Mix dry ingredients:</strong> Combine baking soda, citric acid, and cornstarch until uniform.</li>\r\n	<li><strong>Add menthol crystals:</strong> Stir evenly into dry mixture.</li>\r\n	<li><strong>Add carrier oil and essential oils:</strong> Mix thoroughly to distribute aroma and moisture.</li>\r\n	<li><strong>Spray water slowly:</strong> Add 1&ndash;2 sprays at a time, mixing immediately to avoid premature fizzing. Stop when the texture clumps like damp sand.</li>\r\n	<li><strong>Pack molds:</strong> Press mixture firmly into silicone molds; smooth tops.</li>\r\n	<li><strong>Drying:</strong> Let sit undisturbed for 24&ndash;48 hours until completely hardened.</li>\r\n	<li><strong>Store:</strong> Keep in airtight containers away from humidity. Add silica gel packets if available.</li>\r\n</ol>\r\n\r\n<h2>How to Use</h2>\r\n\r\n<ol>\r\n	<li>Place one tablet on the shower floor, away from direct water flow.</li>\r\n	<li>Turn on hot water to generate steam and activate the steamer gradually.</li>\r\n	<li>Inhale deeply for 5&ndash;15 minutes for full aromatic effect.</li>\r\n</ol>\r\n\r\n<h2>Popular Variations</h2>\r\n\r\n<ul>\r\n	<li><strong>Spa Morning:</strong> Eucalyptus, lemon, and rosemary oils &ndash; energizing and refreshing.</li>\r\n	<li><strong>Evening Calm:</strong> Lavender and chamomile &ndash; for relaxation and stress relief.</li>\r\n	<li><strong>Cold &amp; Flu Fighter:</strong> Eucalyptus, peppermint, and tea tree &ndash; for congestion relief.</li>\r\n</ul>\r\n\r\n<h2>Scientific Background</h2>\r\n\r\n<p>Effervescence results from the reaction between baking soda and citric acid when water activates them:</p>\r\n\r\n<p><code>3NaHCO₃ + C₆H₈O₇ &rarr; C₆H₅Na₃O₇ + 3H₂O + 3CO₂</code></p>\r\n\r\n<p>This reaction releases carbon dioxide bubbles and mild heat, helping vaporize menthol. The vapors stimulate TRPM8 receptors, producing a cooling, decongestant sensation and promoting easier breathing.</p>\r\n\r\n<h2>Safety Notes</h2>\r\n\r\n<ul>\r\n	<li>Wear gloves when handling menthol crystals and citric acid.</li>\r\n	<li>Do not place tablets directly under the water stream&mdash;they will dissolve too fast.</li>\r\n	<li>Keep away from eyes and children; for external use only.</li>\r\n	<li>Ensure bathroom ventilation and discontinue use if irritation occurs.</li>\r\n</ul>\r\n', NULL, NULL, NULL, 'published', 1, '2025-10-08 21:20:00', NULL, '2025-10-08 21:20:13', '2025-10-08 21:21:37');

-- --------------------------------------------------------

--
-- Table structure for table `article_materials`
--

CREATE TABLE `article_materials` (
  `id` int(10) UNSIGNED NOT NULL,
  `article_id` int(10) UNSIGNED NOT NULL,
  `material_id` int(10) UNSIGNED NOT NULL,
  `quantity` varchar(50) DEFAULT NULL COMMENT 'Amount needed: "12 oz", "1 unit", "as needed"',
  `optional` tinyint(1) DEFAULT 0 COMMENT 'Is this material optional or essential?',
  `notes` text DEFAULT NULL COMMENT 'Special instructions for this material in this recipe',
  `sort_order` int(11) DEFAULT 0 COMMENT 'Display order in materials list'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `article_materials`
--

INSERT INTO `article_materials` (`id`, `article_id`, `material_id`, `quantity`, `optional`, `notes`, `sort_order`) VALUES
(6, 1, 1, '2 cups', 0, NULL, 1),
(19, 19, 6, '5–10 crystals per sachet or 1 tbsp per cup of alco', 0, 'Derived from peppermint oil; emits strong odor that repels rodents', 1),
(23, 20, 6, '2–4 tbsp per batch (8–10 tablets)', 0, 'Provide cooling and decongestant vapor effects when exposed to steam', 1);

-- --------------------------------------------------------

--
-- Table structure for table `article_seasons`
--

CREATE TABLE `article_seasons` (
  `article_id` int(10) UNSIGNED NOT NULL,
  `season` enum('Spring','Summer','Fall','Winter') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `article_seasons`
--

INSERT INTO `article_seasons` (`article_id`, `season`) VALUES
(1, 'Spring'),
(1, 'Fall'),
(19, 'Fall'),
(19, 'Winter'),
(20, 'Spring'),
(20, 'Fall'),
(20, 'Winter');

-- --------------------------------------------------------

--
-- Table structure for table `article_tags`
--

CREATE TABLE `article_tags` (
  `article_id` int(10) UNSIGNED NOT NULL,
  `tag_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `article_tags`
--

INSERT INTO `article_tags` (`article_id`, `tag_id`) VALUES
(1, 1),
(1, 2),
(1, 9),
(19, 6),
(19, 61),
(19, 62),
(20, 4),
(20, 63),
(20, 64);

-- --------------------------------------------------------

--
-- Table structure for table `issues`
--

CREATE TABLE `issues` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `period_label` varchar(100) DEFAULT NULL,
  `intro` text DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `status` enum('draft','published') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `issues`
--

INSERT INTO `issues` (`id`, `title`, `slug`, `period_label`, `intro`, `cover_image`, `published_at`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Fall 2025', 'fall-2025', 'September-November 2025', 'Preparing for winter: preservation, storage, and workshop projects for the cooler months.', NULL, '2025-09-01 04:00:00', 'published', '2025-10-06 18:53:22', '2025-10-06 18:53:22'),
(2, 'Winter 2025', 'winter-2025', 'December 2025 - February 2026', 'Indoor projects and winter maintenance for homestead and workshop.', NULL, NULL, 'draft', '2025-10-06 18:53:22', '2025-10-06 18:53:22'),
(3, 'Spring 2026', 'spring-2026', 'March-May 2026', 'Garden preparation, planting guides, and spring maintenance.', NULL, NULL, 'draft', '2025-10-06 20:44:43', '2025-10-06 20:44:43');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(100) NOT NULL COMMENT 'sodium-hydroxide, ph-meter, glass-jar-32oz',
  `common_name` varchar(200) NOT NULL COMMENT 'Lye, pH Meter, Glass Jar - user-friendly name',
  `technical_name` varchar(200) DEFAULT NULL COMMENT 'Sodium Hydroxide, Digital pH Tester - scientific/technical name',
  `other_names` text DEFAULT NULL COMMENT 'JSON array: ["caustic soda", "soda lye", "sodium hydrate"]',
  `chemical_formula` varchar(50) DEFAULT NULL COMMENT 'NaOH, H2SO4 (null for equipment)',
  `cas_number` varchar(20) DEFAULT NULL COMMENT 'CAS Registry Number: 1310-73-2',
  `category_id` int(10) UNSIGNED NOT NULL,
  `subcategory_id` int(10) UNSIGNED DEFAULT NULL,
  `description` mediumtext NOT NULL COMMENT 'What it is and what it does',
  `traditional_uses` text DEFAULT NULL COMMENT 'Historical homesteading applications',
  `modern_applications` text DEFAULT NULL COMMENT 'Current uses and applications',
  `safety_notes` text DEFAULT NULL COMMENT 'Safety precautions and warnings',
  `storage_instructions` text DEFAULT NULL COMMENT 'How to store properly',
  `maintenance_care` text DEFAULT NULL COMMENT 'Care instructions (for equipment/tools)',
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Technical specs: capacity, accuracy, material, dimensions, etc.' CHECK (json_valid(`specifications`)),
  `image_url` varchar(255) DEFAULT NULL COMMENT 'Main product image',
  `gallery_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of additional image URLs' CHECK (json_valid(`gallery_images`)),
  `featured` tinyint(1) DEFAULT 0 COMMENT 'Show on homepage/featured lists',
  `essential` tinyint(1) DEFAULT 0 COMMENT 'Essential vs nice-to-have',
  `difficulty_level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `purchase_url` varchar(255) DEFAULT NULL COMMENT 'Direct link to purchase this material',
  `abantecart_embed_code` text DEFAULT NULL COMMENT 'Complete AbanteCart widget HTML',
  `seo_title` varchar(255) DEFAULT NULL COMMENT 'Override for meta title',
  `seo_description` varchar(255) DEFAULT NULL COMMENT 'Override for meta description',
  `canonical_url` varchar(255) DEFAULT NULL COMMENT 'Canonical URL if needed',
  `status` enum('draft','published','discontinued') DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `slug`, `common_name`, `technical_name`, `other_names`, `chemical_formula`, `cas_number`, `category_id`, `subcategory_id`, `description`, `traditional_uses`, `modern_applications`, `safety_notes`, `storage_instructions`, `maintenance_care`, `specifications`, `image_url`, `gallery_images`, `featured`, `essential`, `difficulty_level`, `purchase_url`, `abantecart_embed_code`, `seo_title`, `seo_description`, `canonical_url`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 'sodium-hydroxide', 'Lye', 'Sodium Hydroxide', '[\"caustic soda\",\"soda lye\",\"sodium hydrate\"]', 'NaOH', '1310-73-2', 1, 1, 'Traditional soap-making ingredient used by homesteaders for generations. When mixed with fats, creates pure soap through saponification. Also used in food processing and cleaning applications.', 'Soap making has been a homestead essential for centuries. Lye was traditionally made from wood ash, but modern food-grade sodium hydroxide provides consistent, reliable results. Used for making lye soap, hominy, and pretzels.', 'Cold process soap making, hot process soap making, liquid soap production, drain cleaning, pH adjustment in food processing, and traditional nixtamalization of corn.', 'CAUTION: Highly caustic material. Can cause severe chemical burns. Always wear safety goggles and chemical-resistant gloves. Add lye to water - NEVER add water to lye (causes violent reaction). Work in ventilated area. Keep away from children and pets. Store in clearly labeled, sealed container.', 'Store in airtight container in cool, dry location. Keep away from moisture, acids, and aluminum. Label clearly with contents and hazard warnings. Keep in original container or food-grade HDPE plastic.', '', '{}', 'https://shop.chemicalstore.com/image/thumbnails/18/d3/SH1308-101693-800x800.jpg', '[\"https:\\/\\/shop.chemicalstore.com\\/image\\/thumbnails\\/18\\/d3\\/SH1308-101693-800x800.jpg\"]', 0, 1, 'intermediate', 'https://shop.chemicalstore.com/index.php?rt=product/search&keyword=Sodium%20Hydroxide', '<script src=\"https://shop.chemicalstore.com/index.php?rt=r/embed/js\" type=\"text/javascript\"></script>\r\n<div style=\"display:none;\" class=\"abantecart-widget-container\" data-url=\"https://shop.chemicalstore.com/\" data-css-url=\"https://shop.chemicalstore.com/extensions/cs/storefront/view/cs/stylesheet/embed.css\" data-language=\"en\" data-currency=\"USD\">\r\n	<div id=\"abc_596\" class=\"abantecart_product\" data-product-id=\"2971\" data-language=\"en\" data-currency=\"USD\">\r\n		<div class=\"abantecart_image\"></div>\r\n		<h3 class=\"abantecart_name\"></h3>\r\n		<div class=\"abantecart_blurb\"></div>\r\n		<div class=\"abantecart_rating\"></div>\r\n		<div class=\"abantecart_addtocart\"></div>\r\n	</div>\r\n</div>', '', '', '', 'published', '2025-10-07 13:42:13', '2025-10-07 13:42:13', '2025-10-07 18:41:48'),
(2, 'gum-rosin', 'Gum Rosin', 'Pine Resin Extract', '[\"pine rosin\",\"colophony\",\"rosin\"]', '', '8050-09-7', 1, 2, 'Natural tree resin harvested from pine trees. Used traditionally in wood treatments, adhesives, waterproofing, and specialty soap making. Provides tackiness and adhesion properties.', 'Used by boat builders for waterproofing seams, by furniture makers for wood polish, and by musicians for violin bow rosin. Traditional adhesive and sealant in woodworking.', 'Wood finishing, natural adhesives, specialty soap making, friction enhancer for tools and instruments, traditional waterproofing compound.', 'Generally safe for external use. May cause skin sensitivity in some individuals. Use in well-ventilated area when heating. Avoid prolonged skin contact if sensitive.', '', '', '{}', 'https://shop.chemicalstore.com/image/thumbnails/18/ab/HROSIN-101048-1000x1000.jpg', NULL, 0, 0, 'beginner', 'https://shop.chemicalstore.com/gum-rosin?utm_source=thegreenalmanac&utm_medium=referral&utm_campaign=materials', '', '', '', '', 'published', '2025-10-07 13:42:13', '2025-10-07 13:42:13', '2025-10-26 03:35:41'),
(6, 'menthol-crystals', 'Menthol Crystals', '5-methyl-2-(1-methylethyl)cyclohexanol', '[\"2-isopropyl-5-methylcyclohexanol\",\"L-menthol\",\"racemic menthol\",\"D-menthol\",\"peppermint camphor\"]', 'C₁₀H₂₀O', '89-78-1 | 2216-51-5', 1, 1, 'Menthol is a cyclic monoterpene alcohol occurring naturally in mint oils and produced synthetically. It forms colorless to white, needle-like crystals that exhibit a characteristic cooling sensation. The compound is stable under normal conditions and non-reactive with most materials.', 'Historically extracted from peppermint and corn mint oils, menthol has been used in traditional medicine for topical cooling, pain relief, and aromatic therapy.', 'Used extensively in pharmaceutical, cosmetic, food, and industrial formulations. Applications include topical analgesics, oral hygiene products, flavorings, fragrance formulations, and as a penetration enhancer in drug delivery systems.', 'Avoid contact with eyes and prolonged skin exposure.\r\nMay cause irritation to eyes, skin, or respiratory system at high concentrations.\r\nUse gloves, goggles, and lab coat during handling.\r\nDo not ingest.\r\nUse only with adequate ventilation.\r\nToxic gases may form if heated to decomposition.', 'Store in a cool, dry, well-ventilated location away from sunlight, heat, and oxidizing materials. Keep container tightly closed. Protect from moisture and ignition sources.', '', '{}', 'https://shop.chemicalstore.com/image/thumbnails/f4/3b/MENTHOL-1000374-1000x1000.png', NULL, 1, 1, 'beginner', 'https://shop.chemicalstore.com/menthol-crystals', '<script src=\"https://shop.chemicalstore.com/index.php?rt=r/embed/js\" type=\"text/javascript\"></script>\r\n<div style=\"display:none;\" class=\"abantecart-widget-container\" data-url=\"https://shop.chemicalstore.com/\" data-css-url=\"https://shop.chemicalstore.com/extensions/cs/storefront/view/cs/stylesheet/embed.css\" data-language=\"en\" data-currency=\"USD\">\r\n	<div id=\"abc_87\" class=\"abantecart_product\" data-product-id=\"7354\" data-language=\"en\" data-currency=\"USD\">\r\n		<div class=\"abantecart_image\"></div>\r\n		<h3 class=\"abantecart_name\"></h3>\r\n		<div class=\"abantecart_blurb\"></div>\r\n		<div class=\"abantecart_rating\"></div>\r\n		<div class=\"abantecart_addtocart\"></div>\r\n	</div>\r\n</div>', '', '', '', 'published', '2025-10-08 20:20:03', '2025-10-08 20:20:03', '2025-10-26 03:33:05');

-- --------------------------------------------------------

--
-- Table structure for table `material_categories`
--

CREATE TABLE `material_categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(50) NOT NULL COMMENT 'substance, equipment, tool, container, safety, consumable',
  `name` varchar(100) NOT NULL COMMENT 'Display name',
  `description` text DEFAULT NULL COMMENT 'Category description',
  `icon` varchar(50) DEFAULT NULL COMMENT 'Icon class or emoji',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_categories`
--

INSERT INTO `material_categories` (`id`, `slug`, `name`, `description`, `icon`, `sort_order`, `created_at`) VALUES
(1, 'substance', 'Substances', 'Chemicals, natural materials, and minerals used in recipes and processes', '🧪', 1, '2025-10-07 13:42:13'),
(2, 'equipment', 'Equipment', 'Reusable tools and devices for measuring, mixing, and processing', '⚖️', 2, '2025-10-07 13:42:13'),
(3, 'tool', 'Tools', 'Hand tools and implements for various homestead tasks', '🔧', 3, '2025-10-07 13:42:13'),
(4, 'container', 'Containers', 'Jars, bottles, and vessels for storage and processing', '🫙', 4, '2025-10-07 13:42:13'),
(5, 'safety', 'Safety Gear', 'Personal protective equipment for safe handling', '🥽', 5, '2025-10-07 13:42:13'),
(6, 'consumable', 'Consumables', 'Single-use items like test strips, filters, and labels', '📋', 6, '2025-10-07 13:42:13');

-- --------------------------------------------------------

--
-- Table structure for table `material_clicks`
--

CREATE TABLE `material_clicks` (
  `id` int(10) UNSIGNED NOT NULL,
  `material_id` int(10) UNSIGNED NOT NULL,
  `click_type` enum('purchase_link','detail_view') DEFAULT 'purchase_link' COMMENT 'Type of interaction',
  `source_page` varchar(255) DEFAULT NULL COMMENT 'Page where click occurred: /material.php, /materials.php, /article.php, etc.',
  `source_article_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'If clicked from an article, which article?',
  `user_ip` varchar(45) DEFAULT NULL COMMENT 'IP address (anonymized for privacy)',
  `user_agent` text DEFAULT NULL COMMENT 'Browser user agent',
  `referrer` varchar(255) DEFAULT NULL COMMENT 'HTTP referrer',
  `clicked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_clicks`
--

INSERT INTO `material_clicks` (`id`, `material_id`, `click_type`, `source_page`, `source_article_id`, `user_ip`, `user_agent`, `referrer`, `clicked_at`) VALUES
(2, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 14:12:55'),
(3, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 14:39:52'),
(4, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 14:41:24'),
(5, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 14:49:29'),
(6, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 14:52:11'),
(7, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 14:53:35'),
(8, 1, 'purchase_link', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 14:53:41'),
(9, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 14:54:26'),
(10, 1, 'purchase_link', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 14:54:29'),
(11, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 14:55:23'),
(12, 1, 'purchase_link', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 14:55:24'),
(13, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 14:58:01'),
(14, 1, 'purchase_link', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 14:58:08'),
(15, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 15:13:07'),
(16, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 17:32:22'),
(17, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 17:36:52'),
(18, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 17:40:51'),
(19, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 17:43:36'),
(21, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 17:48:22'),
(22, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 17:49:29'),
(23, 1, 'purchase_link', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-07 17:49:33'),
(25, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-08 17:48:11'),
(26, 1, 'purchase_link', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-08 17:48:14'),
(27, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-08 17:49:54'),
(28, 1, 'purchase_link', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-08 17:52:41'),
(29, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-08 18:02:13'),
(30, 1, 'purchase_link', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-08 18:02:15'),
(31, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-08 18:14:54'),
(32, 1, 'purchase_link', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-08 18:14:55'),
(34, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-08 18:55:04'),
(35, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://www.thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-08 19:01:36'),
(36, 2, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=gum-rosin', '2025-10-08 19:09:07'),
(37, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-08 19:09:14'),
(43, 2, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://www.thegreenalmanac.com/material.php?slug=gum-rosin', '2025-10-08 19:33:13'),
(44, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://www.thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-08 19:38:26'),
(45, 1, 'purchase_link', '/article.php?slug=restoring-rusty-tools-washing-soda', 1, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://www.thegreenalmanac.com/article.php?slug=restoring-rusty-tools-washing-soda', '2025-10-08 19:44:57'),
(48, 6, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-08 20:21:08'),
(49, 6, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-08 20:21:44'),
(50, 6, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-08 20:22:52'),
(51, 6, 'purchase_link', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '108.35.206.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-08 20:36:57'),
(52, 6, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-08 20:42:53'),
(53, 6, 'purchase_link', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-08 20:43:29'),
(54, 6, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-08 20:48:08'),
(55, 6, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-08 20:49:09'),
(56, 6, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-08 20:59:35'),
(57, 6, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-08 20:59:49'),
(58, 6, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-08 21:00:33'),
(59, 6, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-08 21:12:13'),
(60, 6, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-08 21:15:47'),
(61, 2, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=gum-rosin', '2025-10-08 21:15:59'),
(62, 1, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=sodium-hydroxide', '2025-10-08 21:16:04'),
(63, 6, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-08 21:16:15'),
(64, 2, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=gum-rosin', '2025-10-08 21:16:20'),
(65, 6, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-08 21:23:24'),
(66, 6, 'purchase_link', '/article.php?slug=shower-steamers-with-menthol-crystals', 20, '75.203.121.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://www.thegreenalmanac.com/article.php?slug=shower-steamers-with-menthol-crystals', '2025-10-17 00:59:45'),
(67, 6, 'detail_view', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '172.59.213.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-25 23:50:10'),
(68, 6, 'detail_view', '/material.php', NULL, '172.59.213.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-25 23:50:10'),
(69, 6, 'purchase_link', '/material.php', NULL, '172.59.213.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-25 23:50:18'),
(70, 6, 'purchase_link', '/material.php', NULL, '172.59.213.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-25 23:50:30'),
(71, 6, 'purchase_link', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '75.203.121.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-26 01:20:22'),
(72, 6, 'detail_view', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '75.203.121.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-26 01:20:26'),
(73, 6, 'detail_view', '/material.php', NULL, '75.203.121.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 01:20:27'),
(74, 6, 'purchase_link', '/material.php', NULL, '75.203.121.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 01:20:34'),
(75, 6, 'detail_view', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '75.203.121.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-26 01:20:40'),
(76, 6, 'detail_view', '/material.php', NULL, '75.203.121.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 01:20:40'),
(77, 6, 'detail_view', '/material.php', NULL, '75.203.121.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 01:20:45'),
(78, 6, 'purchase_link', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '75.203.121.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-26 01:20:50'),
(79, 6, 'purchase_link', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '75.203.121.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-26 02:18:23'),
(80, 6, 'detail_view', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-26 03:29:26'),
(81, 6, 'detail_view', '/material.php', NULL, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 03:29:26'),
(82, 6, 'detail_view', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-26 03:29:53'),
(83, 6, 'detail_view', '/material.php', NULL, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 03:29:53'),
(84, 6, 'purchase_link', '/material.php', NULL, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 03:29:57'),
(85, 6, 'detail_view', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-26 03:31:12'),
(86, 6, 'detail_view', '/material.php', NULL, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 03:31:12'),
(87, 6, 'purchase_link', '/material.php', NULL, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 03:31:16'),
(88, 6, 'purchase_link', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-26 03:31:23'),
(89, 6, 'detail_view', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-26 03:31:45'),
(90, 6, 'detail_view', '/material.php', NULL, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 03:31:45'),
(91, 6, 'detail_view', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-26 03:34:11'),
(92, 6, 'detail_view', '/material.php', NULL, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 03:34:11'),
(93, 6, 'purchase_link', '/material.php', NULL, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 03:34:19'),
(94, 2, 'detail_view', '/material.php', NULL, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=gum-rosin', '2025-10-26 03:35:09'),
(95, 2, 'detail_view', '/material.php', NULL, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=gum-rosin', '2025-10-26 03:35:43'),
(96, 6, 'purchase_link', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '75.203.121.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-26 03:36:21'),
(97, 6, 'purchase_link', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '172.59.209.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-26 19:42:36'),
(98, 6, 'detail_view', '/article.php?slug=natural-mouse-repellent-using-menthol-crystals', 19, '172.59.209.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/article.php?slug=natural-mouse-repellent-using-menthol-crystals', '2025-10-26 19:42:40'),
(99, 6, 'detail_view', '/material.php', NULL, '172.59.209.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 19:42:40'),
(100, 6, 'purchase_link', '/material.php', NULL, '172.59.209.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 19:42:43'),
(101, 6, 'detail_view', '/material.php', NULL, '172.59.209.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 20:10:22'),
(102, 6, 'detail_view', '/material.php', NULL, '172.59.209.0', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-10-26 20:33:41'),
(103, 6, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-12-18 21:02:01'),
(104, 6, 'purchase_link', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-12-18 21:02:03'),
(105, 6, 'detail_view', '/material.php', NULL, '108.35.206.0', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-12-18 21:07:05'),
(106, 6, 'detail_view', '/material.php', NULL, '66.249.83.0', 'Mozilla/5.0 (compatible; Google-Structured-Data-Testing-Tool +https://search.google.com/structured-data/testing-tool)', 'https://thegreenalmanac.com/material.php?slug=menthol-crystals', '2025-12-18 21:07:12');

-- --------------------------------------------------------

--
-- Stand-in structure for view `material_click_stats`
-- (See below for the actual view)
--
CREATE TABLE `material_click_stats` (
`id` int(10) unsigned
,`slug` varchar(100)
,`common_name` varchar(200)
,`total_clicks` bigint(21)
,`purchase_clicks` bigint(21)
,`widget_clicks` bigint(21)
,`detail_views` bigint(21)
,`unique_visitors` bigint(21)
,`last_clicked_at` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `material_subcategories`
--

CREATE TABLE `material_subcategories` (
  `id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `slug` varchar(50) NOT NULL COMMENT 'chemical, natural, mineral, glassware, measuring, etc',
  `name` varchar(100) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_subcategories`
--

INSERT INTO `material_subcategories` (`id`, `category_id`, `slug`, `name`, `sort_order`) VALUES
(1, 1, 'chemical', 'Chemicals', 1),
(2, 1, 'natural', 'Natural Materials', 2),
(3, 1, 'mineral', 'Minerals', 3),
(4, 1, 'botanical', 'Botanicals', 4),
(5, 2, 'measuring', 'Measuring Tools', 1),
(6, 2, 'mixing', 'Mixing Equipment', 2),
(7, 2, 'heating', 'Heating & Cooling', 3),
(8, 2, 'testing', 'Testing Devices', 4),
(9, 2, 'glassware', 'Glassware', 5),
(10, 3, 'cutting', 'Cutting Tools', 1),
(11, 3, 'stirring', 'Stirring & Mixing', 2),
(12, 3, 'pouring', 'Pouring & Transfer', 3),
(13, 3, 'molding', 'Molds & Forms', 4),
(14, 4, 'storage', 'Storage Containers', 1),
(15, 4, 'processing', 'Processing Vessels', 2),
(16, 4, 'dispensing', 'Dispensing Bottles', 3),
(17, 5, 'eye', 'Eye Protection', 1),
(18, 5, 'hand', 'Hand Protection', 2),
(19, 5, 'body', 'Body Protection', 3),
(20, 5, 'respiratory', 'Respiratory Protection', 4),
(21, 6, 'testing', 'Test Strips & Papers', 1),
(22, 6, 'filtration', 'Filters & Membranes', 2),
(23, 6, 'labeling', 'Labels & Tags', 3);

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `name`, `slug`, `description`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Calendar & Seasons', 'calendar-seasons', 'Seasonal activities and chemical applications throughout the year', 1, '2025-10-06 18:53:22', '2025-10-06 18:53:22'),
(2, 'Farming & Garden', 'farming-garden', 'Agricultural and gardening chemistry for homesteads', 2, '2025-10-06 18:53:22', '2025-10-06 18:53:22'),
(3, 'Home & Workshop', 'home-workshop', 'Practical chemistry for household and workshop projects', 3, '2025-10-06 18:53:22', '2025-10-06 18:53:22');

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'rust', 'rust', '2025-10-06 18:53:22'),
(2, 'cleaning', 'cleaning', '2025-10-06 18:53:22'),
(3, 'preservation', 'preservation', '2025-10-06 18:53:22'),
(4, 'soap making', 'soap-making', '2025-10-06 18:53:22'),
(5, 'fertilizer', 'fertilizer', '2025-10-06 18:53:22'),
(6, 'pest control', 'pest-control', '2025-10-06 18:53:22'),
(7, 'water treatment', 'water-treatment', '2025-10-06 18:53:22'),
(8, 'food preservation', 'food-preservation', '2025-10-06 18:53:22'),
(9, 'metal work', 'metal-work', '2025-10-06 18:53:22'),
(10, 'wood preservation', 'wood-preservation', '2025-10-06 18:53:22'),
(61, 'natural repellents', 'natural-repellents', '2025-10-08 20:08:11'),
(62, 'home maintenance', 'home-maintenance', '2025-10-08 20:08:26'),
(63, 'aromatherapy', 'aromatherapy', '2025-10-08 21:20:32'),
(64, 'personal care', 'personal-care', '2025-10-08 21:20:45');

-- --------------------------------------------------------

--
-- Structure for view `material_click_stats`
--
DROP TABLE IF EXISTS `material_click_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`toys2000`@`localhost` SQL SECURITY DEFINER VIEW `material_click_stats`  AS SELECT `m`.`id` AS `id`, `m`.`slug` AS `slug`, `m`.`common_name` AS `common_name`, count(`mc`.`id`) AS `total_clicks`, count(case when `mc`.`click_type` = 'purchase_link' then 1 end) AS `purchase_clicks`, count(case when `mc`.`click_type` = 'widget_click' then 1 end) AS `widget_clicks`, count(case when `mc`.`click_type` = 'detail_view' then 1 end) AS `detail_views`, count(distinct `mc`.`user_ip`) AS `unique_visitors`, max(`mc`.`clicked_at`) AS `last_clicked_at` FROM (`materials` `m` left join `material_clicks` `mc` on(`m`.`id` = `mc`.`material_id`)) GROUP BY `m`.`id`, `m`.`slug`, `m`.`common_name` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `issue_id` (`issue_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_section` (`section_id`),
  ADD KEY `idx_format` (`format`),
  ADD KEY `idx_difficulty` (`difficulty`),
  ADD KEY `idx_published` (`published_at`),
  ADD KEY `idx_featured` (`featured`);

--
-- Indexes for table `article_materials`
--
ALTER TABLE `article_materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_article_material` (`article_id`,`material_id`),
  ADD KEY `idx_article` (`article_id`),
  ADD KEY `idx_material` (`material_id`),
  ADD KEY `idx_optional` (`optional`);

--
-- Indexes for table `article_seasons`
--
ALTER TABLE `article_seasons`
  ADD PRIMARY KEY (`article_id`,`season`),
  ADD KEY `idx_season` (`season`);

--
-- Indexes for table `article_tags`
--
ALTER TABLE `article_tags`
  ADD PRIMARY KEY (`article_id`,`tag_id`),
  ADD KEY `idx_article` (`article_id`),
  ADD KEY `idx_tag` (`tag_id`);

--
-- Indexes for table `issues`
--
ALTER TABLE `issues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_common_name` (`common_name`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_subcategory` (`subcategory_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_featured` (`featured`),
  ADD KEY `idx_essential` (`essential`);
ALTER TABLE `materials` ADD FULLTEXT KEY `ft_search` (`common_name`,`technical_name`,`description`,`traditional_uses`,`modern_applications`);

--
-- Indexes for table `material_categories`
--
ALTER TABLE `material_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_sort` (`sort_order`);

--
-- Indexes for table `material_clicks`
--
ALTER TABLE `material_clicks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_material` (`material_id`),
  ADD KEY `idx_click_type` (`click_type`),
  ADD KEY `idx_source_article` (`source_article_id`),
  ADD KEY `idx_clicked_at` (`clicked_at`);

--
-- Indexes for table `material_subcategories`
--
ALTER TABLE `material_subcategories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_category_slug` (`category_id`,`slug`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `article_materials`
--
ALTER TABLE `article_materials`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `issues`
--
ALTER TABLE `issues`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `material_categories`
--
ALTER TABLE `material_categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `material_clicks`
--
ALTER TABLE `material_clicks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `material_subcategories`
--
ALTER TABLE `material_subcategories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`issue_id`) REFERENCES `issues` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `article_materials`
--
ALTER TABLE `article_materials`
  ADD CONSTRAINT `article_materials_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_materials_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `article_seasons`
--
ALTER TABLE `article_seasons`
  ADD CONSTRAINT `article_seasons_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `article_tags`
--
ALTER TABLE `article_tags`
  ADD CONSTRAINT `article_tags_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `material_categories` (`id`),
  ADD CONSTRAINT `materials_ibfk_2` FOREIGN KEY (`subcategory_id`) REFERENCES `material_subcategories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `material_clicks`
--
ALTER TABLE `material_clicks`
  ADD CONSTRAINT `material_clicks_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_clicks_ibfk_2` FOREIGN KEY (`source_article_id`) REFERENCES `articles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `material_subcategories`
--
ALTER TABLE `material_subcategories`
  ADD CONSTRAINT `material_subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `material_categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
