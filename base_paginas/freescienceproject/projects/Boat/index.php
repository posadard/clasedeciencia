<?php
// Project: Air Propulsion Motor Boats (Electric Boat)
$project_title = "Air Propulsion Motor Boats";
$project_description = "Build an air-propelled motor boat using Styrofoam, electric motors, and simple circuits to learn about electricity, force, and floatation.";
$project_keywords = "electric boat, air propulsion, motor boat, simple circuit, Styrofoam boat, STEM project";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Physics, Engineering";
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
  <p class="subtitle" style="color: #808080; font-size: 1.1rem; margin-top: -0.5rem; margin-bottom: 1rem;">(Simple Electric Circuit)</p>

  <style scoped>
    /* Scoped modern styles for this legacy project page - non-destructive */
    .project-body { max-width: 980px; margin: 0 auto; padding: 1rem; }
    .project-title-simple { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color: #2c5aa0; font-size: 1.5rem; margin: 0 0 0.75rem 0; }
    .subtitle { font-family: Verdana, Geneva, Tahoma, sans-serif; }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; }
    .two-col-60-40 { display: grid; grid-template-columns: 60% 40%; gap: 1rem; align-items: start; }
    .responsive-img { max-width: 100%; height: auto; display:block; }
    .center { text-align: center; }
    .parts-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; border: 1px solid #ccc; }
    .parts-grid img { max-width: 100%; height: auto; display: block; }
    .parts-grid-item { border: 1px solid #ccc; padding: 0.25rem; text-align: center; }
    @media (max-width: 720px) {
      .two-col, .two-col-60-40 { grid-template-columns: 1fr; }
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

  <div class="two-col-60-40">
    <div>
      <p>Combine the joy and excitement of mechanical toys with your science project by constructing an air propelled motor boat. This is also a good way of learning about simple electric circuits. Your boat will have a battery, a switch and a motor with propeller. This can be used as a science project related to electricity, force or floatation. This idea is good for a display project, an engineering project and an experimental (investigatory) project.</p>
      <p>The main structure is made of Styrofoam board that is available at hardware stores and home improvement stores.</p>
    </div>
    <div>
      <img class="responsive-img" src="foam_boat_2.jpg" alt="Styrofoam air propulsion boat" width="255" height="203">
    </div>
  </div>

  <h3>Materials</h3>
  <div class="two-col-60-40">
    <div>
      <p><strong>Material used in this project are:</strong> Simple Switch, Electric Motor, Battery Holder, Propeller, Screws and Wire.</p>
      <p>All the material are from the "Simple Electric Circuit Kit"; however, the kit also include light bulb and light holder that may be used for other projects. To utilize the extra material in this project, you may install the light in front side of your boat and use a piece of aluminum foil as reflector.</p>
    </div>
    <div>
      <img class="responsive-img" src="carboat_parts_2.jpg" alt="Boat parts and components" width="236" height="171">
    </div>
  </div>

  <h3>Procedure</h3>
  <div class="two-col-60-40">
    <div>
      <p>Start by cutting the foam. You can cut the Styrofoam easily with utility knives. Just practice for a few minutes and you will get the grip on it. Hold the knife in a sharp angle and don't push too much. You may need to go over the same line a few times until you get a clean cut.</p>
      <p>Styrofoam boards can be glued using white glue or wood glue. They can also be painted using any latex paint or water based paint.</p>
    </div>
    <div>
      <img class="responsive-img" src="Air_boat_1a.jpg" alt="Cutting and assembling the boat" width="244" height="184">
    </div>
  </div>

  <div class="two-col-60-40" style="margin-top: 1rem;">
    <div>
      <p>Feel free on making your own design. Just reserve a place where you can place the electric motor and secure it with tape.</p>
      <p>Battery is a heavy piece; it must be centered in order for your boat to have a balance on water. Adjustments may be made by placing other heavy objects onboard.</p>
    </div>
    <div>
      <img class="responsive-img" src="Air_boat_2.jpg" alt="Motor placement" width="230" height="174">
    </div>
  </div>

  <div class="two-col-60-40" style="margin-top: 1rem;">
    <div>
      <p>Please note that with this method you are not restricted to a boat. You may also make a car that drives by pushing the air backward. You just need for wheels and four nails. Make sure that the wheels are large enough and can spin freely.</p>
      <p>To hide the battery and switch, you may also use some cardboard to make a cabin and place it over those parts.</p>
    </div>
    <div>
      <img class="responsive-img" src="Air_boat_1_top.jpg" alt="Top view of boat" width="233" height="182">
    </div>
  </div>

  <div class="two-col-60-40" style="margin-top: 1rem;">
    <div>
      <p>The boat that you see in the picture does not have a steering mechanism. You may try different possible methods to construct a steeling mechanism in your boat.</p>
    </div>
    <div>
      <img class="responsive-img" src="Air_boat_1.jpg" alt="Completed air boat" width="258" height="199">
    </div>
  </div>

  <h3>Making a Land & Water Vehicle</h3>
  <div class="two-col-60-40">
    <div>
      <p>Finally you can make a vehicle that can drive both on land and in water. To do that simply attach the wheels to the sides of the boat.</p>
      <p>With your kit you may also receive four sheaves that may be used as wheels. Sheaves are almost like the ring in a bicycle; they are just missing the tiers. Students often use sheaves to make pulleys as a part of a simple machine project.</p>
      <p>If you did not get sheaves in your kit, use the wheels of any plastic toy car instead.</p>
    </div>
    <div>
      <img class="responsive-img" src="carboat1.jpg" alt="Car-boat hybrid vehicle" width="277" height="203">
    </div>
  </div>

  <p>Sheaves are added as a bonus to some of the kits purchased in the beginning of the school year. Material that comes with the kit may be used in many other projects as well.</p>

  <h3>Kit Components</h3>
  <div class="two-col">
    <div>
      <div class="parts-grid">
        <div class="parts-grid-item"><img src="part_switch.jpg" alt="Switch" width="182" height="133"></div>
        <div class="parts-grid-item"><img src="part_motor.jpg" alt="Motor" width="134" height="104"></div>
        <div class="parts-grid-item" style="grid-column: 1 / -1;"><img src="part_propeller.jpg" alt="Propeller" width="246" height="67"></div>
      </div>
    </div>
    <div>
      <div class="parts-grid">
        <div class="parts-grid-item"><img src="part_base.jpg" alt="Light base" width="127" height="123"></div>
        <div class="parts-grid-item"><img src="part_bulb.jpg" alt="Light bulb" width="87" height="60"></div>
        <div class="parts-grid-item" style="grid-column: 1 / -1;"><img src="part_wire.jpg" alt="Wire" width="126" height="79"></div>
      </div>
    </div>
  </div>

  <div class="parts-grid" style="margin-top: 0.5rem; max-width: 600px;">
    <div class="parts-grid-item"><img src="part_screw.jpg" alt="Screws" width="145" height="111"></div>
    <div class="parts-grid-item"><img src="part_battery_holder.jpg" alt="Battery holder" width="200" height="131"></div>
  </div>

  <h3>Air Propelled Car Example</h3>
  <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; align-items: start; margin: 1rem 0;">
    <div>
      <p>Two pictures of a simple air propelled electric car. With some creativity and artwork you can make this a must better project.</p>
    </div>
    <div>
      <img class="responsive-img" src="air_car2.jpg" alt="Air car example 1" width="199" height="166">
    </div>
    <div>
      <img class="responsive-img" src="air_car.jpg" alt="Air car example 2" width="173" height="145">
    </div>
  </div>

  <h3>What You Need</h3>
  <div class="two-col-60-40">
    <div>
      <p>The Kit Contains the electric motor, simple switch, battery holder, wire, light bulb, screw base for light bulb, wire, screws and propeller.</p>
      <p>You will need additional material and tools such as Styrofoam board, wood, nail, water color, screw driver, battery and utility knife.</p>
      <p class="center">
        <a href="https://shop.miniscience.com/kitcb">
          <span class="buy-now-btn" style="display:inline-block; margin: 0.5rem;">Buy Now</span>
          <img src="../../images/btr_add1tobasket.gif" alt="Add to basket" width="119" height="33" style="display:inline-block; margin: 0.5rem;">
        </a>
      </p>
    </div>
    <div>
      <img class="responsive-img" src="1.jpg" alt="Complete kit contents" width="223" height="205">
    </div>
  </div>

  <hr style="margin: 2rem 0; border: 0; border-top: 1px solid #ccc;">

  <h3>Simple Electric Circuit</h3>
  <div class="two-col-60-40">
    <div>
      <p>For a simple electric circuit, you may install the battery holder, switch and light on a board. The switch is missing in this picture.</p>
      <p><strong>Simple Electric Circuit <a href="https://shop.miniscience.com/kitsec">Part#KITSEC</a></strong></p>
    </div>
    <div>
      <img class="responsive-img" src="Circuit.jpg" alt="Simple electric circuit" width="225" height="145">
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

<!-- MiniScience / Miniscience product embed widget -->
<section class="related-projects">
  <h3>Related Science Projects &amp; Kit</h3>
  
  <!-- Kit/Products Section (arriba) -->
  <div class="related-grid" style="margin-bottom: 1.5rem;">
    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <div style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <div id="abc_30" class="abantecart_product" data-product-id="1682" data-language="en" data-currency="USD">
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
        <div id="abc_621" class="abantecart_product" data-product-id="1709" data-language="en" data-currency="USD">
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
        <li id="abc_176046295416888" class="abantecart_category" data-category-id="88" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176046297514297" class="abantecart_category" data-category-id="97" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760462995823100" class="abantecart_category" data-category-id="100" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760463009904102" class="abantecart_category" data-category-id="102" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760463024613129" class="abantecart_category" data-category-id="129" data-language="en" data-currency="USD">
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
    <summary>What type of Styrofoam should I use for the boat?</summary>
    <p>Use rigid polystyrene foam boards (typically 1-2 inches thick) available at hardware stores. These are lightweight, easy to cut, and provide excellent flotation. Avoid thin packing foam as it's too fragile.</p>
  </details>
  <details>
    <summary>How do I waterproof the electrical components?</summary>
    <p>Place the battery holder and switch above the waterline. Use waterproof tape or plastic bags to protect connections. Consider coating wire connections with hot glue or silicone sealant for added protection.</p>
  </details>
  <details>
    <summary>Can I use a different motor or propeller?</summary>
    <p>Yes! Hobby store DC motors (1.5V-6V) work well. Match the propeller size to your motor - larger propellers provide more thrust but require more power. Experiment to find the best combination.</p>
  </details>
  <details>
    <summary>How do I add steering to my boat?</summary>
    <p>Add a simple rudder behind the propeller using a piece of plastic or cardboard. Attach it with a wire or straw that allows it to pivot. You can control it with a string from the side of the boat.</p>
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
