<?php
// Project: Crystal Radio
$project_title = "Crystal Radio";
$project_description = "Build a working crystal radio receiver that uses electromagnetic waves from radio stations to power itself without batteries, demonstrating principles of radio frequency, diodes, and antenna design.";
$project_keywords = "crystal radio, AM radio, electromagnetic waves, diode detector, antenna, radio frequency, electronics project";
$project_grade_level = "Intermediate"; // recommended
$project_subject = "Physics, Electronics, Engineering";
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
    .kit-showcase { display: grid; grid-template-columns: 240px 1fr; gap: 1rem; align-items: start; background: #f8f9ff; border: 1px solid #2c5aa0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .kit-showcase img { width: 100%; height: auto; display: block; }
    .kit-info h3 { color: #ff00ff; font-weight: bold; margin: 0 0 0.5rem 0; font-size: 1.1rem; }
    .kit-info p { margin-bottom: 0.75rem; line-height: 1.6; }
    .purchase-buttons { display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; margin-top: 0.75rem; }
    .purchase-buttons img { height: 33px; width: auto; }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; }
    .responsive-img { max-width: 100%; height: auto; display: block; }
    .center { text-align: center; }
    .section-title { color: #ff00ff; font-weight: bold; font-size: 1.1rem; margin: 1rem 0 0.5rem 0; }
    .schematic { text-align: center; margin: 1rem 0; }
    .materials-list { background: #f8f9ff; border-left: 4px solid #ff00ff; padding: 1rem; margin: 1rem 0; }
    .science-fair { background: #fff6f8; border: 1px solid #ff33ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .science-fair h4 { color: #ff33ff; margin: 0 0 0.75rem 0; }
    /* Banner row (bottom of the page) */
    .banner-row { display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-top: 0.75rem; }
    .banner-row .banner { flex: 1 1 0; text-align: center; }
    .banner-row img { max-width: 100%; height: auto; display: inline-block; }
    @media (max-width: 720px) {
      .kit-showcase { grid-template-columns: 1fr; text-align: center; }
      .two-col { grid-template-columns: 1fr; }
      .banner-row { flex-direction: column; gap: 0.5rem; }
      .purchase-buttons { justify-content: center; }
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

  <div class="kit-showcase">
    <img src="crystalradio.png" alt="Crystal Radio Kit assembled">
    <div class="kit-info">
      <h3>Crystal Radio Science Kit</h3>
      <p>Collect electromagnetic waves that radio stations broadcast and convert them to electricity. Then transmit it to an earphone and listen to the radio.</p>
      <div class="purchase-buttons">
        
        <a href="https://shop.miniscience.com/CRADIO">
          <span class="buy-now-btn">Buy Now</span>
        </a>
        <a href="https://shop.miniscience.com/">
          <img src="../../images/btr_onlinestore.gif" alt="Online store" width="96" height="33">
        </a>
      </div>
    </div>
  </div>

  <p>A crystal radio does not need a battery. Electromagnetic waves from the air provide enough energy for the radio to work.</p>

  <h3 class="section-title">General Instructions:</h3>
  <p>The main components of a crystal radio are a coil of wire that you will make, a diode, an earphone and some wires. These materials can be purchased in pieces from a local electronic store or as a kit from this site.</p>

  <div class="schematic">
    <img src="schematic.gif" alt="Crystal radio schematic diagram" width="114" height="140">
  </div>

  <p>The first step in making the radio is winding the tuning coil. This is best done with two people. One person should hold the paper roll and the other the spool of wire. Poke a small hole about a 1/2" from each end of the paper roll. Take the end of the wire from the wire spool and thread it through the hole. Pull the wire through so that it is about a foot long. Put a narrow strip of transparent tape around the end of the tube to keep the wire from slipping out during winding.</p>

  <p>Now, begin winding the coil. When winding the coil, do not overlap the turns, they should lie adjacent to each other. When you are close to reaching the second hole on the other side of the roll, unwind an additional foot of wire and cut it off near the spool. Thread the end of the wire through the hole as before and apply a strip of tape.</p>

  <p>One end of the coil will be attached to the antenna that can be any long wire. The other end goes to the ground. In other words you can connect it to any metal pipe or door or wire that is grounded.</p>

  <p>The earphone also has 2 wires. One goes to the ground and the other will be connected to a diode.</p>

  <p>The other end of diode will touch some place of the coil and depending on the spot that it touches the coil, you can be tuned on different radio stations.</p>

  <div class="materials-list">
    <h3 class="section-title">Material needed (Included in the kit):</h3>
    <ul>
      <li>Paper roll (as coil tube)</li>
      <li>Spool of insulated wire</li>
      <li>Germanium diode</li>
      <li>Screw, washer, and nut</li>
      <li>Tuner rod (or copper strip)</li>
      <li>Some wires</li>
      <li>Piece of sandpaper</li>
      <li>Plastic, wood or cardboard base</li>
    </ul>

    <h3 class="section-title">Additional Materials Required:</h3>
    <p>If you purchase a kit, you will not need any additional material. If you buy parts in pieces additional material required for your crystal radio are:</p>
    <ul>
      <li>Push-pins</li>
      <li>Clear adhesive tape</li>
    </ul>
  </div>

  <div class="science-fair">
    <h4>Opportunities for Science Fair Projects</h4>
    <p>Making a crystal radio is a good experimental project. If you want to do some more research, design experiments to answer the following questions. Prior to performing experiment, suggest a write down a hypothesis as the answer to your question.</p>
    <ol>
      <li>What is the effect of antenna on the performance of a radio?</li>
      <li>What shape, size and height antenna offers the optimum performance?</li>
      <li>What is the effect of ground wire in a crystal radio?</li>
      <li>Why is it called a crystal radio?</li>
    </ol>
  </div>

  <div class="center" style="margin: 1rem 0;">
    <a href="https://shop.miniscience.com/CRADIO">
      <img src="../../images/btr_add1tobasket.gif" alt="Add to basket" width="119" height="33">
    </a>
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

<!-- YouTube Video Section -->
<section class="youtube-video" style="margin-top: 1.5rem; margin-bottom: 1.5rem;">
  <h3 style="color: #2c5aa0; margin-bottom: 1rem; text-align: center;">Crystal Radio Demonstration Video</h3>
  <div style="position: relative; width: 100%; max-width: 560px; margin: 0 auto; padding-bottom: 31.25%; /* 16:9 aspect ratio */ height: 0; overflow: hidden;">
    <iframe 
      style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;" 
      src="https://www.youtube.com/embed/o9sDLw3Gqxo?si=d1uGq3GUrRMUI_CZ" 
      title="YouTube video player" 
      frameborder="0" 
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
      referrerpolicy="strict-origin-when-cross-origin" 
      allowfullscreen>
    </iframe>
  </div>
</section>

<!-- MiniScience / Miniscience product embed widget -->
<section class="related-projects">
  <h3>Related Science Projects &amp; Kits</h3>
  
  <!-- Kit/Products Section (arriba) -->
  <div class="related-grid" style="margin-bottom: 1.5rem;">
    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <div style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <div id="abc_117" class="abantecart_product" data-product-id="7302" data-language="en" data-currency="USD">
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
        <div id="abc_635" class="abantecart_product" data-product-id="7341" data-language="en" data-currency="USD">
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
        <div id="abc_459" class="abantecart_product" data-product-id="909" data-language="en" data-currency="USD">
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
        <div id="abc_716" class="abantecart_product" data-product-id="908" data-language="en" data-currency="USD">
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
        <li id="abc_176046539621487" class="abantecart_category" data-category-id="87" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760465411641103" class="abantecart_category" data-category-id="103" data-language="en" data-currency="USD">
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
    <summary>Why doesn't a crystal radio need batteries?</summary>
    <p>Crystal radios are powered entirely by the electromagnetic energy broadcast by radio stations. The radio waves induce a small current in the antenna, which is then detected by the crystal detector (diode) and converted to audio signals strong enough to drive high-impedance earphones.</p>
  </details>
  <details>
    <summary>What type of radio stations can I receive with a crystal radio?</summary>
    <p>Crystal radios work best with strong local AM (amplitude modulation) radio stations. They cannot receive FM stations because FM requires more complex circuitry. The stronger the station signal and the closer you are to the transmitter, the better the reception.</p>
  </details>
  <details>
    <summary>How important is the antenna and ground connection?</summary>
    <p>Both are crucial for good performance. The antenna should be as long and high as possible (50+ feet ideal). The ground connection should be to a good earth ground like a water pipe, ground rod, or electrical ground. Poor antenna or ground connections will result in weak or no reception.</p>
  </details>
  <details>
    <summary>Why is it called a "crystal" radio?</summary>
    <p>Early radio detectors used natural mineral crystals like galena (lead sulfide) with a fine wire "cat's whisker" touching the crystal surface to detect radio signals. Modern crystal radios use semiconductor diodes (usually germanium) which perform the same function more reliably.</p>
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