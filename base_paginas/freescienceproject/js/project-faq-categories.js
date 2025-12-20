// Project FAQ Categories Mapping
// Maps science projects to relevant FAQs based on subject, grade level, and difficulty

const projectFAQCategories = {
  // Subject-based FAQ mapping
  subjectFAQs: {
    'physics': [
      {
        question: "What safety precautions should I take with physics experiments?",
        answer: "Always wear safety goggles when working with moving parts or electrical components. Keep your workspace clear and follow all instructions carefully. Adult supervision is recommended for electrical experiments."
      },
      {
        question: "How can I make my physics project more accurate?",
        answer: "Use precise measuring tools, repeat your experiment multiple times, and record all observations. Consider environmental factors like temperature and humidity that might affect your results."
      },
      {
        question: "Why didn't my physics experiment work as expected?",
        answer: "Physics experiments can be sensitive to many variables. Check your setup, ensure all connections are secure, and verify you're using the correct materials and measurements as specified."
      },
      {
        question: "How do I explain the scientific principles behind my physics project?",
        answer: "Research the fundamental laws and theories that apply to your experiment. Use diagrams and real-world examples to help explain concepts like magnetism, electricity, or motion to your audience."
      }
    ],
    'chemistry': [
      {
        question: "What safety measures are essential for chemistry experiments?",
        answer: "Always work in a well-ventilated area, wear safety goggles and gloves, and have an adult present. Never mix chemicals unless instructed, and always add acid to water, never the reverse."
      },
      {
        question: "How do I properly measure chemicals for my experiment?",
        answer: "Use appropriate measuring tools like graduated cylinders or precise scales. Always measure at eye level and on a flat surface. Record exact amounts used for reproducibility."
      },
      {
        question: "What should I do if my chemical reaction doesn't work?",
        answer: "Check the freshness of your chemicals, verify measurements, and ensure proper temperature conditions. Some reactions require specific pH levels or catalysts to proceed."
      },
      {
        question: "How can I document chemical changes in my experiment?",
        answer: "Record color changes, temperature variations, gas production, precipitate formation, and any odors (safely). Photos and detailed observations help explain the chemical processes occurring."
      }
    ],
    'biology': [
      {
        question: "How do I keep biological specimens healthy during my experiment?",
        answer: "Maintain proper temperature, lighting, and nutrition for living specimens. Follow ethical guidelines for animal care and ensure proper hygiene when handling biological materials."
      },
      {
        question: "What's the best way to observe biological processes?",
        answer: "Use magnifying glasses or microscopes when available. Document changes over time with photos and detailed notes. Be patient as biological processes often take time to show results."
      },
      {
        question: "How do I control variables in biological experiments?",
        answer: "Use control groups, maintain consistent environmental conditions, and replicate your experiment with multiple specimens to ensure reliable results."
      },
      {
        question: "What ethical considerations should I keep in mind?",
        answer: "Treat all living organisms with respect, minimize harm, and follow local guidelines for working with biological materials. Consider the environmental impact of your experiment."
      }
    ],
    'earth-science': [
      {
        question: "How can I simulate Earth processes safely at home?",
        answer: "Use safe materials like baking soda and vinegar for volcanic eruptions, sand and water for erosion models, and household items for weather demonstrations. Always prioritize safety over dramatic effects."
      },
      {
        question: "What tools help me measure environmental conditions?",
        answer: "Simple thermometers, rain gauges, and pH strips can help you collect meaningful data. Many weather apps provide additional environmental data for comparison."
      },
      {
        question: "How do I collect and analyze geological samples?",
        answer: "Collect samples safely and legally, document their location and conditions, and use simple tests like hardness, color, and reaction to acids to identify rock and mineral properties."
      },
      {
        question: "Why are my Earth science results different from predictions?",
        answer: "Earth systems are complex and influenced by many variables. Weather, season, location, and scale can all affect results. Document these factors as part of your analysis."
      }
    ],
    'space': [
      {
        question: "How can I observe space phenomena without expensive equipment?",
        answer: "Many astronomical observations can be made with the naked eye or simple binoculars. Use astronomy apps to identify celestial objects and track their movements over time."
      },
      {
        question: "What's the best time and conditions for space observations?",
        answer: "Clear, dark skies away from city lights provide the best viewing conditions. Check weather forecasts and moon phases, as a new moon provides the darkest skies for deep space observation."
      },
      {
        question: "How do I document astronomical observations?",
        answer: "Record date, time, location, weather conditions, and what you observe. Draw sketches or take photos when possible. Star charts help identify and track celestial objects."
      },
      {
        question: "Can I simulate space conditions in my experiment?",
        answer: "While you can't recreate the vacuum of space, you can demonstrate concepts like gravity, orbital mechanics, and solar energy using simple models and household materials."
      }
    ],
    'engineering': [
      {
        question: "What's the engineering design process I should follow?",
        answer: "Define the problem, research solutions, design and plan, build a prototype, test and evaluate, then improve your design. Document each step for a complete engineering portfolio."
      },
      {
        question: "How do I choose the right materials for my engineering project?",
        answer: "Consider strength, weight, cost, and availability. Test different materials to see which works best for your specific application. Sometimes simple materials work better than expensive ones."
      },
      {
        question: "What should I do when my design doesn't work?",
        answer: "Failure is part of engineering! Analyze what went wrong, modify your design, and try again. Each iteration teaches you something valuable about the problem and potential solutions."
      },
      {
        question: "How do I test and improve my engineering solution?",
        answer: "Create specific tests that measure your design's performance. Change one variable at a time to see what improves results. Document all modifications and their effects."
      }
    ]
  },

  // Grade-level specific FAQs
  gradeFAQs: {
    'primary': [
      {
        question: "Is this experiment safe for young children?",
        answer: "All our primary level experiments use safe, non-toxic materials and are designed for children ages 5-9 with adult supervision. Always read safety instructions together before starting."
      },
      {
        question: "How can I help my child understand what's happening?",
        answer: "Ask lots of questions during the experiment: 'What do you see?' 'What do you think will happen next?' Connect the experiment to things they see in everyday life."
      },
      {
        question: "What if my child loses interest during the experiment?",
        answer: "Keep sessions short (15-30 minutes), let them lead when possible, and focus on the fun and discovery. It's okay to take breaks and come back to it later."
      },
      {
        question: "How do I encourage my child to think like a scientist?",
        answer: "Encourage them to make predictions, ask questions, and observe carefully. Celebrate their curiosity and help them connect their observations to the world around them."
      }
    ],
    'elementary': [
      {
        question: "How detailed should my science fair presentation be?",
        answer: "Focus on clear explanations of what you did, what you observed, and what you learned. Use pictures, charts, and simple graphs to show your results. Practice explaining your project in your own words."
      },
      {
        question: "Can I work with a partner on this project?",
        answer: "Check your science fair rules first. If partners are allowed, divide responsibilities clearly and make sure both students understand all aspects of the project."
      },
      {
        question: "How long should I spend on data collection?",
        answer: "Plan for at least a week of data collection to get reliable results. Some experiments may need longer. Start early so you have time to repeat trials if needed."
      },
      {
        question: "What makes a good hypothesis for my grade level?",
        answer: "A good hypothesis makes a specific prediction that you can test. Use 'if...then' statements: 'If I change X, then Y will happen because...'. Base your prediction on research or observations."
      }
    ],
    'intermediate': [
      {
        question: "How can I make my project stand out at the science fair?",
        answer: "Focus on original research questions, collect substantial data, use statistical analysis when appropriate, and draw meaningful conclusions. Consider real-world applications of your findings."
      },
      {
        question: "What level of complexity is appropriate for my grade?",
        answer: "Challenge yourself with multi-variable experiments, but ensure you can complete the project thoroughly. It's better to do a simpler project exceptionally well than a complex one poorly."
      },
      {
        question: "How do I handle unexpected results in my experiment?",
        answer: "Unexpected results often lead to the best discoveries! Analyze why results differed from your hypothesis, research possible explanations, and consider designing follow-up experiments."
      },
      {
        question: "What research sources should I use for background information?",
        answer: "Use scientific journals (simplified versions), educational websites, and books from your library. Always cite your sources and verify information with multiple reliable sources."
      }
    ],
    'senior': [
      {
        question: "How can I ensure my project meets high school science fair standards?",
        answer: "Follow proper experimental design with controls, statistical analysis, and peer review when possible. Your project should contribute new knowledge or test established theories in novel ways."
      },
      {
        question: "What statistical analysis should I include?",
        answer: "Use appropriate statistical tests for your data type. Calculate means, standard deviations, and use tests like t-tests or chi-square when applicable. Graph your data professionally."
      },
      {
        question: "How do I write a proper scientific abstract and report?",
        answer: "Follow standard scientific format: Abstract, Introduction, Methods, Results, Discussion, Conclusion, References. Write clearly and objectively, focusing on data and evidence-based conclusions."
      },
      {
        question: "Can this project lead to publication or competition opportunities?",
        answer: "High-quality projects can be submitted to regional and national science competitions. Consider connecting with local universities or research institutions for mentorship and collaboration opportunities."
      }
    ]
  },

  // Difficulty-based FAQs
  difficultyFAQs: {
    'easy': [
      {
        question: "How long will this experiment take to complete?",
        answer: "Most easy-level experiments can be completed in 1-2 hours, though some may require observation over several days. Plan ahead and allow extra time for setup and cleanup."
      },
      {
        question: "What if I don't have all the materials listed?",
        answer: "Many materials can be substituted with household items. Check our substitution guide or contact us for alternatives. The key is understanding the purpose of each material."
      }
    ],
    'medium': [
      {
        question: "What additional skills will I learn from this project?",
        answer: "Medium difficulty projects help you develop data collection, analysis, and presentation skills. You'll also learn to troubleshoot problems and think critically about results."
      },
      {
        question: "How can I extend this experiment for deeper learning?",
        answer: "Try changing variables, testing different materials, or scaling up/down your experiment. Research related phenomena and design follow-up experiments to test new questions."
      }
    ],
    'hard': [
      {
        question: "What preparation do I need before starting this advanced project?",
        answer: "Review all background material, ensure you have proper safety equipment, and consider practicing key techniques. Some projects may benefit from consulting with teachers or experts."
      },
      {
        question: "How do I troubleshoot complex experimental problems?",
        answer: "Approach problems systematically: check each component individually, verify all measurements and procedures, research similar experiments, and don't hesitate to start over if necessary."
      }
    ],
    'advanced': [
      {
        question: "What level of accuracy and precision should I aim for?",
        answer: "Advanced projects require high standards for data quality. Use calibrated instruments, multiple trials, proper controls, and statistical analysis to ensure reliable, publishable results."
      },
      {
        question: "How do I connect my project to current scientific research?",
        answer: "Review recent publications in your field, cite relevant studies in your background research, and consider how your project advances or tests current scientific understanding."
      }
    ]
  },

  // Materials-based FAQs
  materialsFAQs: {
    'household': [
      {
        question: "What household items can I substitute if I don't have the exact materials?",
        answer: "Common substitutions include using aluminum foil instead of wire, clear containers instead of beakers, and measuring cups instead of graduated cylinders. The key is understanding each material's purpose in the experiment."
      },
      {
        question: "How do I ensure household materials are clean and safe for experiments?",
        answer: "Wash all containers thoroughly, use food-grade materials when appropriate, and avoid containers that previously held cleaning products or chemicals. Always check expiration dates on food items."
      }
    ],
    'electrical': [
      {
        question: "What electrical safety precautions should I take?",
        answer: "Always have an adult present, use only low-voltage batteries (never household current), check connections before powering on, and keep electrical components away from water unless specifically designed for wet conditions."
      },
      {
        question: "What should I do if my electrical circuit isn't working?",
        answer: "Check all connections are secure, ensure batteries aren't dead, verify polarity (+ and -), and test each component individually. Use a multimeter if available to troubleshoot systematically."
      }
    ],
    'biological': [
      {
        question: "How do I handle biological materials safely?",
        answer: "Use gloves when handling specimens, maintain proper hygiene, wash hands thoroughly after experiments, and dispose of biological materials according to local guidelines. Keep living specimens in appropriate conditions."
      },
      {
        question: "What ethical considerations apply to biological experiments?",
        answer: "Treat all living organisms with respect, minimize harm, follow local guidelines for animal welfare, and consider the environmental impact. Never collect specimens from protected areas without permission."
      }
    ],
    'food': [
      {
        question: "Can I eat the food used in experiments?",
        answer: "Generally no - food used in experiments should not be consumed due to potential contamination. Use separate, fresh food items if you want to eat them after the experiment is complete."
      },
      {
        question: "How do I prevent food spoilage during longer experiments?",
        answer: "Refrigerate perishable items between observations, use preservatives when appropriate, take photos to document changes, and have backup food items ready in case of unexpected spoilage."
      }
    ],
    'craft': [
      {
        question: "What craft materials work best for science projects?",
        answer: "Choose materials based on your project needs: cardboard for structure, tape for connections, string for measurements, and modeling clay for flexibility. Consider durability and how materials interact with your experiment."
      },
      {
        question: "How can I make my craft-based project more scientific?",
        answer: "Focus on precise measurements, test different materials systematically, document all modifications, and ensure your construction doesn't interfere with the scientific principles you're demonstrating."
      }
    ],
    'water': [
      {
        question: "What type of water should I use for experiments?",
        answer: "Distilled water is best for most experiments as it doesn't contain minerals that might affect results. Tap water can be used for general demonstrations, but note that mineral content varies by location."
      },
      {
        question: "How do I measure and control water temperature accurately?",
        answer: "Use a thermometer for precise readings, allow time for temperature equilibration, consider room temperature effects, and measure temperature at multiple points in larger volumes for accuracy."
      }
    ]
  },

  // Grade-specific FAQs (more granular than category)
  specificGradeFAQs: {
    'K-4': [
      {
        question: "How can I make this experiment engaging for very young children?",
        answer: "Use colorful materials, short activity periods (10-15 minutes), lots of hands-on interaction, and connect to familiar experiences. Ask 'What do you think will happen?' and celebrate their observations."
      },
      {
        question: "What safety considerations are most important for young children?",
        answer: "Ensure constant adult supervision, use only non-toxic materials, avoid small parts that could be choking hazards, and choose experiments with no sharp edges or hot materials."
      }
    ],
    '4-6': [
      {
        question: "How detailed should my experimental records be at this level?",
        answer: "Keep simple but complete records with drawings, basic measurements, and observations in your own words. Focus on what you see, hear, smell (safely), and what changes occur."
      },
      {
        question: "Can I work independently on this project?",
        answer: "Many parts can be done independently, but always have an adult check your setup and be available for questions. It's okay to ask for help - that's part of learning!"
      }
    ],
    '7-8': [
      {
        question: "How can I make my project competitive for science fairs?",
        answer: "Focus on clear research questions, collect quantitative data when possible, use proper controls, and practice explaining your project clearly. Consider real-world applications and future investigations."
      },
      {
        question: "What level of background research is expected?",
        answer: "Research the scientific principles behind your experiment, cite 3-5 reliable sources, understand key vocabulary, and be able to explain why your experiment works the way it does."
      }
    ],
    '9-12': [
      {
        question: "How can I ensure my project meets college-level standards?",
        answer: "Use proper experimental design with statistical analysis, follow scientific writing conventions, include peer review when possible, and consider submitting to science competitions or journals."
      },
      {
        question: "How do I connect my project to career opportunities?",
        answer: "Research related careers, connect with professionals in the field, consider internship opportunities, and think about how your project relates to current industry needs and challenges."
      }
    ]
  },

  // General FAQs that apply to all projects
  generalFAQs: [
    {
      question: "How do I document my results for a science fair?",
      answer: "Keep detailed notes throughout your experiment, including photos, measurements, observations, and any unexpected results. Create a hypothesis before starting and compare your results to your predictions."
    },
    {
      question: "Can I modify this experiment?",
      answer: "Absolutely! Science is about exploration. Try changing variables, testing different materials, or expanding the scope of your investigation. Just remember to maintain safety protocols."
    },
    {
      question: "What should I do if my experiment doesn't work as expected?",
      answer: "Don't worry! Failed experiments teach us just as much as successful ones. Analyze what went wrong, research possible causes, and try modifying your approach. Document everything for learning."
    },
    {
      question: "Where can I get help if I'm stuck?",
      answer: "Ask teachers, parents, or librarians for guidance. Many science museums and universities offer student support. Online science communities and forums can also provide helpful advice from experienced experimenters."
    }
  ]
};

