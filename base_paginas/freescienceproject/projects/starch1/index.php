<?php
// Project: Starch Identification Test - Food Chemistry Experiment
$project_title = "Starch Identification Test - What Foods Contain Starch?";
$project_description = "Discover which foods and vegetables contain starch using iodine solution as a chemical indicator. Learn about carbohydrates and their role in nutrition through hands-on testing.";
$project_keywords = "starch test, iodine solution, carbohydrates, food chemistry, nutrition science, biochemistry experiment";
$project_grade_level = "Elementary to Middle"; // ages 10-16
$project_subject = "Chemistry, Nutrition Science";
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
    .project-body { max-width: 980px; margin: 0 auto; padding: 1rem; background: #ffffcc; }
    .project-title-simple { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color: #0000ff; font-size: 1.4rem; margin: 0 0 0.75rem 0; text-align: center; }
    .intro-section { background: #f8f9ff; border: 1px solid #0000ff; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .info-box { border: 1px solid #69c; background: #f4f8ff; padding: 0.75rem; color: #0033cc; font-family: Verdana, Geneva, Tahoma, sans-serif; margin: 1rem 0; }
    .highlight-box { border: 1px solid #ff6b35; background: #fff6f0; padding: 1rem; margin: 1rem 0; }
    .materials-box { border: 1px solid #34c759; background: #f0fff0; padding: 1rem; margin: 1rem 0; }
    .section-title { color: #0000ff; font-weight: bold; font-size: 1.1rem; margin: 1rem 0 0.5rem 0; }
    .highlight-text { color: #0000ff; font-weight: bold; }
    .materials-grid { display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; align-items: start; margin: 1rem 0; }
    .two-col { display: grid; grid-template-columns: 1fr 320px; gap: 1rem; align-items: start; margin: 1rem 0; }
    .responsive-img { max-width: 100%; height: auto; display: block; border-radius: 8px; }
    .center { text-align: center; }
    .sample-table { width: 100%; max-width: 500px; border-collapse: collapse; margin: 1rem auto; background: white; }
    .sample-table th, .sample-table td { border: 1px solid #ddd; padding: 0.75rem; text-align: left; }
    .sample-table th { background: #f0f8ff; font-weight: bold; }
    .results-table { width: 100%; border-collapse: collapse; margin: 1rem 0; background: white; }
    .results-table th, .results-table td { border: 1px solid #ddd; padding: 0.75rem; text-align: left; }
    .results-table th { background: #e8f5e8; font-weight: bold; }
    .positive { background: #d4edda; color: #155724; font-weight: bold; }
    .negative { background: #f8d7da; color: #721c24; font-weight: bold; }
    @media (max-width: 720px) {
      .materials-grid, .two-col { grid-template-columns: 1fr; }
      .banner-row { grid-template-columns: 1fr; }
      .sample-table, .results-table { font-size: 0.9rem; }
      .sample-table th, .sample-table td, .results-table th, .results-table td { padding: 0.5rem; }
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

  <h3 class="section-title">State the Problem:</h3>
  <p><strong>Research Question:</strong> What foods or vegetables contain starch?</p>

  <h3 class="section-title">Research the Problem:</h3>
  <div class="info-box">
    <p>Before we start, we need to know more about starch. Studies show that <strong>starch is a white, odorless, tasteless carbohydrate powder</strong> that is soluble in cold water. This information will help us extract starch from our samples for more accurate tests.</p>
    
    <p><strong>Biochemical Importance:</strong> Starch plays a vital role in the biochemistry of both plants and animals. It is made in green plants by photosynthesis and is one of the main forms in which plants store food. Animals obtain starch from plants and store it as glycogen. Both plants and animals convert starch to glucose when energy is needed.</p>
    
    <p><strong>Commercial Production:</strong> Commercially, starch is made chiefly from corn and potatoes, and is widely used as a food additive in many processed products.</p>
  </div>

  <h3 class="section-title">Hypothesis:</h3>
  <p>Starch is a substance found in most fruits and vegetables, which means that it is most likely present in our vegetable and fruit samples. Since starch is an inexpensive and widely available food product, it is being used as a food additive in many processed food products.</p>

  <h3 class="section-title">Materials Needed:</h3>
  <div class="materials-box">
    <ul>
      <li><strong>Iodine solution</strong> (potassium iodide) - main reagent</li>
      <li><strong>Dropper or pipette</strong> - for applying iodine</li>
      <li><strong>Small dishes or plates</strong> - for sample testing</li>
      <li><strong>Knife and cutting board</strong> - for sample preparation</li>
      <li><strong>Mortar and pestle</strong> (optional) - for crushing samples</li>
      <li><strong>Cold water</strong> - for extraction</li>
      <li><strong>Strainer or coffee filter</strong> - for filtering solutions</li>
      <li><strong>Safety gloves and goggles</strong> - protection when handling iodine</li>
      <li><strong>Data recording sheet</strong> - for results</li>
    </ul>
  </div>

  <h3 class="section-title">Experiment Procedure:</h3>
  
  <div class="highlight-box">
    <h4><strong>Testing Method:</strong></h4>
    <p>We will use <strong>iodine solution as a reagent for starch detection</strong>. One drop of this solution on any sample can detect starch by changing the color of the tested area to <strong>dark blue or black</strong>.</p>
    
    <p><strong>Sample Preparation:</strong></p>
    <ol>
      <li>For solid samples: Crush or cut into small pieces</li>
      <li>Add a small amount of cold or room temperature water</li>
      <li>Filter the solution to get a clear liquid (eliminates color interference)</li>
      <li>Test both the solid sample and the filtered liquid</li>
    </ol>
  </div>

  <h3 class="section-title">Test Samples:</h3>
  <table class="sample-table">
    <thead>
      <tr>
        <th>Natural Foods</th>
        <th>Processed Foods</th>
      </tr>
    </thead>
    <tbody>
      <tr><td>Rice</td><td>Milk</td></tr>
      <tr><td>Potatoes</td><td>Yogurt</td></tr>
      <tr><td>Grains (wheat, oats)</td><td>Ice Cream</td></tr>
      <tr><td>Apples</td><td>Macaroni/Pasta</td></tr>
      <tr><td>Carrots</td><td>Nuts</td></tr>
      <tr><td>Broccoli</td><td>Cereal</td></tr>
      <tr><td>Bananas</td><td>Bread</td></tr>
      <tr><td>Corn</td><td>Crackers</td></tr>
    </tbody>
  </table>

  <h3 class="section-title">Expected Results and Data Analysis:</h3>
  
  <div class="results-table">
    <table style="width: 100%; border-collapse: collapse; margin: 1rem 0; background: white;">
      <thead>
        <tr style="background: #e8f5e8;">
          <th style="border: 1px solid #ddd; padding: 0.75rem; font-weight: bold;">Food Item</th>
          <th style="border: 1px solid #ddd; padding: 0.75rem; font-weight: bold;">Expected Result</th>
          <th style="border: 1px solid #ddd; padding: 0.75rem; font-weight: bold;">Your Result</th>
          <th style="border: 1px solid #ddd; padding: 0.75rem; font-weight: bold;">Starch Level</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">Potatoes</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;" class="positive">Dark Blue/Black</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">_________</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">Very High</td>
        </tr>
        <tr>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">Rice</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;" class="positive">Dark Blue</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">_________</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">High</td>
        </tr>
        <tr>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">Bread/Pasta</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;" class="positive">Dark Blue</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">_________</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">High</td>
        </tr>
        <tr>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">Bananas (ripe)</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;" class="positive">Light Blue</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">_________</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">Medium</td>
        </tr>
        <tr>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">Apples</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;" class="negative">No Change</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">_________</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">Low/None</td>
        </tr>
        <tr>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">Milk</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;" class="negative">No Change</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">_________</td>
          <td style="border: 1px solid #ddd; padding: 0.75rem;">None</td>
        </tr>
      </tbody>
    </table>
  </div>

  <h3 class="section-title">Safety Considerations:</h3>
  <div class="highlight-box">
    <p><strong>Important Safety Notes:</strong></p>
    <ul>
      <li><strong>Iodine Solution:</strong> Can stain skin and clothing - wear gloves and old clothes</li>
      <li><strong>Adult Supervision:</strong> Required for younger children</li>
      <li><strong>Eye Protection:</strong> Wear safety goggles when handling chemicals</li>
      <li><strong>Ventilation:</strong> Work in well-ventilated area</li>
      <li><strong>Food Safety:</strong> Do not eat any tested food samples</li>
    </ul>
  </div>

  <h3 class="section-title">Advanced Extensions:</h3>
  <div class="info-box">
    <p><strong>Additional Research Opportunities:</strong></p>
    <ul>
      <li><strong>Ripeness Effect:</strong> Test fruits at different stages of ripeness</li>
      <li><strong>Processing Impact:</strong> Compare fresh vs. processed versions of the same food</li>
      <li><strong>Quantitative Analysis:</strong> Develop a color intensity scale (1-5)</li>
      <li><strong>Cooking Effects:</strong> Test how cooking affects starch content</li>
      <li><strong>Industrial Applications:</strong> Research starch use in non-food products</li>
      <li><strong>Nutritional Analysis:</strong> Compare starch results with nutrition labels</li>
    </ul>
  </div>

  <div class="two-col">
    <div>
      <h4 class="highlight-text">Why This Test Works:</h4>
      <p>Iodine forms a complex with starch molecules, creating a distinctive blue-black color. This reaction is specific to starch and doesn't occur with other carbohydrates like sugars.</p>
      
      <p><strong>Understanding Results:</strong></p>
      <ul>
        <li><strong>Dark Blue/Black:</strong> High starch content</li>
        <li><strong>Light Blue:</strong> Moderate starch content</li>
        <li><strong>No Color Change:</strong> Little to no starch</li>
      </ul>
    </div>
    <div>
      <img src="KITST.jpg" alt="Starch test kit with iodine solution and testing materials" class="responsive-img" />
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
        <div id="abc_30" class="abantecart_product" data-product-id="1712" data-language="en" data-currency="USD">
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
        <div id="abc_470" class="abantecart_product" data-product-id="3399" data-language="en" data-currency="USD">
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
        <div id="abc_210" class="abantecart_product" data-product-id="2392" data-language="en" data-currency="USD">
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
        <div id="abc_649" class="abantecart_product" data-product-id="3410" data-language="en" data-currency="USD">
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
        <li id="abc_1760558244889103" class="abantecart_category" data-category-id="103" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176055825552571" class="abantecart_category" data-category-id="71" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760558266306139" class="abantecart_category" data-category-id="139" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760558276802106" class="abantecart_category" data-category-id="106" data-language="en" data-currency="USD">
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
    <summary>Why does iodine turn blue-black when it contacts starch?</summary>
    <p>Iodine molecules fit into the helical structure of starch molecules, forming a complex that absorbs light differently and appears blue-black. This is a specific chemical reaction that only occurs with starch, not other carbohydrates like sugar.</p>
  </details>
  <details>
    <summary>What if my iodine test shows a brown color instead of blue?</summary>
    <p>Brown coloring usually indicates the iodine solution is too concentrated or there's no starch present. The iodine itself is brownish, so this is its natural color. Try diluting your iodine solution or testing a known starch source like potato.</p>
  </details>
  <details>
    <summary>Why do I need to filter some samples before testing?</summary>
    <p>Filtering removes color compounds that might interfere with seeing the blue color change. For example, red tomatoes or purple grapes might mask the blue color, giving false negative results even if starch is present.</p>
  </details>
  <details>
    <summary>Are there foods that might give unexpected results?</summary>
    <p>Yes! Ripe bananas have less starch than green bananas (starch converts to sugar as they ripen). Processed foods often have added starch even when the original ingredient doesn't. Always check ingredient labels.</p>
  </details>
  <details>
    <summary>How can I make this into a quantitative experiment?</summary>
    <p>Create a color intensity scale from 0-5, test multiple samples of the same food, measure the time it takes for color to develop, or test different concentrations of known starch solutions to create a standard curve.</p>
  </details>
  <details>
    <summary>Is this test safe to do at home?</summary>
    <p>Yes, with proper precautions. Use diluted iodine solution (available at pharmacies), wear gloves to prevent staining, work in a ventilated area, and don't eat any tested food samples. Adult supervision is recommended for younger children.</p>
  </details>
  <details>
    <summary>What's the difference between starch and sugar in foods?</summary>
    <p>Starch is a complex carbohydrate made of many glucose molecules linked together, while sugars are simple carbohydrates. Plants store energy as starch, but as fruits ripen, enzymes break starch down into sugars, which is why ripe fruits taste sweeter.</p>
  </details>
  <details>
    <summary>Can I test non-food items for starch?</summary>
    <p>Absolutely! Try testing paper, cardboard, some fabrics, or adhesives. Many industrial products use starch as a binding agent or filler. This can extend your project to explore starch's role in manufacturing.</p>
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