// animations.js - Shared animation functionality for hearing aid stock management system

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the animated background
    initAnimatedBackground();
    
    // Initialize animation classes
    initAnimations();
    
    // Initialize interactive elements
    initInteractiveElements();
});

/**
 * Creates and initializes the animated background with particles and waves
 */
function initAnimatedBackground() {
    // Check if animated background container exists
    const bgContainer = document.querySelector('.animated-bg');
    if (!bgContainer) {
        // Create animated background if it doesn't exist
        const animatedBg = document.createElement('div');
        animatedBg.className = 'animated-bg';
        document.body.prepend(animatedBg);
        
        // Add ambient light effects
        for (let i = 1; i <= 3; i++) {
            const light = document.createElement('div');
            light.className = `light light-${i}`;
            animatedBg.appendChild(light);
        }
        
        // Add wave effects
        for (let i = 1; i <= 3; i++) {
            const wave = document.createElement('div');
            wave.className = 'wave';
            animatedBg.appendChild(wave);
        }
        
        // Create floating particles
        for (let i = 0; i < 20; i++) {
            createParticle(animatedBg);
        }
    }
}

/**
 * Creates a single particle element with random properties
 * @param {HTMLElement} container - The container to add the particle to
 */
function createParticle(container) {
    const size = Math.random() * 15 + 5;
    const particle = document.createElement('div');
    particle.classList.add('particle');
    
    // Random positions and animations
    const startPositionX = Math.random() * 100;
    const startPositionY = Math.random() * 100 + 50;
    const animationDuration = Math.random() * 15 + 10;
    const animationDelay = Math.random() * 5;
    
    particle.style.width = size + 'px';
    particle.style.height = size + 'px';
    particle.style.left = startPositionX + 'vw';
    particle.style.top = startPositionY + 'vh';
    particle.style.animationDuration = animationDuration + 's';
    particle.style.animationDelay = animationDelay + 's';
    particle.style.opacity = Math.random() * 0.5 + 0.1;
    
    // Randomize colors
    const hue = Math.floor(Math.random() * 60) + 200; // Blues and purples
    particle.style.background = `hsla(${hue}, 70%, 60%, 0.2)`;
    
    container.appendChild(particle);
}

/**
 * Initializes elements with animation classes
 */
function initAnimations() {
    // Add animation classes to elements that should be animated on load
    animateHeaders();
    animateCards();
    animateButtons();
    animateIcons();
    animateFormElements();
}

/**
 * Animates header elements with staggered timing
 */
function animateHeaders() {
    const headers = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
    headers.forEach((header, index) => {
        if (!header.classList.contains('no-animation')) {
            header.classList.add('fade-in-up');
            header.style.animationDelay = (0.1 + (index * 0.1)) + 's';
        }
    });
}

/**
 * Animates card elements with staggered timing
 */
function animateCards() {
    const cards = document.querySelectorAll('.card, .stat-card');
    cards.forEach((card, index) => {
        if (!card.classList.contains('no-animation')) {
            card.classList.add('animated-card');
            card.classList.add('fade-in-up');
            card.style.animationDelay = (0.2 + (index * 0.1)) + 's';
        }
    });
}

/**
 * Applies animation classes to button elements
 */
function animateButtons() {
    const buttons = document.querySelectorAll('.btn, button:not(.no-animation)');
    buttons.forEach(button => {
        if (!button.classList.contains('no-animation')) {
            button.classList.add('animated-btn');
            
            // Add ripple effect to buttons
            button.addEventListener('click', function(e) {
                // Create ripple effect
                const ripple = document.createElement('span');
                ripple.classList.add('ripple');
                this.appendChild(ripple);
                
                // Position the ripple
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = `${size}px`;
                ripple.style.left = `${x}px`;
                ripple.style.top = `${y}px`;
                
                // Remove after animation completes
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        }
    });
}

/**
 * Applies animation classes to icon elements
 */
function animateIcons() {
    const icons = document.querySelectorAll('.fa, .fas, .far, .fab');
    icons.forEach(icon => {
        const parent = icon.parentElement;
        if (!parent.classList.contains('btn') && !parent.classList.contains('no-animation') && !icon.closest('.no-animation')) {
            icon.classList.add('animated-icon');
        }
    });
}

/**
 * Applies animation classes to form elements
 */
function animateFormElements() {
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        if (!input.classList.contains('no-animation')) {
            input.classList.add('animated-input');
        }
    });
}