// Enhanced function to get 4 most relevant FAQs for a project using all available tags
function getProjectFAQs(project, category = null) {
  const faqs = [];
  const maxFAQs = 4;
  
  // Safely get properties with fallbacks
  const safeProject = {
    subject: project?.subject || null,
    category: category || project?.category || null,
    difficulty: project?.difficulty || null,
    materials: project?.materials || null,
    grade: project?.grade || null,
    title: project?.title || null
  };
  
  // Priority 1: Subject-specific FAQs (up to 2 FAQs)
  if (safeProject.subject && projectFAQCategories.subjectFAQs[safeProject.subject]) {
    const subjectFAQs = projectFAQCategories.subjectFAQs[safeProject.subject];
    faqs.push(...subjectFAQs.slice(0, Math.min(2, subjectFAQs.length)));
  }
  
  // Priority 2: Materials-specific FAQs (up to 1 FAQ if space available)
  if (faqs.length < maxFAQs && safeProject.materials && projectFAQCategories.materialsFAQs[safeProject.materials]) {
    const materialsFAQs = projectFAQCategories.materialsFAQs[safeProject.materials];
    faqs.push(...materialsFAQs.slice(0, Math.min(1, materialsFAQs.length, maxFAQs - faqs.length)));
  }
  
  // Priority 3: Specific grade FAQs (higher priority than general category)
  if (faqs.length < maxFAQs && safeProject.grade && projectFAQCategories.specificGradeFAQs[safeProject.grade]) {
    const gradeFAQs = projectFAQCategories.specificGradeFAQs[safeProject.grade];
    faqs.push(...gradeFAQs.slice(0, Math.min(1, gradeFAQs.length, maxFAQs - faqs.length)));
  }
  
  // Priority 4: General category FAQs (fallback if no specific grade FAQs)
  if (faqs.length < maxFAQs && safeProject.category && projectFAQCategories.gradeFAQs[safeProject.category]) {
    const categoryFAQs = projectFAQCategories.gradeFAQs[safeProject.category];
    faqs.push(...categoryFAQs.slice(0, Math.min(1, categoryFAQs.length, maxFAQs - faqs.length)));
  }
  
  // Priority 5: Difficulty-based FAQs
  if (faqs.length < maxFAQs && safeProject.difficulty && projectFAQCategories.difficultyFAQs[safeProject.difficulty]) {
    const difficultyFAQs = projectFAQCategories.difficultyFAQs[safeProject.difficulty];
    faqs.push(...difficultyFAQs.slice(0, Math.min(1, difficultyFAQs.length, maxFAQs - faqs.length)));
  }
  
  // Additional logic for special combinations
  // Beginner-friendly projects (easy difficulty)
  if (faqs.length < maxFAQs && safeProject.difficulty === 'easy') {
    const beginnerFAQ = {
      question: "Is this a good first science project?",
      answer: "Yes! This project is designed to be beginner-friendly with simple materials and clear instructions. It's perfect for building confidence in scientific experimentation."
    };
    // Only add if not already present
    if (!faqs.find(faq => faq.question === beginnerFAQ.question)) {
      faqs.push(beginnerFAQ);
    }
  }
  
  // Household materials bonus FAQ
  if (faqs.length < maxFAQs && ['household', 'food', 'water'].includes(safeProject.materials)) {
    const householdFAQ = {
      question: "Can I do this experiment safely at home?",
      answer: "Absolutely! This experiment uses common household materials and is designed for home use. Always follow safety guidelines and have adult supervision when recommended."
    };
    if (!faqs.find(faq => faq.question === householdFAQ.question)) {
      faqs.push(householdFAQ);
    }
  }
  
  // Fill remaining slots with general FAQs if needed
  while (faqs.length < maxFAQs && projectFAQCategories.generalFAQs) {
    const generalFAQIndex = faqs.length % projectFAQCategories.generalFAQs.length;
    const generalFAQ = projectFAQCategories.generalFAQs[generalFAQIndex];
    
    // Avoid duplicates
    if (generalFAQ && !faqs.find(faq => faq.question === generalFAQ.question)) {
      faqs.push(generalFAQ);
    } else {
      break; // Prevent infinite loop if all general FAQs are already included
    }
  }
  
  return faqs.slice(0, maxFAQs); // Ensure exactly maxFAQs or less
}

