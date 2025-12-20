<?php
// Project: Double Helix DNA Model
$project_title = "Double Helix DNA Model";
$project_description = "Build a detailed 3D model of DNA's double helix structure using colored balls and toothpicks to learn about molecular biology, genetics, and the building blocks of life.";
$project_keywords = "DNA model, double helix, genetics, molecular biology, cytosine, guanine, adenine, thymine, nucleotides, molecular structure";
$project_grade_level = "Intermediate"; // recommended
$project_subject = "Biology, Genetics, Molecular Biology";
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
    .intro-section { display: grid; grid-template-columns: 3fr 2fr; gap: 1rem; align-items: start; background: #f8f9ff; border: 1px solid #ff99cc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .intro-section img { width: 100%; height: auto; display: block; }
    .materials-section { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; margin: 1rem 0; }
    .materials-section img { width: 100%; height: auto; display: block; }
    .instructions-section { display: grid; grid-template-columns: 3fr 2fr; gap: 1rem; align-items: start; margin: 1rem 0; }
    .instructions-section img { width: 100%; height: auto; display: block; }
    .molecules-section { display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 1rem; align-items: center; margin: 1rem 0; background: #f0f8f0; padding: 1rem; border-radius: 8px; }
    .molecules-section img { width: 100%; height: auto; display: block; }
    .pricing-section { background: #fff6f8; border: 1px solid #ff0000; padding: 1rem; border-radius: 8px; margin: 1rem 0; text-align: center; }
    .purchase-buttons { display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap; align-items: center; margin-top: 1rem; }
    .purchase-buttons img { height: 33px; width: auto; }
    .responsive-img { max-width: 100%; height: auto; display: block; }
    .section-title { color: #ff0000; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .sub-title { color: #008000; font-weight: normal; font-size: 1rem; margin: 0.75rem 0 0.5rem 0; }
    .center { text-align: center; }
    /* Banner row (bottom of the page) */
    .banner-row { display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-top: 0.75rem; }
    .banner-row .banner { flex: 1 1 0; text-align: center; }
    .banner-row img { max-width: 100%; height: auto; display: inline-block; }
    @media (max-width: 720px) {
      .intro-section { grid-template-columns: 1fr; text-align: center; }
      .materials-section { grid-template-columns: 1fr; }
      .instructions-section { grid-template-columns: 1fr; }
      .molecules-section { grid-template-columns: 1fr; text-align: center; }
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
      <p>Making a model is the best way for learning about the elements of a DNA molecule. You can use your model as a separate school project or as an addition to any DNA related science project.</p>
      <p>A well constructed model enhances your display and results in a higher level of attention to your presentation.</p>
      <p>The model described here is the same model suggested in <a href="http://www.ScienceProject.com">ScienceProject.com</a> for DNA related science fair projects.</p>
    </div>
    <div>
      <img src="DNA_model5.jpg" alt="Completed DNA double helix model" class="responsive-img">
    </div>
  </div>

  <div class="materials-section">
    <div>
      <h3 class="section-title">Material:</h3>
      <p>To construct a DNA model you will need the following material:</p>
      <ul>
        <li>Styrofoam balls (about 100)</li>
        <li>Double end toothpicks (75)</li>
        <li>Wooden or metal laboratory stand</li>
        <li>Brushes for painting the balls</li>
        <li>Additional material such as paint or water color, glue, string</li>
      </ul>
      <p>You may purchase all the required material separately from different local stores or you may prefer to order a kit; however, you should know that kits do not come with paint and glue.</p>
    </div>
    <div>
      <img src="box.jpg" alt="DNA model kit contents" class="responsive-img">
    </div>
  </div>

  <p>You may already have white glue and water color at home. If not, you may purchase paints and glues from any local hardware store or paint store.</p>

  <p>DNA model kit comes with 100 white balls that you must paint them with any water based or latex paint. (paint is not included)</p>

  <p>A kit also contains a base and a column that together form a stand for your DNA model.</p>

  <p>A stand makes it easier for your model to be transported from home to school or your science fair.</p>

  <p>Kit also includes brush and matching toothpicks for the balls.</p>

  <h3 class="section-title">Instructions:</h3>
  <p class="sub-title">This is a short instruction. If you purchase a kit, please use the URL or web address provided in the kit to access a more comprehensive instruction and tutorial for making your DNA model.</p>

  <div class="instructions-section">
    <div>
      <p>Decide what colors you want to use for small molecules forming each large DNA molecule. The model shown above is based on colors suggested in the kit instructions; however, you may select any other colors for the balls.</p>
      <p>Paint all the balls and let them dry. Depending on the paint it may take up to 24 hours for paints to dry.</p>
      <p>Assemble your stand if it is not already done. A wooden stand is preferred for your model because of lighter weight.</p>
    </div>
    <div>
      <img src="DNA_model10.jpg" alt="DNA model assembly process" class="responsive-img">
    </div>
  </div>

  <p>Start from the base and connect the molecules to each other using toothpicks. The large DNA molecule must wrap around the stand's column.</p>

  <p>For the first row make a pair of C-G (Cytosine-Guanine). Add the phosphates to the backbone and then assemble the second row that again can be C-G or A-T (Adenine-Thymine).</p>

  <p>Continue the ladder until you run out of balls.</p>

  <p>Note that in constructing the DNA model, we used one ball for each small molecule forming the DNA polymer.</p>

  <p class="sub-title">You may use the same balls as atoms to make models of different chemicals.</p>

  <div class="molecules-section">
    <div>
      <img src="Acetone.jpg" alt="Acetone molecule model" class="responsive-img">
    </div>
    <div>
      <p class="sub-title">Image on the left shows a molecule of Acetone. White balls are Hydrogen. Black balls are carbon, Red ball is Oxygen (connected with two bonds)</p>
      <p class="sub-title">Image on the right is a molecule of Benzene.</p>
    </div>
    <div>
      <img src="C6H6.jpg" alt="Benzene molecule model" class="responsive-img">
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
        <div id="abc_828" class="abantecart_product" data-product-id="646" data-language="en" data-currency="USD">
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
        <div id="abc_534" class="abantecart_product" data-product-id="7147" data-language="en" data-currency="USD">
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
        <li id="abc_176046721898671" class="abantecart_category" data-category-id="71" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760467260084106" class="abantecart_category" data-category-id="106" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760467276347139" class="abantecart_category" data-category-id="139" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760467292865104" class="abantecart_category" data-category-id="104" data-language="en" data-currency="USD">
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
    <summary>What do the different colored balls represent in a DNA model?</summary>
    <p>Each color represents a different component: typically one color for each of the four bases (Adenine, Thymine, Cytosine, Guanine), another for phosphate groups, and another for sugar (deoxyribose). The specific colors can vary, but consistency is important throughout your model.</p>
  </details>
  <details>
    <summary>How do I show the base pairing rules in my model?</summary>
    <p>DNA follows strict base pairing rules: Adenine (A) always pairs with Thymine (T), and Cytosine (C) always pairs with Guanine (G). Make sure your model shows these pairs connected across the center of the double helix, like rungs on a twisted ladder.</p>
  </details>
  <details>
    <summary>Why does the DNA model need to twist around the stand?</summary>
    <p>The "double helix" structure means DNA naturally twists in a spiral shape. This twisting is crucial to DNA's function and stability. Your model should show this helical twist to accurately represent real DNA structure.</p>
  </details>
  <details>
    <summary>Can I use this same kit to make other molecular models?</summary>
    <p>Yes! The balls and toothpicks can be used to create models of many different molecules like water, methane, caffeine, or other organic compounds. Just use different colors to represent different atoms (carbon, hydrogen, oxygen, nitrogen, etc.).</p>
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