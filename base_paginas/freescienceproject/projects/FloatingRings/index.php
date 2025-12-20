<?php
// Project: Floating Rings - Magnetic Levitation
$project_title = "Floating Rings - Magnetic Levitation Experiment";
$project_description = "Explore magnetic levitation using ring magnets that float above each other due to magnetic repulsion. Build floating ring demonstrations and magnetic spring scales to understand magnetic forces and repulsion principles.";
$project_keywords = "magnetic levitation, floating rings, magnetic repulsion, ring magnets, magnetic spring scale, magnetism experiment, magnetic forces";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Physics, Magnetism";
$project_difficulty = "Elementary";

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
    .intro-section { background: #f8f9ff; border: 1px solid #ff99cc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .floating-rings-section { display: grid; grid-template-columns: 4fr 1fr; gap: 1rem; align-items: start; margin: 1rem 0; background: #f0f8ff; padding: 1rem; border-radius: 8px; }
    .floating-rings-section img { width: 100%; height: auto; display: block; }
    .magnetic-scale-section { display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; align-items: start; margin: 1rem 0; background: #f8fff8; padding: 1rem; border-radius: 8px; }
    .magnetic-scale-section img { width: 100%; height: auto; display: block; }
    .materials-section { background: #fff6f0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .procedure-section { background: #f0f0ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .purchase-section { display: grid; grid-template-columns: 3fr 2fr; gap: 1rem; align-items: center; margin: 1rem 0; background: #fff8f0; padding: 1rem; border-radius: 8px; }
    .purchase-section img { width: 100%; height: auto; display: block; }
    .age-group-section { background: #f0fff0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .responsive-img { max-width: 100%; height: auto; display: block; }
    .section-title { color: #0000ff; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .highlight-text { color: #0000ff; font-weight: bold; }
    .center { text-align: center; }
    /* Banner row (bottom of the page) */
    .banner-row { display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-top: 0.75rem; }
    .banner-row .banner { flex: 1 1 0; text-align: center; }
    .banner-row img { max-width: 100%; height: auto; display: inline-block; }
    @media (max-width: 720px) {
      .floating-rings-section { grid-template-columns: 1fr; text-align: center; }
      .magnetic-scale-section { grid-template-columns: 1fr; text-align: center; }
      .purchase-section { grid-template-columns: 1fr; text-align: center; }
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
    <h3 class="section-title">Magnet Levitation</h3>
    <p>The fact that same magnetic poles repel each other is the base for design of many industrial equipments. Repelling magnets are often part of another electrical or mechanical system. When you attempt to move the North pole of one magnet toward the North pole of another magnet, initially the other magnet may be pushed away, but soon it flips over and the South pole of that face and attract your magnet.</p>
    
    <p>Many studies have been done on levitating objects with magnetic force, however it is now proven that <strong>100% levitation for a non-moving object is impossible</strong>. Partial levitation is now used in construction of high speed magnetic trains. Many other instruments and equipment also use repelling properties of magnets.</p>
    
    <p>Following are some of the projects that can be made using magnets with same poles facing each other. They are all applications of <strong>magnet levitation</strong>.</p>
  </div>

  <div class="floating-rings-section">
    <div>
      <h3 class="section-title">Floating Rings</h3>
      <p>In this project you will make a set of magnet rings to float above each other while their balance is maintained using a wood dowel. You will then examine the flexibility of the floating rings and propose uses for such a floating set of rings.</p>
      
      <h4 style="color: #0000ff;">Material:</h4>
      <p>You will need a base board, a 6" wood dowel or pencil and six ring ceramic magnets, make sure that the wood dowel or pencil fits the hole in the center of magnets. Also try to get painted magnets. A layer of paint will protect ceramic magnets from chipping.</p>
      
      <h4 style="color: #0000ff;">Procedure:</h4>
      <p>Mount the pencil or wood dowel vertically in the center of the base board. If you use glue, you will need to wait a few hours until the glue is fully dry. Place the first ring magnet over the wood dowel and let it go down.</p>
      
      <p>Get a second magnet and bring it close to the first magnet to feel the magnetic forces and find out which two poles repel each other. Then insert this magnet in a way that when it gets to the first magnet, same poles are faced each other and two magnets will repel. So the second magnet will float.</p>
      
      <p><strong>Continue these steps with the other four magnets.</strong> Finally you will have 6 ceramic ring magnets on a column that can freely move up and down, but gravity force is not able to pull them down because the same poles of magnets are facing each other.</p>
      
      <p><strong>Experiment:</strong> Push the upper magnet down. How much force do you need to put all magnets together? Now release it. What happens? Why?</p>
      
      <p><strong>Challenge:</strong> Can you use this magnet levitation model to make other products?</p>
    </div>
    <div>
      <img src="rings.jpg" alt="Floating ring magnets demonstration" class="responsive-img">
    </div>
  </div>

  <div class="magnetic-scale-section">
    <div>
      <h3 class="section-title">Magnetic Spring Scale</h3>
      <p>One of the ideas have been a magnetic spring scale. As you see a clear plastic tube is placed above the upper magnet. Then another plastic tray is placed above the plastic tube. You may use a paper tube and a paper tray instead.</p>
      
      <p>When weight is placed on the tray, the tray goes down. The amount that it moves depends on the amount of weight. A piece of paper is used as the indicator hand. Also a Popsicle stick is used to mark the weight.</p>
      
      <p><strong>Flexibility:</strong> As you see most of the material can be replaced by other material that you may have around your home.</p>
    </div>
    <div>
      <img src="MagnetScale1.jpg" alt="Magnetic spring scale demonstration" class="responsive-img">
    </div>
  </div>

  <div class="age-group-section">
    <h3 class="section-title">Age Group</h3>
    <p>This is a good science project <strong>for ages 6 to 13</strong>. The concepts are simple enough for younger children to understand, yet sophisticated enough to engage older students in deeper experimentation.</p>
  </div>

  <div class="purchase-section">
    <div>
      <h3 class="section-title">Where to buy material?</h3>
      <p>Material for this project can be purchased from local hardware stores. You may also order it online from <a href="http://shop.miniscience.com/navigation/detail.asp?id=KITFR">MiniScience.com</a> or from <a href="http://www.klk.com/navigation/detail.asp?id=KITFR">KLK.com</a>. It is known as floating rings. The price of the kit with colored ceramic magnets is $12.00.</p>
      <div class="center">
        <a href="https://shop.miniscience.com/KITFRSG" class="buy-now-btn">Buy Now</a>
      </div>
    </div>
    <div>
      <a href="http://store.kidslovekits.com/navigation/detail.asp?id=KITFR"><img src="floatingringskit.jpg" alt="Floating rings kit" class="responsive-img"></a>
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
        <div id="abc_112" class="abantecart_product" data-product-id="7151" data-language="en" data-currency="USD">
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
        <div id="abc_203" class="abantecart_product" data-product-id="1690" data-language="en" data-currency="USD">
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
        <div id="abc_282" class="abantecart_product" data-product-id="1692" data-language="en" data-currency="USD">
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
        <li id="abc_1760473531330103" class="abantecart_category" data-category-id="103" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760473549088130" class="abantecart_category" data-category-id="130" data-language="en" data-currency="USD">
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
    <summary>Why do the ring magnets float instead of sticking together?</summary>
    <p>The ring magnets float because they are positioned with the same magnetic poles facing each other (North to North or South to South). Since like poles repel, the magnetic force pushes them apart, counteracting gravity and creating the floating effect.</p>
  </details>
  <details>
    <summary>Is true magnetic levitation possible?</summary>
    <p>Complete levitation of a stationary object using only permanent magnets is impossible due to Earnshaw's theorem. However, partial levitation (like our floating rings) works great for demonstrations and practical applications like magnetic trains that use additional stabilization systems.</p>
  </details>
  <details>
    <summary>How does the magnetic spring scale work?</summary>
    <p>The magnetic spring scale works by compressing the magnetic field between floating magnets. When weight is added to the top, it pushes down against the magnetic repulsion, causing measurable displacement that corresponds to the weight applied.</p>
  </details>
  <details>
    <summary>What happens if I flip one of the magnets around?</summary>
    <p>If you flip a magnet so opposite poles face each other, those two magnets will attract and stick together strongly. The magnetic repulsion effect will be broken, and the floating demonstration won't work properly until you restore the same-pole configuration.</p>
  </details>
  <details>
    <summary>Can I use different types of magnets for this project?</summary>
    <p>Ring magnets work best because they can slide on the dowel while maintaining alignment. Ceramic ring magnets are ideal because they're affordable and safe. Stronger neodymium magnets would work but require more careful handling due to their powerful magnetic fields.</p>
  </details>
  <details>
    <summary>Why do magnetic trains use this principle?</summary>
    <p>Magnetic levitation trains (maglev) use magnetic repulsion to eliminate friction between the train and track, allowing for extremely high speeds and smooth rides. However, they use electromagnets and computer control systems for stability, unlike our simple permanent magnet demonstration.</p>
  </details>
  <details>
    <summary>What real-world applications use magnetic repulsion?</summary>
    <p>Magnetic repulsion is used in maglev trains, magnetic bearings in industrial equipment, vibration isolation systems, magnetic levitation displays, and some types of magnetic locks and switches. The principle helps reduce friction and wear in mechanical systems.</p>
  </details>
  <details>
    <summary>How can I make the floating effect stronger?</summary>
    <p>To increase the floating effect, use stronger magnets, ensure proper pole alignment, keep the magnets clean and unchipped, and use a smooth dowel that allows free movement. Multiple rings create a more dramatic stacked floating effect.</p>
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