// Enhanced function to generate FAQ HTML with error handling
function generateProjectFAQHTML(project, category = null) {
  try {
    const faqs = getProjectFAQs(project, category);
    
    if (!faqs || faqs.length === 0) {
      return `
        <section class="project-faq">
          <h2>Frequently Asked Questions</h2>
          <div class="faq-item">
            <button class="faq-question" onclick="toggleFAQ(this)" tabindex="0" role="button" aria-expanded="false">
              How can I get help with this project?
            </button>
            <div class="faq-answer">
              <p>For assistance with this project, please consult your teacher, parent, or visit our general help section. We're here to support your scientific learning journey!</p>
            </div>
          </div>
        </section>
      `;
    }
    
    const faqItems = faqs.map((faq, index) => {
      // Safely handle FAQ properties
      const question = faq?.question || `Question ${index + 1}`;
      const answer = faq?.answer || 'Please refer to the project instructions for details.';
      
      return `
        <div class="faq-item">
          <button class="faq-question" onclick="toggleFAQ(this)" tabindex="0" role="button" aria-expanded="false">
            ${question}
          </button>
          <div class="faq-answer">
            <p>${answer}</p>
          </div>
        </div>
      `;
    }).join('');
    
    return `
      <section class="project-faq">
        <h2>Frequently Asked Questions</h2>
        <div class="faq-intro">
          <p>Here are some common questions about this ${project?.subject || 'science'} project for ${project?.grade || 'students'}:</p>
        </div>
        ${faqItems}
      </section>
    `;
  } catch (error) {
    console.warn('Error generating FAQ HTML:', error);
    return `
      <section class="project-faq">
        <h2>Frequently Asked Questions</h2>
        <div class="faq-item">
          <button class="faq-question" onclick="toggleFAQ(this)" tabindex="0" role="button" aria-expanded="false">
            How can I get help with this project?
          </button>
          <div class="faq-answer">
            <p>For assistance with this project, please consult your teacher, parent, or visit our general help section. We're here to support your scientific learning journey!</p>
          </div>
        </div>
      </section>
    `;
  }
}

