<?php
// Project: Electric Motor/Generator
$project_title = "Electric Motor/Generator Science Kit";
$project_description = "Build a working electric generator that converts mechanical energy to electrical energy. Create electricity from motion using magnetic fields and wire coils to light up bulbs and understand electromagnetic induction.";
$project_keywords = "electric generator, motor, mechanical energy, electrical energy, electromagnetic induction, magnetic fields, coil, generator kit";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Physics, Engineering, Electromagnetism";
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
    .kit-details-section { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; margin: 1rem 0; }
    .kit-contents { background: #f0f8f0; padding: 1rem; border-radius: 8px; }
    .science-fair-section { background: #fff6f8; border: 1px solid #ff0000; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .working-generator-section { display: grid; grid-template-columns: 1fr 2fr; gap: 1rem; align-items: center; margin: 1rem 0; background: #f8f9ff; padding: 1rem; border-radius: 8px; }
    .working-generator-section img { width: 100%; height: auto; display: block; }
    .purchase-buttons { display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap; align-items: center; margin-top: 1rem; }
    .purchase-buttons img { height: 33px; width: auto; }
    .responsive-img { max-width: 100%; height: auto; display: block; }
    .section-title { color: #ff0000; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .sub-title { color: #008000; font-weight: normal; font-size: 1rem; margin: 0.75rem 0 0.5rem 0; }
    .highlight-text { color: #ff00ff; font-weight: bold; }
    .center { text-align: center; }
    /* Banner row (bottom of the page) */
    .banner-row { display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-top: 0.75rem; }
    .banner-row .banner { flex: 1 1 0; text-align: center; }
    .banner-row img { max-width: 100%; height: auto; display: inline-block; }
    @media (max-width: 720px) {
      .intro-section { grid-template-columns: 1fr; text-align: center; }
      .kit-details-section { grid-template-columns: 1fr; }
      .working-generator-section { grid-template-columns: 1fr; text-align: center; }
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
      <img src="http://MiniScience.com/kits/woodengenerator/Exp00.jpg" alt="Electric generator science kit components" class="responsive-img">
    </div>
    <div>
      <p><strong class="highlight-text">Electric Generator Science Kit: $25</strong></p>
      <p>Electric Generator Science set, also known as <strong>Wooden Generator</strong>, is a simple experiment of producing electrical energy from mechanical energy.</p>
      <p>A moving magnetic field in the center of a coil can produce enough electricity to light up a light bulb (included). The electric generator that you make can also be used for your presentation.</p>
      <p><strong>Build a working generator that converts motion into electricity!</strong></p>
    </div>
  </div>

  <div class="kit-details-section">
    <div class="kit-contents">
      <h3 class="section-title">Electric Generator kit includes:</h3>
      <ul>
        <li>Web address for online instructions</li>
        <li>Coil of Magnet Wire</li>
        <li>Sand paper</li>
        <li>Strong Magnet</li>
        <li>Pre-cut and drilled wood pieces</li>
        <li>Light bulb</li>
        <li>Base for light bulb</li>
      </ul>
    </div>
    <div>
      <h3 class="section-title">Additional Materials Required:</h3>
      <p>Additional Materials Required for your experiments can be found at home or purchased locally. Some of these materials are:</p>
      <ul>
        <li>Wood glue</li>
      </ul>
      <p><strong>Simple and accessible!</strong> Most materials are included in the kit, with only basic wood glue needed from home.</p>
    </div>
  </div>

  <div class="science-fair-section">
    <h3 class="section-title">Opportunities for Science Fair Projects</h3>
    <p>Making an electric generator provides many good options for a science fair project. Some of the questions that can be used for a science project are:</p>
    <ul>
      <li><strong>How does the speed of rotor affect the production of electrical energy in a generator?</strong></li>
      <li><strong>How does the diameter of wire coil affect the production of electricity?</strong></li>
      <li><strong>How does the number of coils affect power generation?</strong></li>
    </ul>
    <p>In setting up your project, you would first state the question or <u>problem</u>, then <u>hypothesis</u> (a guess as the answer to your problem), next <u>procedure</u> to check the hypothesis, and finally a <u>conclusion</u> that answers the stated problem based on what you actually observe in your research.</p>
    <p>In addition you may be interested in proposing your own, specific research that will expand on your conclusion.</p>
    <p><strong>Visual Impact:</strong> Since magnets are visually enticing in themselves as they interact with each other, it would be strongly suggested that your presentation include the apparatus you used in your research.</p>
  </div>

  <div class="working-generator-section">
    <div>
      <img src="http://www.MiniScience.com/kits/woodengenerator/Exp15.jpg" alt="Working electric generator producing electricity" class="responsive-img">
    </div>
    <div class="center">
      <p><strong class="highlight-text">Your completed generator in action!</strong></p>
      <p>Watch your wooden generator produce real electricity as you turn the handle. The moving magnet creates a changing magnetic field that induces electrical current in the wire coil, lighting up the bulb.</p>
      <p><strong>Perfect demonstration of electromagnetic induction principles!</strong></p>
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
    <summary>How does an electric generator work?</summary>
    <p>An electric generator converts mechanical energy (motion) into electrical energy using electromagnetic induction. When you turn the handle, a magnet spins inside a wire coil. The moving magnetic field causes electrons in the wire to move, creating an electric current that powers the light bulb.</p>
  </details>
  <details>
    <summary>What is the difference between a motor and a generator?</summary>
    <p>A motor converts electrical energy into mechanical energy (motion), while a generator does the opposite - it converts mechanical energy into electrical energy. Interestingly, the same device can often work as both a motor and generator depending on how you use it!</p>
  </details>
  <details>
    <summary>Why does the generator need a strong magnet?</summary>
    <p>A stronger magnetic field produces more electrical current when it moves through the wire coil. The strength of the magnetic field directly affects how much electricity the generator can produce. That's why the kit includes a strong magnet for better performance.</p>
  </details>
  <details>
    <summary>How does the speed of turning affect electricity production?</summary>
    <p>Faster rotation creates more electrical current because the magnetic field changes more rapidly through the coil. This is why power plants use high-speed turbines - faster motion means more electricity generation. Try turning your generator at different speeds to see the difference!</p>
  </details>
  <details>
    <summary>What real-world generators work like this wooden one?</summary>
    <p>Many generators use the same basic principle! Wind turbines, hydroelectric dams, steam turbines in power plants, and even bicycle dynamos all work by spinning magnets near wire coils. Your wooden generator demonstrates the fundamental physics behind most of our electricity production.</p>
  </details>
  <details>
    <summary>Can I make the generator produce more electricity?</summary>
    <p>Yes! You can increase output by: using a stronger magnet, adding more coils of wire, spinning faster, or making the coils closer to the magnet. Each of these changes increases the rate of magnetic field change, which produces more electrical current.</p>
  </details>
  <details>
    <summary>Why does electromagnetic induction work?</summary>
    <p>Electromagnetic induction happens because changing magnetic fields create electric fields. When a magnet moves near a wire, it creates a changing magnetic field that pushes electrons in the wire, making them flow as electric current. This fundamental physics principle was discovered by Michael Faraday in 1831.</p>
  </details>
  <details>
    <summary>What can I power with my generator?</summary>
    <p>Your wooden generator produces enough electricity to light the included bulb. While it won't power large devices, it demonstrates the same principles used in power plants that light entire cities. The amount of power depends on how fast and consistently you turn the handle.</p>
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