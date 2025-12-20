<?php
// Project: Electromagnet Science Kit
$project_title = "Electromagnet Science Kit";
$project_description = "Build electromagnets and explore magnetism through hands-on experiments. Construct doorbells, telegraph systems, electric catapults, buzzers, and relays while learning about electromagnetic principles.";
$project_keywords = "electromagnet, magnetism, magnetic fields, electric catapult, telegraph, buzzer, relay, electromagnetic induction, science kit";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Physics, Magnetism, Electromagnetism";
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
    .bottom-showcase { display: grid; grid-template-columns: 1fr 2fr; gap: 1rem; align-items: center; margin: 1rem 0; background: #f8f9ff; padding: 1rem; border-radius: 8px; }
    .bottom-showcase img { width: 100%; height: auto; display: block; }
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
      .bottom-showcase { grid-template-columns: 1fr; text-align: center; }
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
      <img src="Electromagnet1.png" alt="Electromagnet science kit components and experiments" class="responsive-img">
    </div>
    <div>
      <p><strong style="color: #ff00ff;">Electromagnet Science Kit</strong></p>
      <p>Electromagnet Science set is a <strong style="color: #ff0000;">kit</strong> with instructions and material to perform many different experiments related to electromagnets. These materials can also be used for your presentation.</p>
      <p><strong>Build a doorbell, telegraph system, even a catapult, using a true electromagnet.</strong></p>
      <p>Electromagnet Science Set includes several introductory experiments in magnetism as well as six complete electromagnet projects.</p>
    </div>
  </div>

  <div class="experiments-section">
    <h3 class="section-title">Complete Projects Included:</h3>
    <div class="experiment-list">
      <p><strong>1- Construct an electromagnet</strong><br>
      Learn the fundamental principles of electromagnetism by building your own electromagnet from wire coils and iron cores.</p>
      
      <p><strong>2- Construct an electric catapult</strong><br>
      Build a catapult powered by electromagnetic force to launch small objects and study magnetic attraction.</p>
      
      <p><strong>3- Make pictures of magnetic field</strong><br>
      Visualize invisible magnetic fields using iron filings to see the patterns created by magnets and electromagnets.</p>
      
      <p><strong>4- Construction of telegraph</strong><br>
      Build a working telegraph system to understand how electromagnetic communication systems work.</p>
      
      <p><strong>5- Construct a buzzer</strong><br>
      Create an electromagnetic buzzer that demonstrates oscillating magnetic fields and mechanical vibration.</p>
      
      <p><strong>6- Construct a relay</strong><br>
      Build an electromagnetic relay switch to control circuits remotely using magnetic switching principles.</p>
    </div>
  </div>

  <div class="kit-details-section">
    <div class="kit-contents">
      <h3 class="section-title">Electromagnet Science kit includes:</h3>
      <ul>
        <li>Experiment and Project book</li>
        <li>Compass</li>
        <li>Coil of magnet wire in spool</li>
        <li>Neodymium magnet</li>
        <li>Latch Magnets</li>
        <li>Plastic coated hookup wire</li>
        <li>Heavy magnet wire</li>
        <li>Sand paper</li>
        <li>Plastic drinking straws</li>
        <li>8 Sheet metal pieces</li>
        <li>6 disc magnets</li>
        <li>Small wood screws</li>
        <li>4 small nails for securing wires</li>
        <li>Large common nails</li>
        <li>Large finishing nail</li>
        <li>Small finishing nail</li>
        <li>Small lights with leads</li>
        <li>Battery holder</li>
        <li>Iron filings</li>
        <li>2 Pre-drilled wood blocks</li>
        <li>Light emitting diode (LED)</li>
      </ul>
    </div>
    <div>
      <h3 class="section-title">Additional Materials Required:</h3>
      <p>Additional Materials Required for your experiments can be found at home or purchased locally. Some of these material are:</p>
      <ul>
        <li>Four "D" cell flashlight batteries</li>
        <li>Philips screw driver</li>
        <li>String/thread</li>
        <li>1 spoon</li>
        <li>1 pair of scissors</li>
        <li>1 Roll of masking tape</li>
        <li>1 Nickel (US five cent piece)</li>
        <li>1 US dollar bill</li>
        <li>5 US pennies</li>
        <li>6 Small paper clips</li>
        <li>Several Magazines</li>
        <li>1 piece of paper (8.5 x 11)</li>
        <li>One book</li>
      </ul>
    </div>
  </div>

  <div class="science-fair-section">
    <h3 class="section-title">Opportunities for Science Fair Projects</h3>
    <p>Many of the questions asked in the Electromagnet Projects, can serve as the "Problem to be solved" in a science project. In setting up your project, you would first state the <u>problem</u>, then <u>hypothesis</u>, ( a guess as the answer to your problem), next <u>procedure</u> to check the hypothesis, and finally a <u>conclusion</u> that answers the stated problem based on what you actually observe in your research.</p>
    <p>In addition you may be interested in proposing your own, specific research that will expand on your conclusion.</p>
    <p>Since magnets are visually enticing in themselves as they interact with each other, it would be strongly suggested that your presentation include the apparatus you used in your research.</p>
  </div>

  <div class="bottom-showcase">
    <div>
      <img src="boxcover.png" alt="Electromagnet science kit box cover" class="responsive-img">
    </div>
    <div class="center">
      <p><strong>Complete electromagnet science kit with comprehensive experiments!</strong></p>
      <p>Perfect for understanding magnetism, electromagnetic fields, and building practical electromagnetic devices through hands-on experimentation.</p>
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
        <div id="abc_229" class="abantecart_product" data-product-id="1689" data-language="en" data-currency="USD">
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
        <div id="abc_193" class="abantecart_product" data-product-id="1688" data-language="en" data-currency="USD">
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

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760470913594130" class="abantecart_category" data-category-id="130" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760470930295102" class="abantecart_category" data-category-id="102" data-language="en" data-currency="USD">
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
    <summary>What is an electromagnet and how does it work?</summary>
    <p>An electromagnet is a magnet created by running electric current through a coil of wire wrapped around an iron core. Unlike permanent magnets, electromagnets can be turned on and off by controlling the electric current. The magnetic field strength can also be controlled by adjusting the current or the number of wire coils.</p>
  </details>
  <details>
    <summary>How is an electromagnet different from a regular magnet?</summary>
    <p>Permanent magnets are always magnetic and cannot be turned off, while electromagnets only work when electricity flows through them. Electromagnets can be much stronger than permanent magnets, and their strength can be controlled. They're also temporary - turn off the power and the magnetism disappears.</p>
  </details>
  <details>
    <summary>What makes an electromagnet stronger?</summary>
    <p>Several factors increase electromagnet strength: more coils of wire around the core, higher electric current, using a better iron core material, and making the coils tighter. The kit includes different materials to experiment with these variables and see their effects.</p>
  </details>
  <details>
    <summary>Why do we use iron in the electromagnet core?</summary>
    <p>Iron is ferromagnetic, meaning it greatly amplifies magnetic fields. When you wrap wire around iron and send electricity through the wire, the iron becomes temporarily magnetized and creates a much stronger magnetic field than the wire coil alone would produce.</p>
  </details>
  <details>
    <summary>How does the electromagnetic relay work?</summary>
    <p>A relay uses a small electromagnet to control a larger electrical circuit. When current flows through the electromagnet coil, it pulls a metal switch that closes (or opens) contacts in another circuit. This allows a small current to control a much larger current safely.</p>
  </details>
  <details>
    <summary>What real-world devices use electromagnets?</summary>
    <p>Electromagnets are everywhere! Electric motors, generators, speakers, headphones, MRI machines, electric door locks, car starters, computer hard drives, and many industrial machines all use electromagnets. Even your doorbell likely uses an electromagnet!</p>
  </details>
  <details>
    <summary>Can I make the electromagnet catapult more powerful?</summary>
    <p>Yes, but safely! You can increase power by adding more coils of wire, using fresh batteries, or improving the electrical connections. However, always follow safety guidelines and never exceed recommended voltages. More power also means more heat, so use caution.</p>
  </details>
  <details>
    <summary>How do I visualize magnetic fields with iron filings?</summary>
    <p>Place a piece of paper over your magnet or electromagnet, then sprinkle iron filings on top. The tiny iron pieces align with the magnetic field lines, creating visible patterns that show the invisible magnetic field. Tap the paper gently to help the filings settle into clear patterns.</p>
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