// Enhanced function to add FAQ Schema.org structured data with error handling
function addFAQSchema(faqs) {
  try {
    if (!faqs || faqs.length === 0) {
      return; // Don't add schema if no FAQs
    }
    
    const faqSchema = {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": faqs.filter(faq => faq?.question && faq?.answer).map(faq => ({
        "@type": "Question",
        "name": faq.question,
        "acceptedAnswer": {
          "@type": "Answer",
          "text": faq.answer
        }
      }))
    };
    
    // Only add schema if we have valid FAQs
    if (faqSchema.mainEntity.length > 0) {
      const script = document.createElement('script');
      script.type = 'application/ld+json';
      script.textContent = JSON.stringify(faqSchema, null, 2);
      document.head.appendChild(script);
    }
  } catch (error) {
    console.warn('Error adding FAQ Schema.org data:', error);
  }
}

// Enhanced function that generates FAQs and adds Schema.org data with comprehensive error handling
function initializeProjectFAQs(project, category = null) {
  try {
    // Validate input
    if (!project || typeof project !== 'object') {
      console.warn('Invalid project data provided to initializeProjectFAQs');
      return generateFallbackFAQHTML();
    }
    
    const faqs = getProjectFAQs(project, category);
    
    // Add Schema.org structured data (only if we have valid FAQs)
    if (faqs && faqs.length > 0) {
      addFAQSchema(faqs);
    }
    
    // Generate and return HTML
    return generateProjectFAQHTML(project, category);
  } catch (error) {
    console.error('Error in initializeProjectFAQs:', error);
    return generateFallbackFAQHTML();
  }
}

