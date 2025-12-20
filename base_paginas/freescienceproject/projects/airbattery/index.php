<?php
// Project: Air Battery (Saltwater Air Battery)
$project_title = "Air Battery (Saltwater)";
$project_description = "Build a simple air/saltwater battery using magnesium and iron electrodes to demonstrate oxidation-reduction reactions and low-voltage electricity production.";
$project_keywords = "air battery, saltwater battery, electrochemistry, magnesium, iron, STEM project";
$project_grade_level = "Intermediate"; // recommended
$project_subject = "Chemistry, Environmental Science";
$project_difficulty = "Advanced";

// Project pages live under /projects/<slug>/ so include the shared project header
// using a path relative to this file that reaches the repository includes folder.
include __DIR__ . '/../../includes/project-header.php';
?>

<article class="project-article">
<!-- The original page used nested <html>/<head>/<body> tags from FrontPage.
     Remove duplicates and keep content inside the shared site <head>/<body>.
     Add a small scoped stylesheet to modernize typography and layout while
     keeping the visual placement identical. Later this CSS can be moved to
     the global `css/modern-styles.css` if desired. -->

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
    .materials { display: grid; grid-template-columns: 1fr 260px; gap: 1rem; align-items: start; }
    .materials img { width: 100%; height: auto; display: block; }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; }
    .responsive-img { max-width: 100%; height: auto; display:block; }
    .center { text-align: center; }
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

  <div class="notice">
    <p>This science project experiment is a simplified version of the air battery project available at ScienceProject.com.</p>
    <p>Pictures and excerpts of information are published here with permission.</p>
  </div>

  <p><strong>Introduction:</strong> We all know that the world is now facing an energy crisis and everyone is trying to do something about that. Now you can show everyone that electrical energy or electricity can be made from air and saltwater. After all, both the air and the saltwater are freely available everywhere.</p>

  <div class="two-col">
    <div>
      <p>This may seem impossible. I could not believe it myself the first time that I heard about it. It almost sounds like a magic trick. Finally, I decided to test it anyway.</p>
      <p>I tried different concentrations of salt water, different temperatures, and different electrodes and had no success. It took me a few months thinking about it until I solved the problem in my mind and decided to repeat my tests again. This time everything worked fine and I was able to make enough electricity to light up a small light bulb.</p>
    </div>
    <div>
      <img class="responsive-img" src="Salt_battery_1.jpg" alt="Saltwater air battery setup">
    </div>
  </div>

  <p>The concept is easy. The same way that you burn wood and make heat energy, you should be able to burn metals and get electricity (or electrical energy). The difference is that you are not really burning anything; instead, you are producing a condition for oxidization which by itself is the same as slow burning. So what you really do is oxidizing iron in saltwater using the oxygen from the air or any other source.</p>

  <p>I don’t know if this method of producing electricity is economical and cost effective. What I know is that it is worth to try. If with one cup of salt water and some metals I was able to light up a small light bulb, maybe you can light up the entire building by a tank of salt water and a few hundred pounds of scrap metal.</p>

  <p>It took me a long time to make the first working battery using the salt water; however, you don’t have to waste that much time. I have combined the results of all my experiments and made a recipe for success. Just follow the instructions and you will get results in the first try.</p>

  <div class="legacy-callout info-box">
    <p>Actually there are many different combinations of many different materials that can produce some electricity. Experimenting with saltwater and air is suggested for the younger students because these are relatively safer material.</p>
  </div>

  <h3>List of materials:</h3>

  <div class="materials">
    <div>
      <p>This is the minimum list of material you need for your experiment.</p>
      <ol>
        <li>Miniature light bulb (low voltage, low current)</li>
        <li>Miniature base for light bulb</li>
        <li>Pair of insulated solid copper wire AWG=20</li>
        <li>Pair of alligator clips</li>
        <li>Magnesium Electrode</li>
        <li>Iron Electrode (not in the picture — use steel wool as iron electrode)</li>
        <li>A cup of saltwater (not in the picture)</li>
        <li>Screws for the miniature base</li>
      </ol>
    </div>
    <div>
      <img class="responsive-img" src="airbattery_parts_2.jpg" alt="Air battery parts and components">
    </div>
  </div>

  <h4>Additional optional materials you may use:</h4>
  <ul>
    <li>A wooden board to mount the miniature base (light holder)</li>
    <li>Plastic container about 4" x 4" x 4"</li>
    <li>Hydrogen Peroxide</li>
  </ul>

  <div class="legacy-callout info-box">
    <strong>What is a good title for my project?</strong>
    <p>You can call it "Air battery", "Salt water battery", "electricity from air" or "electricity from the salt water".</p>
  </div>

  <h3>Procedure:</h3>
  <ol>
    <li>Remove the plastic insulation of about one inch from both ends of the wires.</li>
    <li>Loosen the screw on both contacts of the bulb holder. Place one end of the red wire under one screw, make a loop and then tighten the screw. Place one end of the black wire under the other screw, make a loop and then tighten the screw.</li>
    <li>Pass the open end of the red wire through the arm of the red alligator clip and secure it under the screw.</li>
    <li>Pass the open end of the black wire through the arm of the black alligator clip and secure it under the screw.</li>
    <li>Screw the light bulb on the miniature base.</li>
    <li>Connect the red alligator clip to the iron electrode and secure it on one side of the plastic container or the cup.</li>
    <li>Connect the black alligator clip to the magnesium electrode and secure it on the opposite side of the container.</li>
    <li>In another pitcher, prepare some strong, warm salt water. Add enough salt so at the end some salt will be left at the bottom of the pitcher.</li>
    <li>Transfer the salt water from the pitcher to the container.</li>
    <li>At this time, if all the connections are secure and the electrodes are large enough, you should get a light.</li>
  </ol>

  <h3>How can I get more light?</h3>
  <div class="two-col">
    <div>
      <ol>
        <li>Make sure your electrodes are not touching each other.</li>
        <li>Make sure there is nothing blocking the space between the electrodes.</li>
        <li>Make sure that the alligator clips are not touching the salt water.</li>
        <li>Both electrodes must have the maximum possible surface contact with salt water.</li>
      </ol>
    </div>
    <div>
      <img class="responsive-img" src="air_battery_1.jpg" alt="Close up of air battery electrodes">
    </div>
  </div>

  <p>The test tube electrodes (magnesium electrodes in test tubes) are formed like a spring. This provides the largest possible surface contact. For Iron electrode you may use steel wool. Steel wool has a very large surface contact. A steel screen may work as well.</p>

  <p>You may notice that you will get more light if you stir the solution or if you remove the iron electrode and insert it back again. Such actions provide oxygen to the surface of the iron.</p>

  <p style="color:#CC33FF">Note: Steel is about 98% iron.</p>

  <div class="two-col">
    <div>
      <p>The oxygen in the air may not be enough for your demonstration and you may get a dim light.</p>
      <p>In this case you may add some oxygen (in the form of hydrogen peroxide) to the salt water. That should immediately increase the light.</p>
    </div>
    <div>
      <img class="responsive-img" src="Salt_battery_2.jpg" alt="Salt battery with peroxide">
    </div>
  </div>

  <div class="two-col">
    <div>
      <p>A cup is relatively small. If you have access to a larger container, you will get a better result. In a larger container, it is easier to secure the electrodes in two opposite sides so they will not touch each other.</p>
    </div>
    <div>
      <img class="responsive-img" src="air_battery_2.jpg" alt="Air battery in larger container">
    </div>
  </div>

  <div class="two-col">
    <div>
      <h3>Where to buy the material?</h3>
      <p>The main components of this project are available as a set in MiniScience.com online store and KidsLoveKits.com. This set will only include the essential components. You must have a plastic container, a wooden board, some iron and some hydrogen peroxide to complete your material.</p>
      <p>This set includes 2 Magnesium electrodes, insulated wire, light bulb, light base, alligator clips and screws.</p>
      <p><strong><a href="https://shop.miniscience.com/airbat">Part# AIRBAT</a></strong></p>
    </div>
    <div>
      <img class="responsive-img" src="airbattery_parts.jpg" alt="Parts included in the air battery kit">
    </div>
  </div>

  <div class="two-col">
    <div class="center">
      <p>The electricity produced in this way may be used to light up a light bulb, an LED or run a low voltage electric motor.</p>
      <p><a href="https://shop.miniscience.com/airbat" class="buy-now-btn">Buy Now</a></p>
    </div>
    <div>
      <img class="responsive-img" src="IC032.jpg" alt="Circuit illustration">
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
  
  <!-- Kit/Products Section (arriba) -->
  <div class="related-grid" style="margin-bottom: 1.5rem;">
    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <div style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <div id="abc_803" class="abantecart_product" data-product-id="542" data-language="en" data-currency="USD">
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
        <div id="abc_216" class="abantecart_product" data-product-id="543" data-language="en" data-currency="USD">
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

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176046261743692" class="abantecart_category" data-category-id="92" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760462639292102" class="abantecart_category" data-category-id="102" data-language="en" data-currency="USD">
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
    <summary>Is the air battery safe for classroom demonstrations?</summary>
    <p>With adult supervision and proper PPE (gloves and eye protection) this demonstration using salt water and common metals is suitable for classroom demonstrations. Avoid ingesting solutions and dispose of used materials responsibly.</p>
  </details>
  <details>
    <summary>How long will the battery produce electricity?</summary>
    <p>Duration depends on electrode size, concentration of salt solution, and oxygen exposure. Small setups will power a tiny bulb for minutes to hours; scaling up increases output and duration.</p>
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