/**
 * Initializes interactive elements like tooltips and tables
 */
function initInteractiveElements() {
    initTooltips();
    initTableRows();
    initTablesLoading();
    setupFormValidation();
}

/**
 * Initializes tooltip functionality
 */
function initTooltips() {
    // Find elements with data-tooltip attribute
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.classList.add('tooltip');
    });
}

/**
 * Adds animation classes to table rows
 */
function initTableRows() {
    const tableRows = document.querySelectorAll('tr:not(.table-header):not(.no-animation)');
    tableRows.forEach(row => {
        row.classList.add('animated-row');
    });
}

/**
 * Sets up loading animations for tables
 */
function initTablesLoading() {
    // Add shimmer effect to table cells with loading class
    const loadingCells = document.querySelectorAll('td.loading, th.loading');
    loadingCells.forEach(cell => {
        cell.classList.add('shimmer');
    });
}

/**
 * Sets up form validation with animations
 */
function setupFormValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        // Add shake animation to invalid fields on submit
        form.addEventListener('submit', function(e) {
            const invalidFields = form.querySelectorAll(':invalid');
            if (invalidFields.length > 0) {
                invalidFields.forEach(field => {
                    field.classList.add('shake');
                    setTimeout(() => {
                        field.classList.remove('shake');
                    }, 500);
                });
            }
        });
    });
}

/**
 * Shows a toast notification
 * @param {string} message - The message to display
 * @param {string} type - The type of toast ('success', 'error', 'warning')
 * @param {number} duration - How long to show the toast in ms
 */
function showToast(message, type = 'success', duration = 3000) {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => {
        toast.remove();
    });
    
    // Create new toast
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Remove after duration
    setTimeout(() => {
        toast.style.animation = 'fadeInLeft 0.5s var(--animation-timing) reverse forwards';
        setTimeout(() => {
            toast.remove();
        }, 500);
    }, duration);
    
    return toast;
}

// Make toast function globally available
window.showToast = showToast;

/**
 * Shows a loading spinner in the target element
 * @param {HTMLElement|string} target - The element or selector to show the spinner in
 * @param {string} size - Size of the spinner ('sm', 'md', 'lg')
 * @returns {HTMLElement} - The spinner element
 */
function showSpinner(target, size = 'md') {
    const targetElement = typeof target === 'string' ? document.querySelector(target) : target;
    if (!targetElement) return null;
    
    // Create spinner
    const spinner = document.createElement('div');
    spinner.className = `spinner spinner-${size}`;
    
    // Clear target and add spinner
    targetElement.innerHTML = '';
    targetElement.appendChild(spinner);
    
    return spinner;
}

// Make spinner function globally available
window.showSpinner = showSpinner;

/**
 * Adds staggered animation to a collection of elements
 * @param {string} selector - CSS selector for the elements
 * @param {string} animation - Animation class to add ('fade-in-up', 'fade-in-left', etc.)
 * @param {number} delay - Base delay in seconds
 * @param {number} increment - Delay increment per element in seconds
 */
function staggerAnimation(selector, animation = 'fade-in-up', delay = 0.1, increment = 0.1) {
    const elements = document.querySelectorAll(selector);
    elements.forEach((element, index) => {
        element.classList.add(animation);
        element.style.animationDelay = (delay + (index * increment)) + 's';
    });
}

// Make stagger function globally available
window.staggerAnimation = staggerAnimation; 