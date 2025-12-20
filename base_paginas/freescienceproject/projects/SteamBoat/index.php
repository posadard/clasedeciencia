<?php
// Project: Steam Engine & Steam Boat - Historical Educational Project
$project_title = "Steam Engine & Steam Boat - Classic Physics Demonstration";
$project_description = "Learn about steam power and thermodynamics through this classic educational project. Understand how steam engines work and their historical importance in transportation and industry.";
$project_keywords = "steam engine, steam boat, thermodynamics, heat engine, physics demonstration, historical technology, candle power";
$project_grade_level = "Elementary to Middle"; // ages 10 and up with adult supervision
$project_subject = "Physics, History of Science";
$project_difficulty = "Intermediate";

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
    .project-title-simple { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color: #990000; font-size: 1.6rem; margin: 0 0 0.75rem 0; text-align: center; font-weight: bold; }
    .intro-section { background: #f8f9ff; border: 1px solid #2c5aa0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .info-box { border: 1px solid #69c; background: #f4f8ff; padding: 0.75rem; color: #0033cc; font-family: Verdana, Geneva, Tahoma, sans-serif; margin: 1rem 0; }
    .historical-box { border: 1px solid #990000; background: #fff5f5; padding: 1rem; margin: 1rem 0; }
    .safety-box { border: 2px solid #ff0000; background: #ffefef; padding: 1rem; margin: 1rem 0; }
    .materials-box { border: 1px solid #34c759; background: #f0fff0; padding: 1rem; margin: 1rem 0; }
    .section-title { color: #990000; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .highlight-text { color: #990000; font-weight: bold; }
    .red-text { color: #ff0000; font-weight: bold; }
    .materials-grid { display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem; align-items: start; margin: 1rem 0; }
    .two-col { display: grid; grid-template-columns: 1fr 320px; gap: 1rem; align-items: start; margin: 1rem 0; }
    .responsive-img { max-width: 100%; height: auto; display: block; border-radius: 8px; }
    .center { text-align: center; }
    .discontinued-notice { background: #ffec99; border: 2px solid #f1c40f; padding: 1rem; border-radius: 8px; margin: 1rem 0; text-align: center; }
    @media (max-width: 720px) {
      .materials-grid, .two-col { grid-template-columns: 1fr; }
      .banner-row { grid-template-columns: 1fr; }
    }
    /* Banner row (bottom of the page) */
    .banner-row { display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-top: 0.75rem; }
    .banner-row .banner { flex: 1 1 0; text-align: center; }
    .banner-row img { max-width: 100%; height: auto; display: inline-block; }
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
    /* Footer styles - ensure all text in footer is white */
    footer, footer *, 
    .footer, .footer *,
    .project-footer, .project-footer *,
    [class*="footer"] *, [id*="footer"] * {
      color: white !important;
      fill: white !important;
    }
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

  <div class="discontinued-notice">
    <p><strong>Historical Project Notice:</strong> This page describes a classic educational steam boat that was popular in the mid-20th century. While the original product is no longer available, the educational concepts remain valuable for understanding steam power and thermodynamics.</p>
  </div>

  <div class="intro-section">
    <p><strong class="highlight-text">Classic Steam Power Education!</strong> The steam boat was a beloved educational toy that demonstrated the fundamental principles of heat engines and thermodynamics. This simple yet effective design showed how easy it can be to harness steam power for propulsion.</p>
  </div>

  <div class="materials-grid">
    <div>
      <img src="candleboat.jpg" alt="Classic metal steam boat with candle power system" class="responsive-img" />
      <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;"><strong>For ages 10 or more. Adult supervision required.</strong></p>
    </div>
    <div>
      <h3 class="highlight-text">Historical Steam Boat Design</h3>
      
      <p>This metal steam boat was a classic educational toy that showed how <strong>steam engines work in practice</strong>. The simple design made it easy to understand the basic principles while being safe enough for educational use.</p>
      
      <p><strong>Educational Value:</strong> If you were planning to design and build your own steam engine, this steam boat served as an excellent starting point for experimentation and understanding the fundamental concepts.</p>
      
      <p class="red-text">Note: The original product featured in historical photos is no longer manufactured, but the educational principles remain valuable for modern learning.</p>
    </div>
  </div>

  <h3 class="section-title">What the Original Steam Boat Included:</h3>
  
  <div class="historical-box">
    <p class="highlight-text">Original Kit Components:</p>
    <ul>
      <li><strong>Metal boat hull</strong> - Lightweight yet durable construction</li>
      <li><strong>Removable candle holder</strong> - Safe mounting system for heat source</li>
      <li><strong>6 small candles</strong> - Fuel for the steam generation</li>
      <li><strong>Dropper assembly</strong> - Water injection and steam escape system</li>
    </ul>
  </div>

  <h3 class="section-title">Additional Materials That Were Required:</h3>
  
  <div class="materials-box">
    <p><strong>Users needed to provide:</strong></p>
    <ul>
      <li><strong>Matches or lighter</strong> - To ignite the candles</li>
      <li><strong>Water</strong> - To create steam for propulsion</li>
      <li><strong>Adult supervision</strong> - Essential for safety</li>
      <li><strong>Large container or bathtub</strong> - For testing the boat</li>
    </ul>
  </div>

  <h3 class="section-title">How Steam Boats Work - The Science:</h3>
  
  <div class="info-box">
    <h4><strong>Thermodynamic Principles:</strong></h4>
    <p><strong>1. Heat Input:</strong> The candle flame heats water in a small chamber or tube system.</p>
    <p><strong>2. Phase Change:</strong> Liquid water absorbs heat energy and transforms into steam (water vapor).</p>
    <p><strong>3. Pressure Generation:</strong> Steam occupies much more volume than liquid water, creating pressure.</p>
    <p><strong>4. Propulsion:</strong> Pressurized steam escapes through nozzles, creating thrust that propels the boat forward.</p>
    <p><strong>5. Cycle Repeats:</strong> As steam escapes, more water is drawn in to continue the cycle.</p>
  </div>

  <div class="center">
    <img src="candleboat1.jpg" alt="Alternative view of steam boat operation" class="responsive-img" style="max-width: 400px; margin: 1rem auto;" />
  </div>

  <h3 class="section-title">Building Your Own Steam Boat (Advanced Project):</h3>
  
  <div class="historical-box">
    <p class="highlight-text">DIY Construction Approach:</p>
    <p>You may build a working model of a steam boat using <strong>thin and soft sheet copper or brass</strong>. These materials have excellent heat conduction properties and are workable by hand.</p>
    
    <p><strong>Construction Tips:</strong></p>
    <ul>
      <li>Thin metal sheets can be cut using household scissors</li>
      <li>Copper and brass can be bent by hand for shaping</li>
      <li>Parts can be joined using a small torch or soldering gun</li>
      <li>Create coiled tubes for efficient heat exchange</li>
    </ul>
  </div>

  <div class="safety-box">
    <h4 class="red-text">Critical Safety Requirements:</h4>
    <p class="red-text"><strong>Safety precautions, protective clothing, and adult supervision are absolutely required for any steam-powered project.</strong></p>
    
    <ul>
      <li><strong>Fire Safety:</strong> Always have water or fire extinguisher nearby</li>
      <li><strong>Burn Protection:</strong> Wear heat-resistant gloves when handling hot components</li>
      <li><strong>Eye Protection:</strong> Safety glasses required when working with tools</li>
      <li><strong>Ventilation:</strong> Ensure adequate air circulation</li>
      <li><strong>Adult Supervision:</strong> Never attempt this project without experienced adult guidance</li>
    </ul>
  </div>

  <h3 class="section-title">Educational Extensions & Modern Applications:</h3>
  
  <div class="info-box">
    <p><strong>Learning Opportunities:</strong></p>
    <ul>
      <li><strong>History of Transportation:</strong> Research how steam power revolutionized travel</li>
      <li><strong>Industrial Revolution:</strong> Study steam engines' role in industrialization</li>
      <li><strong>Thermodynamics:</strong> Calculate efficiency of heat engines</li>
      <li><strong>Environmental Impact:</strong> Compare steam power to modern propulsion</li>
      <li><strong>Engineering Design:</strong> Optimize boiler and nozzle configurations</li>
    </ul>
  </div>

  <h3 class="section-title">Historical Context:</h3>
  
  <div class="historical-box">
    <p><strong>Steam Power Legacy:</strong> Steam boats like Robert Fulton's "Clermont" (1807) revolutionized river transportation and commerce. These educational models helped students understand the same principles that powered:</p>
    <ul>
      <li><strong>Steamships:</strong> Transatlantic passenger and cargo vessels</li>
      <li><strong>River Boats:</strong> Mississippi River commerce and exploration</li>
      <li><strong>Steam Locomotives:</strong> Railroad development across continents</li>
      <li><strong>Factory Engines:</strong> Industrial manufacturing power systems</li>
    </ul>
  </div>

  <h3 class="section-title">Modern Alternatives:</h3>
  
  <div class="info-box">
    <p>While the original candle-powered steam boat is no longer available, you can explore similar concepts with:</p>
    <ul>
      <li><strong>Model Steam Engines:</strong> Modern educational kits with improved safety</li>
      <li><strong>Stirling Engines:</strong> External combustion engines with similar principles</li>
      <li><strong>Steam Demonstrations:</strong> Laboratory-grade equipment for classroom use</li>
      <li><strong>Computer Simulations:</strong> Virtual thermodynamics experiments</li>
    </ul>
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
        <div id="abc_803" class="abantecart_product" data-product-id="1537" data-language="en" data-currency="USD">
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
        <div id="abc_423" class="abantecart_product" data-product-id="173" data-language="en" data-currency="USD">
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
        <div id="abc_891" class="abantecart_product" data-product-id="7290" data-language="en" data-currency="USD">
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
        <li id="abc_1760559001105103" class="abantecart_category" data-category-id="103" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760559018269127" class="abantecart_category" data-category-id="127" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760559032425126" class="abantecart_category" data-category-id="126" data-language="en" data-currency="USD">
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
    <summary>Why is this steam boat no longer available?</summary>
    <p>The original candle-powered steam boat was discontinued due to modern safety regulations and liability concerns. While educationally valuable, open flames and hot steam present risks that are difficult to mitigate in a consumer product. Modern alternatives focus on safer demonstration methods.</p>
  </details>
  <details>
    <summary>How did the original steam boat actually work?</summary>
    <p>The boat used a simple heat engine principle: candles heated water in a small boiler or coiled tube, creating steam pressure that escaped through nozzles to provide thrust. The clever design used the boat's motion to help draw in fresh water for continuous operation.</p>
  </details>
  <details>
    <summary>Can I still build a steam boat myself?</summary>
    <p>Yes, but it requires careful attention to safety and adult supervision. Modern makers use electric heating elements, proper pressure relief valves, and safer materials. Consider starting with Stirling engines or other external combustion engines that are inherently safer.</p>
  </details>
  <details>
    <summary>What made this educational toy so effective for learning?</summary>
    <p>The steam boat provided immediate visual feedback - students could see the direct relationship between heat input and motion output. The simple design made thermodynamic principles tangible and observable, which is why it remained popular for decades.</p>
  </details>
  <details>
    <summary>Are there modern alternatives that teach the same concepts?</summary>
    <p>Yes! Modern options include Stirling engines, educational steam engine kits with improved safety features, thermal-powered toys, and computer simulations. These provide similar learning outcomes with much better safety profiles.</p>
  </details>
  <details>
    <summary>What safety issues led to discontinuation of these products?</summary>
    <p>Primary concerns included burns from hot metal and steam, fire hazards from open flames, and potential pressure-related injuries. Modern safety standards require better risk mitigation than was practical with the simple candle-powered design.</p>
  </details>
  <details>
    <summary>How do modern steam demonstrations work in classrooms?</summary>
    <p>Modern educational steam demonstrations use electric heating elements with temperature controls, pressure relief systems, and clear protective barriers. They maintain the educational value while meeting current safety requirements for classroom use.</p>
  </details>
  <details>
    <summary>What historical impact did steam boats have on society?</summary>
    <p>Steam boats revolutionized transportation, enabling reliable river commerce, westward expansion in America, and transcontinental trade routes. They were crucial to the Industrial Revolution and economic development of the 19th century.</p>
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