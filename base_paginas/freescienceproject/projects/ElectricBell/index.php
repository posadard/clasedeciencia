<?php
// Project: Electric Bell
$project_title = "Make an Electric Bell";
$project_description = "Build a working electric bell kit using electromagnets. Learn about electromagnetism, electrical circuits, and mechanical switches through hands-on construction and experimentation.";
$project_keywords = "electric bell, electromagnet, electrical circuits, magnetic switch, buzzer, electromagnetism, electrical engineering, science project";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Physics, Electrical Engineering, Electromagnetism";
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
    .intro-section { display: grid; grid-template-columns: 2fr 3fr; gap: 1rem; align-items: start; background: #f8f9ff; border: 1px solid #ff99cc; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .intro-section img { width: 100%; height: auto; display: block; }
    .kit-details-section { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; align-items: start; margin: 1rem 0; }
    .kit-contents { background: #f0f8f0; padding: 1rem; border-radius: 8px; }
    .science-fair-section { background: #fff6f8; border: 1px solid #ff0000; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .bottom-showcase { display: grid; grid-template-columns: 1fr 2fr; gap: 1rem; align-items: center; margin: 1rem 0; background: #f8f9ff; padding: 1rem; border-radius: 8px; }
    .bottom-showcase img { width: 100%; height: auto; display: block; }
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
    /* Step-by-step instructions styling */
    .step-container { background: white; padding: 1rem; border-radius: 6px; margin: 1rem 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .step-title { color: #ff6b35; margin: 0 0 0.5rem 0; font-size: 1.1rem; font-weight: bold; }
    .step-number { background: #ff6b35; color: white; border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold; margin-right: 0.5rem; }
    .experiment-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; }
    .experiment-card { background: white; padding: 1rem; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .experiment-title { color: #34c759; margin-top: 0; font-size: 1rem; font-weight: bold; }
    @media (max-width: 720px) {
      .intro-section { grid-template-columns: 1fr; text-align: center; }
      .kit-details-section { grid-template-columns: 1fr; }
      .bottom-showcase { grid-template-columns: 1fr; text-align: center; }
      .banner-row { flex-direction: column; gap: 0.5rem; }
      .experiment-grid { grid-template-columns: 1fr; }
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

  <!-- Kids-friendly introduction -->
  <div class="intro-section">
    <div>
      <img src="ElectricBell.jpg" alt="Electric bell kit showing electromagnet and bell mechanism" class="responsive-img">
    </div>
    <div>
      <p><strong style="color: #ff00ff; font-size: 1.2rem;">Build Your Own Electric Bell!</strong></p>
      <p><strong>Hi Young Scientists!</strong> Are you ready to build something amazing? Today we're going to make a real electric bell that rings when you press a button - just like a doorbell!</p>
      <p><strong>What's so cool about this?</strong> Your bell will use the power of <em>magnetism</em> and <em>electricity</em> working together. When electricity flows through a special wire coil, it becomes a magnet that can move things!</p>
      <p><strong>What will you learn?</strong> You'll discover how doorbells work, why magnets are important in everyday objects, and how to build your own electrical circuits safely.</p>
    </div>
  </div>

  <div class="kit-details-section">
    <div class="kit-contents">
      <h3 class="section-title">Electric Bell kit includes:</h3>
      <ul>
        <li>Plastic base</li>
        <li>Bobbin</li>
        <li>Magnet wire</li>
        <li>Bell</li>
        <li>Bell hammer</li>
        <li>Compass needle</li>
        <li>Screw and screw driver</li>
        <li>Spring</li>
        <li>Metal contacts</li>
        <li>Sandpaper</li>
      </ul>
    </div>
    <div>
      <h3 class="section-title">Additional Materials Required:</h3>
      <p>Additional Materials Required for your experiments can be found at home or purchased locally. Some of these material are:</p>
      <ul>
        <li>Only a D cell battery.</li>
      </ul>
    </div>
  </div>

  <!-- Step-by-step instructions for kids -->
  <div style="background: #e8f5e8; padding: 1.5rem; border-radius: 8px; margin: 1.5rem 0; border-left: 4px solid #34c759;">
    <h3 style="color: #2c5aa0; margin-top: 0;">Step-by-Step</h3>
    
    <div style="background: white; padding: 1rem; border-radius: 6px; margin: 1rem 0;">
      <h4 style="color: #ff6b35; margin: 0 0 0.5rem 0;">Step 1: Get Ready!</h4>
      <p><strong>Safety First:</strong> Ask an adult to help you. Make sure your hands are clean and dry.</p>
      <p><strong>What you need:</strong> Your electric bell kit and one D-size battery (the big fat kind!)</p>
      <p><strong>Workspace:</strong> Find a clean, flat surface with good light where you can spread out all your parts.</p>
    </div>

    <div style="background: white; padding: 1rem; border-radius: 6px; margin: 1rem 0;">
      <h4 style="color: #ff6b35; margin: 0 0 0.5rem 0;">Step 2: Meet Your Parts!</h4>
      <p>Take out all the pieces and lay them on the table. You should see:</p>
      <ul>
        <li><strong>Plastic base</strong> - This is like the foundation of a house</li>
        <li><strong>Bobbin with wire</strong> - This will become your electromagnet</li>
        <li><strong>Bell</strong> - The part that makes the "ring" sound</li>
        <li><strong>Hammer</strong> - The part that hits the bell</li>
        <li><strong>Spring</strong> - This helps the hammer bounce back</li>
        <li><strong>Metal contacts</strong> - These help electricity flow</li>
        <li><strong>Compass needle</strong> - To test your electromagnet</li>
      </ul>
    </div>

    <div style="background: white; padding: 1rem; border-radius: 6px; margin: 1rem 0;">
      <h4 style="color: #ff6b35; margin: 0 0 0.5rem 0;">Step 3: Build the Electromagnet!</h4>
      <p><strong>This is the magic part!</strong> The bobbin already has wire wrapped around it. This creates an electromagnet.</p>
      <p><strong>Test it:</strong> Connect the battery to the wire ends and hold the compass needle near the bobbin. Watch the needle move - that's your electromagnet working!</p>
      <p><strong>Cool fact:</strong> When electricity flows through the wire, it creates an invisible magnetic field that can push and pull metal objects!</p>
    </div>

    <div style="background: white; padding: 1rem; border-radius: 6px; margin: 1rem 0;">
      <h4 style="color: #ff6b35; margin: 0 0 0.5rem 0;">Step 4: Mount Everything!</h4>
      <p><strong>Attach the electromagnet:</strong> Screw the bobbin to the plastic base using the screwdriver.</p>
      <p><strong>Position the bell:</strong> Mount the bell so it's close to where the hammer will be.</p>
      <p><strong>Install the hammer:</strong> Attach the hammer with its spring so it can move back and forth.</p>
      <p><strong>Important:</strong> The hammer should be able to touch the electromagnet when it's pulled, but spring back when the magnet turns off.</p>
    </div>

    <div style="background: white; padding: 1rem; border-radius: 6px; margin: 1rem 0;">
      <h4 style="color: #ff6b35; margin: 0 0 0.5rem 0;">Step 5: Wire It Up!</h4>
      <p><strong>Connect the circuit:</strong> Use the metal contacts to create a complete path for electricity.</p>
      <p><strong>The magic happens:</strong></p>
      <ol>
        <li>When you press the button, electricity flows to the electromagnet</li>
        <li>The electromagnet pulls the hammer toward it</li>
        <li>The hammer hits the bell - RING!</li>
        <li>But wait! When the hammer moves, it breaks the circuit</li>
        <li>No electricity = no magnet = hammer springs back</li>
        <li>This reconnects the circuit and the whole thing starts again!</li>
      </ol>
    </div>

    <div style="background: white; padding: 1rem; border-radius: 6px; margin: 1rem 0;">
      <h4 style="color: #ff6b35; margin: 0 0 0.5rem 0;">Step 6: Test Your Bell!</h4>
      <p><strong>The moment of truth:</strong> Connect your battery and press the button!</p>
      <p><strong>If it works:</strong> Congratulations! You've built a working electromagnet device!</p>
      <p><strong>If it doesn't work:</strong> Don't worry! Check all your connections and make sure the battery is fresh.</p>
      <p><strong>Troubleshooting tip:</strong> Make sure the hammer can move freely and the spring isn't too tight.</p>
    </div>
  </div>


 

  <div class="bottom-showcase">
    <div>
      <img src="ElectricBell_tn.jpg" alt="Electric bell kit thumbnail" class="responsive-img">
    </div>
    <div class="center">
      <p><strong>Build your own working electric bell and learn about electromagnets!</strong></p>
      <p>Perfect for understanding electrical circuits, magnetic fields, and mechanical switches through hands-on experimentation.</p>
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
        <div id="abc_872" class="abantecart_product" data-product-id="682" data-language="en" data-currency="USD">
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
        <div id="abc_326" class="abantecart_product" data-product-id="684" data-language="en" data-currency="USD">
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
        <div id="abc_566" class="abantecart_product" data-product-id="482" data-language="en" data-currency="USD">
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
        <li id="abc_176046911588289" class="abantecart_category" data-category-id="89" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760469140905100" class="abantecart_category" data-category-id="100" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760469156554102" class="abantecart_category" data-category-id="102" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176046917288088" class="abantecart_category" data-category-id="88" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>
  </div>
</section>

<section class="faq">
  <h3>Questions, About Electric Bells</h3>
  <details>
    <summary>How does an electric bell work? (The simple answer!)</summary>
    <p><strong>Think of it like this:</strong> When you press the button, electricity flows through a special wire coil that becomes a magnet. This magnet pulls a hammer toward it, and the hammer hits the bell - RING! But here's the cool part: when the hammer moves, it breaks the electrical connection, so the magnet turns off and the hammer springs back. This makes the connection again, and the whole thing repeats super fast - that's why you hear a continuous ringing!</p>
  </details>
  <details>
    <summary>What makes the electromagnet strong enough to move things?</summary>
    <p><strong>It's all about the wire coils!</strong> The more times you wrap wire around the iron core, the stronger your electromagnet becomes. It's like making a stronger rope by twisting more threads together. The electricity flowing through all those coils creates a powerful magnetic field that can pull metal objects toward it!</p>
  </details>
  <details>
    <summary>Why do we need a spring in the electric bell?</summary>
    <p><strong>The spring is like a rubber band!</strong> Without it, the hammer would get stuck to the electromagnet and stay there. The spring's job is to pull the hammer back to its starting position so it can ring the bell again and again. It's what makes the bell keep ringing instead of just making one "clunk" sound.</p>
  </details>
  <details>
    <summary>Can I make my bell sound different?</summary>
    <p><strong>Absolutely!</strong> Try using different objects as your "bell" - a spoon, a small pot, or even a bottle cap. Each material makes a different sound. You can also try adjusting how hard the hammer hits by changing the spring tension or moving the hammer closer or farther from the bell.</p>
  </details>
  <details>
    <summary>What's the best battery to use?</summary>
    <p><strong>A D-size battery works great!</strong> These are the big, fat batteries. They have more power stored inside than smaller batteries like AA or AAA, so they'll keep your bell ringing longer. Always use a fresh battery for the best results - old batteries are like tired muscles, they don't work as well!</p>
  </details>
  <details>
    <summary>How can I make my electromagnet even stronger?</summary>
    <p><strong>Great question!</strong> You can make your electromagnet stronger by wrapping more wire around the bobbin (with an adult's help). Each extra loop of wire adds to the magnetic power. You could also try using a thicker iron core inside the coil, or even connect two batteries together (but always ask an adult first!).</p>
  </details>
  <details>
    <summary>Where do we use electromagnets in real life?</summary>
    <p><strong>Electromagnets are everywhere!</strong> They're in doorbells (just like yours!), speakers that play music, headphones, car horns, electric motors in toys and appliances, MRI machines in hospitals, and even in some roller coasters! Now that you know how they work, you'll start noticing them everywhere!</p>
  </details>
  <details>
    <summary>Is this safe for kids to build?</summary>
    <p><strong>Yes, with adult supervision!</strong> This project uses low-voltage electricity that's safe when handled properly. Always have an adult help you, keep your hands dry, and never touch bare wires when the battery is connected. If something doesn't work right, ask for help instead of trying to fix it yourself.</p>
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