<?php
// Project: Triops - Historical Educational Project
$project_title = "Triops - Living Fossils from the Triassic Period";
$project_description = "Learn about these fascinating 208-million-year-old creatures through observation and scientific study. Understand evolution, adaptation, and biological research methods.";
$project_keywords = "triops, living fossils, triassic period, crustaceans, biology project, evolution, life cycle, observation study";
$project_grade_level = "Elementary to Middle"; // ages 8-16
$project_subject = "Biology, Paleontology";
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
    .timeline-box { background: #f0f8ff; border-left: 4px solid #2c5aa0; padding: 1rem; margin: 1rem 0; }
    .data-table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
    .data-table th, .data-table td { border: 1px solid #ddd; padding: 0.5rem; text-align: left; }
    .data-table th { background: #f5f5f5; font-weight: bold; }
    .research-questions { background: #fff8dc; border: 1px solid #daa520; padding: 1rem; margin: 1rem 0; }
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
    <p><strong>Historical Project Notice:</strong> This page describes Triops educational kits that were popular for decades. While specific commercial kits may no longer be available, Triops eggs and supplies can still be found from various educational sources, and the learning opportunities remain valuable.</p>
  </div>

  <div class="intro-section">
    <p><strong class="highlight-text">The Science Project That Comes to Life!</strong> Looking for a unique science project or a pet that no other kid has? Triassic Triops were perfect for students interested in paleontology, evolution, and hands-on biological observation.</p>
  </div>

  <div class="materials-grid">
    <div>
      <img src="TRIOPpic.jpg" alt="Triops - living fossils from the Triassic period" class="responsive-img" />
      <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #666;"><strong>Educational project for ages 8-16</strong></p>
    </div>
    <div>
      <h3 class="highlight-text">Living Fossils from 208 Million Years Ago</h3>
      
      <p>Over 208 million years ago, even before dinosaurs ruled the earth, there was the <strong>Triassic Period</strong>. During this time, small crustaceans called Triops thrived in ancient waters.</p>
      
      <p><strong>Remarkable Survival:</strong> These three-eyed creatures have remained virtually unchanged for over 200 million years, making them true "living fossils." Their eggs can survive out of water for years and still hatch when conditions are right.</p>
      
      <p class="red-text">These omnivorous crustaceans are similar to miniature horseshoe crabs and offer incredible insights into ancient life on Earth.</p>
    </div>
  </div>

  <div class="timeline-box">
    <h4 class="highlight-text">Triops Timeline & Facts:</h4>
    <ul>
      <li><strong>208+ Million Years Ago:</strong> Triops first appeared during the Triassic Period</li>
      <li><strong>Unique Features:</strong> Three eyes (two compound eyes + one simple eye)</li>
      <li><strong>Growth Rate:</strong> Can double in size each day until reaching full adult size</li>
      <li><strong>Life Span:</strong> Typically 60-90 days under optimal conditions</li>
      <li><strong>Egg Survival:</strong> Eggs can remain viable for decades in dry conditions</li>
    </ul>
  </div>

  <h3 class="section-title">What Made Triops Perfect for Science Projects:</h3>
  
  <div class="research-questions">
    <p class="highlight-text">Research Questions Students Could Investigate:</p>
    <ol>
      <li><strong>Hatching Time:</strong> How long do eggs take to hatch under different conditions?</li>
      <li><strong>Growth Patterns:</strong> How big can they grow and at what rate?</li>
      <li><strong>Environmental Factors:</strong> What climate and water temperature work best?</li>
      <li><strong>Life Span Studies:</strong> How long do they live under various conditions?</li>
      <li><strong>Optimal Conditions:</strong> What combination of factors produces healthiest Triops?</li>
    </ol>
  </div>

  <h3 class="section-title">Historical Kit Components & Setup:</h3>
  
  <div class="materials-grid">
    <div>
      <img src="triops-anglais.jpg" alt="Historical Triops kit contents and setup guide" class="responsive-img" />
    </div>
    <div>
      <div class="materials-box">
        <h4><strong>What Original Kits Included:</strong></h4>
        <ul>
          <li><strong>20+ Triops eggs</strong> - Pre-selected viable specimens</li>
          <li><strong>Specialized food</strong> - Proper nutrition for growth</li>
          <li><strong>Water conditioner</strong> - To neutralize harmful chemicals</li>
          <li><strong>Clear plastic tank</strong> - Observation container</li>
          <li><strong>Thermometer</strong> - Temperature monitoring</li>
          <li><strong>Instruction booklet</strong> - Complete care guide</li>
        </ul>
      </div>
    </div>
  </div>

  <h3 class="section-title">Setup Procedure (Historical Reference):</h3>
  
  <div class="info-box">
    <h4><strong>Critical Setup Steps:</strong></h4>
    <ol>
      <li><strong>Water Preparation:</strong> Always use distilled or spring water - never tap water due to chlorine toxicity</li>
      <li><strong>Temperature Control:</strong> Maintain 74-80°F (23-27°C) consistently</li>
      <li><strong>Lighting:</strong> Provide 12+ hours of light daily (sunlight or cool fluorescent)</li>
      <li><strong>Ventilation:</strong> Never seal tank completely - fresh air circulation required</li>
      <li><strong>Monitoring:</strong> Check temperature twice daily for optimal conditions</li>
      <li><strong>Placement:</strong> Choose undisturbed location away from vibrations</li>
    </ol>
  </div>

  <div class="center">
    <img src="triopstriassic.jpg" alt="Historical Triops educational kit packaging" class="responsive-img" style="max-width: 400px; margin: 1rem auto;" />
  </div>

  <h3 class="section-title">Educational Value & Scientific Learning:</h3>
  
  <div class="info-box">
    <p><strong>Key Learning Concepts:</strong></p>
    <ul>
      <li><strong>Evolution & Adaptation:</strong> Understanding how species survive over geological time</li>
      <li><strong>Life Cycles:</strong> Observing complete development from egg to adult</li>
      <li><strong>Environmental Biology:</strong> Effects of temperature, light, and water quality</li>
      <li><strong>Data Collection:</strong> Recording growth measurements and behavioral observations</li>
      <li><strong>Paleontology:</strong> Connecting modern organisms to ancient fossil records</li>
      <li><strong>Comparative Biology:</strong> Relating Triops to modern crustaceans</li>
    </ul>
  </div>

  <h3 class="section-title">Data Collection & Analysis:</h3>
  
  <table class="data-table">
    <thead>
      <tr>
        <th>Day</th>
        <th>Length (mm)</th>
        <th>Behavior Observed</th>
        <th>Water Temp (°F)</th>
        <th>Notes</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>1-3</td>
        <td>1-2</td>
        <td>Hatching, initial movement</td>
        <td>76-78</td>
        <td>Microscopic examination needed</td>
      </tr>
      <tr>
        <td>4-7</td>
        <td>3-6</td>
        <td>Active swimming, feeding</td>
        <td>74-80</td>
        <td>Growth rate accelerates</td>
      </tr>
      <tr>
        <td>8-14</td>
        <td>8-15</td>
        <td>Molting behavior observed</td>
        <td>74-80</td>
        <td>Shell shedding every 2-3 days</td>
      </tr>
      <tr>
        <td>15-30</td>
        <td>20-40</td>
        <td>Adult behaviors, egg laying</td>
        <td>74-80</td>
        <td>Peak activity period</td>
      </tr>
    </tbody>
  </table>

  <h3 class="section-title">Modern Educational Alternatives:</h3>
  
  <div class="historical-box">
    <p class="highlight-text">Current Options for Similar Learning:</p>
    <ul>
      <li><strong>Online Suppliers:</strong> Various educational companies still offer Triops eggs and supplies</li>
      <li><strong>Science Centers:</strong> Many museums have live Triops displays for observation</li>
      <li><strong>Virtual Labs:</strong> Computer simulations of Triops life cycles and growth</li>
      <li><strong>Video Documentation:</strong> Time-lapse photography of complete life cycles</li>
      <li><strong>Comparative Studies:</strong> Study modern crustaceans like brine shrimp or water fleas</li>
    </ul>
  </div>

  <div class="safety-box">
    <h4 class="red-text">Safety & Care Considerations:</h4>
    <ul>
      <li><strong>Water Quality:</strong> Always use dechlorinated water - chlorine is toxic</li>
      <li><strong>Temperature Stability:</strong> Sudden temperature changes can be fatal</li>
      <li><strong>Clean Environment:</strong> Maintain tank cleanliness to prevent bacterial growth</li>
      <li><strong>Proper Feeding:</strong> Overfeeding can contaminate water and harm Triops</li>
      <li><strong>Humane Care:</strong> These are living creatures requiring responsible stewardship</li>
    </ul>
  </div>

  <h3 class="section-title">Connection to Modern Science:</h3>
  
  <div class="info-box">
    <p><strong>Research Applications:</strong> Modern scientists study Triops to understand:</p>
    <ul>
      <li><strong>Evolutionary Biology:</strong> How organisms remain unchanged over millions of years</li>
      <li><strong>Developmental Biology:</strong> Rapid growth and regeneration mechanisms</li>
      <li><strong>Environmental Science:</strong> Adaptation to changing aquatic environments</li>
      <li><strong>Biotechnology:</strong> Potential applications of drought-resistant egg technology</li>
      <li><strong>Climate Research:</strong> Historical indicators of environmental conditions</li>
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
        <div id="abc_716" class="abantecart_product" data-product-id="2277" data-language="en" data-currency="USD">
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
        <div id="abc_936" class="abantecart_product" data-product-id="2480" data-language="en" data-currency="USD">
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
        <li id="abc_176056147818874" class="abantecart_category" data-category-id="74" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176056149250871" class="abantecart_category" data-category-id="71" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760561509215106" class="abantecart_category" data-category-id="106" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760561518917139" class="abantecart_category" data-category-id="139" data-language="en" data-currency="USD">
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
    <summary>Can I still get Triops for educational projects?</summary>
    <p>Yes! While specific commercial kits may be discontinued, Triops eggs and supplies are still available from various educational suppliers and online retailers. Many science teachers and homeschool suppliers carry Triops materials for classroom use.</p>
  </details>
  <details>
    <summary>How long have Triops existed on Earth?</summary>
    <p>Triops have existed for over 208 million years, making them one of the oldest living species on Earth. They survived multiple mass extinction events, including the one that killed the dinosaurs, essentially unchanged in their basic body plan.</p>
  </details>
  <details>
    <summary>What makes Triops eggs so special?</summary>
    <p>Triops eggs can enter a state called cryptobiosis, where all metabolic activity essentially stops. In this state, they can survive drought, extreme temperatures, and even decades of storage while remaining viable to hatch when conditions improve.</p>
  </details>
  <details>
    <summary>Why are Triops called "living fossils"?</summary>
    <p>Triops are called living fossils because their basic body structure and lifestyle have remained virtually unchanged for over 200 million years. Fossil Triops from the Triassic period look nearly identical to modern specimens.</p>
  </details>
  <details>
    <summary>What can students learn from raising Triops?</summary>
    <p>Students learn about evolution, adaptation, life cycles, environmental biology, data collection, and scientific observation. They also gain insights into paleontology and how modern organisms connect to ancient life forms.</p>
  </details>
  <details>
    <summary>Are there safety concerns with Triops projects?</summary>
    <p>Triops are generally safe, but students should avoid using tap water (chlorine is toxic), maintain proper water temperature, and handle the creatures gently. It's also important to maintain tank cleanliness to prevent harmful bacterial growth.</p>
  </details>
  <details>
    <summary>How do Triops compare to other aquatic pets?</summary>
    <p>Unlike fish or other aquatic pets, Triops have a short but fascinating life cycle (60-90 days), rapid growth rates, and unique behaviors like molting. They're excellent for short-term observation projects and don't require long-term commitment.</p>
  </details>
  <details>
    <summary>What modern research uses Triops?</summary>
    <p>Modern scientists study Triops for evolutionary biology research, developmental studies, environmental indicator research, and biotechnology applications. Their drought-resistant eggs are of particular interest for understanding survival mechanisms.</p>
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