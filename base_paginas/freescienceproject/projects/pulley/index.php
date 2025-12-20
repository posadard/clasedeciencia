<?php
// Project: Compound Machines - Pulleys for Science Projects
$project_title = "Compound Machines Science Projects Using Pulleys";
$project_description = "Learn about simple machines and compound machines using pulleys to create working models that demonstrate mechanical advantage and force reduction principles.";
$project_keywords = "pulleys, compound machines, simple machines, mechanical advantage, block and tackle, force reduction, physics experiments";
$project_grade_level = "Elementary to Senior"; // broad appeal
$project_subject = "Physics, Engineering";
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
    .info-box { border: 1px solid #69c; background: #f4f8ff; padding: 0.75rem; color: #0033cc; font-family: Verdana, Geneva, Tahoma, sans-serif; margin: 1rem 0; }
    .highlight-box { border: 1px solid #0033cc; background: #f9eaff; padding: 1rem; margin: 1rem 0; }
    .definition-box { border: 1px solid #0033cc; background: #ebebeb; padding: 1rem; margin: 1rem 0; }
    .section-title { color: #2c5aa0; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .highlight-text { color: #2c5aa0; font-weight: bold; }
    .two-col { display: grid; grid-template-columns: 320px 1fr; gap: 1rem; align-items: start; margin: 1rem 0; }
    .two-col-reverse { display: grid; grid-template-columns: 1fr 320px; gap: 1rem; align-items: start; margin: 1rem 0; }
    .responsive-img { max-width: 100%; height: auto; display: block; }
    .center { text-align: center; }
    .green-highlight { color: #00ca00; font-weight: bold; }
    @media (max-width: 720px) {
      .two-col, .two-col-reverse { grid-template-columns: 1fr; }
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
    <p><strong class="highlight-text">Introduction:</strong> If you need to do a science project on simple machines or compound machines, you can use pulleys to construct a variety of working models. Pulleys are fundamental simple machines that demonstrate mechanical advantage and force reduction principles through clever engineering design.</p>
  </div>

  <div class="highlight-box">
    <p><strong>What is a Pulley?</strong></p>
    <p>A pulley is a simple machine consisting essentially of a wheel with a grooved rim in which a pulled rope or chain can run to change the direction of the pull and thereby lift a load.</p>
    <p>A wheel turned by or driving a belt is also called pulley.</p>
    <p>The plural of pulley is pulleys.</p>
  </div>

  <h3 class="section-title">Simple Science Fair Project for Compound Machines</h3>

  <div class="two-col">
    <div>
      <img src="Double%20Tandem%20Combo-m.jpg" alt="Double tandem pulley system demonstration" class="responsive-img" />
    </div>
    <div>
      <p><strong>Educational Value:</strong> We can't say enough about the educational value of pulley systems. Their prize-winning design enables students to learn mechanics and spatial perception. Watch as they are inspired to effortlessly incorporate these concepts into their creative ideas.</p>
      
      <p>In your compound machine project you may show how a set of pulleys may be used to lift heavy objects effortlessly. Show how pulleys are used to lift trucks, other heavy objects and people.</p>
      
      <p><strong>Force Measurement:</strong> Optionally use a spring scale to show how pulleys reduce the force needed to lift heavy objects.</p>
      
      <p>This pulley set increases the force by about 4 times. For example you may show that a 4 kilogram weight can be lifted with about 1 kilogram force.</p>
    </div>
  </div>

  <div class="definition-box">
    <h4><strong>Force and Mechanical Advantage</strong></h4>
    <p>Pulleys are clever devices that allow you to lift large weights with much smaller forces. The length of the string used to lift the pulley determines how much force is needed. For example three string lengths allow us to lift a heavy object with a force equal to 1/3rd of its weight.</p>
    
    <p><strong>Experiment:</strong> Do some experiments and measure the force it takes to lift the pulley by attaching a spring scale to the end of the string.</p>
  </div>

  <h3 class="section-title">How Pulleys Work</h3>

  <div class="two-col-reverse">
    <div>
      <p>Pulleys can be used to simply change the direction of an applied force or to provide a force/distance tradeoff in addition to a directional change.</p>
      
      <p><strong>Flexibility:</strong> Pulleys are very flexible because they use ropes to transfer force rather than a rigid object such as a board or a rod. Ropes can be routed through virtually any path. They are able to abruptly change directions in three-dimensions without consequence. Ropes can be wrapped around a motor's shaft and either wound up or let out as the motor turns.</p>
      
      <p><strong>Length Independence:</strong> Ropes also have the advantage that their performance is not affected by length.</p>
    </div>
    <div>
      <img src="Pulleys-m.jpg" alt="Various pulley configurations and arrangements" class="responsive-img" />
    </div>
  </div>

  <h4><strong>Compound Pulley Mechanics</strong></h4>
  <p>A compound pulley 'trades' force for distance through an action/reaction force pair. In a double pulley, as the rope passes over the pulley the force is transmitted entirely but the direction has changed. The effort is now pulling up on the left side of the bottom pulley.</p>

  <h4><strong>Disadvantages and Solutions</strong></h4>
  <p>The disadvantages of pulleys, in contrast to machines that use rigid objects to transfer force, are slipping and stretching. A rope will permanently stretch under tension, which may affect the future performance of a device. If a line becomes slack, then the operation of a machine may change entirely. Also, ropes will slip and stick along pulley wheels just like belts.</p>
  
  <p><strong>Chain Solution:</strong> One solution to the problems associated with rope is to use chain. Chain is pliable like rope, and is able to transfer force through many direction changes, but the chain links are inflexible in tension, so that the chain will not stretch. Chains may also be made to fit on gears so that slipping is not a problem.</p>

  <h3 class="section-title">Lifting Heavy Objects - Block and Tackle</h3>
  
  <div class="center">
    <img src="pulleys.gif" alt="Block and tackle pulley system schematic diagram" />
  </div>
  
  <p>A common way to lift heavy objects is with a <em>block-and-tackle</em>, a schematic diagram of which is shown above (usually an actual block-and-tackle has all the pulleys on the same axle, but that's hard to draw). Suppose there is a heavy crate (mass 100 kg) attached to the lower pulleys. What force must be applied to rope R to lift the crate off the ground? Assume all the pulleys are frictionless and massless.</p>

  <h3 class="section-title">Practical Applications - Accessibility Solutions</h3>
  
  <div class="info-box">
    <p><strong>Benefits of Pulleys:</strong> While searching about uses of pulleys, we find that homes and communities may be modified to adapt the needs of disabled village children. This makes an excellent science project idea for students who need to make a compound machine or show the applications of pulleys.</p>
  </div>

  <p>With the help of pulleys a person can lift themselves to an elevated area such as a tree or second floor of a village house. A model of house may be made using wood sticks and cardboards.</p>

  <div class="center">
    <img src="dwe00253g02.gif" alt="Accessibility pulley system for lifting wheelchair users" />
  </div>

  <p><strong>Project Purpose:</strong> The purpose of this project is to show that a system of ropes and pulleys may be the best way for a person with strong arms to lift themselves without help to a 'house on stilts'.</p>

  <p>The 'lift' can be made with a platform so that the whole wheelchair can be lifted. But if the house is small and people cook and eat at floor level, it may be best to leave the wheelchair outside.</p>

  <p>For people who do not have strong arms, you may use pulley blocks with 2 or more pulleys. For example a pair of pulley blocks with 3 pulleys in each will increase the force by 6.</p>

  <h3 class="section-title">Available Pulley Sets for Science Fair Projects</h3>

  <div class="two-col-reverse">
    <div>
      <p><strong>Single Pulley System:</strong> Two, single plastic pulleys can be combined to form a fixed/moveable block and tackle pulley system. These pulleys have sheaves with deep V-grooves with diameters of 50 mm.</p>
      
      <p>In addition to the pulleys you will need a wooden frame or structure to hang your fixed pulley. You will also need Nylon Mason line #18 or any similar nylon string (Available from Hardware stores). This set will reduce the lifting force by about <strong>50%</strong>.</p>
    </div>
    <div>
      <img src="2pulley5_demo.jpg" alt="Single pulley system demonstration" class="responsive-img" />
    </div>
  </div>

  <div class="two-col-reverse">
    <div>
      <p><strong>Double Pulley System:</strong> Two, double pulley blocks can be combined to form a fixed/moveable block and tackle pulley system. Each pulley block has 2 plastic sheaves (mounted side by side) with deep V-grooves with diameters of 50 mm.</p>
      
      <p>In addition to the pulley blocks you will need a wooden frame or structure to hang your fixed pulley. You will also need Nylon Mason line #18 or any similar nylon string (Available from Hardware stores). This set will reduce the lifting force by about <strong>75%</strong>.</p>
    </div>
    <div>
      <img src="2pulley55_demo.jpg" alt="Double pulley system demonstration" class="responsive-img" />
    </div>
  </div>

  <div class="two-col-reverse">
    <div>
      <p><strong>Quad Pulley System:</strong> Two, quad pulley blocks can be combined to form a fixed/moveable block and tackle pulley system. Each pulley block has 2 plastic sheaves (mounted side by side) with deep V-grooves with diameters of 50 mm.</p>
      
      <p>In addition to the pulley blocks you will need a wooden frame or structure to hang your fixed pulley. You will also need Nylon Mason line #18 or any similar nylon string (Available from Hardware stores). This set will reduce the lifting force by about <strong>87%</strong>.</p>
      
      <p>Pairs of triple parallel pulley blocks are also available for intermediate force reduction.</p>
    </div>
    <div>
      <img src="2pulley5555_demo.jpg" alt="Quad pulley system demonstration" class="responsive-img" />
    </div>
  </div>

  <h3 class="section-title">Advanced Compound Machines</h3>

  <div class="two-col">
    <div>
      <img src="Pulleysystem-sm.jpg" alt="Advanced elevator pulley system" class="responsive-img" />
    </div>
    <div>
      <p><strong>Elevator Design:</strong> This picture shows an elevator for lifting heavy loads. This is one of the best compound machines for science projects. In this design pulleys are used to reduce the lifting force as well as changing the direction of the force. This design also incorporates a wheel and axle.</p>
    </div>
  </div>

  <div class="two-col-reverse">
    <div>
      <p><strong>Experimental Applications:</strong> This elevator project shows how pulley blocks may be made using sheaves. This simple design reduces the lifting force by 75%.</p>
      
      <p>Compound machines are generally classified as engineering projects. If you need to do a compound machine project as an experimental project, you can use your elevator to lift different masses from 1 Lb up to 5 Lbs and use a 2000-gram spring scale to measure the lifting force. Your results table will show the lifting force for different actual weights. You can then use your results table to draw a graph.</p>
    </div>
    <div>
      <img src="5%20Sheave%20combo-m.jpg" alt="Five sheave compound pulley system" class="responsive-img" />
    </div>
  </div>

  <div class="two-col">
    <div>
      <img src="pulley.bmp" alt="Pulley system with spring scale for force measurement" class="responsive-img" />
    </div>
    <div>
      <p><strong>Force Measurement Setup:</strong> The diagram shows a pulley system using 2 single pulleys and 2 sheaves. The end of the string is connected to a spring scale in order to measure the force required to lift the heavy block.</p>
      
      <p>In this design the upper block (fixed block) is made of sheaves. Sheaves may be used in fixed blocks or may be used to construct pulleys or pulley blocks. In the middle of each sheave is a hole that can be used to nail it to a board or block.</p>
    </div>
  </div>

  <div class="two-col-reverse">
    <div>
      <p><strong>Individual Sheaves:</strong> Sheaves may be used in fixed blocks or may be used to construct pulleys or pulley blocks. In the middle of each sheave is a hole that can be used to nail it to a board or block.</p>
    </div>
    <div>
      <img src="5SHEAVES-tn.jpg" alt="Individual sheaves for custom pulley construction" class="responsive-img" />
    </div>
  </div>

  <p class="green-highlight" style="font-size: 1.2rem;">We have the largest collection of pulleys for science fair projects and educational activities.</p>

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
        <div id="abc_907" class="abantecart_product" data-product-id="1594" data-language="en" data-currency="USD">
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
        <div id="abc_776" class="abantecart_product" data-product-id="2203" data-language="en" data-currency="USD">
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
        <div id="abc_738" class="abantecart_product" data-product-id="2204" data-language="en" data-currency="USD">
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
        <div id="abc_169" class="abantecart_product" data-product-id="2548" data-language="en" data-currency="USD">
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
        <li id="abc_1760549676075103" class="abantecart_category" data-category-id="103" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760549710111138" class="abantecart_category" data-category-id="138" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760549727796137" class="abantecart_category" data-category-id="137" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760549748696126" class="abantecart_category" data-category-id="126" data-language="en" data-currency="USD">
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
    <summary>What is mechanical advantage and how do pulleys provide it?</summary>
    <p>Mechanical advantage is the factor by which a machine multiplies the force put into it. Pulleys provide mechanical advantage by distributing the load across multiple rope segments. For example, a system with 4 rope segments supporting the load provides a 4:1 mechanical advantage, meaning you only need 1/4 the force to lift the object.</p>
  </details>
  <details>
    <summary>What's the difference between fixed and movable pulleys?</summary>
    <p>A fixed pulley is attached to a stationary support and only changes the direction of force - it doesn't provide mechanical advantage. A movable pulley moves with the load and does provide mechanical advantage. Combining both types creates compound pulley systems with greater mechanical advantage.</p>
  </details>
  <details>
    <summary>Why do I need more rope length with compound pulleys?</summary>
    <p>This is the trade-off for mechanical advantage. While you use less force to lift an object, you must pull the rope a greater distance. If a pulley system gives you a 4:1 advantage, you'll need to pull 4 feet of rope to lift the object 1 foot.</p>
  </details>
  <details>
    <summary>What materials work best for pulley experiments?</summary>
    <p>Use nylon rope or mason's line for best results - they don't stretch much and are strong. Avoid cotton rope which stretches significantly. For weights, use known masses like books, water bottles, or proper weights. A spring scale is essential for measuring forces accurately.</p>
  </details>
  <details>
    <summary>How do I calculate the theoretical mechanical advantage?</summary>
    <p>Count the number of rope segments that directly support the movable load (not including the effort rope). This number equals your theoretical mechanical advantage. Real systems have less advantage due to friction in the pulleys and rope stretching.</p>
  </details>
  <details>
    <summary>What safety precautions should I take with pulley experiments?</summary>
    <p>Always secure fixed pulleys firmly to prevent falling. Don't exceed weight limits of your pulleys and rope. Wear safety glasses when under suspended loads. Have adult supervision when working with heavy weights. Check all connections before lifting loads.</p>
  </details>
  <details>
    <summary>Can I use pulleys for accessibility applications?</summary>
    <p>Yes! Pulleys are excellent for creating lifting systems for people with mobility challenges. They can be used to lift wheelchairs, create manual elevators, or assist with transferring between levels. Always ensure proper engineering and safety factors for human-lifting applications.</p>
  </details>
  <details>
    <summary>What's the difference between pulleys and gears?</summary>
    <p>Pulleys use flexible ropes or belts to transmit force and can change direction in 3D space, while gears use rigid teeth and typically work in one plane. Pulleys are better for long-distance force transmission and direction changes, while gears provide more precise mechanical advantage and don't slip.</p>
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