// Fallback function for when there are errors or missing data
function generateFallbackFAQHTML() {
  return `
    <section class="project-faq">
      <h2>Frequently Asked Questions</h2>
      <div class="faq-item">
        <button class="faq-question" onclick="toggleFAQ(this)" tabindex="0" role="button" aria-expanded="false">
          How can I get help with this project?
        </button>
        <div class="faq-answer">
          <p>For assistance with this project, please consult your teacher, parent, or visit our general help section. We're here to support your scientific learning journey!</p>
        </div>
      </div>
      <div class="faq-item">
        <button class="faq-question" onclick="toggleFAQ(this)" tabindex="0" role="button" aria-expanded="false">
          What if my experiment doesn't work as expected?
        </button>
        <div class="faq-answer">
          <p>Don't worry! Failed experiments teach us just as much as successful ones. Analyze what went wrong, research possible causes, and try modifying your approach. Document everything for learning.</p>
        </div>
      </div>
      <div class="faq-item">
        <button class="faq-question" onclick="toggleFAQ(this)" tabindex="0" role="button" aria-expanded="false">
          Can I modify this experiment?
        </button>
        <div class="faq-answer">
          <p>Absolutely! Science is about exploration. Try changing variables, testing different materials, or expanding the scope of your investigation. Just remember to maintain safety protocols.</p>
        </div>
      </div>
      <div class="faq-item">
        <button class="faq-question" onclick="toggleFAQ(this)" tabindex="0" role="button" aria-expanded="false">
          How do I document my results for a science fair?
        </button>
        <div class="faq-answer">
          <p>Keep detailed notes throughout your experiment, including photos, measurements, observations, and any unexpected results. Create a hypothesis before starting and compare your results to your predictions.</p>
        </div>
      </div>
    </section>
  `;
}