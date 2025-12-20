<?php
// Project: Magnetic Levitation Platform Experiment Kit
$project_title = "Magnetic Levitation Platform Experiment Kit";
$project_description = "Hands-on kit explores magnetic forces, weight, and levitation principles. Adjustable magnetic platform levitates above a sturdy magnetic base with no physical contact, demonstrating force, gravity, and equilibrium.";
$project_keywords = "magnetic levitation platform, magnetic forces, weight relationships, levitation experiment, STEM education, magnetic equilibrium, frictionless levitation";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Physics, Magnetism, STEM";
$project_difficulty = "Elementary";

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
    .product-showcase { display: grid; grid-template-columns: 2fr 3fr; gap: 1rem; align-items: start; margin: 1rem 0; background: #f0f8ff; padding: 1rem; border-radius: 8px; }
    .hero-section img { width: 100%; height: auto; display: block; }
    .features-section { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; margin: 1rem 0; }
    .features-list { background: #f0fff0; padding: 1rem; border-radius: 8px; }
    .specifications { background: #fff6f0; padding: 1rem; border-radius: 8px; }
    .components-section { display: grid; grid-template-columns: 2fr 3fr; gap: 1rem; align-items: start; margin: 1rem 0; background: #f8fff8; padding: 1rem; border-radius: 8px; }
    .components-section img { width: 100%; height: auto; display: block; }
    .instructions-section { display: grid; grid-template-columns: 3fr 2fr; gap: 1rem; align-items: start; margin: 1rem 0; background: #fff8f0; padding: 1rem; border-radius: 8px; }
    .instructions-section img { width: 100%; height: auto; display: block; }
    .safety-section { background: #ffe6e6; border: 1px solid #ff4444; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .applications-section { background: #f0f0ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
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
      .product-showcase { grid-template-columns: 1fr; text-align: center; }
      .features-section { grid-template-columns: 1fr; }
      .components-section { grid-template-columns: 1fr; text-align: center; }
      .instructions-section { grid-template-columns: 1fr; text-align: center; }
      .banner-row { flex-direction: column; gap: 0.5rem; }
    }
    /* Modern Buy Now Button */
    .buy-now-btn { 
      display: inline-block; 
      background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); 
      color: #000; 
      padding: 0.75rem 1.5rem; 
      border-radius: 8px; 
      text-decoration: none; 
      font-weight: 600; 
      font-family: inherit;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(255, 107, 53, 0.3);
      position: relative;
      z-index: 10;
      cursor: pointer;
    }
    .buy-now-btn:hover { 
      background: linear-gradient(135deg, #e55100 0%, #ff6b35 100%); 
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4);
      color: #000;
      text-decoration: none;
    }
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
    <p>The <strong class="highlight-text">Magnetic Levitation Platform Experiment Kit</strong> is a unique tool for exploring the fascinating physics of magnetic levitation. This hands-on kit enables students, educators, and enthusiasts to dive into STEM learning while discovering the interplay of magnetic forces and weight.</p>
    
    <p>The adjustable magnetic platform levitates above a sturdy magnetic base with <strong>no physical contact</strong>, demonstrating the principles of force, gravity, and equilibrium.</p>
  </div>

  <div class="product-showcase">
    <div>
      <img src="2.png" alt="Magnetic Levitation Platform Experiment Kit demonstration" class="responsive-img">
    </div>
    <div>
      <h3 class="section-title">Hands-on Learning Experience</h3>
      <p>The kit includes a <strong>built-in measurement scale</strong> for precise data collection, allowing users to record changes in levitation height as weights are added to the platform.</p>
      
      <p>Designed for safe use by individuals <strong>aged 14 and up</strong> (with adult supervision), this educational tool offers an engaging way to foster curiosity and critical thinking.</p>
      
      <p>Whether for classroom use, science fairs, or personal experimentation, this kit provides a perfect balance of education and fun.</p>
      
      <div class="center" style="margin-top: 1.5rem;">
        <a href="https://shop.miniscience.com/MAGPRING" class="buy-now-btn">Buy Now</a>
      </div>
    </div>
  </div>

  <div class="features-section">
    <div class="features-list">
      <h3 class="section-title">Key Features</h3>
      <ul>
        <li><strong>Adjustable magnetic platform</strong> for frictionless levitation experiments</li>
        <li><strong>Built-in ruler</strong> for precise height measurements</li>
        <li><strong>Encourages hands-on learning</strong> of magnetic forces and weight relationships</li>
        <li><strong>Safe and engaging</strong> for ages 14+ (adult supervision required)</li>
        <li><strong>Perfect for science fairs</strong>, classrooms, and STEM workshops</li>
        <li><strong>Compact design</strong> - excellent conversation starter or desk accessory</li>
      </ul>
    </div>
    <div class="specifications">
      <h3 class="section-title">Product Specifications</h3>
      <table style="width: 100%; border-collapse: collapse;">
        <tr style="border-bottom: 1px solid #ddd;">
          <td style="padding: 0.5rem; font-weight: bold;">Type</td>
          <td style="padding: 0.5rem;">Magnetic Levitation Experiment</td>
        </tr>
        <tr style="border-bottom: 1px solid #ddd;">
          <td style="padding: 0.5rem; font-weight: bold;">Recommended Age</td>
          <td style="padding: 0.5rem;">14+ (Adult supervision required)</td>
        </tr>
        <tr style="border-bottom: 1px solid #ddd;">
          <td style="padding: 0.5rem; font-weight: bold;">Included Materials</td>
          <td style="padding: 0.5rem;">6 ring magnets, 1 wooden model platform, 1 steel sphere (1/2", 8.4g), 4 steel spheres (1/4", 1g each)</td>
        </tr>
        <tr>
          <td style="padding: 0.5rem; font-weight: bold;">Safety Information</td>
          <td style="padding: 0.5rem;">Small and sharp parts. Adult supervision required.</td>
        </tr>
      </table>
    </div>
  </div>

  <div class="components-section">
    <div>
      <h3 class="section-title">What's Included</h3>
      <p>All necessary components are included, so you can begin your experiments right away:</p>
      <ul>
        <li><strong>6 Ring Magnets</strong> - Ceramic magnets for stable levitation</li>
        <li><strong>1 Wooden Model Platform</strong> - Precision-crafted base with measurement scale</li>
        <li><strong>1 Steel Sphere (1/2", 8.4g)</strong> - Primary weight for experiments</li>
        <li><strong>4 Steel Spheres (1/4", 1g each)</strong> - Fine-tuning weights</li>
        <li><strong>Assembly Instructions</strong> - Step-by-step guide</li>
      </ul>
      <p><strong>Ready to use:</strong> No additional materials needed!</p>
    </div>
    <div>
      <img src="3.png" alt="Magnetic levitation kit components" class="responsive-img">
    </div>
  </div>

  <div class="instructions-section">
    <div>
      <h3 class="section-title">How It Works</h3>
      <ol>
        <li><strong>Setup:</strong> Arrange the ring magnets on the wooden platform base according to the instructions</li>
        <li><strong>Levitation:</strong> Position the upper platform so it floats above the magnetic base</li>
        <li><strong>Measurement:</strong> Use the built-in ruler to record the initial levitation height</li>
        <li><strong>Experimentation:</strong> Add steel spheres one by one and measure height changes</li>
        <li><strong>Data Collection:</strong> Record your observations and analyze the weight-to-height relationship</li>
      </ol>
      <p><strong>Scientific Principle:</strong> The magnetic repulsion force balances against gravity and the added weight, creating a stable levitation point that changes predictably with load.</p>
    </div>
    <div>
      <img src="1.png" alt="Assembly and usage instructions" class="responsive-img">
    </div>
  </div>

  <div class="safety-section">
    <h3 class="section-title" style="color: #cc0000;">⚠️ Safety Information</h3>
    <ul>
      <li><strong>Adult supervision required</strong> for children under 12</li>
      <li><strong>Small parts warning:</strong> Contains small steel spheres - choking hazard for young children</li>
      <li><strong>Magnetic safety:</strong> Keep away from electronic devices, credit cards, and pacemakers</li>
      <li><strong>Handle with care:</strong> Ceramic magnets can chip if dropped</li>
    </ul>
  </div>

  <div class="applications-section">
    <h3 class="section-title">Educational Applications</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
      <div>
        <h4>🏫 Classroom Use</h4>
        <p>Demonstrate magnetic forces, equilibrium, and data collection in physics lessons</p>
      </div>
      <div>
        <h4>🏆 Science Fairs</h4>
        <p>Perfect project for exploring weight-force relationships with measurable results</p>
      </div>
      <div>
        <h4>🔬 STEM Workshops</h4>
        <p>Hands-on experimentation that combines physics concepts with practical measurement</p>
      </div>
      <div>
        <h4>🏠 Home Learning</h4>
        <p>Engaging educational tool that doubles as an impressive desk display</p>
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
  
  <!-- Kit/Products Section (arriba) -->
  <div class="related-grid" style="margin-bottom: 1.5rem;">
    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <div style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <div id="abc_458" class="abantecart_product" data-product-id="7295" data-language="en" data-currency="USD">
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
        <div id="abc_507" class="abantecart_product" data-product-id="7298" data-language="en" data-currency="USD">
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
        <div id="abc_997" class="abantecart_product" data-product-id="1833" data-language="en" data-currency="USD">
          <div class="abantecart_image"></div>
          <h3 class="abantecart_name"></h3>
          <div class="abantecart_blurb"></div>
          <div class="abantecart_rating"></div>
          <div class="abantecart_addtocart"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Related Categories Section (abajo) -->
  <h4 style="margin-top: 1rem; margin-bottom: 0.75rem; color: #2c5aa0; font-size: 1.1rem;">Related Categories</h4>
  <div class="related-grid">
    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760473531330103" class="abantecart_category" data-category-id="103" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760473549088130" class="abantecart_category" data-category-id="130" data-language="en" data-currency="USD">
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
    <summary>How does the magnetic levitation platform work?</summary>
    <p>The platform uses magnetic repulsion between carefully arranged ring magnets. The upper platform floats because the magnetic force pushing it away from the base balances exactly with gravity and any added weight, creating a stable levitation point.</p>
  </details>
  <details>
    <summary>What can I learn from this experiment?</summary>
    <p>This kit teaches magnetic force principles, equilibrium physics, data collection methods, and weight-to-force relationships. Students learn to measure, record data, and analyze how adding weight affects the levitation height in predictable ways.</p>
  </details>
  <details>
    <summary>Is this safe for children to use?</summary>
    <p>Yes, with proper adult supervision. The kit is designed for ages 14+, but contains small parts that require careful handling. The magnets are ceramic (not super-strong neodymium), making them safer while still effective for the experiments.</p>
  </details>
  <details>
    <summary>How accurate are the measurements?</summary>
    <p>The built-in ruler provides measurements accurate to approximately 1mm, which is sufficient for educational experiments. Students can observe clear changes in levitation height as weights are added, making the physics principles visible and measurable.</p>
  </details>
  <details>
    <summary>Can this be used for science fair projects?</summary>
    <p>Absolutely! This kit is perfect for science fair projects exploring magnetic forces, equilibrium, or weight relationships. Students can create hypotheses, collect data, and present measurable results that demonstrate clear scientific principles.</p>
  </details>
  <details>
    <summary>What makes this different from simple floating rings?</summary>
    <p>Unlike basic floating ring demonstrations, this platform includes precise measurement capabilities, calibrated weights, and a structured experimental approach. It's designed for quantitative learning rather than just visual demonstration.</p>
  </details>
  <details>
    <summary>How much weight can the platform support while levitating?</summary>
    <p>The platform can support the included steel spheres (up to about 12.4g total) while maintaining stable levitation. Adding too much weight will cause the platform to settle onto the base, which itself demonstrates the limits of magnetic force.</p>
  </details>
  <details>
    <summary>Do I need any additional materials?</summary>
    <p>No additional materials are required! The kit includes everything needed: magnets, platform, weights, and instructions. This makes it perfect for classroom use where setup time is limited.</p>
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