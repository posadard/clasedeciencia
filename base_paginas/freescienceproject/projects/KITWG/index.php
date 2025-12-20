<?php
// Project: Wooden Electric Generator
$project_title = "Wooden Electric Generator Science Kit";
$project_description = "Build a working wooden electric generator that converts mechanical energy into electrical energy. Learn electromagnetic induction principles through hands-on construction and experimentation with coils, magnets, and wood components.";
$project_keywords = "wooden generator, electric generator, electromagnetic induction, mechanical energy, electrical energy, DIY generator, science project, coil construction";
$project_grade_level = "Middle"; // recommended
$project_subject = "Physics, Engineering, Electromagnetism";
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
    .intro-section { display: grid; grid-template-columns: 2fr 3fr; gap: 1rem; align-items: start; background: #f8f9ff; border: 1px solid #0000ff; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .intro-section img { width: 100%; height: auto; display: block; }
    .questions-section { background: #f0f8f0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .materials-section { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; margin: 1rem 0; }
    .materials-list { background: #fff6f0; padding: 1rem; border-radius: 8px; }
    .preparation-section { background: #f0f0ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .procedure-section { background: #f8fff8; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .step-images { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: center; margin: 1rem 0; }
    .kit-showcase { display: grid; grid-template-columns: 3fr 2fr; gap: 1rem; align-items: center; margin: 1rem 0; background: #f8f9ff; padding: 1rem; border-radius: 8px; border: 1px solid #0000ff; }
    .kit-showcase img { width: 100%; height: auto; display: block; }
    .parts-showcase { display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 1rem; align-items: center; margin: 1rem 0; background: #fff8f0; padding: 1rem; border-radius: 8px; }
    .parts-showcase img { width: 100%; height: auto; display: block; }
    .purchase-buttons { display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap; align-items: center; margin-top: 1rem; }
    .purchase-buttons img { height: 33px; width: auto; }
    .responsive-img { max-width: 100%; height: auto; display: block; }
    .section-title { color: #0000ff; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .warning-text { color: #ff0000; font-weight: bold; background: #ffe6e6; padding: 0.5rem; border-radius: 4px; }
    .center { text-align: center; }
    /* Banner row (bottom of the page) */
    .banner-row { display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-top: 0.75rem; }
    .banner-row .banner { flex: 1 1 0; text-align: center; }
    .banner-row img { max-width: 100%; height: auto; display: inline-block; }
    @media (max-width: 720px) {
      .intro-section { grid-template-columns: 1fr; text-align: center; }
      .materials-section { grid-template-columns: 1fr; }
      .step-images { grid-template-columns: 1fr; }
      .kit-showcase { grid-template-columns: 1fr; text-align: center; }
      .parts-showcase { grid-template-columns: 1fr; text-align: center; }
      .banner-row { flex-direction: column; gap: 0.5rem; }
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
    /* Modern Buy Now Button */
    .buy-now-btn { 
      display: inline-block; 
      background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); 
      color: white; 
      padding: 0.75rem 1.5rem; 
      border-radius: 8px; 
      text-decoration: none; 
      font-weight: 600; 
      font-family: inherit;
      transition: all 0.3s ease;
      box-shadow: 0 2px 8px rgba(255, 107, 53, 0.3);
    }
    .buy-now-btn:hover { 
      background: linear-gradient(135deg, #e55100 0%, #ff6b35 100%); 
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(255, 107, 53, 0.4);
      color: white;
      text-decoration: none;
    }
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
    <div>
      <a href="https://shop.miniscience.com/kitwg"><img src="KITWG.jpg" alt="Wooden electric generator science kit" class="responsive-img"></a>
    </div>
    <div>
      <p><strong style="color: #0000ff;">Wooden Generator Science Kit</strong></p>
      <p>Making an electric generator is a good way of learning the principles of generators. It also is an exciting science project.</p>
      <p>As a <strong>display project</strong>, you just need to make it and demonstrate its structure. As an <strong>experimental project</strong>, you need to come up with questions about the factors that may affect the rate of production of electricity.</p>
      <p><strong>Build your own working generator from wood and basic materials!</strong></p>
    </div>
  </div>

  <div class="questions-section">
    <h3 class="section-title">Experimental Project Questions</h3>
    <p>If you want to do this as an experimental project, following are some suggested questions:</p>
    <ol>
      <li><strong>How does the speed of turning affect the production of electricity?</strong></li>
      <li><strong>How does the diameter of wire coil affect the amount of electricity?</strong></li>
      <li><strong>How does the number of loops of wire in the coil affect the amount of electricity?</strong></li>
      <li><strong>How does the diameter of coil wire affect the electric current?</strong></li>
      <li><strong>How do the materials used in the construction of an electric generator affect the production of electricity?</strong></li>
    </ol>
    
    <h4 style="color: #0000ff; margin-top: 1rem;">Hypothesis:</h4>
    <p>Depending on the question that you select, you may predict an answer. That is called your hypothesis.</p>
    
    <h4 style="color: #0000ff; margin-top: 1rem;">Dependent and Independent Variables</h4>
    <p>The factor that you are testing is your <strong>independent variable</strong>. For example the speed of turning and diameter of wire are samples of independent variables. The rate of production of electricity is the <strong>dependent variable</strong>.</p>
  </div>

  <div class="materials-section">
    <div class="materials-list">
      <h3 class="section-title">Materials Required:</h3>
      <p>Following are the materials that you need in order to construct a wooden electric generator:</p>
      <ol>
        <li>Wood dowel 3/8" diameter</li>
        <li>Wood Dowel 1" diameter</li>
        <li>Rod magnet 3" long</li>
        <li>Insulated copper wire</li>
        <li>1.2 Volt Screw Base light Bulb</li>
        <li>Base for the light bulb</li>
        <li>Small sand paper</li>
        <li>Wood Glue</li>
        <li>1/2 Square foot Balsa wood (1/8" diameter)</li>
      </ol>
    </div>
    <div>
      <h3 class="section-title">Kit Advantages:</h3>
      <p>If you are buying a kit, all the wooden parts are included and they are already cut to the size. So you just need to connect them.</p>
      <p><strong>Time-saving:</strong> Pre-cut parts eliminate the need for precision cutting and drilling.</p>
      <p><strong>Safety:</strong> No power tools required - just assembly and wiring.</p>
      <p><strong>Quality:</strong> Professional-grade components ensure better performance.</p>
    </div>
  </div>

  <div class="preparation-section">
    <h3 class="section-title">Preparation (DIY Version):</h3>
    <p>If you don't have a kit, prepare the wooden parts as follows:</p>
    <ol>
      <li>Cut two square pieces from the balsa wood (3.5" x 3.5")</li>
      <li>Make a 3/8" hole in the center of each square</li>
      <li>Cut four 1" x 3 7/16" pieces</li>
      <li>Cut a 3/4" piece from the 1" wood dowel. Make a 3/8" hole in the center of it. Insert a 6" long 3/8" wood dowel in the hole, apply some glue, center it and wait for it to dry</li>
      <li>Make another hole with the diameter of your rod magnet in the center of the larger wood dowel piece for the magnet to go through</li>
    </ol>
    
    <div class="step-images">
      <div>
        <p><strong>Wood dowels after completing step 4:</strong></p>
        <img src="dowel1.gif" alt="Wood dowels assembly step 4" class="responsive-img">
      </div>
      <div>
        <p><strong>Wood dowels after completing step 5:</strong></p>
        <img src="dowel2.gif" alt="Wood dowels assembly step 5" class="responsive-img">
      </div>
    </div>
    
    <div class="warning-text">
      <strong>Adult supervision and professional help is required for all cuttings and hole makings.</strong>
    </div>
  </div>

  <div class="procedure-section">
    <h3 class="section-title">Assembly Procedure:</h3>
    <p><em>(If you buy a kit, make sure to read the procedure suggested in your kit)</em></p>
    <ol>
      <li>Insert the magnet in the hole of the wood dowel. Center it and use some glue to secure it</li>
      <li>Use one large square balsa wood and four smaller rectangular balsa woods to make a box</li>
      <li>Insert your wood dowel into the hole in the center of the box. At this time the magnet is inside the box</li>
      <li>Place the other large square to complete the box. Apply some glue to the edges and wait for the glue to dry. By now, you have a box and inside the box you have a magnet that can spin when you spin the wood dowel</li>
      <li><strong>Wrap the copper wire around the box</strong> and use masking tape to secure it. Note that more copper wire around the box results in more electricity</li>
      <li>Remove the insulation from the ends of the wire and connect it to the screws of the bulb holder or base</li>
      <li>Insert the light bulb</li>
      <li><strong>Spin the wood dowel fast to get the light!</strong></li>
    </ol>
    
    <p><strong>More Detail Instructions:</strong> A more detailed and step-by-step online instruction page is available for the users of the kit. If you have got the kit, please make sure to use the URL (web address) suggested in the kit to access the instruction details.</p>
  </div>

  <div class="kit-showcase">
    <div>
      <p class="center"><strong>You may order wooden generator science set online. All parts are cut to size and ready to use.</strong></p>
      <div class="purchase-buttons">
        <a href="https://shop.miniscience.com/kitwg" class="buy-now-btn">Buy Now</a>
      </div>
      <p><em>Wood glue, masking tape and sand paper are not included. Additional wooden parts may be included.</em></p>
    </div>
    <div>
      <a href="https://shop.miniscience.com/kitwg"><img src="http://www.MiniScience.com/kits/woodengenerator/Exp00.jpg" alt="Wooden generator kit components" class="responsive-img"></a>
    </div>
  </div>

  <div class="parts-showcase">
    <div>
      <img src="https://www.MiniScience.com/kits/woodengenerator/socket.jpg" alt="Light bulb socket component" class="responsive-img">
    </div>
    <div>
      <p>The high quality parts included in this science set may be useful for many of your future projects. All parts other than balsa wood are reusable. You may purchase additional balsa wood from craft stores for your future projects.</p>
    </div>
    <div>
      <img src="https://www.MiniScience.com/kits/woodengenerator/parts2.jpg" alt="Generator parts components" class="responsive-img">
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
        <div id="abc_189" class="abantecart_product" data-product-id="1716" data-language="en" data-currency="USD">
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
        <div id="abc_143" class="abantecart_product" data-product-id="1719" data-language="en" data-currency="USD">
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
        <li id="abc_1760472036383103" class="abantecart_category" data-category-id="103" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176047205051887" class="abantecart_category" data-category-id="87" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760472063358130" class="abantecart_category" data-category-id="130" data-language="en" data-currency="USD">
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
    <summary>What makes this wooden generator different from other generators?</summary>
    <p>This wooden generator is specifically designed for educational purposes, using safe, accessible materials like wood dowels and balsa wood. It demonstrates the same electromagnetic induction principles as commercial generators but at a scale perfect for learning and experimentation.</p>
  </details>
  <details>
    <summary>How much electricity can this wooden generator produce?</summary>
    <p>The generator produces enough low-voltage electricity to light a 1.2V bulb when turned by hand. The exact amount depends on how fast you turn it and how many wire coils you wrap around the box. More coils and faster spinning produce more electricity.</p>
  </details>
  <details>
    <summary>Is it safe for students to build this generator?</summary>
    <p>Yes, this project is designed with safety in mind. The voltage produced is very low (1.2V), and when using the pre-cut kit, no power tools are required. However, adult supervision is still recommended, especially for younger students during assembly.</p>
  </details>
  <details>
    <summary>Can I modify the design to produce more electricity?</summary>
    <p>Absolutely! This is perfect for experimental projects. You can increase output by: using a stronger magnet, adding more wire coils, using thicker wire, spinning faster, or making the coils closer to the magnet. Each modification teaches different aspects of electromagnetic induction.</p>
  </details>
  <details>
    <summary>What scientific principles does this demonstrate?</summary>
    <p>This generator demonstrates Faraday's Law of electromagnetic induction - when a magnetic field moves through a wire coil, it generates electrical current. It also shows energy conversion (mechanical to electrical) and the relationship between motion speed and electrical output.</p>
  </details>
  <details>
    <summary>How long does it take to build the wooden generator?</summary>
    <p>With the pre-cut kit, assembly typically takes 2-3 hours including drying time for wood glue. Building from scratch (cutting your own parts) can take 4-6 hours and requires woodworking tools and adult supervision for cutting and drilling.</p>
  </details>
  <details>
    <summary>What makes a good science fair project with this generator?</summary>
    <p>Great science fair projects test one variable at a time: coil diameter, number of wire loops, spinning speed, magnet strength, or wire thickness. Create a hypothesis, measure the electrical output for each variation, and graph your results to show the relationship.</p>
  </details>
  <details>
    <summary>Can the parts be reused for other projects?</summary>
    <p>Yes! The magnet, wire, light bulb, socket, and tools are all reusable for future experiments. Only the balsa wood box might need replacement if you want to try different designs. This makes it a great value for ongoing science exploration.</p>
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