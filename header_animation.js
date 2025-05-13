/**
 * header_animation.js - Animations for the header section across all pages
 * For the hearing aid stock management system
 */

document.addEventListener('DOMContentLoaded', function() {
    // Add animation classes to header elements
    enhanceHeader();
    
    // Add interactive effects and animations
    addInteractiveEffects();
    
    // Add color theme to the header based on our new color scheme
    applyHeaderColorTheme();
});

/**
 * Enhance the header with animation classes
 */
function enhanceHeader() {
    // Get header elements
    const header = document.querySelector('.header');
    const logo = document.querySelector('.logo');
    const logoIcon = logo?.querySelector('i');
    const logoText = logo?.querySelector('span');
    const userInfo = document.querySelector('.user-info');
    const userButtons = userInfo?.querySelectorAll('.btn');
    const userName = userInfo?.querySelector('.user-name');
    
    // If header exists, add animations
    if (header) {
        // Add a subtle gradient animation to the header background
        header.style.background = 'linear-gradient(90deg, #4e54c8, #363795)';
        header.style.animation = 'gradientShift 10s ease infinite';
        
        // Add background particle effect to the header
        const headerParticles = document.createElement('div');
        headerParticles.className = 'header-particles';
        headerParticles.style.position = 'absolute';
        headerParticles.style.top = '0';
        headerParticles.style.left = '0';
        headerParticles.style.width = '100%';
        headerParticles.style.height = '100%';
        headerParticles.style.overflow = 'hidden';
        headerParticles.style.zIndex = '0';
        header.style.position = 'relative';
        header.insertBefore(headerParticles, header.firstChild);
        
        // Create floating particles
        for (let i = 0; i < 10; i++) {
            createHeaderParticle(headerParticles);
        }
    }
    
    // Animate logo
    if (logo) {
        logo.style.zIndex = '1';
        logo.style.position = 'relative';
    }
    
    if (logoIcon) {
        logoIcon.style.color = '#00d2ff'; // Secondary color
        logoIcon.style.textShadow = '0 0 10px rgba(0, 210, 255, 0.5)';
        logoIcon.style.transition = 'all 0.3s ease';
        logoIcon.classList.add('animated-icon');
        
        // Add pulse animation to logo icon
        const keyframes = `
            @keyframes pulseIcon {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
        `;
        
        const style = document.createElement('style');
        style.innerHTML = keyframes;
        document.head.appendChild(style);
        
        logoIcon.style.animation = 'pulseIcon 2s ease-in-out infinite';
    }
    
    if (logoText) {
        logoText.style.position = 'relative';
        logoText.style.color = 'white';
        logoText.style.fontWeight = 'bold';
        logoText.style.zIndex = '1';
    }
    
    // Animate user info section
    if (userInfo) {
        userInfo.style.zIndex = '1';
        userInfo.style.position = 'relative';
    }
    
    if (userName) {
        userName.style.color = 'rgba(255, 255, 255, 0.9)';
        userName.style.textShadow = '0 0 10px rgba(0, 0, 0, 0.2)';
    }
    
    // Add hover effects to buttons
    if (userButtons) {
        userButtons.forEach(button => {
            button.classList.add('animated-btn');
            button.style.transition = 'all 0.3s ease';
            button.style.transform = 'translateY(0)';
            
            button.addEventListener('mouseover', function() {
                this.style.transform = 'translateY(-3px)';
                this.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.2)';
            });
            
            button.addEventListener('mouseout', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '';
            });
        });
    }
}

/**
 * Create a floating particle in the header
 */
function createHeaderParticle(parent) {
    const size = Math.random() * 6 + 2;
    const particle = document.createElement('div');
    
    // Styling the particle
    particle.style.position = 'absolute';
    particle.style.width = `${size}px`;
    particle.style.height = `${size}px`;
    particle.style.borderRadius = '50%';
    particle.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
    particle.style.boxShadow = '0 0 10px rgba(255, 255, 255, 0.1)';
    
    // Random position
    particle.style.left = `${Math.random() * 100}%`;
    particle.style.top = `${Math.random() * 100}%`;
    
    // Animation properties
    const duration = Math.random() * 10 + 10;
    const xMove = (Math.random() * 40) - 20;
    const yMove = (Math.random() * 20) - 10;
    
    // Define animation
    particle.style.transition = `transform ${duration}s linear, opacity ${duration}s linear`;
    particle.style.opacity = '0';
    
    // Add to parent and start animation
    parent.appendChild(particle);
    
    // Start animation after a small delay
    setTimeout(() => {
        particle.style.opacity = '0.7';
        particle.style.transform = `translate(${xMove}vw, ${yMove}vh)`;
        
        // Remove and recreate particle after animation completes
        setTimeout(() => {
            particle.remove();
            createHeaderParticle(parent);
        }, duration * 1000);
    }, 100);
}

/**
 * Add interactive effects to header elements
 */
function addInteractiveEffects() {
    // Add interactive effects to logo
    const logo = document.querySelector('.logo');
    
    if (logo) {
        logo.style.cursor = 'pointer';
        
        logo.addEventListener('click', function() {
            // Navigate to home/product list when logo is clicked
            window.location.href = 'product_list.php';
        });
        
        // Add hover effect
        logo.addEventListener('mouseover', function() {
            const logoIcon = this.querySelector('i');
            if (logoIcon) {
                logoIcon.style.transform = 'scale(1.2) rotate(5deg)';
                logoIcon.style.color = '#60e6ff'; // Lighter cyan
            }
        });
        
        logo.addEventListener('mouseout', function() {
            const logoIcon = this.querySelector('i');
            if (logoIcon) {
                logoIcon.style.transform = '';
                logoIcon.style.color = '#00d2ff'; // Back to secondary color
            }
        });
    }
}

/**
 * Apply the new color theme to header elements
 */
function applyHeaderColorTheme() {
    // Style header buttons with our new color scheme
    const primaryBtns = document.querySelectorAll('.btn-primary');
    const dangerBtns = document.querySelectorAll('.btn-danger');
    
    primaryBtns.forEach(btn => {
        btn.style.background = 'linear-gradient(90deg, #00d2ff, #00a8e8)';
        btn.style.border = 'none';
        btn.style.boxShadow = '0 4px 10px rgba(0, 210, 255, 0.3)';
    });
    
    dangerBtns.forEach(btn => {
        btn.style.background = 'linear-gradient(90deg, #ff6b6b, #e74c3c)';
        btn.style.border = 'none';
        btn.style.boxShadow = '0 4px 10px rgba(255, 107, 107, 0.3)';
    });
    
    // Add keyframes for gradient shift animation
    const keyframes = `
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    `;
    
    const style = document.createElement('style');
    style.innerHTML = keyframes;
    document.head.appendChild(style);
    
    // Apply to header
    const header = document.querySelector('.header');
    if (header) {
        header.style.backgroundSize = '200% 200%';
    }
} 