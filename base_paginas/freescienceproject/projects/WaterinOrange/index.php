<?php
// Project: Water Content in Oranges - Food Science Investigation
$project_title = "How Much Water Is in an Orange? - Food Science Experiment";
$project_description = "Discover the water content of oranges through evaporation and precise measurement. Learn about food preservation, the water cycle, and quantitative analysis in this chemistry and biology investigation.";
$project_keywords = "water content, orange, food science, evaporation, dehydration, food preservation, quantitative analysis, chemistry experiment";
$project_grade_level = "Elementary to Middle"; // ages 8-14
$project_subject = "Chemistry, Biology, Food Science";
$project_difficulty = "Beginner to Intermediate";

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
    .intro-section { background: #f8f9ff; border: 1px solid #2c5aa0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .problem-section { background: #fff6f0; border: 1px solid #ff6b35; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .research-section { background: #f0fff0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .materials-section { background: #f0f8ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .procedure-section { background: #fff8f0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .results-section { background: #f8fff8; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .safety-section { background: #ffe6e6; border: 1px solid #ff4444; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .extensions-section { background: #f0f0ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .responsive-img { max-width: 100%; height: auto; display: block; }
    .section-title { color: #2c5aa0; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .highlight-text { color: #2c5aa0; font-weight: bold; }
    .center { text-align: center; }
    .image-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin: 1rem 0; }
    .image-card { background: white; padding: 0.5rem; border-radius: 8px; text-align: center; }
    .formula-box { background: #e6f3ff; border: 1px solid #2c5aa0; padding: 1rem; margin: 1rem 0; border-radius: 8px; font-family: monospace; }
    /* Ensure all paragraphs have dark text for readability */
    p { color: #333; line-height: 1.6; }
    /* Footer styles - ensure all text in footer is white */
    footer, footer *, 
    .footer, .footer *,
    .project-footer, .project-footer *,
    [class*="footer"] *, [id*="footer"] * {
      color: white !important;
      fill: white !important;
    }
    /* Banner row (bottom of the page) */
    .banner-row { display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-top: 0.75rem; }
    .banner-row .banner { flex: 1 1 0; text-align: center; }
    .banner-row img { max-width: 100%; height: auto; display: inline-block; }
    @media (max-width: 720px) {
      .banner-row { flex-direction: column; gap: 0.5rem; }
      .image-grid { grid-template-columns: 1fr; }
    }
    /* Data table styling */
    .data-table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
    .data-table th, .data-table td { border: 1px solid #ddd; padding: 0.75rem; text-align: left; }
    .data-table th { background: #2c5aa0; color: white; font-weight: bold; }
    .data-table tr:nth-child(even) { background: #f8f9fa; }
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
    <p>The <strong class="highlight-text">Water Content Analysis</strong> is a fascinating food science investigation that reveals how much water is hidden inside fresh fruits. This hands-on experiment teaches students about evaporation, food preservation, and quantitative measurement techniques.</p>
  </div>

  <div class="center">
    <img src="WaterinOrange.gif" alt="How much water is in an orange - food science experiment" class="responsive-img" style="max-width: 400px; margin: 1rem auto;" />
  </div>

  <div class="problem-section">
    <h3 class="section-title">Problem Statement</h3>
    <p><strong>Research Question:</strong> What percentage of an orange is water?</p>
    <p>Fresh fruits contain significant amounts of water, but exactly how much? Through controlled dehydration and precise measurement, we can determine the water content of common fruits and understand why food preservation techniques work.</p>
  </div>

  <div class="research-section">
    <h3 class="section-title">Background Research</h3>
    
    <p><strong class="highlight-text">Water in Living Things:</strong> All living organisms, including humans and plants, must have water to survive. Water is essential for cellular processes, nutrient transport, and maintaining structural integrity in fruits and vegetables.</p>
    
    <p><strong class="highlight-text">The Water Cycle:</strong> Water gets recycled through evaporation, condensation, and precipitation. Evaporation is the change of liquid water to water vapor, occurring constantly in nature and in laboratory conditions.</p>
    
    <h4>Food Preservation Through Dehydration</h4>
    <p>The food industry uses dehydration as a major preservation method. By removing water:</p>
    <ul>
      <li><strong>Bacterial Growth Stops:</strong> Microorganisms need water to reproduce</li>
      <li><strong>Storage Costs Decrease:</strong> Dried foods are lighter and more compact</li>
      <li><strong>Shelf Life Extends:</strong> Products last months or years instead of days</li>
      <li><strong>Seasonal Availability:</strong> Fruits can be preserved year-round</li>
    </ul>
    
    <p><strong>Commercial Applications:</strong> Foods like raisins, dried apricots, jerky, instant coffee, and orange juice concentrate all use dehydration. Orange juice concentrate removes 80% of water content for efficient shipping and storage.</p>
  </div>

  <div class="problem-section">
    <h3 class="section-title">Hypothesis</h3>
    <p><strong>Our Prediction:</strong> Since oranges contain abundant liquid juice and feel heavy when fresh, we hypothesize that oranges contain <strong>50% or more water by weight</strong>.</p>
    
    <p><strong>Reasoning:</strong> Fresh oranges are juicy, heavy, and spoil quickly - all indicators of high water content. We expect the water percentage to be substantial but are conducting this experiment to determine the exact amount.</p>
  </div>

  <div class="materials-section">
    <h3 class="section-title">Materials Needed</h3>
    
    <p><strong>Essential Equipment:</strong></p>
    <ul>
      <li><strong>1 fresh orange</strong> (medium to large size for best results)</li>
      <li><strong>Digital kitchen scale</strong> (accurate to 1 gram minimum)</li>
      <li><strong>Sharp kitchen knife</strong> (adult supervision required)</li>
      <li><strong>Cutting board</strong></li>
      <li><strong>Paper plates</strong> (2-3 plates)</li>
      <li><strong>Aluminum foil</strong> (for covering and drying)</li>
    </ul>
    
    <p><strong>Drying Setup (Choose One Method):</strong></p>
    <ul>
      <li><strong>Heat Lamp Method:</strong> 150-watt desk lamp + small fan</li>
      <li><strong>Oven Method:</strong> Oven set to lowest temperature (170°F or less)</li>
      <li><strong>Air Dry Method:</strong> Warm, dry location with good airflow</li>
      <li><strong>Food Dehydrator:</strong> If available (most efficient)</li>
    </ul>
    
    <p><strong>Data Recording:</strong></p>
    <ul>
      <li><strong>Notebook</strong> for recording measurements</li>
      <li><strong>Calculator</strong> for percentage calculations</li>
      <li><strong>Timer</strong> to track drying progress</li>
    </ul>
  </div>

  <div class="procedure-section">
    <h3 class="section-title">Step-by-Step Procedure</h3>
    
    <h4>Preparation Phase:</h4>
    <ol>
      <li><strong>Initial Weighing:</strong> Weigh the whole orange and record the mass</li>
      <li><strong>Container Preparation:</strong> Weigh paper plates and aluminum foil separately</li>
      <li><strong>Setup Documentation:</strong> Record all initial measurements in your data table</li>
    </ol>
    
    <h4>Sample Preparation:</h4>
    <ol start="4">
      <li><strong>Slice the Orange:</strong> Cut into very thin slices (2-3mm thick) for faster drying</li>
      <li><strong>Remove Seeds:</strong> Take out any seeds and weigh them separately if desired</li>
      <li><strong>Arrange Slices:</strong> Spread orange slices on paper-lined aluminum foil without overlapping</li>
    </ol>
    
    <h4>Drying Process:</h4>
    <ol start="7">
      <li><strong>Heat Source Setup:</strong> Position lamp 12 inches above slices, fan 10 feet away for airflow</li>
      <li><strong>Monitor Progress:</strong> Check and weigh samples every 2-4 hours</li>
      <li><strong>Complete Dehydration:</strong> Continue until weight remains constant (4-36 hours)</li>
      <li><strong>Final Weighing:</strong> Weigh completely dried orange slices</li>
    </ol>
  </div>

  <div class="center">
    <div class="image-grid">
      <div class="image-card">
        <img src="StartWeight.gif" alt="Weighing fresh orange on digital scale" class="responsive-img" />
        <p><small>Step 1: Weigh the orange, paper, and aluminum foil separately and record results</small></p>
      </div>
      <div class="image-card">
        <img src="Scale.gif" alt="Precise digital scale for accurate measurements" class="responsive-img" />
        <p><small>Step 2: Use a precise scale for maximum accuracy in measurements</small></p>
      </div>
      <div class="image-card">
        <img src="CutOrange.gif" alt="Cutting orange into thin slices" class="responsive-img" />
        <p><small>Step 3: Cut orange into thin slices and spread over paper and foil</small></p>
      </div>
      <div class="image-card">
        <img src="WetOrange.gif" alt="Fresh orange slices arranged for drying" class="responsive-img" />
        <p><small>Step 4: Keep slices in warm place with adequate airflow</small></p>
      </div>
      <div class="image-card">
        <img src="Weightagain.gif" alt="Weighing samples during drying process" class="responsive-img" />
        <p><small>Step 5: Weigh samples periodically to track drying progress</small></p>
      </div>
      <div class="image-card">
        <img src="DryOrange.gif" alt="Completely dried orange slices" class="responsive-img" />
        <p><small>Step 6: Weigh final dried slices to calculate water loss</small></p>
      </div>
    </div>
  </div>

  <div class="results-section">
    <h3 class="section-title">Data Recording & Analysis</h3>
    
    <h4>Sample Data Table:</h4>
    <table class="data-table">
      <thead>
        <tr>
          <th>Measurement</th>
          <th>Weight (grams)</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Fresh Orange (initial)</td>
          <td>309 g</td>
          <td>Whole orange before cutting</td>
        </tr>
        <tr>
          <td>Paper + Aluminum Foil</td>
          <td>15 g</td>
          <td>Container materials (to subtract later)</td>
        </tr>
        <tr>
          <td>Dried Orange Slices</td>
          <td>58 g</td>
          <td>After complete dehydration</td>
        </tr>
        <tr>
          <td>Water Lost</td>
          <td>251 g</td>
          <td>309g - 58g = 251g</td>
        </tr>
      </tbody>
    </table>
    
    <h4>Mathematical Analysis:</h4>
    <div class="formula-box">
      <p><strong>Percentage of Solids (Dry Matter):</strong></p>
      <p>Dry Weight ÷ Original Weight = 58g ÷ 309g = 0.1877 = <strong>18.8%</strong></p>
      
      <p><strong>Percentage of Water:</strong></p>
      <p>(Original Weight - Dry Weight) ÷ Original Weight</p>
      <p>(309g - 58g) ÷ 309g = 251g ÷ 309g = 0.8123 = <strong>81.2%</strong></p>
    </div>
    
    <h4>Conclusion:</h4>
    <p><strong class="highlight-text">Result: The orange consists of approximately 81% water and 19% solid matter.</strong></p>
    
    <p>This confirms our hypothesis that oranges contain more than 50% water, and shows that the vast majority of an orange's weight comes from its water content. This explains why fresh oranges spoil quickly and why dried fruit products are so much lighter and longer-lasting.</p>
  </div>

  <div class="safety-section">
    <h3 class="section-title">Safety Considerations</h3>
    <ul>
      <li><strong>Knife Safety:</strong> Adult supervision required when cutting oranges</li>
      <li><strong>Heat Source Safety:</strong> Keep lamps and heaters away from flammable materials</li>
      <li><strong>Electrical Safety:</strong> Ensure all electrical equipment is safely positioned</li>
      <li><strong>Food Safety:</strong> Use clean equipment and wash hands before handling food</li>
      <li><strong>Accurate Measurement:</strong> Handle scales carefully and record all data immediately</li>
    </ul>
  </div>

  <div class="extensions-section">
    <h3 class="section-title">Experiment Extensions & Variations</h3>
    
    <h4>Comparative Studies:</h4>
    <ul>
      <li><strong>Different Citrus Fruits:</strong> Compare water content in lemons, limes, grapefruits</li>
      <li><strong>Fruit Variety Testing:</strong> Test apples, grapes, strawberries, watermelon</li>
      <li><strong>Vegetable Analysis:</strong> Analyze water content in cucumbers, tomatoes, lettuce</li>
      <li><strong>Seasonal Variations:</strong> Test same fruit types in different seasons</li>
    </ul>
    
    <h4>Advanced Investigations:</h4>
    <ul>
      <li><strong>Temperature Effects:</strong> Compare drying rates at different temperatures</li>
      <li><strong>Surface Area Impact:</strong> Test thick vs. thin slices on drying time</li>
      <li><strong>Ripeness Studies:</strong> Compare water content in ripe vs. unripe fruits</li>
      <li><strong>Storage Conditions:</strong> Analyze how storage affects water content over time</li>
    </ul>
    
    <h4>Real-World Applications:</h4>
    <ul>
      <li><strong>Food Industry:</strong> Research commercial dehydration methods</li>
      <li><strong>Nutrition Analysis:</strong> Calculate how drying affects nutritional density</li>
      <li><strong>Preservation Methods:</strong> Study traditional food preservation techniques</li>
      <li><strong>Economic Impact:</strong> Analyze cost savings from food dehydration</li>
    </ul>
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
        <div id="abc_240" class="abantecart_product" data-product-id="2548" data-language="en" data-currency="USD">
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
        <div id="abc_695" class="abantecart_product" data-product-id="2350" data-language="en" data-currency="USD">
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
        <div id="abc_578" class="abantecart_product" data-product-id="1849" data-language="en" data-currency="USD">
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
        <li id="abc_1760563550202106" class="abantecart_category" data-category-id="106" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760563560089139" class="abantecart_category" data-category-id="139" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760563573152128" class="abantecart_category" data-category-id="128" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176056358807471" class="abantecart_category" data-category-id="71" data-language="en" data-currency="USD">
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
    <summary>How long does it take to completely dry orange slices?</summary>
    <p>Drying time varies from 4-36 hours depending on your method. Using a heat lamp with fan (recommended setup) typically takes 8-12 hours. Oven drying at low temperature takes 4-8 hours, while air drying can take 24-36 hours depending on humidity and airflow.</p>
  </details>
  <details>
    <summary>What should I do if my scale isn't accurate enough?</summary>
    <p>For best results, use a digital kitchen scale accurate to at least 1 gram. If you only have a less precise scale, use a larger orange or multiple oranges to increase the total weight, making percentage calculations more accurate. School science labs often have precise balances you can borrow.</p>
  </details>
  <details>
    <summary>Can I use other fruits for this experiment?</summary>
    <p>Absolutely! This method works for any fruit or vegetable. Try apples, grapes, tomatoes, cucumbers, or watermelon. Different fruits will have different water percentages, making for excellent comparative studies. Watermelon typically has even higher water content than oranges.</p>
  </details>
  <details>
    <summary>How do I know when the orange slices are completely dry?</summary>
    <p>Orange slices are fully dried when their weight remains constant between measurements (within 1-2 grams). They should feel brittle and break easily. If weight is still decreasing, continue drying. Properly dried orange slices will keep for months without spoiling.</p>
  </details>
  <details>
    <summary>Why do we slice the orange instead of drying it whole?</summary>
    <p>Thin slices dramatically increase surface area, allowing water to evaporate much faster. A whole orange might take weeks to dry completely and could develop mold before finishing. Slicing also ensures even drying and prevents the outer layer from forming a barrier that traps moisture inside.</p>
  </details>
  <details>
    <summary>What causes the variation in water content between different fruits?</summary>
    <p>Water content varies based on the fruit's cellular structure, growing conditions, ripeness, and variety. Fruits grown in dry climates may have less water, while those from humid areas have more. Riper fruits often have higher water content than unripe ones.</p>
  </details>
  <details>
    <summary>How does this relate to commercial food preservation?</summary>
    <p>Commercial food dehydration uses the same principle but with controlled temperature, humidity, and airflow. Industries create products like raisins, dried fruit, jerky, and instant foods by removing water to prevent bacterial growth and extend shelf life while concentrating flavors and nutrients.</p>
  </details>
  <details>
    <summary>Can I make this into a science fair project?</summary>
    <p>Yes! Compare water content across different fruits, test how ripeness affects water percentage, analyze seasonal variations, or investigate how drying temperature affects final results. Create graphs, document the process with photos, and research the commercial applications of your findings.</p>
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