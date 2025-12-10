// Modern Gym Website JavaScript

// Initialize AOS (Animate On Scroll)
AOS.init({
    duration: 1000,
    easing: 'ease-in-out',
    once: true,
    mirror: false
});

// Navbar functionality
class Navbar {
    constructor() {
        this.navbar = document.getElementById('navbar');
        this.hamburger = document.getElementById('hamburger');
        this.navMenu = document.getElementById('nav-menu');
        this.navLinks = document.querySelectorAll('.nav-link');
        
        this.init();
    }

    init() {
        // Scroll effect
        window.addEventListener('scroll', () => this.handleScroll());
        
        // Mobile menu toggle
        this.hamburger.addEventListener('click', () => this.toggleMobileMenu());
        
        // Close mobile menu when clicking on links
        this.navLinks.forEach(link => {
            link.addEventListener('click', () => this.closeMobileMenu());
        });

        // Smooth scrolling for navigation links
        this.navLinks.forEach(link => {
            link.addEventListener('click', (e) => this.smoothScroll(e));
        });
    }

    handleScroll() {
        if (window.scrollY > 100) {
            this.navbar.classList.add('scrolled');
        } else {
            this.navbar.classList.remove('scrolled');
        }
    }

    toggleMobileMenu() {
        this.hamburger.classList.toggle('active');
        this.navMenu.classList.toggle('active');
        document.body.style.overflow = this.navMenu.classList.contains('active') ? 'hidden' : '';
    }

    closeMobileMenu() {
        this.hamburger.classList.remove('active');
        this.navMenu.classList.remove('active');
        document.body.style.overflow = '';
    }

    smoothScroll(e) {
        const href = e.target.getAttribute('href');
        
        if (href.startsWith('#')) {
            e.preventDefault();
            const target = document.querySelector(href);
            
            if (target) {
                const offsetTop = target.offsetTop - 80; // Account for fixed navbar
                
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        }
    }
}

// Hero section animations
class HeroAnimations {
    constructor() {
        this.heroText = document.querySelector('.hero-text');
        this.heroStats = document.querySelector('.hero-stats');
        
        this.init();
    }

    init() {
        // Animate hero content on load
        setTimeout(() => {
            if (this.heroText) {
                this.heroText.style.opacity = '1';
                this.heroText.style.transform = 'translateY(0)';
            }
        }, 500);

        setTimeout(() => {
            if (this.heroStats) {
                this.heroStats.style.opacity = '1';
                this.heroStats.style.transform = 'translateY(0)';
            }
        }, 800);

        // Parallax effect for hero background
        window.addEventListener('scroll', () => this.parallaxEffect());
    }

    parallaxEffect() {
        const scrolled = window.pageYOffset;
        const hero = document.querySelector('.hero');
        
        if (hero && scrolled < hero.offsetHeight) {
            const rate = scrolled * -0.5;
            hero.style.transform = `translateY(${rate}px)`;
        }
    }
}

// Counter animation for statistics
class CounterAnimation {
    constructor() {
        this.counters = document.querySelectorAll('.stat-item h3');
        this.animated = false;
        
        this.init();
    }

    init() {
        window.addEventListener('scroll', () => this.checkCounters());
    }

    checkCounters() {
        if (this.animated) return;

        const heroStats = document.querySelector('.hero-stats');
        if (!heroStats) return;

        const rect = heroStats.getBoundingClientRect();
        const isVisible = rect.top < window.innerHeight && rect.bottom > 0;

        if (isVisible) {
            this.animated = true;
            this.animateCounters();
        }
    }

    animateCounters() {
        this.counters.forEach(counter => {
            const target = parseInt(counter.textContent.replace(/\D/g, ''));
            const suffix = counter.textContent.replace(/\d/g, '');
            let current = 0;
            const increment = target / 60;
            
            counter.textContent = '0' + suffix;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                counter.textContent = Math.floor(current) + suffix;
            }, 50);
        });
    }
}

// Service cards hover effects
class ServiceCards {
    constructor() {
        this.cards = document.querySelectorAll('.service-card');
        this.init();
    }

    init() {
        this.cards.forEach(card => {
            card.addEventListener('mouseenter', () => this.handleHover(card));
            card.addEventListener('mouseleave', () => this.handleLeave(card));
        });
    }

