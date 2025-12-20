<?php
// Project: Music Effects on Plant Growth
$project_title = "Music Effects on Plant Growth - What Effect Does Music Have on Plant Growth?";
$project_description = "Investigate whether different types of music affect plant growth and health. Explore the controversial topic of plant acoustics, sound vibrations, and their potential influence on botanical development.";
$project_keywords = "plant music, sound vibrations, plant acoustics, music effects on plants, botanical experiment, vibration effects, plant growth";
$project_grade_level = "Elementary"; // recommended
$project_subject = "Biology, Plant Science";
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
    .project-body { max-width: 980px; margin: 0 auto; padding: 1rem; }
    .project-title-simple { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color: #2c5aa0; font-size: 1.5rem; margin: 0 0 0.75rem 0; }
    .intro-section { background: #f8f9ff; border: 1px solid #2c5aa0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
    .problem-section { background: #fff6f0; border: 1px solid #ff6b35; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .research-section { background: #f0fff0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .hypothesis-section { background: #f0f8ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .materials-section { background: #fff8f0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .procedure-section { background: #f8fff8; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .results-section { background: #fff6f0; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .safety-section { background: #ffe6e6; border: 1px solid #ff4444; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .extensions-section { background: #f0f0ff; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .science-section { background: #e6ffe6; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .student-results { background: #f0f8ff; border: 1px solid #6ba3d6; padding: 1rem; border-radius: 8px; margin: 1rem 0; }
    .responsive-img { max-width: 100%; height: auto; display: block; }
    .section-title { color: #2c5aa0; font-weight: bold; font-size: 1.2rem; margin: 1rem 0 0.5rem 0; }
    .highlight-text { color: #2c5aa0; font-weight: bold; }
    .center { text-align: center; }
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
    <p>The <strong class="highlight-text">Music Effects on Plant Growth Experiment</strong> investigates the controversial topic of whether different types of music influence plant growth and health. This intriguing botanical experiment explores the potential effects of sound vibrations on living organisms.</p>
    
    <p>Perfect for exploring <strong>plant acoustics, sound vibrations, and experimental design</strong>, this project examines whether plants respond to different musical genres and provides practice in controlled scientific observation.</p>
  </div>

  <div class="problem-section">
    <h3 class="section-title">🔬 Research Question</h3>
    <p class="highlight-text" style="font-size: 1.1rem;">What effect does music have on plant growth?</p>
    
    <p>This experiment tests whether exposure to different types of music (classical, rock, pop, etc.) affects plant growth, health, and development compared to plants grown in silence.</p>
  </div>

  <div class="research-section">
    <h3 class="section-title">🧠 Background Research</h3>
    <p>The idea that music affects plant growth has been studied for decades, though results remain <strong>controversial and inconsistent</strong>. Some researchers claim positive effects, while others find no significant differences.</p>
    
    <p><strong>Theoretical mechanisms for music effects:</strong></p>
    <ul>
      <li><strong>Sound vibrations</strong> - May stimulate cellular activity or fluid movement</li>
      <li><strong>Frequency effects</strong> - Different frequencies might affect plant processes</li>
      <li><strong>Resonance</strong> - Plant cells might respond to specific sound frequencies</li>
      <li><strong>Stomata response</strong> - Sound waves could influence pore opening/closing</li>
      <li><strong>Growth hormones</strong> - Vibrations might stimulate hormone production</li>
    </ul>
    
    <p><strong>Scientific challenges:</strong></p>
    <ul>
      <li><strong>Confounding variables</strong> - Many factors affect plant growth simultaneously</li>
      <li><strong>Reproducibility issues</strong> - Results often vary between experiments</li>
      <li><strong>Small sample sizes</strong> - Most studies use too few plants for statistical significance</li>
      <li><strong>Subjective measurements</strong> - "Health" assessments can be biased</li>
      <li><strong>Environmental factors</strong> - Temperature, humidity, light differences between rooms</li>
    </ul>
    
    <p>While the science remains uncertain, this experiment provides excellent practice in controlled observation and critical thinking about experimental design.</p>
  </div>

  <div class="hypothesis-section">
    <h3 class="section-title">💭 Hypothesis</h3>
    <p><strong>Prediction:</strong> Classical music will help plant growth while rock music will hinder growth, since studies suggest that classical music has beneficial effects on concentration and stress reduction in humans and other organisms.</p>
    
    <p><strong>Alternative hypothesis:</strong> Music type will have no significant effect on plant growth, as plants lack nervous systems to process sound in the same way animals do.</p>
  </div>

  <div class="materials-section">
    <h3 class="section-title">🛠️ Materials Needed</h3>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>Living Materials:</h4>
        <ul>
          <li>3-4 identical plants (same species, size, and age)</li>
          <li>Similar-sized pots with saucers</li>
          <li>Potting soil (if repotting needed)</li>
        </ul>
        
        <h4>Audio Equipment:</h4>
        <ul>
          <li>2-3 small stereos or Bluetooth speakers</li>
          <li>Classical music CD or playlist</li>
          <li>Rock music CD or playlist</li>
          <li>Optional: Other genres (pop, jazz, etc.)</li>
        </ul>
      </div>
      <div>
        <h4>Measurement Tools:</h4>
        <ul>
          <li>Ruler for measuring plant height</li>
          <li>Plant labels (Classical, Rock, Control, etc.)</li>
          <li>Data recording sheets</li>
          <li>Camera (for documentation)</li>
          <li>Timer or schedule for music exposure</li>
        </ul>
        
        <h4>Environment Control:</h4>
        <ul>
          <li>3-4 separate rooms or areas</li>
          <li>Identical light conditions in each area</li>
          <li>Thermometers to monitor temperature</li>
          <li>Watering containers</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="procedure-section">
    <h3 class="section-title">⚗️ Experimental Procedure</h3>
    
    <ol>
      <li><strong>Plant preparation:</strong> Select 3-4 identical plants of the same species, size, and health. Label them clearly: "Classical," "Rock," "Control" (and additional genres if testing more).</li>
      
      <li><strong>Environment setup:</strong> Place each plant in a separate room or area with identical conditions:
        <ul>
          <li>Same amount of natural light or artificial lighting</li>
          <li>Similar temperature and humidity</li>
          <li>Same watering schedule and soil conditions</li>
        </ul>
      </li>
      
      <li><strong>Initial measurements:</strong> Record starting height, number of leaves, stem thickness, and overall health condition of each plant. Take photos for reference.</li>
      
      <li><strong>Music exposure schedule:</strong> 
        <ul>
          <li>Classical plant: Play classical music 2-3 hours daily at moderate volume</li>
          <li>Rock plant: Play rock music 2-3 hours daily at same volume level</li>
          <li>Control plant: Keep in quiet environment with no music</li>
        </ul>
      </li>
      
      <li><strong>Daily care:</strong> Water all plants equally when soil feels dry. Maintain identical care routines for all plants.</li>
      
      <li><strong>Daily observations:</strong> Record plant height, leaf count, color, and overall health using a consistent 1-10 scale. Note any visible changes.</li>
      
      <li><strong>Duration:</strong> Continue the experiment for 2-3 weeks, making daily observations and measurements.</li>
      
      <li><strong>Final analysis:</strong> Compare all measurements and analyze whether music exposure created any measurable differences.</li>
    </ol>
  </div>

  <div class="results-section">
    <h3 class="section-title">📊 Expected Results & Analysis</h3>
    
    <p><strong>Original student results (as reported):</strong></p>
    
    <table class="data-table">
      <thead>
        <tr>
          <th>Treatment</th>
          <th>Final Ranking</th>
          <th>Observed Condition</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Classical Music</strong></td>
          <td>1st (Best condition)</td>
          <td>Healthy growth and good color</td>
        </tr>
        <tr>
          <td><strong>No Music (Control)</strong></td>
          <td>2nd</td>
          <td>Normal, steady growth</td>
        </tr>
        <tr>
          <td><strong>Rock Music</strong></td>
          <td>3rd (Worst condition)</td>
          <td>Slower growth, less vigor</td>
        </tr>
      </tbody>
    </table>
    
    <p><strong>Alternative student results (5th grade project):</strong></p>
    
    <table class="data-table">
      <thead>
        <tr>
          <th>Treatment</th>
          <th>Final Ranking</th>
          <th>Student Notes</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><strong>Pop Music</strong></td>
          <td>1st place</td>
          <td>Best growth and appearance</td>
        </tr>
        <tr>
          <td><strong>Rock Music</strong></td>
          <td>2nd place</td>
          <td>Good growth, second best</td>
        </tr>
        <tr>
          <td><strong>No Music</strong></td>
          <td>3rd place</td>
          <td>Average growth</td>
        </tr>
        <tr>
          <td><strong>Rap Music</strong></td>
          <td>4th place (last)</td>
          <td>Poorest growth observed</td>
        </tr>
      </tbody>
    </table>
    
    <p><strong>Analysis:</strong> Results show <span class="highlight-text">significant variation between experiments</span>, which is typical for this type of study. The inconsistent outcomes highlight the importance of proper experimental controls and the challenges in plant acoustics research. Both experiments found differences, but they don't agree on which music types are beneficial.</p>
  </div>

  <div class="student-results">
    <h3 class="section-title">📝 Student Report (Historical Results)</h3>
    <div style="font-style: italic; color: #4a5568; background: #edf2f7; padding: 1rem; border-radius: 6px;">
      <p><strong>From a 5th Grade Science Fair Project:</strong></p>
      <p>"I did the project entitled 'Do Plants Grow Better to Music', and I got different results! I did Rap, Pop, Rock, and No Music. My Results were Pop 1st, Rock 2nd, None 3rd, and Rap was last! Thanks for the help!!! My 5th Grade Science Fair Project was a BIG Hit!!!!!!"</p>
    </div>
    
    <p><strong>Learning from contradictory results:</strong> These different outcomes teach us about the importance of replication in science and show why controversial topics require many experiments before drawing conclusions.</p>
  </div>

  <div class="safety-section">
    <h3 class="section-title" style="color: #cc0000;">⚠️ Safety Information</h3>
    <ul>
      <li><strong>Hearing protection</strong> - Keep music at moderate volumes to protect hearing</li>
      <li><strong>Electrical safety</strong> - Keep stereo equipment away from water and plants</li>
      <li><strong>Noise consideration</strong> - Respect neighbors and household members</li>
      <li><strong>Equipment stability</strong> - Secure audio equipment to prevent damage</li>
      <li><strong>Plant handling</strong> - Be gentle when measuring and observing plants</li>
      <li><strong>Consistent environment</strong> - Maintain similar conditions in all test areas</li>
    </ul>
  </div>

  <div class="science-section">
    <h3 class="section-title">🌱 Plant Acoustics and Vibration Science</h3>
    <p><strong>Understanding the theoretical basis and scientific challenges:</strong></p>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>Possible Mechanisms:</h4>
        <ul>
          <li>Sound waves creating cellular vibrations</li>
          <li>Frequency resonance with plant structures</li>
          <li>Stimulation of stomata (leaf pores)</li>
          <li>Enhancement of nutrient transport</li>
        </ul>
      </div>
      <div>
        <h4>Scientific Skepticism:</h4>
        <ul>
          <li>Plants lack auditory organs like animals</li>
          <li>Many studies lack proper controls</li>
          <li>Results are often not reproducible</li>
          <li>Alternative explanations not ruled out</li>
        </ul>
      </div>
    </div>
  </div>

  <div class="extensions-section">
    <h3 class="section-title">🔬 Project Extensions & Variations</h3>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      <div>
        <h4>Advanced Investigations:</h4>
        <ul>
          <li>Test specific frequencies (pure tones)</li>
          <li>Vary volume levels systematically</li>
          <li>Test different exposure durations</li>
          <li>Use more plant species</li>
          <li>Include vibration without sound</li>
        </ul>
      </div>
      <div>
        <h4>Science Fair Enhancements:</h4>
        <ul>
          <li>Use larger sample sizes (10+ plants per group)</li>
          <li>Measure multiple variables quantitatively</li>
          <li>Create detailed statistical analysis</li>
          <li>Research professional plant acoustics studies</li>
          <li>Design blind observation protocols</li>
        </ul>
      </div>
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
        <div id="abc_648" class="abantecart_product" data-product-id="3399" data-language="en" data-currency="USD">
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
        <div id="abc_102" class="abantecart_product" data-product-id="7265" data-language="en" data-currency="USD">
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
        <div id="abc_718" class="abantecart_product" data-product-id="2396" data-language="en" data-currency="USD">
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
        <li id="abc_1760464461106106" class="abantecart_category" data-category-id="106" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_1760464483492139" class="abantecart_category" data-category-id="139" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176054719587671" class="abantecart_category" data-category-id="71" data-language="en" data-currency="USD">
          <span class="abantecart_image"></span>
          <h3 class="abantecart_name"></h3>
          <p class="abantecart_products_count"></p>
        </li>
      </ul>
    </div>

    <div class="miniscience-widget" aria-hidden="false" style="margin:0.75rem 0;">
      <script src="https://shop.miniscience.com/index.php?rt=r/embed/js" type="text/javascript"></script>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        <li id="abc_176054721058387" class="abantecart_category" data-category-id="87" data-language="en" data-currency="USD">
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
    <summary>Do plants really respond to music?</summary>
    <p>The scientific evidence is mixed and controversial. Some studies report effects, while others find no significant differences. Plants lack ears and nervous systems like animals, so any effects would likely be through vibrations rather than "hearing" music as we understand it.</p>
  </details>
  <details>
    <summary>Why do different experiments get different results?</summary>
    <p>Plant growth is affected by many factors simultaneously: light, temperature, humidity, soil conditions, plant species, and genetic variation. Small differences between experimental setups can produce different outcomes, making this a challenging area to study scientifically.</p>
  </details>
  <details>
    <summary>What should I do if my results don't match the original study?</summary>
    <p>Different results are completely normal and valuable! Science advances through replication and variation. Document your actual observations carefully - contradictory results teach us about experimental limitations and the complexity of biological systems.</p>
  </details>
  <details>
    <summary>How can I make my experiment more scientific?</summary>
    <p>Use more plants (at least 10 per group), maintain identical environmental conditions, measure quantitative variables (height, leaf count, weight), use blind observation (don't know which plant got which treatment when measuring), and run the experiment multiple times.</p>
  </details>
  <details>
    <summary>Could the music effects be due to other factors?</summary>
    <p>Absolutely! Different rooms might have slightly different temperatures, humidity, or light levels. The act of setting up music equipment might change air circulation. Even the experimenter's behavior might differ between treatments. Good experiments try to control for these variables.</p>
  </details>
  <details>
    <summary>What does real plant acoustics research show?</summary>
    <p>Some legitimate research suggests plants might respond to specific frequencies or vibrations, but the effects are usually small and difficult to reproduce. Most scientists remain skeptical about music specifically affecting plant growth.</p>
  </details>
  <details>
    <summary>Is this experiment still worth doing if the science is uncertain?</summary>
    <p>Yes! This experiment teaches excellent lessons about experimental design, variable control, observation skills, and critical thinking. Understanding why results vary is just as important as the results themselves.</p>
  </details>
  <details>
    <summary>How long should I play music for the plants?</summary>
    <p>Most studies that report effects use 1-4 hours daily. Continuous music might be stressful or unrealistic. Try 2-3 hours at consistent times, and keep volume at moderate levels to avoid any potential negative effects from excessive sound exposure.</p>
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