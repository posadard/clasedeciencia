<?php
// Project: Potato Battery - Generate Electricity from a Potato
$project_title = "Potato Battery - Make Electricity from Potato";
$project_description = "Build a simple potato battery using copper and zinc electrodes to demonstrate electrochemistry principles and generate low-voltage electricity from organic materials.";
$project_keywords = "potato battery, electrochemistry, copper electrode, zinc electrode, organic battery, STEM project, electricity generation";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Chemistry, Physics";
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
    .notice { border: 1px solid #c00; background: #fff6f6; padding: 0.75rem; color: #900; text-align: center; font-family: Verdana, Geneva, Tahoma, sans-serif; font-size: 0.95rem; }
    .info-box { border: 1px solid #69c; background: #f4f8ff; padding: 0.75rem; color: #0033cc; font-family: Verdana, Geneva, Tahoma, sans-serif; }
    .materials { display: grid; grid-template-columns: 1fr 320px; gap: 1rem; align-items: start; }
    .materials img { width: 100%; height: auto; display: block; }
    .two-col { display: grid; grid-template-columns: 203px 1fr; gap: 1rem; align-items: start; }
    .responsive-img { max-width: 100%; height: auto; display:block; }
    .center { text-align: center; }
    .intro-section { background: #f8f9ff; border: 1px solid #2c5aa0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .section-title { color: #2c5aa0; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .highlight-text { color: #2c5aa0; font-weight: bold; }
    /* Preserve legacy table borders look for callout tables */
    .legacy-callout { border: 1px solid #0033cc; padding: 0.6rem; background: #fff; }
    @media (max-width: 720px) {
      .materials { grid-template-columns: 1fr; }
      .two-col { grid-template-columns: 1fr; }
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

  <div class="intro-section">
    <p><strong class="highlight-text">Introduction:</strong> Batteries generate electricity through a chemical reaction between two different electrodes and one electrolyte. Use of copper and zinc electrodes with sulfuric acid as electrolyte is a proven method. We wondered if we can use any other liquid as electrolyte - which gave us the idea of using a potato! After all, a fresh potato has a lot of juice that may serve as our electrolyte.</p>
  </div>

  <h3 class="section-title">Problem</h3>
  <p><strong class="highlight-text">Can a potato be used to generate electricity?</strong></p>

  <h3 class="section-title">Hypothesis</h3>
  <p>Potato juice contains many water-soluble chemicals that may cause a chemical reaction with one or both of our electrodes. So we may get some electricity from that.</p>

  <h3 class="section-title">Materials</h3>
  <div class="materials">
    <div>
      <p><strong>For this experiment we use:</strong></p>
      <ul>
        <li>A fresh potato</li>
        <li>Copper electrode</li>
        <li>Zinc electrode</li>
        <li>A digital or analog multimeter to measure voltage or current of produced electricity</li>
        <li>Alligator clips/leads</li>
      </ul>
    </div>
    <div>
      <img src="PotatoVolts.gif" alt="Potato battery setup with electrodes and multimeter" class="responsive-img" />
    </div>
  </div>

  <h3 class="section-title">Procedure</h3>
  <p>We insert copper and zinc electrodes into the potato, close but not touching each other. We use clip leads to connect our electrodes to the multimeter to measure voltage between two electrodes or current passing through the multimeter. For this experiment we removed the shell of a broken AA battery for our zinc electrode.</p>
  
  <div class="info-box">
    <strong>Important:</strong> Make sure to test your multimeter by connecting its positive and negative wires to each other - this should show no current and no voltage.
  </div>

  <h3 class="section-title">Record and Analyze Data</h3>
  <div class="two-col">
    <div>
      <img src="PotatoVolts3.gif" alt="Digital multimeter showing voltage reading from potato battery" class="responsive-img" />
    </div>
    <div>
      <p>A digital multimeter showed <strong class="highlight-text">1.2 volts</strong> between the electrodes, but the analog multimeter showed a much smaller value.</p>
      
      <p>This means that even though the voltage between electrodes is 1.2 volts, the speed of production of electricity is not high enough for an analog multimeter to show the exact voltage. (Analog multimeters get their power from our potato to show the voltage, but digital multimeters get their power from an internal battery and do not consume any of the electricity produced by our potato - that's why it shows a larger and more accurate value.)</p>
      
      <p><strong>Extended Testing:</strong> We repeated this experiment with some other fruits and all resulted almost the same. In all cases the produced voltage is between 1 and 1.5 volts, and in all cases they do not produce enough current to turn on a small light.</p>
    </div>
  </div>

  <div class="info-box" style="margin: 1rem 0;">
    <p><strong>Key Learning:</strong> Another thing that we learned from this experiment is that creating electricity and making a battery is easy - the main challenge is producing a battery that can continue to produce larger amounts of electricity for larger amounts of time.</p>
  </div>

  <h3 class="section-title">Understanding the Science</h3>
  <p><strong>How does the potato battery work?</strong></p>
  <ul>
    <li><strong>Electrolyte:</strong> The potato's juice contains phosphoric acid and other chemicals that act as an electrolyte</li>
    <li><strong>Electrodes:</strong> Copper and zinc metals have different electrical potentials</li>
    <li><strong>Chemical Reaction:</strong> Zinc oxidizes (loses electrons) while copper reduces (gains electrons)</li>
    <li><strong>Electron Flow:</strong> This creates a flow of electrons through the external circuit</li>
  </ul>

  <h3 class="section-title">Safety Information</h3>
  <div class="notice">
    <p><strong>WARNING:</strong> This experiment contains small and sharp objects. Keep it out of reach of small children. Adult supervision is required.</p>
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
        <div id="abc_579" class="abantecart_product" data-product-id="1702" data-language="en" data-currency="USD">
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
        <div id="abc_122" class="abantecart_product" data-product-id="1699" data-language="en" data-currency="USD">
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
        <li id="abc_176054760150492" class="abantecart_category" data-category-id="92" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176054762104098" class="abantecart_category" data-category-id="98" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760547639530102" class="abantecart_category" data-category-id="102" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176054765713995" class="abantecart_category" data-category-id="95" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176054767780496" class="abantecart_category" data-category-id="96" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176054768763787" class="abantecart_category" data-category-id="87" data-language="en" data-currency="USD">
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
    <summary>Why does a potato generate electricity?</summary>
    <p>The potato acts as an electrolyte containing phosphoric acid and other water-soluble chemicals. When copper and zinc electrodes are inserted, a chemical reaction occurs where zinc oxidizes (loses electrons) and copper reduces (gains electrons), creating an electrical current.</p>
  </details>
  <details>
    <summary>Why do digital and analog multimeters show different readings?</summary>
    <p>Digital multimeters have their own internal battery and don't draw power from the potato, so they show accurate voltage readings. Analog multimeters draw power from the circuit being tested, and the potato can't provide enough current to drive the analog meter properly.</p>
  </details>
  <details>
    <summary>Can I light up an LED or bulb with a potato battery?</summary>
    <p>A single potato typically produces only 1-1.5 volts with very low current. Most LEDs need at least 1.8-3 volts and more current than a potato can provide. You would need to connect multiple potatoes in series to increase voltage and current.</p>
  </details>
  <details>
    <summary>What other fruits or vegetables work as batteries?</summary>
    <p>Citrus fruits (lemons, limes, oranges) work well due to their citric acid content. Other acidic fruits and vegetables like tomatoes, apples, and even pickles can generate small amounts of electricity using the same electrode method.</p>
  </details>
  <details>
    <summary>How long does a potato battery last?</summary>
    <p>A potato battery will gradually lose voltage over hours or days as the electrodes corrode and the potato's moisture content changes. The zinc electrode will slowly dissolve as it oxidizes, eventually stopping the chemical reaction.</p>
  </details>
  <details>
    <summary>What's the best electrode combination for maximum voltage?</summary>
    <p>Copper and zinc provide a good voltage difference (about 1.1V). Other combinations like aluminum and copper, or magnesium and copper can also work. The key is using two metals with different positions on the electrochemical series.</p>
  </details>
  <details>
    <summary>Is this the same principle as commercial batteries?</summary>
    <p>Yes! Commercial batteries use the same basic principle of electrochemical reactions between different materials. However, they use optimized chemicals and electrode materials to provide higher voltage, current, and longer-lasting power.</p>
  </details>
  <details>
    <summary>Can I improve the potato battery's performance?</summary>
    <p>You can try using larger electrodes with more surface area, connecting multiple potatoes in series for higher voltage, or using fresher potatoes with higher moisture content. Adding salt water to the potato can also increase conductivity.</p>
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
