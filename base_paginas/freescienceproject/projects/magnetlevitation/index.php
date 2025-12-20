<?php
// Project: Magnet Levitation Set (Magnetic Levitating Train)
$project_title = "Magnet Levitation Set - Magnetic Levitating Train";
$project_description = "Build gravity-defying magnetic levitation experiments including floating trains, magnetic field visualization, and suspension apparatus. Complete kit with 20 ceramic magnets, neodymium magnet, and comprehensive instructions.";
$project_keywords = "magnetic levitation, levitating train, magnetic field, equilibrium, maglev, STEM education, physics experiment, magnetic suspension";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Physics, Magnetism, Engineering";
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
    .project-title-simple { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color: #2c5aa0; font-size: 1.5rem; margin: 0 0 0.75rem 0; }
    .intro-section { background: #f8f9ff; border: 1px solid #2c5aa0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .hero-section { display: grid; grid-template-columns: 3fr 2fr; gap: 1rem; align-items: start; margin: 1rem 0; background: #f0f8ff; padding: 1rem; border-radius: 8px; }
    .hero-section img { width: 100%; height: auto; display: block; }
    .features-section { display: grid; grid-template-columns: 2fr 3fr; gap: 1rem; align-items: start; margin: 1rem 0; }
    .features-list { background: #f0fff0; padding: 1rem; border-radius: 8px; }
    .kit-contents { background: #fff6f0; padding: 1rem; border-radius: 8px; }
    .experiments-section { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; margin: 1rem 0; background: #f8fff8; padding: 1rem; border-radius: 8px; }
    .experiments-section img { width: 100%; height: auto; display: block; }
    .materials-section { background: #fff8f0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .science-fair-section { background: #f0f0ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
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
      .hero-section { grid-template-columns: 1fr; text-align: center; }
      .features-section { grid-template-columns: 1fr; }
      .experiments-section { grid-template-columns: 1fr; text-align: center; }
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
    <p>The <strong class="highlight-text">Magnet Levitation Science Set</strong> contains all the materials you need to perform many different experiments related to magnets and magnetic fields. Learn about equilibrium and magnetic fields while building gravity-defying trains and suspension apparatus.</p>
    
    <p>These materials can also be used in your presentations or as part of your science fair display. The kit includes comprehensive online instructions that are always kept up-to-date with the latest experimental procedures.</p>
  </div>

  <div class="hero-section">
    <div>
      <h3 class="section-title">Magnetic Levitating Train Kit</h3>
      <p>Build an amazing <strong>magnetic levitating train</strong> that floats above its track using the power of magnetic repulsion. This hands-on kit demonstrates the same principles used in real-world maglev transportation systems.</p>
      
      <p>Perfect for students aged <strong>10 and up</strong> who want to explore the fascinating world of magnetic forces, equilibrium, and engineering principles.</p>
      
      <p>The kit includes everything needed to complete multiple experiments, from basic magnetic field visualization to advanced levitation projects.</p>
      
      <div class="center" style="margin-top: 1.5rem;">
        <a href="https://shop.miniscience.com/kitml" class="buy-now-btn">Buy Complete Kit</a>
      </div>
    </div>
    <div>
      <img src="Magnet_levitating_train_m.jpg" alt="Magnetic levitating train demonstration" class="responsive-img">
    </div>
  </div>

  <div class="features-section">
    <div class="features-list">
      <h3 class="section-title">Experiments Included</h3>
      <ul>
        <li><strong>Magnetic Levitating Train</strong> - Build a train that floats above its track</li>
        <li><strong>Floating Rings</strong> - Demonstrate magnetic repulsion with ring magnets</li>
        <li><strong>Print the Magnetic Field</strong> - Visualize invisible magnetic fields using iron filings</li>
        <li><strong>Magnet Suspension Apparatus</strong> - Create stable magnetic suspension systems</li>
        <li><strong>Equilibrium Studies</strong> - Investigate balance and stability in magnetic systems</li>
      </ul>
    </div>
    <div class="kit-contents">
      <h3 class="section-title">Complete Kit Contents</h3>
      <ul>
        <li><strong>20 Ceramic Magnets</strong> - Various sizes for different experiments</li>
        <li><strong>Super-strong NEODYMIUM Magnet</strong> - For advanced demonstrations</li>
        <li><strong>Hi-force Magnetic Strips</strong> - Flexible magnetic materials</li>
        <li><strong>Plastic Guide Rails</strong> - Track system for the levitating train</li>
        <li><strong>Compass</strong> - For magnetic field direction studies</li>
        <li><strong>Iron Filings</strong> - Visualize magnetic field patterns</li>
        <li><strong>Wood Block</strong> - Base for building experiments</li>
        <li><strong>Wooden Dowel</strong> - Construction material</li>
        <li><strong>Online Instructions</strong> - Always up-to-date procedures</li>
      </ul>
    </div>
  </div>

  <div class="experiments-section">
    <div>
      <img src="Magnet_levitating_train_parts_large.jpg" alt="Complete kit contents and components" class="responsive-img">
    </div>
    <div>
      <h3 class="section-title">Build Your Levitating Train</h3>
      <p>The magnetic levitating train is the centerpiece experiment that demonstrates how magnetic forces can overcome gravity to create stable, floating motion.</p>
      
      <p>Students learn about:</p>
      <ul>
        <li><strong>Magnetic repulsion</strong> and attraction forces</li>
        <li><strong>Equilibrium</strong> and balance in physical systems</li>
        <li><strong>Engineering design</strong> and problem-solving</li>
        <li><strong>Real-world applications</strong> of magnetic levitation</li>
      </ul>
      
      <p>The initial train you build provides a foundation for more advanced decorative and functional modifications.</p>
    </div>
  </div>

  <div class="materials-section">
    <h3 class="section-title">Additional Materials Required</h3>
    <p class="highlight-text">Common household items needed for complete experiments:</p>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1rem;">
      <div>
        <ul>
          <li>Clear adhesive tape</li>
          <li>String/thread</li>
          <li>1 book</li>
          <li>1 Nickel (US five cent piece)</li>
          <li>1 US dollar bill</li>
          <li>5 US pennies</li>
          <li>6 Small paper clips</li>
          <li>Several magazines</li>
          <li>1 piece of paper (8.5 x 11")</li>
          <li>Lightweight tape</li>
          <li>2 US quarters</li>
          <li>Sheet of sandpaper</li>
        </ul>
      </div>
      <div>
        <img src="Levitating_train_block.jpg" alt="Basic levitating train construction" class="responsive-img">
        <p style="font-size: 0.9rem; margin-top: 0.5rem; color: #666;"><em>Initial levitating train construction. You can build and paint a decorative train to mount above your plain train block.</em></p>
      </div>
    </div>
  </div>

  <div class="science-fair-section">
    <h3 class="section-title">Science Fair Project Opportunities</h3>
    <p>Many questions explored in the Magnet Levitation Projects can serve as the <strong>"Problem to be solved"</strong> in a science project. Structure your project with:</p>
    
    <ol>
      <li><strong>Problem Statement</strong> - What magnetic phenomenon do you want to investigate?</li>
      <li><strong>Hypothesis</strong> - Your educated guess about the answer</li>
      <li><strong>Procedure</strong> - Step-by-step method to test your hypothesis</li>
      <li><strong>Conclusion</strong> - Results based on actual observations</li>
      <li><strong>Further Research</strong> - Propose additional investigations</li>
    </ol>
    
    <p>Since magnets are visually captivating as they interact with each other, it's strongly suggested that your presentation include the actual apparatus used in your research for maximum impact.</p>
  </div>

  <div class="center" style="margin: 2rem 0;">
    <img src="Magnet_levitating_train_set_1.jpg" alt="Complete magnetic levitation science set" class="responsive-img" style="max-width: 400px;">
    <div style="margin-top: 1rem;">
      <a href="https://shop.miniscience.com/kitml" class="buy-now-btn">Order Your Kit Today</a>
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
        <div id="abc_341" class="abantecart_product" data-product-id="7332" data-language="en" data-currency="USD">
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
        <div id="abc_998" class="abantecart_product" data-product-id="1707" data-language="en" data-currency="USD">
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
        <div id="abc_180" class="abantecart_product" data-product-id="1838" data-language="en" data-currency="USD">
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
    <summary>How does magnetic levitation work?</summary>
    <p>Magnetic levitation works by using magnetic repulsion forces to counteract gravity. When magnets with the same poles face each other, they push apart with enough force to lift and suspend objects in mid-air, creating the levitation effect.</p>
  </details>
  <details>
    <summary>What age group is this kit suitable for?</summary>
    <p>This kit is recommended for students aged 10 and up, with adult supervision for younger children. The experiments range from basic magnetic demonstrations to more complex engineering challenges suitable for middle and high school students.</p>
  </details>
  <details>
    <summary>Are the magnets safe to use?</summary>
    <p>Yes, the kit includes both ceramic magnets (which are safer) and one neodymium magnet for advanced experiments. Always follow safety guidelines: keep magnets away from electronic devices, pacemakers, and credit cards. Adult supervision is recommended.</p>
  </details>
  <details>
    <summary>Can this be used for science fair projects?</summary>
    <p>Absolutely! The kit provides excellent opportunities for science fair projects. You can investigate magnetic field strength, levitation height vs. weight, magnetic shielding, or compare different magnet types. The visual nature makes for compelling presentations.</p>
  </details>
  <details>
    <summary>What makes this different from simple floating ring toys?</summary>
    <p>This is a complete educational kit with multiple experiments, comprehensive instructions, and scientific explanations. It includes materials for train construction, field visualization, and quantitative measurements - not just simple demonstrations.</p>
  </details>
  <details>
    <summary>Do I need additional tools or materials?</summary>
    <p>The kit includes all specialized materials. You'll need common household items like tape, string, paper clips, and coins - all listed in the materials section. No special tools or expensive equipment required.</p>
  </details>
  <details>
    <summary>How long do the experiments take to complete?</summary>
    <p>Individual experiments can be completed in 30-60 minutes each. The full levitating train project may take 2-3 hours to build and perfect. The kit provides weeks of educational exploration and experimentation.</p>
  </details>
  <details>
    <summary>Are instructions provided online?</summary>
    <p>Yes, comprehensive instructions are provided online and are regularly updated. Your kit includes the specific web address to access the latest procedures, ensuring you always have the most current experimental methods.</p>
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