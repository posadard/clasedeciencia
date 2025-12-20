// Project Shop Categories Mapping
// Maps science projects to relevant shop.miniscience.com categories

const projectShopCategories = {
  // Subject-based category mapping
  subjectCategories: {
    'physics': {
      primary: ['Physics > Magnetism > Magnets', 'Lab Supplies > Plasticware'],
      categories: [
        { id: 132, name: 'Physics > Magnetism > Magnets' },
        { id: 112, name: 'Lab Supplies > Plasticware' }
      ]
    },
    'chemistry': {
      primary: ['Chemicals', 'Lab Supplies > Test Tubes'],
      categories: [
        { id: 75, name: 'Chemicals' },
        { id: 116, name: 'Lab Supplies > Test Tubes' }
      ]
    },
    'biology': {
      primary: ['Biology > Specimen samples', 'Lab Supplies > Magnifiers'],
      categories: [
        { id: 74, name: 'Biology > Specimen samples' },
        { id: 110, name: 'Lab Supplies > Magnifiers' }
      ]
    },
    'earth-science': {
      primary: ['Chemicals', 'Lab Supplies > Glassware'],
      categories: [
        { id: 75, name: 'Chemicals' },
        { id: 108, name: 'Lab Supplies > Glassware' }
      ]
    },
    'space': {
      primary: ['Lab Supplies > Magnifiers', 'Tools > Scales'],
      categories: [
        { id: 110, name: 'Lab Supplies > Magnifiers' },
        { id: 144, name: 'Tools > Scales' }
      ]
    }
  },

  // Grade-level specific categories
  gradeCategories: {
    'primary': {
      // K-4: Focus on basic, safe materials
      defaultCategories: [
        { id: 132, name: 'Physics > Magnetism > Magnets' },
        { id: 112, name: 'Lab Supplies > Plasticware' }
      ]
    },
    'elementary': {
      // 4-6: More sophisticated tools and materials
      defaultCategories: [
        { id: 110, name: 'Lab Supplies > Magnifiers' },
        { id: 108, name: 'Lab Supplies > Glassware' }
      ]
    },
    'intermediate': {
      // 7-8: Advanced tools and electrical components
      defaultCategories: [
        { id: 87, name: 'Electricity' },
        { id: 145, name: 'Tools > Thermometers' }
      ]
    },
    'senior': {
      // 9-12: Professional level equipment
      defaultCategories: [
        { id: 98, name: 'Electricity > Multimeters' },
        { id: 117, name: 'Metals' }
      ]
    }
  },

  // Special project mappings
  projectSpecific: {
    'volcano': [
      { id: 75, name: 'Chemicals' },
      { id: 144, name: 'Tools > Scales' }
    ],
    'electricity': [
      { id: 87, name: 'Electricity' },
      { id: 102, name: 'Electricity > Wires' }
    ],
    'magnetism': [
      { id: 132, name: 'Physics > Magnetism > Magnets' },
      { id: 131, name: 'Physics > Magnetism > Compass' }
    ],
    'battery': [
      { id: 88, name: 'Electricity > Battery Holders' },
      { id: 92, name: 'Electricity > Electrodes' }
    ],
    'motor': [
      { id: 97, name: 'Electricity > Motors' },
      { id: 132, name: 'Physics > Magnetism > Magnets' }
    ],
    'dna': [
      { id: 74, name: 'Biology > Specimen samples' },
      { id: 104, name: 'KITS > Science Kits' }
    ],
    'solar': [
      { id: 93, name: 'Electricity > Energy' },
      { id: 145, name: 'Tools > Thermometers' }
    ],
    'crystal': [
      { id: 75, name: 'Chemicals' },
      { id: 110, name: 'Lab Supplies > Magnifiers' }
    ]
  }
};

// Function to get categories for a project
function getProjectCategories(project, gradeLevel) {
  const categories = [];
  
  // Check for specific project type
  const projectTitle = project.title.toLowerCase();
  
  if (projectTitle.includes('volcano')) {
    return projectShopCategories.projectSpecific.volcano;
  }
  if (projectTitle.includes('electric') || projectTitle.includes('battery')) {
    return projectShopCategories.projectSpecific.electricity;
  }
  if (projectTitle.includes('magnet') || projectTitle.includes('compass')) {
    return projectShopCategories.projectSpecific.magnetism;
  }
  if (projectTitle.includes('motor')) {
    return projectShopCategories.projectSpecific.motor;
  }
  if (projectTitle.includes('dna')) {
    return projectShopCategories.projectSpecific.dna;
  }
  if (projectTitle.includes('solar')) {
    return projectShopCategories.projectSpecific.solar;
  }
  if (projectTitle.includes('crystal') || projectTitle.includes('salt')) {
    return projectShopCategories.projectSpecific.crystal;
  }
  
  // Fallback to subject-based categories
  if (projectShopCategories.subjectCategories[project.subject]) {
    return projectShopCategories.subjectCategories[project.subject].categories;
  }
  
  // Fallback to grade-level defaults
  return projectShopCategories.gradeCategories[gradeLevel].defaultCategories;
}

// Function to generate widget HTML
function generateShopWidget(categories, containerId) {
  const categoryItems = categories.map(cat => `
    <li id="abc_${Date.now()}_${cat.id}" class="abantecart_category" data-category-id="${cat.id}" data-language="en" data-currency="USD">
      <span class="abantecart_image"></span>
      <h3 class="abantecart_name"></h3>
      <p class="abantecart_products_count"></p>
    </li>
  `).join('');

  return `
    <div class="project-shop-widget">
      <h4>Related Supplies</h4>
      <ul style="display:none;" class="abantecart-widget-container" data-url="https://shop.miniscience.com/" data-css-url="https://shop.miniscience.com/extensions/foxy_template/storefront/view/foxy_template/stylesheet/embed.css" data-language="en" data-currency="USD">
        ${categoryItems}
      </ul>
    </div>
  `;
}