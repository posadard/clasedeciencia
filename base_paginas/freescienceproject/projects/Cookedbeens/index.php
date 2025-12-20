<?php
// Project: Cooked Beans Experiment
$project_title = "Cooked Beans Experiment";
$project_description = "Compare the growth rates of lentil beans with different cooking times to understand how heat treatment affects seed germination and plant development.";
$project_keywords = "bean experiment, plant growth, germination, cooking effects, seed viability, botany project";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Biology, Botany, Plant Science";
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
    .experiment-section { border: 3px solid #2c5aa0; background: #f8f9ff; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; }
    .experiment-section h3 { color: #990099; font-size: 1.2rem; margin: 0 0 0.75rem 0; font-weight: bold; }
    .experiment-section p { margin-bottom: 0.75rem; line-height: 1.6; }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; }
    .responsive-img { max-width: 100%; height: auto; display: block; }
    .center { text-align: center; }
    /* Banner row (bottom of the page) */
    .banner-row { display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-top: 0.75rem; }
    .banner-row .banner { flex: 1 1 0; text-align: center; }
    .banner-row img { max-width: 100%; height: auto; display: inline-block; }
    @media (max-width: 720px) {
      .two-col { grid-template-columns: 1fr; }
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

  <div class="experiment-section">
    <h3>Problem:</h3>
    <p>How can we speed up the growth of a plant? The Goal of this experiment is to find out if heat and cooking will reduce or increase the growth of a plant.</p>
  </div>

  <div class="experiment-section">
    <h3>Research:</h3>
    <p>There are many farms and agriculture centers all over the world today. They all plant raw seeds, which result to all the vegetables, and fruit we eat as food. Yet, when beans are cooked for a few minutes, they may become more fertile because they have already absorbed some water. Heat from cooking may destroy many harmful bacteria resulting in healthier plants also. It may also quicken up the process since it is getting rid of all unwanted material from the crop. To complete this project and to benefit from it, we must do accurate measures and have correct information.</p>
  </div>

  <div class="experiment-section">
    <h3>Hypothesis:</h3>
    <p>When you boil a food, it will get rid of most bacteria and unwanted material. This means that if you cook the beans before planting them, it may speed up the process since there isn't many bacteria and such material to hold back the process of plant growth. In this case, the beans that are cooked more should grow better when planted than those less cooked or not cooked at all.</p>
  </div>

  <div class="experiment-section">
    <h3>Material:</h3>
    <p>For this experiment, we used lentil beans.</p>
    <p><strong>Additional materials needed:</strong></p>
    <ul>
      <li>½ pound of lentil beans</li>
      <li>7 petri dishes</li>
      <li>Paper towels</li>
      <li>Water for cooking and moistening</li>
      <li>A pot for boiling water</li>
      <li>A spoon for measuring beans</li>
      <li>Labels (A through G)</li>
      <li>Timer or clock</li>
    </ul>
  </div>

  <div class="experiment-section">
    <h3>Procedure:</h3>
    <ol>
      <li>Take half a pound of beans and put them in a pot that is 1/3 filled up with water.</li>
      <li>Take 7 petri dishes and label them with letters A-G.</li>
      <li>Cut out paper towels just to fit the bottom of the petri dishes.</li>
      <li>Take a spoon full of beans and put them in sample dish A. This is the sample that isn't cooked at all.</li>
      <li>Turn on the stove so the water starts to boil.</li>
      <li>After 5 minutes of boiling, take another spoon full of beans and put them in sample dish B. This is the sample that is cooked for five minutes.</li>
      <li>At the 10-minute mark, take a spoon full of beans and put them in sample dish C. This is the sample that is boiled for ten minutes.</li>
      <li>At the 15-minute mark, take a spoon full of beans and put them in sample dish D. This is the sample dish that is cooked for fifteen minutes.</li>
      <li>At the 20-minute mark, take a spoon full of beans and put them in sample dish E. This is the sample that is cooked for twenty minutes.</li>
      <li>At the 25-minute mark, put a spoon full of beans in sample dish F. This is the sample that is cooked for twenty-five minutes.</li>
      <li>At the 30-minute mark, take a spoon full of beans and put them in sample dish G. This is the sample that is boiled for thirty minutes.</li>
      <li>Take 7 moist paper towels and lay them on top of the samples to keep them moist.</li>
      <li>Place them next to a window in order for them to receive sunlight.</li>
      <li>Water them daily to keep them moist for 4-5 days and observe results.</li>
    </ol>
  </div>

  <div class="experiment-section">
    <h3>Record And Analyze Data:</h3>
    <p>Create a data table to record daily observations of each sample (A through G). Note:</p>
    <ul>
      <li>Number of beans that sprout in each dish</li>
      <li>Length of sprouts (in centimeters)</li>
      <li>General health and appearance of plants</li>
      <li>Any signs of decay or mold</li>
    </ul>
    <p><strong>Expected Results:</strong> Record your observations and compare which cooking time produces the best germination rate and plant growth.</p>
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

<!-- MiniScience / Miniscience product embed widget -->
<section class="related-projects">
  <h3>Related Science Projects &amp; Kits</h3>
  
  <!-- Kit/Products Section (arriba) -->
  <div class="related-grid" style="margin-bottom: 1.5rem;">
    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <div style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <div id="abc_718" class="abantecart_product" data-product-id="2396" data-language="en" data-currency="USD">
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
        <div id="abc_680" class="abantecart_product" data-product-id="2392" data-language="en" data-currency="USD">
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
        <li id="abc_1760464461106106" class="abantecart_category" data-category-id="106" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760464483492139" class="abantecart_category" data-category-id="139" data-language="en" data-currency="USD">
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
    <summary>Why might cooked beans grow differently than raw beans?</summary>
    <p>Cooking can affect seed viability in multiple ways: it may kill harmful bacteria and pathogens, but excessive heat can also damage the seed's internal structure and proteins needed for germination. The optimal cooking time varies by seed type.</p>
  </details>
  <details>
    <summary>What should I expect to see in my results?</summary>
    <p>Raw beans (sample A) typically have the highest germination rate. Lightly cooked beans (5-10 minutes) may show similar or slightly reduced germination, while heavily cooked beans (25-30 minutes) usually show poor or no germination due to protein denaturation.</p>
  </details>
  <details>
    <summary>How long should I observe the experiment?</summary>
    <p>Observe daily for at least 7 days. Lentil beans typically germinate within 2-4 days under proper conditions. Continue observations until you see clear differences between samples or until day 10.</p>
  </details>
  <details>
    <summary>What if some samples develop mold?</summary>
    <p>Mold growth indicates too much moisture or contamination. Remove moldy samples safely (with adult supervision), reduce watering frequency, and ensure good air circulation around remaining samples.</p>
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