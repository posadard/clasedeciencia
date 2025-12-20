<?php
// Project: Battery Power - Testing Battery Life
$project_title = "Battery Power - Which Battery Lasts Longest?";
$project_description = "Compare different battery brands to determine which lasts longest and investigate the relationship between battery cost and performance.";
$project_keywords = "battery test, battery life, Duracell, Energizer, electrochemistry, electrical circuits, STEM project";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Physical Science, Chemistry";
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
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; }
    .responsive-img { max-width: 100%; height: auto; display:block; margin: 0 auto; }
    .center { text-align: center; }
    .vocab-list { margin-left: 1.5rem; }
    .vocab-list li { margin-bottom: 0.5rem; }
    @media (max-width: 720px) {
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

  <div class="center">
    <img class="responsive-img" src="project.gif" alt="Battery Power experiment illustration" style="max-width: 296px;">
  </div>

  <h3>Introduction</h3>
  <p>In my project I was trying to find out what battery lasts the longest. I will also try to determine if the cost of the battery has anything to do with the power it has.</p>

  <h3>Hypothesis</h3>
  <p>I think the Duracell battery will last the longest. I also believe that the more expensive the battery the longer it will last.</p>

  <h3>Materials</h3>
  <p>Paper, wires, stop watch, battery holders, metal connectors, computer, light bulbs, and graph paper. Batteries - Duracell, Everready, Energizer, and BA 30 "Army batteries."</p>

  <h3>Research/Sources of Information</h3>
  <p>I researched on how a battery produces electricity. The battery is a dry cell. A chemical reaction between the electrolyte and the zinc electrode helps produce electricity.</p>

  <h3>Vocabulary</h3>
  <ul class="vocab-list">
    <li><strong>Electrodes</strong> - The negative or positive part of an electric cell.</li>
    <li><strong>Electrolyte</strong> - A liquid or moist substance that conducts electricity.</li>
    <li><strong>Dry Cell</strong> - An electrical cell that has a moist electrolyte.</li>
    <li><strong>Terminal</strong> - The negative or positive end of an electrolyte.</li>
  </ul>

  <h3>Experiment</h3>
  <p>I experimented by testing the power of four different brands of batteries. I did this by hooking up the batteries to a light bulb. I then kept track of the length of time each bulb stayed lit. I tested two batteries from each of the four brands.</p>

  <h3>Results</h3>
  <p>After the testing was completed the following results were recorded: The Duracell battery lasted the longest, 101 hours and 20 minutes; Energizer battery, second, 99 hours and 17 minutes; Eveready battery, third, 28 hours and 30 minutes, and last but not least was the BA 30 batteries, 25 hours ad 58 minutes.</p>

  <h3>Conclusion</h3>
  <p>I thought the Duracell battery would last the longest. I guessed right! It was two hours and 3 minutes longer than the Energizer. I also determined that the cost of the battery does relate to the amount of battery power.</p>

  <h2>Optional</h2>

  <h3><em>How did I come up with my project idea?</em></h3>
  <p>My dad and I were getting ready to go on a boundary waters canoe trip and we were debating on what kind of batteries to purchase for our flashlights. We wanted ones with a lot of power. So I thought that would be a good science fair experiment.</p>

  <h3><em>What did I learn from my experiment?</em></h3>
  <p>I learned that science fair projects are a lot of hard work. The most powerful battery of the four I tested was Duracell. It also was the most expensive.</p>

  <h3><em>How close were my hypothesis and conclusion?</em></h3>
  <p>I guessed that Duracell would last the longest and I was right. It was also the most expensive.</p>

  <h3><em>Did I learn anything new from my project?</em></h3>
  <p>Yes I learned through this experiment that if you buy a more expensive battery you get a more powerful battery.</p>

  <h3><em>What was the most interesting part of my project?</em></h3>
  <p>It was when my hypothesis and conclusion matched.</p>

  <div class="info-box" style="margin: 1.5rem 0;">
    <h4 style="margin-top: 0;">Order a kit for battery life test</h4>
    <div class="two-col">
      <div>
        <p>This kit is also for a simple electric circuit you will use in this project.</p>
        <p class="center">
          <a href="https://shop.miniscience.com/kitblt" target="_blank">
            <span class="buy-now-btn" style="display:inline-block; margin-right: 0.5rem;">Buy Now</span>
            <img src="btr_add1tobasket.gif" alt="Add to basket" width="119" height="33" style="display:inline-block;">
          </a>
        </p>
      </div>
      <div>
        <img class="responsive-img" src="KITBLT.jpg" alt="Battery life test kit" width="225" height="145">
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

  <!-- right links placeholders preserved -->
  <div class="right-links" aria-hidden="true" style="margin-top:1rem;">
    <p><!-- #include virtual="/google_rightlinks.asp" --></p>
    <p><!-- #include virtual="/google_rightlinks.asp" --></p>
    <p><!-- #include virtual="/google_rightlinks.asp" --></p>
  </div>

  <!-- Print-only related products info -->
  <div class="print-only-products" style="display: none; margin-top: 1.5rem; padding: 1rem; border-top: 2px solid #2c5aa0;">
    <p style="margin: 0; font-weight: bold; color: #2c5aa0;">Where can I find related products? miniscience.com</p>
  </div>

</section>

<!-- MiniScience / Miniscience product embed widget -->
<section class="related-projects">
  <h3>Related Science Projects &amp; Kit</h3>
  
  <!-- Kit/Product Section (arriba) -->
  <div class="related-grid" style="margin-bottom: 1.5rem;">
    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <div style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <div id="abc_147" class="abantecart_product" data-product-id="1680" data-language="en" data-currency="USD">
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
  </div>
</section>

<section class="faq">
  <h3>Frequently Asked Questions</h3>
  <details>
    <summary>What type of light bulb should I use for battery testing?</summary>
    <p>Use a low-voltage flashlight bulb (typically 1.5V to 3V) that matches your battery configuration. The bulb should draw a consistent, measurable current to provide accurate test results.</p>
  </details>
  <details>
    <summary>How do I ensure accurate timing in my battery test?</summary>
    <p>Set up your test in a location where the bulb can be monitored regularly. Use a reliable stopwatch or timer, and check the bulb at consistent intervals (every few hours). Record the exact time when the bulb becomes too dim to be useful.</p>
  </details>
  <details>
    <summary>Can I test rechargeable batteries with this method?</summary>
    <p>Yes, but keep in mind that rechargeable batteries (NiMH, NiCd, Li-ion) have different voltage characteristics than alkaline batteries. Test them separately and note that their performance curves differ significantly from disposable batteries.</p>
  </details>
  <details>
    <summary>Why do some expensive batteries last longer?</summary>
    <p>Premium batteries often use higher-quality materials, better electrolyte formulations, and improved manufacturing processes. This results in more efficient chemical reactions and longer-lasting power output.</p>
  </details>
</section>

<!-- Bottom print control -->
<div class="print-controls" style="margin:1rem 0;">
  <button class="btn-print" id="print-bottom" aria-label="Print this page">Print</button>
</div>

<script>
// Print only the project-body section by cloning content into a temporary container
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

</article>

<?php
include __DIR__ . '/../../includes/project-footer.php';
?>