    handleHover(card) {
        const icon = card.querySelector('.service-icon');
        if (icon) {
            icon.style.transform = 'scale(1.1) rotate(5deg)';
        }
    }

    handleLeave(card) {
        const icon = card.querySelector('.service-icon');
        if (icon) {
            icon.style.transform = 'scale(1) rotate(0deg)';
        }
    }
}

// Pricing cards interactions
class PricingCards {
    constructor() {
        this.cards = document.querySelectorAll('.pricing-card');
        this.init();
    }

    init() {
        this.cards.forEach(card => {
            card.addEventListener('mouseenter', () => this.handleHover(card));
            card.addEventListener('mouseleave', () => this.handleLeave(card));
        });
    }

    handleHover(card) {
        if (!card.classList.contains('featured')) {
            card.style.transform = 'translateY(-15px) scale(1.02)';
        }
    }

    handleLeave(card) {
        if (!card.classList.contains('featured')) {
            card.style.transform = 'translateY(0) scale(1)';
        }
    }
}

// Contact form handling
class ContactForm {
    constructor() {
        this.form = document.querySelector('.contact-form form');
        this.init();
    }

    init() {
        if (this.form) {
            this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    handleSubmit(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(this.form);
        const data = Object.fromEntries(formData);
        
        // Simple validation
        if (this.validateForm(data)) {
            this.showMessage('Thank you for your message! We\'ll get back to you soon.', 'success');
            this.form.reset();
        } else {
            this.showMessage('Please fill in all required fields.', 'error');
        }
    }

    validateForm(data) {
        const required = ['name', 'email', 'phone', 'service', 'message'];
        return required.every(field => data[field] && data[field].trim() !== '');
    }

    showMessage(message, type) {
        // Remove existing messages
        const existingMessage = document.querySelector('.form-message');
        if (existingMessage) {
            existingMessage.remove();
        }

        // Create new message
        const messageDiv = document.createElement('div');
        messageDiv.className = `form-message ${type}`;
        messageDiv.textContent = message;
        messageDiv.style.cssText = `
            padding: 1rem;
            margin-top: 1rem;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
            ${type === 'success' 
                ? 'background: #d4edda; color: #155724; border: 1px solid #c3e6cb;'
                : 'background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'
            }
        `;

        this.form.appendChild(messageDiv);

        // Remove message after 5 seconds
        setTimeout(() => {
            messageDiv.remove();
        }, 5000);
    }
}

// Scroll animations for elements
class ScrollAnimations {
    constructor() {
        this.elements = document.querySelectorAll('.fade-in');
        this.init();
    }

    init() {
        // Initial check
        this.checkElements();
        
        // Check on scroll
        window.addEventListener('scroll', () => this.checkElements());
    }

    checkElements() {
        this.elements.forEach(element => {
            const rect = element.getBoundingClientRect();
            const isVisible = rect.top < window.innerHeight * 0.8;

            if (isVisible) {
                element.classList.add('visible');
            }
        });
    }
}

// Smooth page loading
class PageLoader {
    constructor() {
        this.init();
    }

    init() {
        // Add loading class to body
        document.body.classList.add('loading');

        // Remove loading class when page is loaded
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.body.classList.remove('loading');
                document.body.classList.add('loaded');
            }, 500);
        });
    }
}

// Utility functions
const utils = {
    // Debounce function for performance
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Throttle function for scroll events
    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    // Check if element is in viewport
    isInViewport(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
};

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize all components
    new PageLoader();
    new Navbar();
    new HeroAnimations();
    new CounterAnimation();
    new ServiceCards();
    new PricingCards();
    new ContactForm();
    new ScrollAnimations();

    // Add some interactive effects
    addInteractiveEffects();
});

// Additional interactive effects
function addInteractiveEffects() {
    // Add hover effect to buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Add click ripple effect
    const rippleButtons = document.querySelectorAll('.btn-primary');
    rippleButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // Add CSS for ripple animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        body.loading {
            overflow: hidden;
        }
        
        body.loading::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--white);
            z-index: 9999;
            opacity: 1;
            transition: opacity 0.5s ease;
        }
        
        body.loaded::before {
            opacity: 0;
            pointer-events: none;
        }
    `;
    document.head.appendChild(style);
}

// Performance optimization
window.addEventListener('scroll', utils.throttle(() => {
    // Throttled scroll events go here
}, 16)); // ~60fps