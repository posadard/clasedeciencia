<?php
// Project: Plant Growth and Vitamin Effects
$project_title = "Plant Growth and Vitamin Effects - Will Vitamins Affect Plant Growth?";
$project_description = "Investigate whether vitamins improve plant growth and health. Learn about plant nutrition, fertilizers, and how micronutrients affect plant development through controlled experimentation.";
$project_keywords = "plant growth, vitamins, plant nutrition, fertilizers, micronutrients, botanical experiment, plant health, controlled experiment";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Biology, Plant Science";
$project_difficulty = "Beginner";

// Project pages live under /projects/<slug>/ so include the shared project header
// using a path relative to this file that reaches the repository includes folder.
include __DIR__ . '/../../includes/project-header.php';
?>

<article class="project-article">

<section class="project-body container">
  <!-- Print buttons (top-right and bottom) -->
  <div class="print-controls" aria-hidden="false">
    <button class="btn-print" id="print-top" aria-label="Print this page">Print</button>
  </div>

  <!-- Print-only logo shown only in printed output -->
  <img class="print-logo" src="https://freescienceproject.com/images/freescienceproject02.jpg" alt="Free Science Project logo" />

  <h2 class="project-title-simple"><?php echo htmlspecialchars($project_title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2>

  <style scoped>
    /* Scoped modern styles for this legacy project page - non-destructive */
    .project-body { max-width: 980px; margin: 0 auto; padding: 1rem; }
    .project-title-simple { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color: #2c5aa0; font-size: 1.5rem; margin: 0 0 0.75rem 0; }
    .intro-section { background: #f8f9ff; border: 1px solid #2c5aa0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .problem-section { background: #fff6f0; border: 1px solid #ff6b35; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .research-section { background: #f0fff0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .hypothesis-section { background: #f0f8ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .materials-section { background: #fff8f0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .procedure-section { background: #f8fff8; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .results-section { background: #fff6f0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .safety-section { background: #ffe6e6; border: 1px solid #ff4444; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .extensions-section { background: #f0f0ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .science-section { background: #e6ffe6; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .responsive-img { max-width: 100%; height: auto; display: block; }
    .section-title { color: #2c5aa0; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .highlight-text { color: #2c5aa0; font-weight: bold; }
    .center { text-align: center; }
    /* Ensure all paragraphs have dark text for readability */
    p { color: #333; line-height: 1.6; }
    /* Footer styles - ensure all text in footer is white */
    footer, footer *, 
    .footer, .footer *,
    .project-footer, .project-footer *,
    [class*="footer"] *, [id*="footer"] * {
      color: white !important;
      fill: white !important;
    }
    /* Banner row (bottom of the page) */
    .banner-row { display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-top: 0.75rem; }
    .banner-row .banner { flex: 1 1 0; text-align: center; }
    .banner-row img { max-width: 100%; height: auto; display: inline-block; }
    @media (max-width: 720px) {
      .banner-row { flex-direction: column; gap: 0.5rem; }
    }
    /* Data table styling */
    .data-table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
    .data-table th, .data-table td { border: 1px solid #ddd; padding: 0.75rem; text-align: left; }
    .data-table th { background: #2c5aa0; color: white; font-weight: bold; }
    .data-table tr:nth-child(even) { background: #f8f9fa; }
    /* Miniscience widget centering */
    .miniscience-widget { display:flex; justify-content:center; align-items:center; }
    .miniscience-widget .abantecart-widget-container { display:block; max-width:880px; width:100%; }
    .miniscience-widget .abantecart_product { margin:0 auto; }
    /* Related & FAQ per-project sections */
    .related-projects { margin-top:1.25rem; border-top:1px solid #e6e6e6; padding-top:1rem; }
    .related-projects h3 { margin-top:0; }
    .related-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1rem; align-items:start; }
    .related-grid .miniscience-widget { margin: 0; }
    .faq { margin-top:1rem; }
    .faq details { margin-bottom:0.75rem; }
    /* Print controls */
    .print-controls { display:flex; justify-content:flex-end; margin-bottom:0.5rem; }
    .btn-print { background:#2c5aa0; color:#fff; border:0; padding:0.45rem 0.75rem; border-radius:6px; cursor:pointer; font-family:inherit; }
    .btn-print:focus { outline:3px solid #ffec99; }
    /* Print-only logo: hidden on screen */
    .print-logo { display: none; max-width:220px; }
    @media print {
      /* Hide everything by default when printing */
      body * { visibility: hidden; }
      /* Only show the print-clone, not the original project-body */
      .print-clone, .print-clone * { visibility: visible !important; }
      .print-clone { position: absolute; left: 0; top: 0; width: 100%; }
      /* Explicitly hide the original project-body to prevent duplication */
      .project-body:not(.print-clone) { display: none !important; }
      /* Hide print buttons in printed output */
      .btn-print { display: none !important; }
      /* Show print-only logo */
      .print-logo { display: block !important; max-width:220px; margin: 0 0 1rem 0; }
      /* Show print-only products info */
      .print-only-products { display: block !important; }
    }
  </style>

  <div class="intro-section">
    <p>The <strong class="highlight-text">Plant Growth and Vitamin Effects Experiment</strong> investigates whether vitamin supplements can enhance plant growth and health. This experiment explores plant nutrition science and helps students understand how micronutrients affect living organisms.</p>
    
    <p>Perfect for learning about <strong>plant biology, nutrition science, and agricultural practices</strong>, this controlled experiment provides measurable results that demonstrate important botanical principles.</p>
  </div>

  <div class="problem-section">
    <h3 class="section-title">🔬 Research Question</h3>
    <p class="highlight-text" style="font-size: 1.1rem;">Will vitamins affect the growth of a plant?</p>
    
    <p>This experiment tests whether vitamin supplements (designed for humans) can improve plant growth, health, and development when added to their water supply over time.</p>
  </div>

  <div class="research-section">
    <h3 class="section-title">🧠 Background Research</h3>
    <p>Plants require specific nutrients for healthy growth and development. While they produce their own food through <strong>photosynthesis</strong>, they also need various minerals and nutrients from soil.</p>
    
    <p><strong>Plant nutrition includes both macronutrients and micronutrients:</strong></p>
    <ul>
      <li><strong>Macronutrients</strong> - Nitrogen (N), Phosphorus (P), Potassium (K) needed in large amounts</li>
      <li><strong>Secondary nutrients</strong> - Calcium, Magnesium, Sulfur for structure and processes</li>
      <li><strong>Micronutrients</strong> - Iron, Zinc, Manganese, Copper needed in small amounts</li>
      <li><strong>Trace elements</strong> - Boron, Molybdenum for specific enzyme functions</li>
    </ul>
    
    <p><strong>Human vitamins vs. Plant nutrients:</strong></p>
    <ul>
      <li>Human vitamins are designed for animal metabolism</li>
      <li>Plants may not absorb or use human vitamins effectively</li>
      <li>Some vitamin components might help, while others could be harmful</li>
      <li>Plant-specific fertilizers contain nutrients in forms plants can use</li>
    </ul>
    
    <p>Understanding plant nutrition helps explain why farmers use specialized fertilizers rather than vitamins for crops.</p>
  </div>

  <div class="hypothesis-section">
    <h3 class="section-title">💭 Hypothesis</h3>
    <p><strong>Prediction:</strong> Vitamins may provide some benefit to plant growth since they contain minerals and nutrients, but the effect will likely be minimal because human vitamins are not formulated for plant biology.</p>
    
    <p><strong>Expected outcome:</strong> Plants receiving vitamin supplements might show slight improvement compared to controls, but the effect will be less than what proper plant fertilizer would provide.</p>
  </div>

  <div class="materials-section">
    <h3 class="section-title">🛠️ Materials Needed</h3>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>Living Materials:</h4>
        <ul>
          <li>4 identical plants in pots (same species, size, and age)</li>
          <li>Extra potting soil (if needed)</li>
          <li>Plant saucers or trays</li>
        </ul>
        
        <h4>Test Materials:</h4>
        <ul>
          <li>2 different types of vitamins (liquid or crushable tablets)</li>
          <li>Measuring cups and spoons</li>
          <li>Water for dilution and control</li>
        </ul>
      </div>
      <div>
        <h4>Equipment:</h4>
        <ul>
          <li>4 plant labels or markers</li>
          <li>Ruler for measuring growth</li>
          <li>Camera (optional, for documentation)</li>
          <li>Data recording sheet</li>
          <li>Area with consistent sunlight</li>
          <li>Watering containers</li>
          <li>Scale (for measuring vitamins)</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="procedure-section">
    <h3 class="section-title">⚗️ Experimental Procedure</h3>
    
    <ol>
      <li><strong>Prepare plants:</strong> Select 4 identical plants of the same species, size, and health. Label them as "A" (Control 1), "B" (Control 2), "C" (Vitamin A), and "D" (Vitamin B).</li>
      
      <li><strong>Set up experimental area:</strong> Place all plants in the same location with equal access to sunlight and identical environmental conditions.</li>
      
      <li><strong>Prepare vitamin solutions:</strong> 
        <ul>
          <li>Crush or dissolve first vitamin type in water (follow package directions for dilution)</li>
          <li>Prepare second vitamin solution the same way</li>
          <li>Prepare plain water for control plants</li>
        </ul>
      </li>
      
      <li><strong>Initial measurements:</strong> Record starting height, number of leaves, stem thickness, and overall health condition of each plant.</li>
      
      <li><strong>Daily treatment:</strong> Water each plant with the same amount of its designated solution:
        <ul>
          <li>Plants A & B: Plain water (controls)</li>
          <li>Plant C: Vitamin A solution</li>
          <li>Plant D: Vitamin B solution</li>
        </ul>
      </li>
      
      <li><strong>Daily observations:</strong> Record plant height, leaf count, color, and overall health. Note any changes or differences between treatments.</li>
      
      <li><strong>Duration:</strong> Continue the experiment for 2-3 weeks, making daily observations and measurements.</li>
      
      <li><strong>Final analysis:</strong> Compare final measurements with starting data and analyze which treatments showed the greatest effects.</li>
    </ol>
  </div>

  <div class="results-section">
    <h3 class="section-title">📊 Expected Results & Analysis</h3>
    
    <p><strong>Typical experimental outcomes:</strong></p>
    
    <table class="data-table">
      <thead>
        <tr>
          <th>Plant Treatment</th>
          <th>Week 1 Growth</th>
          <th>Week 2 Growth</th>
          <th>Final Condition</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Control A (Water Only)</strong></td>
          <td>Normal growth</td>
          <td>Steady progress</td>
          <td>✅ Healthy baseline</td>
        </tr>
        <tr>
          <td><strong>Control B (Water Only)</strong></td>
          <td>Normal growth</td>
          <td>Similar to Control A</td>
          <td>✅ Confirms control results</td>
        </tr>
        <tr>
          <td><strong>Vitamin A Treatment</strong></td>
          <td>Slightly better growth</td>
          <td>Modest improvement</td>
          <td>📈 Minor enhancement</td>
        </tr>
        <tr>
          <td><strong>Vitamin B Treatment</strong></td>
          <td>Variable results</td>
          <td>May show improvement</td>
          <td>📈 Possible benefits</td>
        </tr>
      </tbody>
    </table>
    
    <p><strong>Analysis:</strong> Results often show <span class="highlight-text">mild improvements in vitamin-treated plants</span>, but effects are usually modest. Some vitamins contain minerals that plants can use, while others provide no benefit. This demonstrates why specialized plant fertilizers are more effective for plant nutrition.</p>
  </div>

  <div class="safety-section">
    <h3 class="section-title" style="color: #cc0000;">⚠️ Safety Information</h3>
    <ul>
      <li><strong>Adult supervision recommended</strong> when handling vitamins and solutions</li>
      <li><strong>Wash hands thoroughly</strong> after handling vitamin solutions</li>
      <li><strong>Do not taste</strong> vitamin solutions or plant water</li>
      <li><strong>Keep vitamins away from pets</strong> - some can be toxic to animals</li>
      <li><strong>Use only recommended amounts</strong> - too much can harm plants</li>
      <li><strong>Dispose of solutions safely</strong> - don't pour into storm drains</li>
      <li><strong>Label all containers clearly</strong> to avoid confusion</li>
    </ul>
  </div>

  <div class="science-section">
    <h3 class="section-title">🌱 Plant Nutrition Science</h3>
    <p><strong>Understanding plant nutrition principles:</strong></p>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>How Plants Get Nutrients:</h4>
        <ul>
     !    <li>Root absorption from soil solutions</li>
          <li>Ion exchange with soil particles</li>
          <li>Symbiotic relationships with beneficial bacteria</li>
          <li>Mycorrhizal fungi partnerships</li>
        </ul>
      </div>
      <div>
        <h4>Why Plant Fertilizers Work Better:</h4>
        <ul>
          <li>Nutrients in plant-available forms</li>
          <li>Proper ratios for plant metabolism</li>
   0      <li>pH-balanced for soil conditions</li>
          <li>Designed for root uptake mechanisms</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="extensions-section">
    <h3 class="section-title">🔬 Project Extensions & Variations</h3>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>Advanced Investigations:</h4>
        <ul>
          <li>Compare vitamins vs. plant fertilizer</li>
          <li>Test individual vitamin components</li>
          <li>Try different vitamin concentrations</li>
          <li>Test on different plant species</li>
          <li>Measure specific nutrients in soil</li>
        </ul>
      </div>
      <div>
        <h4>Science Fair Enhancements:</h4>
        <ul>
          <li>Create detailed growth charts and graphs</li>
          <li>Photograph plant progression daily</li>
          <li>Research vitamin chemistry and plant biology</li>
          <li>Compare homemade vs. commercial fertilizers</li>
          <li>Study agricultural nutrition practices</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="banner-row" role="group" aria-label="Page banners">
    <div class="banner">
      <a href="http://www.ScienceProject.com"><img src="../../images/spbanner1.jpg" alt="Join ScienceProject.com - support with your science project" width="200" height="81"></a>
    </div>
    <div class="banner">
      <a href="../../projects"><img src="../../images/btr_index.jpg" alt="Index of projects" width="98" height="32"></a>
      <a href="/"><img src="../../images/btr_home.jpg" alt="Back to home" width="98" height="30" style="margin-left:0.5rem;"></a>
    </div>
    <div class="banner">
      <a href="http://store.MiniScience.com"><img src="../../images/msbanner1.jpg" alt="MiniScience store banner" width="200" height="81"></a>
    </div>
  </div>

  <!-- Print-only related products info -->
  <div class="print-only-products" style="display: none; margin-top: 1.5rem; padding: 1rem; border-top: 2px solid #2c5aa0;">
    <p style="margin: 0; font-weight: bold; color: #2c5aa0;">Where can I find related products? miniscience.com</p>
  </div>

</section>

<!-- MiniScience / Miniscience product and category embed widget -->
<section class="related-projects">
  <h3>Related Science Projects &amp; Kits</h3>
  
  <!-- Kit/Products Section -->
  <div class="related-grid" style="margin-bottom: 1.5rem;">
    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <div style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <div id="abc_648" class="abantecart_product" data-product-id="3399" data-language="en" data-currency="USD">
          <div class="abantecart_image"></div>
          <h3 class="abantecart_name"></h3>
          <div class="abantecart_blurb"></div>
          <div class="abantecart_rating"></div>
          <div class="abantecart_addtocart"></div>
        </div>
      </div>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <div style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <div id="abc_102" class="abantecart_product" data-product-id="7265" data-language="en" data-currency="USD">
          <div class="abantecart_image"></div>
          <h3 class="abantecart_name"></h3>
          <div class="abantecart_blurb"></div>
          <div class="abantecart_rating"></div>
          <div class="abantecart_addtocart"></div>
        </div>
      </div>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <div style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <div id="abc_718" class="abantecart_product" data-product-id="2396" data-language="en" data-currency="USD">
          <div class="abantecart_image"></div>
          <h3 class="abantecart_name"></h3>
          <div class="abantecart_blurb"></div>
          <div class="abantecart_rating"></div>
          <div class="abantecart_addtocart"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Related Categories Section -->
  <h4 style="margin-top: 1rem; margin-bottom: 0.75rem; color: #2c5aa0; font-size: 1.1rem;">Related Categories</h4>
  <div class="related-grid">
    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760464461106106" class="abantecart_category" data-category-id="106" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760464483492139" class="abantecart_category" data-category-id="139" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>
  </div>
</section>

<section class="faq">
  <h3>Frequently Asked Questions</h3>
  <details>
    <summary>Do human vitamins actually help plants grow?</summary>
    <p>The results are mixed. Some vitamins contain minerals that plants can use (like iron or magnesium), but most human vitamins are formulated for animal metabolism, not plant biology. Any benefits are usually minimal compared to proper plant fertilizers.</p>
  </details>
  <details>
    <summary>Why do farmers use fertilizers instead of vitamins?</summary>
    <p>Plant fertilizers contain nutrients in forms that plants can easily absorb through their roots. They provide the right balance of nitrogen, phosphorus, and potassium (NPK) plus micronutrients in chemical forms specifically designed for plant uptake.</p>
  </details>
  <details>
    <summary>What nutrients do plants actually need?</summary>
    <p>Plants need macronutrients (nitrogen for leaves, phosphorus for roots/flowers, potassium for overall health), secondary nutrients (calcium, magnesium, sulfur), and micronutrients (iron, zinc, manganese, copper, boron, molybdenum) in much smaller amounts.</p>
  </details>
  <details>
    <summary>Can too many vitamins harm plants?</summary>
    <p>Yes! Just like over-fertilizing, too many vitamins can cause nutrient burn, salt buildup in soil, or toxic effects. Plants can be harmed by excessive amounts of certain minerals, even beneficial ones.</p>
  </details>
  <details>
    <summary>How do I make this experiment more scientific?</summary>
    <p>Use more plants for statistical validity, measure multiple variables (height, leaf count, stem thickness), take daily photos, test different concentrations, include a proper fertilizer control group, and graph your results over time.</p>
  </details>
  <details>
    <summary>What's the difference between organic and synthetic fertilizers?</summary>
    <p>Organic fertilizers (compost, manure) release nutrients slowly as they decompose, while synthetic fertilizers provide immediately available nutrients. Both can be effective, but organic fertilizers also improve soil structure and microbial activity.</p>
  </details>
  <details>
    <summary>Should I use liquid or tablet vitamins for this experiment?</summary>
    <p>Liquid vitamins are easier to measure and mix with water. If using tablets, crush them completely and dissolve in water. Avoid vitamins with artificial colors or heavy coatings that might not dissolve well.</p>
  </details>
  <details>
    <summary>How long should I run this experiment?</summary>
    <p>2-3 weeks provides enough time to see significant growth differences. Shorter periods may not show clear results, while longer periods risk other factors (season, plant maturity) influencing outcomes.</p>
  </details>
</section>

<!-- Bottom print control -->
<div class="print-controls" style="margin:1rem 0;">
  <button class="btn-print" id="print-bottom" aria-label="Print this page">Print</button>
</div>

<script>
// Print only the project-body section by opening a print window with cloned content.
function printProjectBody() {
  try {
    const section = document.querySelector('.project-body');
    if (!section) return window.print();

    // Create a hidden print container and clone the section into it
    const printContainer = document.createElement('div');
    // Give the clone the project-body class so @media print rules make it visible
    printContainer.className = 'project-body print-clone';
    // Prevent the clone from affecting layout before printing
    printContainer.style.display = 'none';
    // Copy content
    printContainer.innerHTML = section.innerHTML;
    // Small inline print-friendly adjustments to reduce blank pages
    printContainer.style.boxSizing = 'border-box';
    printContainer.style.width = '100%';
    printContainer.style.maxWidth = '980px';
    printContainer.style.margin = '0 auto';
    document.body.appendChild(printContainer);

    // Temporarily make the clone visible for printing via CSS media rules
    printContainer.style.display = 'block';
    // Ensure the clone is visible to the print stylesheet
    printContainer.style.visibility = 'visible';

    // Trigger print
    window.print();

    // Clean up after a short delay to allow print dialog to start
    setTimeout(() => {
      try { document.body.removeChild(printContainer); } catch (e) { /* ignore */ }
    }, 500);
  } catch (e) {
    console && console.error && console.error('Print failed', e);
    window.print();
  }
}

document.getElementById('print-top')?.addEventListener('click', printProjectBody);
document.getElementById('print-bottom')?.addEventListener('click', printProjectBody);
</script>

<?php
include __DIR__ . '/../../includes/project-footer.php';
?>