<?php
// Project: Electricity and Conductivity
$project_title = "Electricity and Conductivity";
$project_description = "Explore electrical circuits, conductivity, and properties of electricity through hands-on experiments. Learn about voltage, current, and test materials for electrical conductivity.";
$project_keywords = "electricity, conductivity, electrical circuits, voltage, current, conductivity tester, serial circuits, parallel circuits, electrical conductivity";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Physics, Electrical Engineering, Electronics";
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
    .intro-section { display: grid; grid-template-columns: 2fr 3fr; gap: 1rem; align-items: start; background: #f8f9ff; border: 1px solid #ff99cc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .intro-section img { width: 100%; height: auto; display: block; }
    .experiments-section { background: #f0f8f0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .kit-details-section { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; margin: 1rem 0; }
    .kit-contents { background: #f0f8f0; padding: 1rem; border-radius: 8px; }
    .science-fair-section { background: #fff6f8; border: 1px solid #ff0000; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .pricing-section { background: #fff6f8; border: 1px solid #ff0000; padding: 1rem; border-radius: 8px; margin: 1rem 0; text-align: center; }
    .purchase-buttons { display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap; align-items: center; margin-top: 1rem; }
    .purchase-buttons img { height: 33px; width: auto; }
    .responsive-img { max-width: 100%; height: auto; display: block; }
    .section-title { color: #ff0000; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .sub-title { color: #008000; font-weight: normal; font-size: 1rem; margin: 0.75rem 0 0.5rem 0; }
    .experiment-list { color: #9933ff; font-weight: normal; line-height: 1.6; }
    .center { text-align: center; }
    /* Banner row (bottom of the page) */
    .banner-row { display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-top: 0.75rem; }
    .banner-row .banner { flex: 1 1 0; text-align: center; }
    .banner-row img { max-width: 100%; height: auto; display: inline-block; }
    @media (max-width: 720px) {
      .intro-section { grid-template-columns: 1fr; text-align: center; }
      .kit-details-section { grid-template-columns: 1fr; }
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
      <img src="electricity2.gif" alt="Electricity and conductivity science kit components" class="responsive-img">
    </div>
    <div>
      <p><strong style="color: #ff00ff;">Electricity and Conductivity Science Kit:</strong></p>
      <p>Electricity and Conductivity Science Kit is a collection of the most useful elements that you need in order to experiment and understand electrical circuits, current, voltage and conductivity.</p>
      <p>It also includes ideas and questions that help you to perform Scientific Experiments and to construct your science project with the scientific method.</p>
    </div>
  </div>

  <div class="experiments-section">
    <h3 class="section-title">Complete Projects Included:</h3>
    <div class="experiment-list">
      <p><strong>1- Construct an Electric Circuit</strong><br>
      Learn the basics of electrical circuits and how electricity flows through conductors.</p>
      
      <p><strong>2- Can electricity create heat?</strong><br>
      Investigate how electrical energy converts to thermal energy in resistive materials.</p>
      
      <p><strong>3- Can electricity create magnet?</strong><br>
      Explore the relationship between electricity and magnetism through electromagnets.</p>
      
      <p><strong>4- Construct a Continuity tester and test conductivity of objects around you</strong><br>
      Build your own electrical conductivity tester and explore which materials conduct electricity.</p>
      
      <p><strong>5- What is the effect of Serial or Parallel Light Bulbs on Voltage and Current Distribution?</strong><br>
      Compare how series and parallel circuits affect electrical flow and brightness.</p>
      
      <p><strong>6- What is the effect of Parallel or Serial Batteries on Voltage and Current?</strong><br>
      Understand how battery configurations change electrical output and circuit performance.</p>
    </div>
  </div>

  <div class="kit-details-section">
    <div class="kit-contents">
      <h3 class="section-title">Electricity and Conductivity Science Kit includes:</h3>
      <ul>
        <li>Experiment and Project sheet</li>
        <li>4 Light Bulbs</li>
        <li>2 Socket or base for light bulbs</li>
        <li>2 Battery holders</li>
        <li>1 Wood Plaque</li>
        <li>Heavy Magnet wire</li>
        <li>Wires, Screws, Nails and other components</li>
      </ul>
    </div>
    <div>
      <h3 class="section-title">Additional Materials Required:</h3>
      <p>Additional Materials Required for your experiments can be found at home or purchased locally. Some of these material are:</p>
      <ul>
        <li>Some batteries</li>
        <li>Philips and flat screw drivers</li>
        <li>One 2" or 3" nail</li>
        <li>One pencil</li>
        <li>1 pair of scissors</li>
        <li>1 Roll of masking tape</li>
        <li>1 Nickel (US five cent piece)</li>
        <li>5 US pennies</li>
        <li>6 Small paper clips</li>
        <li>1 piece of paper (8.5 x 11)</li>
      </ul>
    </div>
  </div>

  <div class="science-fair-section">
    <h3 class="section-title">Opportunities for Science Projects</h3>
    <p>Many of the questions asked in the Electricity and Conductivity Projects, can serve as the "Problem to be solved" in a science project. In setting up your project, you would first state the <u>problem</u>, then <u>hypothesis</u>, ( a guess as the answer to your problem), next <u>procedure</u> to check the hypothesis, and finally a <u>conclusion</u> that answers the stated problem based on what you actually observe in your research.</p>
    <p>In addition you may be interested in proposing your own, specific research that will expand on your conclusion.</p>
    <p>Since lights and switches are visually enticing in themselves, it would be strongly suggested that your presentation include the apparatus you used in your research.</p>
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
        <div id="abc_516" class="abantecart_product" data-product-id="1685" data-language="en" data-currency="USD">
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
        <div id="abc_908" class="abantecart_product" data-product-id="1709" data-language="en" data-currency="USD">
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
        <li id="abc_176046203012488" class="abantecart_category" data-category-id="88" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176046208529995" class="abantecart_category" data-category-id="95" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176046213784696" class="abantecart_category" data-category-id="96" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760462159393100" class="abantecart_category" data-category-id="100" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760470529770103" class="abantecart_category" data-category-id="103" data-language="en" data-currency="USD">
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
    <summary>What is electrical conductivity and why does it matter?</summary>
    <p>Electrical conductivity is a material's ability to allow electric current to flow through it. Materials like metals are good conductors, while materials like rubber and plastic are insulators. Understanding conductivity helps us choose the right materials for electrical projects and stay safe around electricity.</p>
  </details>
  <details>
    <summary>How do I test if something conducts electricity safely?</summary>
    <p>The kit includes materials to build a continuity tester that uses low-voltage batteries, making it safe for testing household objects. Never test unknown materials with wall electricity - always use battery-powered circuits for conductivity testing!</p>
  </details>
  <details>
    <summary>What's the difference between series and parallel circuits?</summary>
    <p>In a series circuit, electricity flows through components one after another in a single path. If one bulb burns out, all bulbs go out. In parallel circuits, each component has its own path to the battery, so if one bulb burns out, others stay lit. Most home wiring uses parallel circuits.</p>
  </details>
  <details>
    <summary>Why do light bulbs get dimmer in series circuits?</summary>
    <p>In series circuits, the same current flows through all bulbs, but the voltage is divided among them. Each bulb gets less voltage than it would alone, so it glows dimmer. This is why Christmas lights used to all go dark when one bulb failed!</p>
  </details>
  <details>
    <summary>How does electricity create heat and magnetism?</summary>
    <p>When electricity flows through a resistor (like a light bulb filament), electrical energy converts to heat energy - that's why bulbs get hot. When electricity flows through a coil of wire, it creates a magnetic field around the coil, making an electromagnet.</p>
  </details>
  <details>
    <summary>What common materials are good electrical conductors?</summary>
    <p>Metals like copper, aluminum, silver, and gold are excellent conductors. Water with dissolved salts conducts electricity (but pure water doesn't). Your body also conducts electricity, which is why electrical safety is so important!</p>
  </details>
  <details>
    <summary>What materials make good electrical insulators?</summary>
    <p>Rubber, plastic, glass, dry wood, and ceramic are good insulators. These materials prevent electricity from flowing, which is why electrical wires are covered in plastic and power line workers wear rubber gloves.</p>
  </details>
  <details>
    <summary>Can I expand these experiments with other household items?</summary>
    <p>Absolutely! Test various coins, kitchen utensils, different types of paper, fabrics, and natural materials like leaves or wood. Always use only battery power for safety, and have an adult supervise when testing unknown materials.</p>
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