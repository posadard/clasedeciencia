<?php
// Project: Mold Growth Experiment - Which Food Molds Fastest?
$project_title = "Mold Growth Experiment - Which Food Molds Fastest?";
$project_description = "Compare mold growth rates on different foods including bananas, milk, bread, and cheese. Learn about microorganisms, food preservation, and experimental design in this biology investigation.";
$project_keywords = "mold growth, food spoilage, microbiology, food preservation, biology experiment, fungal growth, scientific method";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Biology, Microbiology";
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
    .materials-section { background: #f0f8ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .procedure-section { background: #fff8f0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .results-section { background: #f8fff8; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .safety-section { background: #ffe6e6; border: 1px solid #ff4444; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .extensions-section { background: #f0f0ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
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
    <p>The <strong class="highlight-text">Mold Growth Experiment</strong> is a fascinating biology investigation that explores how different foods spoil at different rates. This hands-on experiment teaches students about microorganisms, food preservation, and scientific observation methods.</p>
    
    <p>Perfect for understanding the invisible world of <strong>fungi and bacteria</strong> that affect our daily lives, this experiment provides clear, measurable results that demonstrate important biological principles.</p>
  </div>

  <div class="problem-section">
    <h3 class="section-title">🔬 Research Question</h3>
    <p class="highlight-text" style="font-size: 1.1rem;">Which type of food molds the fastest when stored in the same conditions: bananas, milk, bread, or cheese?</p>
    
    <p>This experiment investigates the rate of spoilage in different food types under controlled conditions, helping us understand factors that affect food preservation and microbial growth.</p>
  </div>

  <div class="research-section">
    <h3 class="section-title">🧠 Background Research</h3>
    <p>Most foods need refrigeration to stay fresh and safe to eat. Different foods have varying susceptibilities to mold and bacterial growth based on their:</p>
    
    <ul>
      <li><strong>Moisture content</strong> - Higher water content often leads to faster spoilage</li>
      <li><strong>pH levels</strong> - Acidic foods may resist some types of mold</li>
      <li><strong>Nutrient composition</strong> - Some foods provide better environments for microbial growth</li>
      <li><strong>Preservatives</strong> - Natural or artificial compounds that inhibit spoilage</li>
      <li><strong>Surface area</strong> - More exposed surface allows greater microbial access</li>
    </ul>
    
    <p>Understanding spoilage rates helps us make better decisions about food storage, safety, and waste reduction.</p>
  </div>

  <div class="materials-section">
    <h3 class="section-title">🛠️ Materials Needed</h3>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>Food Samples:</h4>
        <ul>
          <li>1 fresh banana</li>
          <li>1 slice of bread</li>
          <li>1 piece of cheese</li>
          <li>1 glass of milk</li>
        </ul>
      </div>
      <div>
        <h4>Equipment:</h4>
        <ul>
          <li>4 separate dishes/containers</li>
          <li>1 glass for milk</li>
          <li>Labels for identification</li>
          <li>Cabinet or enclosed space</li>
          <li>Camera (optional, for documentation)</li>
          <li>Data recording sheet</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="procedure-section">
    <h3 class="section-title">⚗️ Experimental Procedure</h3>
    
    <ol>
      <li><strong>Prepare samples:</strong> Ensure all food samples are fresh and show no signs of existing mold or spoilage.</li>
      
      <li><strong>Set up containers:</strong> Place each food sample in a separate, labeled dish. Pour milk into a glass.</li>
      
      <li><strong>Choose location:</strong> Place all samples in the same cabinet or enclosed area to ensure consistent temperature and humidity conditions.</li>
      
      <li><strong>Create observation schedule:</strong> Plan to check samples daily at the same time for 7 days.</li>
      
      <li><strong>Record initial conditions:</strong> Document the fresh appearance of each sample with notes or photos.</li>
      
      <li><strong>Daily observations:</strong> Check each sample daily and record any changes in appearance, smell, or texture.</li>
      
      <li><strong>Document results:</strong> Note the first day mold appears on each sample and track its progression.</li>
      
      <li><strong>Safety disposal:</strong> After the experiment, dispose of all samples safely without direct contact.</li>
    </ol>
  </div>

  <div class="results-section">
    <h3 class="section-title">📊 Expected Results & Data Analysis</h3>
    
    <p>Based on typical experimental observations:</p>
    
    <table class="data-table">
      <thead>
        <tr>
          <th>Food Sample</th>
          <th>First Signs of Mold</th>
          <th>Extensive Mold Growth</th>
          <th>Observations</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Milk</strong></td>
          <td>Day 2-3</td>
          <td>Day 4-5</td>
          <td>High moisture content, rapid bacterial growth</td>
        </tr>
        <tr>
          <td><strong>Bread</strong></td>
          <td>Day 3-4</td>
          <td>Day 5-6</td>
          <td>Moderate moisture, good surface for mold spores</td>
        </tr>
        <tr>
          <td><strong>Cheese</strong></td>
          <td>Day 4-5</td>
          <td>Day 6-7</td>
          <td>Some natural preservation, varies by type</td>
        </tr>
        <tr>
          <td><strong>Banana</strong></td>
          <td>Day 5-6</td>
          <td>Day 7+</td>
          <td>Natural skin protection, acidic content</td>
        </tr>
      </tbody>
    </table>
    
    <p><strong>Analysis:</strong> Results typically show that <span class="highlight-text">milk molds fastest</span> due to its high moisture content and neutral pH, creating ideal conditions for microbial growth. The banana often lasts longest due to its protective peel and acidic content.</p>
  </div>

  <div class="safety-section">
    <h3 class="section-title" style="color: #cc0000;">⚠️ Safety Information</h3>
    <ul>
      <li><strong>Adult supervision required</strong> for children under 12</li>
      <li><strong>Do not touch moldy samples</strong> directly with bare hands</li>
      <li><strong>Do not smell samples closely</strong> - some molds can cause respiratory irritation</li>
      <li><strong>Dispose of samples safely</strong> - seal in plastic bags before throwing away</li>
      <li><strong>Wash hands thoroughly</strong> after handling any materials</li>
      <li><strong>Use in well-ventilated area</strong> to avoid inhaling mold spores</li>
    </ul>
  </div>

  <div class="extensions-section">
    <h3 class="section-title">🔬 Project Extensions & Variations</h3>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>Advanced Investigations:</h4>
        <ul>
          <li>Test different storage temperatures</li>
          <li>Compare fresh vs. processed foods</li>
          <li>Investigate preservative effects</li>
          <li>Study different humidity levels</li>
          <li>Test organic vs. conventional foods</li>
        </ul>
      </div>
      <div>
        <h4>Science Fair Applications:</h4>
        <ul>
          <li>Create detailed photo documentation</li>
          <li>Graph mold growth over time</li>
          <li>Research food preservation methods</li>
          <li>Study economic impact of food waste</li>
          <li>Investigate natural preservatives</li>
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
        <div id="abc_718" class="abantecart_product" data-product-id="2396" data-language="en" data-currency="USD">
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
        <div id="abc_680" class="abantecart_product" data-product-id="2392" data-language="en" data-currency="USD">
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
    <summary>Is it safe to grow mold intentionally?</summary>
    <p>Yes, when proper safety precautions are followed. Use adult supervision, avoid direct contact with mold, don't smell samples closely, and dispose of everything safely after the experiment. This controlled approach is much safer than accidental mold exposure.</p>
  </details>
  <details>
    <summary>Why do different foods mold at different rates?</summary>
    <p>Different foods have varying moisture content, pH levels, nutrient composition, and natural preservatives. Foods with higher moisture and neutral pH typically mold faster, while acidic foods or those with natural antimicrobial compounds resist mold growth longer.</p>
  </details>
  <details>
    <summary>What type of mold will grow on these foods?</summary>
    <p>Common food molds include Rhizopus (bread mold), Penicillium (blue-green mold on cheese), and Aspergillus (various colors). The specific types depend on environmental conditions and the food substrate, but all are normal environmental molds.</p>
  </details>
  <details>
    <summary>Can I use this experiment for a science fair?</summary>
    <p>Absolutely! This experiment provides excellent data for graphing, allows for multiple variables to test, and demonstrates important biological concepts. Consider adding photography, microscopic examination, or testing preservation methods for advanced projects.</p>
  </details>
  <details>
    <summary>What if some samples don't grow mold?</summary>
    <p>Some foods may resist mold growth due to preservatives, low moisture, or unfavorable conditions. This is actually valuable data! Document these results and research why certain foods are more resistant to spoilage.</p>
  </details>
  <details>
    <summary>How do I dispose of moldy samples safely?</summary>
    <p>Seal all samples in plastic bags without direct contact, throw away in regular trash, and wash hands thoroughly. Clean containers with bleach solution if you plan to reuse them. Never compost moldy food from this experiment.</p>
  </details>
  <details>
    <summary>What variables can I change to extend this experiment?</summary>
    <p>Try different temperatures, humidity levels, light conditions, food preparation methods (fresh vs. processed), or test natural preservatives like salt, sugar, or vinegar. Each variable can create a new investigation.</p>
  </details>
  <details>
    <summary>How does this relate to food preservation in real life?</summary>
    <p>This experiment demonstrates why we refrigerate food, use preservatives, and package foods in certain ways. Understanding spoilage rates helps explain food safety practices, expiration dates, and methods like canning, freezing, and dehydration.</p>
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