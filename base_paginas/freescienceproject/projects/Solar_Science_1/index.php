<?php
// Project: Solar Science Kit - DIY Photovoltaic Experiments
$project_title = "Solar Science Kit - Build Your Own Solar Powered Motor";
$project_description = "Discover the principles of photovoltaic energy by building a solar-powered motor system using common materials and learning about converting light into electricity.";
$project_keywords = "solar panel, photovoltaic, solar energy, motor, renewable energy, physics experiment, electricity generation";
$project_grade_level = "Elementary to Senior"; // ages 9 and older
$project_subject = "Physics, Environmental Science";
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
    .project-title-simple { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color: #ff0000; font-size: 1.6rem; margin: 0 0 0.75rem 0; text-align: center; }
    .intro-section { background: #f8f9ff; border: 1px solid #2c5aa0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .info-box { border: 1px solid #69c; background: #f4f8ff; padding: 0.75rem; color: #0033cc; font-family: Verdana, Geneva, Tahoma, sans-serif; margin: 1rem 0; }
    .highlight-box { border: 1px solid #ff6b35; background: #fff6f0; padding: 1rem; margin: 1rem 0; }
    .section-title { color: #2c5aa0; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .highlight-text { color: #2c5aa0; font-weight: bold; }
    .green-text { color: #008000; }
    .materials-grid { display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; align-items: start; margin: 1rem 0; }
    .two-col { display: grid; grid-template-columns: 1fr 250px; gap: 1rem; align-items: start; margin: 1rem 0; }
    .responsive-img { max-width: 100%; height: auto; display: block; border-radius: 8px; }
    .center { text-align: center; }
    .project-ideas { background: #f0fff0; border: 1px solid #008000; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .materials-list { background: #fff; border: 1px solid #ccc; padding: 1rem; border-radius: 8px; }
    .age-recommendation { background: #ffec99; border: 1px solid #f1c40f; padding: 0.75rem; border-radius: 8px; margin: 1rem 0; text-align: center; font-weight: bold; }
    @media (max-width: 720px) {
      .materials-grid, .two-col { grid-template-columns: 1fr; }
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
    <p><strong class="highlight-text">Discover the Power of Solar Energy!</strong> This DIY solar science project introduces you to the fascinating world of photovoltaic energy - the process of converting light directly into electricity. Build your own solar-powered motor system and explore renewable energy concepts that power satellites, solar cars, and emergency communication systems worldwide.</p>
  </div>

  <h3 class="section-title">What You'll Learn</h3>
  <p>Photovoltaic (PV) means "light to electricity." Solar cells, also called photovoltaic cells, are finding their way into thousands of applications worldwide. You've probably seen:</p>
  <ul>
    <li><strong>Satellites</strong> with their solar panels stretched out like giant wings</li>
    <li><strong>Solar-powered cars</strong> racing across landscapes in solar car competitions</li>
    <li><strong>Emergency call boxes</strong> along highways with solar panels silently powering communication equipment</li>
    <li><strong>Calculator solar panels</strong> that never need battery replacement</li>
  </ul>

  <h3 class="section-title">Materials Needed</h3>
  <div class="materials-grid">
    <div class="materials-list">
      <p><strong>Build your own solar science kit with these components:</strong></p>
      <ul>
        <li><strong>Small Solar Panel</strong> (photovoltaic cell) - converts light to electricity</li>
        <li><strong>Small Motor</strong> (high efficiency DC motor works best)</li>
        <li><strong>Reflective Disc</strong> - make your own using an old CD and holographic chrome wrap vinyl</li>
        <li><strong>Connecting wires</strong> with alligator clips</li>
        <li><strong>Bright light source</strong> (desk lamp, flashlight, or sunlight)</li>
        <li><strong>Multimeter</strong> (optional, for measuring voltage and current)</li>
      </ul>
    </div>
    <div>
      <img src="1.jpg" alt="Small solar panel for educational experiments" class="responsive-img" style="margin-bottom: 1rem;" />
      <img src="2.jpg" alt="Small DC motor for solar experiments" class="responsive-img" />
    </div>
  </div>

  <div class="age-recommendation">
    <p>Recommended for ages <strong>9 and older</strong> with adult supervision for younger children</p>
  </div>

  <h3 class="section-title">How to Build Your Solar Motor</h3>
  
  <div class="highlight-box">
    <h4><strong>Step-by-Step Instructions:</strong></h4>
    <ol>
      <li><strong>Connect the Components:</strong> Use wires to connect the positive terminal of the solar panel to the positive terminal of the motor, and negative to negative.</li>
      <li><strong>Create Your Display Disc:</strong> Take an old CD and carefully apply holographic chrome wrap vinyl to create a stunning visual display disc.</li>
      <li><strong>Attach the Disc:</strong> Secure the decorated disc to the motor shaft - it will spin and create beautiful light patterns.</li>
      <li><strong>Test Your System:</strong> Expose the solar panel to bright light and watch as the motor comes to life!</li>
      <li><strong>Experiment:</strong> Try different light angles, distances, and light sources to see how they affect motor performance.</li>
    </ol>
  </div>

  <div class="info-box">
    <p><strong>How It Works:</strong> When light hits the solar panel, photons knock electrons loose from atoms in the photovoltaic material, creating an electrical current. This current flows through the wires to power the motor, which spins your decorative disc. It's like magic, but it's actually physics!</p>
  </div>

  <div class="project-ideas">
    <h4><strong class="green-text">Science Project Ideas:</strong></h4>
    <p class="green-text">If you are using this project for your science fair, here are some research questions you can investigate:</p>
    
    <div class="two-col">
      <div>
        <p class="green-text"><strong>1. Light Angle Investigation:</strong> How does the angle of light affect the amount of electricity produced by a solar panel?</p>
        <p class="green-text"><strong>2. Surface Area Study:</strong> How does the light-exposed surface area of a solar panel affect the amount of electricity generated?</p>
        <p class="green-text"><strong>3. Color Spectrum Analysis:</strong> Which color light produces more electricity - red, blue, green, or white light?</p>
      </div>
      <div>
        <p class="green-text"><strong>4. Distance Effects:</strong> How does the distance from the light source affect solar panel performance?</p>
        <p class="green-text"><strong>5. Temperature Impact:</strong> Does the temperature of the solar panel affect its electrical output?</p>
        <p class="green-text"><strong>6. Shading Studies:</strong> How does partial shading affect overall solar panel performance?</p>
      </div>
    </div>
  </div>

  <h3 class="section-title">Advanced Experiments</h3>
  
  <div class="info-box">
    <p><strong>Measurement Tips:</strong> Use a multimeter to measure the voltage (in volts) and current (in amperes) produced by your solar panel under different conditions. Calculate power using the formula: Power (watts) = Voltage × Current</p>
  </div>

  <h4><strong>Variables to Test:</strong></h4>
  <ul>
    <li><strong>Light Intensity:</strong> Compare fluorescent bulbs, LED lights, incandescent bulbs, and natural sunlight</li>
    <li><strong>Weather Conditions:</strong> Test on sunny, cloudy, and overcast days (if using natural light)</li>
    <li><strong>Panel Orientation:</strong> Try different angles from 0° (flat) to 90° (vertical)</li>
    <li><strong>Seasonal Changes:</strong> Test the same setup at different times of year</li>
  </ul>

  <h3 class="section-title">Real-World Applications</h3>
  <p>Solar technology is rapidly advancing and becoming more affordable. Modern applications include:</p>
  <ul>
    <li><strong>Residential Solar:</strong> Rooftop panels that can power entire homes</li>
    <li><strong>Solar Farms:</strong> Large installations that feed electricity into the power grid</li>
    <li><strong>Portable Devices:</strong> Solar chargers for phones, tablets, and laptops</li>
    <li><strong>Transportation:</strong> Solar-powered boats, cars, and even experimental aircraft</li>
    <li><strong>Space Technology:</strong> Powering satellites, space stations, and Mars rovers</li>
  </ul>

  <h3 class="section-title">Safety Information</h3>
  <div class="highlight-box">
    <p><strong>Important Safety Notes:</strong></p>
    <ul>
      <li>Never look directly at bright lights used for testing</li>
      <li>Handle solar panels carefully - they can be fragile</li>
      <li>Be careful with sharp edges when cutting or applying vinyl wrap</li>
      <li>Adult supervision recommended for younger children</li>
      <li>Ensure all electrical connections are secure before testing</li>
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
        <div id="abc_724" class="abantecart_product" data-product-id="3070" data-language="en" data-currency="USD">
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
        <div id="abc_153" class="abantecart_product" data-product-id="3065" data-language="en" data-currency="USD">
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
        <div id="abc_95" class="abantecart_product" data-product-id="7347" data-language="en" data-currency="USD">
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
        <div id="abc_171" class="abantecart_product" data-product-id="3064" data-language="en" data-currency="USD">
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
        <li id="abc_176055125596697" class="abantecart_category" data-category-id="97" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176055127100893" class="abantecart_category" data-category-id="93" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176055130124587" class="abantecart_category" data-category-id="87" data-language="en" data-currency="USD">
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
    <summary>How much electricity does a small solar panel produce?</summary>
    <p>Small educational solar panels typically produce 0.5-6 volts and 10-100 milliamps, depending on their size and the brightness of light. This is enough to power small motors, LEDs, or calculators, but not larger devices.</p>
  </details>
  <details>
    <summary>Why does the motor spin faster in brighter light?</summary>
    <p>Brighter light provides more photons to knock electrons loose in the solar panel, creating more electrical current. More current means more power for the motor, causing it to spin faster. It's a direct relationship between light intensity and electrical output.</p>
  </details>
  <details>
    <summary>Can I use any type of light source for testing?</summary>
    <p>Yes, but different light sources produce different results. LED lights are energy-efficient but may not provide enough intensity. Incandescent bulbs work well but get hot. Halogen lamps provide excellent intensity. Natural sunlight is ideal when available.</p>
  </details>
  <details>
    <summary>What happens if I connect the wires backwards?</summary>
    <p>If you reverse the polarity (positive to negative), the motor will simply spin in the opposite direction. This won't damage most small DC motors, and it's actually a good way to demonstrate how electrical polarity affects motor rotation.</p>
  </details>
  <details>
    <summary>Why doesn't my motor work indoors with room lighting?</summary>
    <p>Regular room lighting (fluorescent or LED ceiling lights) usually isn't bright enough to generate sufficient current for motor operation. You need focused, bright light sources like desk lamps positioned close to the solar panel.</p>
  </details>
  <details>
    <summary>How can I measure the power output of my solar panel?</summary>
    <p>Use a multimeter to measure voltage (V) across the panel terminals and current (A) through the circuit. Calculate power using P = V × I. For example, 3 volts × 0.05 amps = 0.15 watts of power output.</p>
  </details>
  <details>
    <summary>What's the difference between solar panels and solar cells?</summary>
    <p>A solar cell is a single photovoltaic unit that converts light to electricity. A solar panel contains multiple solar cells connected together to produce higher voltage and current. Your small educational unit is typically a single solar cell.</p>
  </details>
  <details>
    <summary>Can I connect multiple solar panels together?</summary>
    <p>Yes! Connect them in series (positive to negative) to increase voltage, or in parallel (positive to positive, negative to negative) to increase current. This demonstrates how larger solar installations are built.</p>
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