// Handle PHP link redirects
document.addEventListener('DOMContentLoaded', function() {
    // Find all links that point to PHP files
    const phpLinks = document.querySelectorAll('a[href$=".php"]');
    
    phpLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show alert and redirect to purchase page
            alert('This feature is available in the full version. Contact the developer to purchase the complete system.');
            window.location.href = 'purchase.html';
        });
    });
    
    // Handle form submissions
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        // Check if form action points to PHP file
        const action = form.getAttribute('action');
        if (action && action.endsWith('.php')) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                alert('This feature is available in the full version. Contact the developer to purchase the complete system.');
                window.location.href = 'purchase.html';
            });
        }
    });
});

// Add developer watermark
function addDeveloperWatermark() {
    const watermark = document.createElement('div');
    watermark.innerHTML = 'Developed by Vivek P S | viveksubhash4@gmail.com';
    watermark.style.cssText = `
        position: fixed;
        bottom: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.8);
        color: white;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
        z-index: 9999;
        font-family: Arial, sans-serif;
    `;
    document.body.appendChild(watermark);
}

// Add watermark when page loads
window.addEventListener('load', addDeveloperWatermark);