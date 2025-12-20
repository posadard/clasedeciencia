<?php
// Project: Solar System Model - DIY Educational Project
$project_title = "Make a Solar System Model - DIY Project Guide";
$project_description = "Learn about the sun and planets by building your own solar system model. Understand planetary sizes, distances, and orbital relationships through hands-on construction.";
$project_keywords = "solar system model, planets, astronomy, space science, educational model, DIY project, sun, planetary system";
$project_grade_level = "Elementary to Middle"; // suitable for various ages
$project_subject = "Earth Science, Astronomy";
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
    .project-title-simple { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color: #ff00ff; font-size: 1.6rem; margin: 0 0 0.75rem 0; text-align: center; }
    .intro-section { background: #f8f9ff; border: 1px solid #2c5aa0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .info-box { border: 1px solid #69c; background: #f4f8ff; padding: 0.75rem; color: #0033cc; font-family: Verdana, Geneva, Tahoma, sans-serif; margin: 1rem 0; }
    .highlight-box { border: 1px solid #ff6b35; background: #fff6f0; padding: 1rem; margin: 1rem 0; }
    .materials-box { border: 1px solid #34c759; background: #f0fff0; padding: 1rem; margin: 1rem 0; }
    .section-title { color: #2c5aa0; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .highlight-text { color: #ff0000; font-weight: bold; }
    .purple-text { color: #ff00ff; font-weight: bold; }
    .materials-grid { display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; align-items: start; margin: 1rem 0; }
    .two-col { display: grid; grid-template-columns: 1fr 320px; gap: 1rem; align-items: start; margin: 1rem 0; }
    .two-col-reverse { display: grid; grid-template-columns: 320px 1fr; gap: 1rem; align-items: start; margin: 1rem 0; }
    .responsive-img { max-width: 100%; height: auto; display: block; border-radius: 8px; }
    .center { text-align: center; }
    .planetary-data { background: #fff; border: 1px solid #ccc; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .planet-table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
    .planet-table th, .planet-table td { border: 1px solid #ddd; padding: 0.75rem; text-align: left; }
    .planet-table th { background: #f0f8ff; font-weight: bold; }
    @media (max-width: 720px) {
      .materials-grid, .two-col, .two-col-reverse { grid-template-columns: 1fr; }
      .banner-row { grid-template-columns: 1fr; }
      .planet-table { font-size: 0.9rem; }
      .planet-table th, .planet-table td { padding: 0.5rem; }
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
    <p><strong class="purple-text">Build Your Own Solar System Model!</strong> Making a model of the solar system is the best way to learn about the sun and the planets revolving around it. This hands-on project helps you understand planetary sizes, distances, and the relationships between celestial bodies in our cosmic neighborhood.</p>
  </div>

  <div class="two-col-reverse">
    <div>
      <img src="Solarystem3.gif" alt="Completed solar system model showing planets in order" class="responsive-img" />
    </div>
    <div>
      <p class="highlight-text" style="text-align: center; font-size: 1.1rem;">Make a model of the solar system</p>
      
      <p>While making your model of the solar system, you will learn that <strong>Pluto</strong> (now classified as a dwarf planet) is one of the smallest objects, and <strong>Jupiter</strong> is the largest planet in our solar system.</p>
      
      <p>It is also important to notice which planets are closer to the sun and which ones are further away from the sun.</p>
    </div>
  </div>

  <h3 class="section-title">Materials You'll Need</h3>
  
  <div class="materials-box">
    <h4><strong>Basic Materials:</strong></h4>
    <ul>
      <li><strong>Styrofoam balls</strong> in various sizes (for sun and 8 planets)</li>
      <li><strong>Styrofoam rings</strong> (for Saturn's rings)</li>
      <li><strong>Acrylic or water-based paints</strong> (various colors)</li>
      <li><strong>Paint brushes</strong> (different sizes)</li>
      <li><strong>Disposable gloves</strong> (for handling paint)</li>
      <li><strong>Wooden dowels or toothpicks</strong> (for mounting)</li>
      <li><strong>Wooden base or poster board</strong> (for display)</li>
      <li><strong>String or fishing line</strong> (if making hanging model)</li>
      <li><strong>Ruler and compass</strong> (for accurate spacing)</li>
      <li><strong>Reference materials</strong> (planet photos, size charts)</li>
    </ul>
  </div>

  <div class="two-col">
    <div>
      <p>This DIY project allows you to create your own solar system model using commonly available materials. You can customize the size and style to fit your space and display preferences.</p>
      
      <p><strong>Paint Selection:</strong> You may purchase and use any water-based, latex, or acrylic paint from your local art store, paint store, or hardware store. Choose colors that match each planet's appearance.</p>
      
      <p><strong>Safety Note:</strong> Always wear gloves when painting and work in a well-ventilated area.</p>
    </div>
    <div>
      <img src="SolarSystemKit.jpg" alt="Materials and components for building solar system model" class="responsive-img" />
    </div>
  </div>

  <h3 class="section-title">Construction Instructions</h3>

  <div class="highlight-box">
    <h4><strong>Step-by-Step Building Process:</strong></h4>
    <ol>
      <li><strong>Plan Your Scale:</strong> Decide whether to show relative sizes, relative distances, or a balanced combination</li>
      <li><strong>Prepare Your Workspace:</strong> Lay out newspaper or plastic sheeting to protect surfaces</li>
      <li><strong>Paint the Sun:</strong> Use yellow and orange paints to create a realistic sun appearance</li>
      <li><strong>Paint Each Planet:</strong> Research and match colors (Mercury-gray, Venus-yellow, Earth-blue/green, Mars-red, etc.)</li>
      <li><strong>Add Special Features:</strong> Create Saturn's rings, Earth's moon, and other distinctive features</li>
      <li><strong>Plan Your Display:</strong> Choose between a hanging mobile or mounted base display</li>
      <li><strong>Mount or Hang:</strong> Secure planets in proper order and spacing</li>
      <li><strong>Add Labels:</strong> Include planet names and interesting facts</li>
    </ol>
  </div>

  <div class="two-col-reverse">
    <div>
      <img src="Solarystem4.gif" alt="Hanging solar system model display option" class="responsive-img" />
    </div>
    <div>
      <p><strong>Display Options:</strong> You have multiple ways to display your finished solar system model:</p>
      
      <p><strong>Hanging Display:</strong> Use string or fishing line to create a mobile that can hang from the ceiling. This allows viewing from all angles.</p>
      
      <p><strong>Base Display:</strong> Mount planets on a wooden base or large poster board. This creates a stable display for presentations.</p>
      
      <p><strong>Combination Display:</strong> Some planets on base, others suspended for a dynamic 3D effect.</p>
    </div>
  </div>

  <h3 class="section-title">Planet Information Guide</h3>
  
  <div class="planetary-data">
    <table class="planet-table">
      <thead>
        <tr>
          <th>Planet</th>
          <th>Distance from Sun</th>
          <th>Size (Diameter)</th>
          <th>Color Suggestions</th>
          <th>Special Features</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Mercury</strong></td>
          <td>36 million miles</td>
          <td>3,032 miles</td>
          <td>Gray, dark brown</td>
          <td>Smallest planet, cratered surface</td>
        </tr>
        <tr>
          <td><strong>Venus</strong></td>
          <td>67 million miles</td>
          <td>7,521 miles</td>
          <td>Yellow, pale orange</td>
          <td>Thick atmosphere, hottest planet</td>
        </tr>
        <tr>
          <td><strong>Earth</strong></td>
          <td>93 million miles</td>
          <td>7,926 miles</td>
          <td>Blue, green, brown</td>
          <td>Our home, has moon, liquid water</td>
        </tr>
        <tr>
          <td><strong>Mars</strong></td>
          <td>142 million miles</td>
          <td>4,220 miles</td>
          <td>Red, rusty brown</td>
          <td>Red planet, polar ice caps</td>
        </tr>
        <tr>
          <td><strong>Jupiter</strong></td>
          <td>484 million miles</td>
          <td>88,846 miles</td>
          <td>Orange, brown, tan</td>
          <td>Largest planet, Great Red Spot</td>
        </tr>
        <tr>
          <td><strong>Saturn</strong></td>
          <td>887 million miles</td>
          <td>74,898 miles</td>
          <td>Pale yellow, gold</td>
          <td>Prominent ring system</td>
        </tr>
        <tr>
          <td><strong>Uranus</strong></td>
          <td>1.8 billion miles</td>
          <td>31,763 miles</td>
          <td>Blue-green, cyan</td>
          <td>Tilted rotation, faint rings</td>
        </tr>
        <tr>
          <td><strong>Neptune</strong></td>
          <td>2.8 billion miles</td>
          <td>30,775 miles</td>
          <td>Deep blue</td>
          <td>Windiest planet, distant</td>
        </tr>
      </tbody>
    </table>
  </div>

  <h3 class="section-title">Educational Extensions</h3>
  
  <div class="info-box">
    <p><strong>Learning Opportunities:</strong> This project can be expanded into various educational activities:</p>
    <ul>
      <li><strong>Scale Comparisons:</strong> Calculate actual size and distance ratios</li>
      <li><strong>Orbital Periods:</strong> Research how long each planet takes to orbit the sun</li>
      <li><strong>Temperature Studies:</strong> Compare surface temperatures across planets</li>
      <li><strong>Moon Systems:</strong> Add major moons for planets that have them</li>
      <li><strong>Asteroid Belt:</strong> Include representation between Mars and Jupiter</li>
      <li><strong>Dwarf Planets:</strong> Add Pluto, Ceres, and other dwarf planets</li>
    </ul>
  </div>

  <h3 class="section-title">Modern Astronomical Updates</h3>
  
  <div class="highlight-box">
    <p><strong>Important Updates:</strong> Astronomy has evolved since traditional solar system models:</p>
    <ul>
      <li><strong>Pluto's Status:</strong> Now classified as a "dwarf planet" (since 2006)</li>
      <li><strong>Eight Planets:</strong> Mercury, Venus, Earth, Mars, Jupiter, Saturn, Uranus, Neptune</li>
      <li><strong>Dwarf Planets:</strong> Pluto, Ceres, Eris, Makemake, Haumea</li>
      <li><strong>Kuiper Belt:</strong> Region beyond Neptune with many small objects</li>
      <li><strong>Exoplanets:</strong> Thousands of planets discovered around other stars</li>
    </ul>
  </div>

  <h3 class="section-title">Science Fair Project Ideas</h3>
  
  <div class="materials-box">
    <h4><strong>Research Questions to Explore:</strong></h4>
    <ul>
      <li>How do planetary sizes compare to Earth?</li>
      <li>What is the relationship between distance from sun and temperature?</li>
      <li>How long would it take to travel to each planet?</li>
      <li>Which planets could potentially support life and why?</li>
      <li>How do the number of moons relate to planet size?</li>
      <li>What causes the different colors we see on each planet?</li>
      <li>How would gravity differ on each planet compared to Earth?</li>
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

  <!-- Related Categories Section -->
  <h4 style="margin-top: 1rem; margin-bottom: 0.75rem; color: #2c5aa0; font-size: 1.1rem;">Related Categories</h4>
  <div class="related-grid">
    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
<ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
	<li id="abc_1760557991711126" class="abantecart_category" data-category-id="126" data-language="en" data-currency="USD">
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
    <summary>What size styrofoam balls should I use for each planet?</summary>
    <p>For a practical model, try these relative sizes: Sun (6-8 inches), Jupiter (3 inches), Saturn (2.5 inches), Neptune and Uranus (1.5 inches), Earth and Venus (1 inch), Mars (0.75 inches), Mercury (0.5 inches). Remember, true scale would make some planets nearly invisible!</p>
  </details>
  <details>
    <summary>How do I make Saturn's rings look realistic?</summary>
    <p>Use thin styrofoam rings or cut rings from cardboard. Paint them with alternating light and dark bands to show the ring divisions. You can also use clear plastic rings and paint them for a translucent effect that looks more realistic.</p>
  </details>
  <details>
    <summary>Should I show accurate distances between planets?</summary>
    <p>True-scale distances would require a model several miles long! Instead, show relative order and approximate spacing relationships. You can create a separate distance chart to show the actual scale differences.</p>
  </details>
  <details>
    <summary>What's the best way to paint realistic planet surfaces?</summary>
    <p>Research NASA images for accurate colors. Use sponges for texture, mix colors while wet for cloud effects, and add details with fine brushes. For Earth, start with blue base, add green continents, and white clouds.</p>
  </details>
  <details>
    <summary>Why is Pluto not included as a planet anymore?</summary>
    <p>In 2006, astronomers reclassified Pluto as a "dwarf planet" because it hasn't cleared its orbital path of other objects. You can still include it in your model but label it correctly as a dwarf planet.</p>
  </details>
  <details>
    <summary>How can I make my model educational for presentations?</summary>
    <p>Add fact cards for each planet, create a data chart showing comparisons, include major moons, and prepare questions about planet characteristics. Consider making parts removable so viewers can handle and examine them closely.</p>
  </details>
  <details>
    <summary>What other objects should I include in my solar system model?</summary>
    <p>Consider adding the asteroid belt (small scattered pieces between Mars and Jupiter), major moons like Earth's Moon and Jupiter's four largest moons, and perhaps some comets with tails made from cotton or fabric.</p>
  </details>
  <details>
    <summary>Can I use this project for a science fair?</summary>
    <p>Absolutely! Focus on a specific research question like comparing planet densities, temperatures, or orbital periods. Include your research process, data collection, and conclusions. The visual model makes an excellent display centerpiece.</p>
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