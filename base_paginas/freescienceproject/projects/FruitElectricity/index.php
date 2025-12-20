<?php
// Project: Fruit Electricity - Chemistry & Physics Experiment
$project_title = "Make Electricity from Fruits - Chemical Energy to Electrical Energy";
$project_description = "Create electrical energy from fruits using copper and zinc electrodes. Learn about chemical reactions, electrochemistry, and how batteries work through this hands-on fruit battery experiment.";
$project_keywords = "fruit battery, electricity from fruits, chemical energy, electrical energy, electrochemistry, copper zinc electrodes, lemon battery, potato battery";
$project_grade_level = "Elementary"; // ages 10-16
$project_subject = "Chemistry, Physics, Electrical Science";
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
    .intro-section { background: #f8f9ff; border: 1px solid #2c5aa0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .problem-section { background: #fff6f0; border: 1px solid #ff6b35; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .research-section { background: #f0fff0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .materials-section { background: #f0f8ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .procedure-section { background: #fff8f0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .results-section { background: #f8fff8; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .safety-section { background: #ffe6e6; border: 1px solid #ff4444; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .extensions-section { background: #f0f0ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .kit-highlight { background: #fff0f5; border: 1px solid #ff69b4; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .experiments-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin: 1rem 0; }
    .experiment-card { background: white; border: 1px solid #ddd; padding: 1rem; border-radius: 8px; }
    .responsive-img { max-width: 100%; height: auto; display: block; }
    .section-title { color: #2c5aa0; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .highlight-text { color: #2c5aa0; font-weight: bold; }
    .center { text-align: center; }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; margin: 1rem 0; }
    .voltage-table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
    .voltage-table th, .voltage-table td { border: 1px solid #ddd; padding: 0.75rem; text-align: left; }
    .voltage-table th { background: #f0f8ff; color: #2c5aa0; font-weight: bold; }
    .voltage-table tr:nth-child(even) { background: #f9f9f9; }
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
      .two-col { grid-template-columns: 1fr; }
      .experiments-grid { grid-template-columns: 1fr; }
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
    <p>The <strong class="highlight-text">Fruit Electricity Experiment</strong> is one of the most famous and successful electricity projects that can be performed by students aged 10-16. This project demonstrates how to convert chemical energy into electrical energy using the same scientific principles that power modern batteries.</p>
  </div>

  <div class="two-col">
    <div>
      <p><strong>Alternative Names for This Project:</strong></p>
      <ol>
        <li><strong class="highlight-text">Fruit Power or Fruit Battery</strong></li>
        <li><strong class="highlight-text">Convert Chemical Energy to Electrical Energy</strong></li>
        <li><strong class="highlight-text">Potato Battery or Lemon Battery</strong></li>
      </ol>
    </div>
    <div class="center">
      <img src="lemonpower.jpg" alt="Lemon battery demonstration" class="responsive-img" style="max-width: 200px;" />
    </div>
  </div>

  <div class="problem-section">
    <h3 class="section-title">🔬 Problem Statement</h3>
    <p><strong>Research Question:</strong> Can fruits and other household materials generate measurable electrical energy?</p>
    <p>We know that batteries power our devices, but how do they actually work? Can we create our own battery using common fruits and simple materials? This experiment will help us understand the fundamental principles of electrochemistry and energy conversion.</p>
  </div>

  <div class="research-section">
    <h3 class="section-title">📚 Background Research</h3>
    
    <p><strong class="highlight-text">How Fruit Batteries Work:</strong> Making electricity from chemicals is based on the same scientific principles that power all modern batteries. When you insert copper and zinc electrodes into an acidic liquid (like fruit juice), you create a chemical reaction between the electrodes and the electrolyte that produces electricity.</p>
    
    <p><strong>The Science Behind It:</strong></p>
    <ul>
      <li><strong>Electrodes:</strong> Copper (positive terminal) and Zinc (negative terminal) act as conductors</li>
      <li><strong>Electrolyte:</strong> Acidic fruit juice allows ions to flow between electrodes</li>
      <li><strong>Chemical Reaction:</strong> Zinc gives up electrons (oxidation) while copper accepts them (reduction)</li>
      <li><strong>Electron Flow:</strong> Movement of electrons through external circuit creates electrical current</li>
    </ul>

    <p><strong>Real-World Applications:</strong></p>
    <ul>
      <li><strong>Battery Technology:</strong> Understanding how alkaline, lithium, and car batteries work</li>
      <li><strong>Renewable Energy:</strong> Principles apply to fuel cells and solar panel storage systems</li>
      <li><strong>Environmental Science:</strong> Developing eco-friendly energy storage solutions</li>
      <li><strong>Emergency Power:</strong> Creating backup power sources in emergency situations</li>
    </ul>
  </div>

  <div class="problem-section">
    <h3 class="section-title">🧪 Hypothesis</h3>
    <p><strong>Our Prediction:</strong> We think that acidic fruits will produce measurable electrical voltage when copper and zinc electrodes are inserted, with some fruits producing more electricity than others based on their acidity levels and electrolyte concentration.</p>
  </div>

  <div class="materials-section">
    <h3 class="section-title">🛠️ Materials Needed</h3>
    
    <div class="two-col">
      <div>
        <p><strong>Electrodes & Equipment:</strong></p>
        <ul>
          <li><strong>Copper Electrode</strong> (positive terminal)</li>
          <li><strong>Zinc Electrode</strong> (negative terminal)</li>
          <li><strong>Multi-meter</strong> (capable of measuring millivolts)</li>
          <li><strong>Flashlight bulb</strong> (1.2 Volts)</li>
          <li><strong>Screw base or socket</strong> for light bulb</li>
          <li><strong>Insulated wires</strong> (various colors)</li>
          <li><strong>Alligator clips</strong> for connections</li>
        </ul>
      </div>
      <div>
        <p><strong>Testing Materials:</strong></p>
        <ul>
          <li><strong>Various fruits:</strong> Lemons, limes, oranges, apples, potatoes</li>
          <li><strong>Fruit juices:</strong> Fresh and bottled varieties</li>
          <li><strong>Household liquids:</strong> Vinegar, baking soda solution</li>
          <li><strong>Plastic or ceramic cups</strong> for liquid testing</li>
          <li><strong>Mounting board</strong> (optional)</li>
        </ul>
      </div>
    </div>

    <div class="kit-highlight">
      <div class="two-col">
        <div>
          <img src="howto1.jpg" alt="Make Electricity Kit components" class="responsive-img" />
        </div>
        <div>
          <h4>🔬 Make Electricity Science Kit - $28</h4>
          <p>When your science project involves making electricity, the biggest challenge is detecting and measuring the small amount of electricity produced. The Make Electricity Kit makes it easy to complete your project successfully.</p>
          <p><strong>Kit Contains:</strong> All electrodes, multi-meter, wires, alligator clips, bulb, socket, and mounting materials needed for professional results.</p>
        </div>
      </div>
    </div>
  </div>

  <div class="procedure-section">
    <h3 class="section-title">📋 Experimental Procedures</h3>
    
    <h4>Basic Fruit Battery Setup:</h4>
    <ol>
      <li><strong>Prepare the Fruit:</strong> Select a fresh, juicy lemon or other citrus fruit</li>
      <li><strong>Insert Electrodes:</strong> Push the copper and zinc electrodes about 2 inches apart into the fruit</li>
      <li><strong>Connect Wires:</strong> Attach alligator clips to each electrode</li>
      <li><strong>Test Voltage:</strong> Connect the multi-meter leads to measure voltage output</li>
      <li><strong>Record Results:</strong> Note the voltage reading and any observations</li>
      <li><strong>Test Light Bulb:</strong> Connect the 1.2V bulb to see if it lights up</li>
    </ol>

    <div class="experiments-grid">
      <div class="experiment-card">
        <h5><strong>Experiment 1: Fruit Comparison</strong></h5>
        <p>Test which fruits produce the most electricity. Compare lemons, limes, oranges, apples, and potatoes.</p>
      </div>
      <div class="experiment-card">
        <h5><strong>Experiment 2: Juice Testing</strong></h5>
        <p>Test fresh fruit juices vs. bottled juices to see which produces more voltage.</p>
      </div>
      <div class="experiment-card">
        <h5><strong>Experiment 3: Liquid Alternatives</strong></h5>
        <p>Test household liquids like vinegar, baking soda solution, and sports drinks.</p>
      </div>
      <div class="experiment-card">
        <h5><strong>Experiment 4: Electrode Materials</strong></h5>
        <p>Replace kit electrodes with coins, nails, or other metal objects to test effectiveness.</p>
      </div>
      <div class="experiment-card">
        <h5><strong>Experiment 5: Series Connection</strong></h5>
        <p>Connect multiple fruit batteries in series to increase total voltage output.</p>
      </div>
      <div class="experiment-card">
        <h5><strong>Experiment 6: Duration Testing</strong></h5>
        <p>Measure how long a fruit battery maintains its voltage over time.</p>
      </div>
    </div>
  </div>

  <div class="results-section">
    <h3 class="section-title">📊 Expected Results & Data Collection</h3>
    
    <p><strong class="highlight-text">Typical Voltage Outputs:</strong> Most fruit batteries produce between 0.5 to 1.0 volts, which is detectable with a sensitive multi-meter but may not be enough to light a standard bulb consistently.</p>
    
    <table class="voltage-table">
      <thead>
        <tr>
          <th>Fruit/Material</th>
          <th>Expected Voltage (V)</th>
          <th>Acidity Level</th>
          <th>Performance Rating</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Lemon</td>
          <td>0.7 - 0.9</td>
          <td>High (pH 2.0)</td>
          <td>Excellent</td>
        </tr>
        <tr>
          <td>Lime</td>
          <td>0.6 - 0.8</td>
          <td>High (pH 2.0)</td>
          <td>Excellent</td>
        </tr>
        <tr>
          <td>Orange</td>
          <td>0.5 - 0.7</td>
          <td>Medium (pH 3.3)</td>
          <td>Good</td>
        </tr>
        <tr>
          <td>Apple</td>
          <td>0.3 - 0.5</td>
          <td>Medium (pH 3.5)</td>
          <td>Fair</td>
        </tr>
        <tr>
          <td>Potato</td>
          <td>0.4 - 0.6</td>
          <td>Low (pH 6.0)</td>
          <td>Good</td>
        </tr>
        <tr>
          <td>Vinegar</td>
          <td>0.8 - 1.0</td>
          <td>Very High (pH 2.5)</td>
          <td>Excellent</td>
        </tr>
      </tbody>
    </table>

    <p><strong>Data Collection Tips:</strong></p>
    <ul>
      <li><strong>Multiple Measurements:</strong> Take 3 readings for each fruit and calculate the average</li>
      <li><strong>Time Recording:</strong> Note how voltage changes over time (immediate, 5 min, 15 min, 1 hour)</li>
      <li><strong>Environmental Factors:</strong> Record temperature and humidity as they affect performance</li>
      <li><strong>Electrode Condition:</strong> Observe any corrosion or chemical changes on electrodes</li>
    </ul>
  </div>

  <div class="extensions-section">
    <h3 class="section-title">🚀 Advanced Investigations</h3>
    
    <h4>Scientific Method Extensions:</h4>
    <ul>
      <li><strong>Variable Testing:</strong> Change electrode distance, depth, or surface area</li>
      <li><strong>Temperature Effects:</strong> Test cold vs. room temperature vs. warm fruits</li>
      <li><strong>pH Correlation:</strong> Measure fruit pH and correlate with voltage output</li>
      <li><strong>Concentration Studies:</strong> Dilute fruit juices to test concentration effects</li>
      <li><strong>Alternative Electrodes:</strong> Test different metal combinations (iron/copper, aluminum/zinc)</li>
    </ul>
    
    <h4>Engineering Applications:</h4>
    <ul>
      <li><strong>LED Circuits:</strong> Use low-voltage LEDs that light up with fruit power</li>
      <li><strong>Digital Clock:</strong> Power a digital clock with multiple fruit batteries</li>
      <li><strong>Capacitor Charging:</strong> Store fruit electricity in capacitors for later use</li>
      <li><strong>Solar Comparison:</strong> Compare fruit batteries to small solar panels</li>
    </ul>
  </div>

  <div class="safety-section">
    <h3 class="section-title">⚠️ Safety Considerations</h3>
    <ul>
      <li><strong>Sharp Objects:</strong> Adult supervision required when handling electrodes and wires</li>
      <li><strong>Small Parts:</strong> Keep electrodes and clips away from small children</li>
      <li><strong>Eye Protection:</strong> Wear safety glasses when inserting electrodes into fruits</li>
      <li><strong>Hand Washing:</strong> Wash hands after handling electrodes and testing materials</li>
      <li><strong>Electrical Safety:</strong> Use only low-voltage materials and avoid water near equipment</li>
      <li><strong>Chemical Awareness:</strong> Some electrode metals may cause skin irritation</li>
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
    <summary>Why don't all fruits produce the same amount of electricity?</summary>
    <p>The amount of electricity depends on the fruit's acidity level (pH), water content, and mineral concentration. Citrus fruits like lemons and limes are highly acidic (pH 2.0-2.5), making them excellent electrolytes, while less acidic fruits like apples produce lower voltages.</p>
  </details>
  <details>
    <summary>Can I get shocked by a fruit battery?</summary>
    <p>No, fruit batteries produce very low voltages (typically 0.5-1.0 volts) and minimal current, making them completely safe. The electricity produced is far below levels that could cause any harm to humans.</p>
  </details>
  <details>
    <summary>How long will a fruit battery last?</summary>
    <p>A fresh fruit battery typically maintains good voltage for 1-3 days, depending on the fruit's freshness and environmental conditions. As the fruit dries out or the electrodes corrode, the voltage gradually decreases.</p>
  </details>
  <details>
    <summary>Why won't my fruit battery light up a regular flashlight bulb?</summary>
    <p>Most flashlight bulbs require 1.5-3.0 volts and significant current to operate. A single fruit battery usually produces only 0.5-1.0 volts. Try connecting multiple fruits in series (positive to negative) to increase voltage, or use a low-voltage LED that requires less power.</p>
  </details>
  <details>
    <summary>What's the best fruit for making electricity?</summary>
    <p>Lemons are generally the best because they're highly acidic (pH 2.0), have high water content, and are readily available. Limes work equally well, followed by oranges and grapefruits. Surprisingly, potatoes also work well due to their electrolyte content.</p>
  </details>
  <details>
    <summary>Can I use different metals instead of copper and zinc?</summary>
    <p>Yes! Try coins (copper pennies and zinc-coated nails), iron nails and copper wire, or aluminum foil and copper strips. Different metal combinations will produce different voltages based on their position in the electrochemical series.</p>
  </details>
  <details>
    <summary>How does this relate to real batteries?</summary>
    <p>Fruit batteries work on the same electrochemical principles as commercial batteries. Both use two different metals (electrodes) separated by an electrolyte solution. The main difference is that commercial batteries use more efficient materials and are designed for consistent, long-term power output.</p>
  </details>
  <details>
    <summary>Can I connect multiple fruit batteries together?</summary>
    <p>Absolutely! Connect them in series (positive electrode of one fruit to negative electrode of the next) to add up the voltages. Three 0.8V lemon batteries in series will produce about 2.4V, which might be enough to light an LED or power a small digital clock.</p>
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