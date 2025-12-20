        </main>

        <!-- Safety notice for all projects -->
        <section class="safety-notice">
            <h4>Safety First</h4>
            <p>Always have an adult help you with science experiments. Read all instructions carefully before starting, and make sure you have all the materials you need. Have fun and stay safe!</p>
        </section>
    </div>
</div>

<!-- Individual Project Page JavaScript - Makes Related Supplies fully clickable -->
<script src="/js/individual-project-page.js?v=<?php echo time(); ?>"></script>

<!-- Project-specific JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Track project interactions
    if (typeof gtag !== 'undefined') {
        gtag('event', 'project_view', {
            project_name: '<?php echo addslashes($project_title ?? "Unknown Project"); ?>',
            grade_level: '<?php echo addslashes($project_grade_level ?? "General"); ?>',
            subject: '<?php echo addslashes($project_subject ?? "General Science"); ?>'
        });
    }
    
    // Add smooth scrolling for project navigation
    document.querySelectorAll('.project-nav-links a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});
</script>

<?php include_once __DIR__ . '/footer.php'; ?>