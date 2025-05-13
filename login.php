<?php
session_start();
require_once 'db_connection.php';

// Define session timeout (30 minutes)
define('SESSION_TIMEOUT', 30 * 60);

// If already logged in, redirect to homepage
if (isset($_SESSION['admin_id'])) {
    header('Location: product_list.php');
    exit;
}

$error = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Simple validation
    if (empty($username) || empty($password)) {
        $error = 'Lütfen kullanıcı adı ve şifrenizi giriniz';
    } else {
        try {
            $pdo = getAdminConnection();
            
            // Get admin by username
            $stmt = $pdo->prepare("SELECT id, username, password_hash, full_name FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password_hash'])) {
                // Valid credentials - regenerate session ID for security
                session_regenerate_id(true);
                
                // Set session variables
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['full_name'] = $admin['full_name'];
                $_SESSION['last_activity'] = time();
                
                // Generate CSRF token if needed
                if (!isset($_SESSION['csrf_token'])) {
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
                
                // Clear old sessions for this admin
                try {
                    $deleteOldStmt = $pdo->prepare("DELETE FROM sessions WHERE admin_id = ?");
                    $deleteOldStmt->execute([$admin['id']]);
                    
                    // Store new session in database
                    $sessionStmt = $pdo->prepare("INSERT INTO sessions (admin_id, session_id) VALUES (?, ?)");
                    $sessionStmt->execute([$admin['id'], session_id()]);
                } catch (PDOException $sessionEx) {
                    // Log but continue - session storage in DB is secondary
                    error_log('Session storage error: ' . $sessionEx->getMessage());
                }
                
                // Redirect to dashboard
                header('Location: product_list.php');
                exit;
                
            } else {
                $error = 'Geçersiz kullanıcı adı veya şifre';
            }
            
        } catch (PDOException $e) {
            $error = 'System error, please try again later';
            error_log('Login error: ' . $e->getMessage());
        }
    }
}

// Check for error query parameters
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'expired') {
        $error = 'Oturumunuz sona erdi. Lütfen tekrar giriş yapın.';
    } elseif ($_GET['error'] === 'system') {
        $error = 'Sistem hatası oluştu. Lütfen tekrar giriş yapın.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İşitme Cihazı Stok Yöneticisi - Giriş</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Animation CSS -->
    <link rel="stylesheet" href="animations.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary: #4e54c8;             /* Vibrant blue-purple */
            --primary-dark: #363795;
            --primary-light: #7377de;
            --secondary: #00d2ff;           /* Bright cyan */
            --secondary-dark: #00a8e8;
            --secondary-light: #60e6ff;
            --accent: #ff6b6b;              /* Soft coral red */
            --accent-light: #ffa5a5;
            --accent-dark: #e74c3c;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --white: #ffffff;
            --box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            --animation-timing: cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            position: relative;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        
        /* Enhanced animated background */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
            background: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);
        }
        
        /* Floating particles */
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            animation: float 15s infinite linear;
        }
        
        /* Wave animation */
        .wave {
            position: absolute;
            width: 100%;
            height: 100px;
            background: linear-gradient(to bottom, rgba(255, 255, 255, 0.08), transparent);
            animation: wave 15s infinite linear;
        }
        
        .wave:nth-child(1) {
            bottom: 0;
            animation-delay: 0s;
            height: 80px;
            opacity: 0.4;
        }
        
        .wave:nth-child(2) {
            bottom: 10%;
            animation-delay: -5s;
            height: 100px;
            opacity: 0.3;
        }
        
        .wave:nth-child(3) {
            bottom: 20%;
            animation-delay: -2s;
            height: 60px;
            opacity: 0.2;
        }
        
        @keyframes wave {
            0% { transform: translateX(0) scaleY(1); }
            25% { transform: translateX(-25%) scaleY(0.8); }
            50% { transform: translateX(-50%) scaleY(1.2); }
            75% { transform: translateX(-75%) scaleY(0.9); }
            100% { transform: translateX(-100%) scaleY(1); }
        }
        
        /* Ambient light effect */
        .light {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.5;
            z-index: -1;
            animation: pulse 8s infinite alternate ease-in-out;
        }
        
        .light-1 {
            width: 400px;
            height: 400px;
            background-color: rgba(255, 107, 107, 0.2); /* Accent color */
            top: 10%;
            left: 15%;
            animation-delay: 0s;
        }
        
        .light-2 {
            width: 350px;
            height: 350px;
            background-color: rgba(0, 210, 255, 0.2); /* Secondary color */
            bottom: 20%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .light-3 {
            width: 300px;
            height: 300px;
            background-color: rgba(255, 255, 255, 0.15); /* White glow */
            top: 50%;
            left: 25%;
            animation-delay: 4s;
        }
        
        @keyframes pulse {
            0% { transform: scale(0.8); opacity: 0.3; }
            100% { transform: scale(1.2); opacity: 0.6; }
        }
        
        @keyframes float {
            0% {
                transform: translateY(0) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.8;
            }
            90% {
                opacity: 0.8;
            }
            100% {
                transform: translateY(-1000px) translateX(100px) rotate(360deg);
                opacity: 0;
            }
        }
        
        .login-page {
            width: 100%;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.5);
            opacity: 1;
            animation: fadeInContainer 1s var(--animation-timing) forwards;
        }
        
        @keyframes fadeInContainer {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-container:hover {
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
        
        /* Glossy container effect */
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: linear-gradient(
                to bottom,
                rgba(255, 255, 255, 0.8) 0%,
                rgba(255, 255, 255, 0.4) 100%
            );
            transform: translateY(-100%);
            transition: transform 0.5s ease;
            pointer-events: none;
            z-index: 0;
        }
        
        .login-container:hover::before {
            transform: translateY(0);
        }
        
        /* Ensure all content inside container is visible */
        .login-container > * {
            position: relative;
            z-index: 2;
            opacity: 1 !important; /* Force visibility */
        }
        
        .login-title, .login-subtitle, .form-icon, .alert-wrapper, .form-group, .btn-wrapper {
            opacity: 1;
            transform: none;
        }
        
        .login-title {
            text-align: center;
            margin-bottom: 10px;
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent)); /* Tricolor gradient */
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: titleGradient 8s infinite alternate;
            position: relative;
            letter-spacing: 1px;
            text-shadow: 0 0 20px rgba(78, 84, 200, 0.2); /* Subtle glow */
        }
        
        @keyframes titleGradient {
            0% { background-position: 0% 50%; filter: hue-rotate(0deg); }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; filter: hue-rotate(30deg); }
        }
        
        .login-subtitle {
            text-align: center;
            color: var(--primary);
            margin-bottom: 25px;
            font-size: 1.1rem;
            font-weight: 500;
            animation: fadeSlideUp 1s var(--animation-timing) forwards;
            animation-delay: 0.2s;
        }
        
        .form-icon {
            text-align: center;
            margin-bottom: 30px;
            animation: fadeSlideUp 1s var(--animation-timing) forwards;
            animation-delay: 0.1s;
        }
        
        .form-icon i {
            font-size: 4.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary), var(--accent));
            background-size: 300% 300%;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: float 6s ease-in-out infinite, titleGradient 8s infinite alternate;
            filter: drop-shadow(0 0 10px rgba(78, 84, 200, 0.3));
        }
        
        .alert-wrapper {
            min-height: 40px;
            margin-bottom: 25px;
            animation: fadeSlideUp 1s var(--animation-timing) forwards;
            animation-delay: 0.3s;
        }
        
        .alert {
            border-radius: 12px;
            padding: 12px 15px;
            background: rgba(255, 107, 107, 0.1);
            color: var(--accent);
            display: flex;
            align-items: center;
            border-left: 4px solid var(--accent);
            animation: shake 0.5s ease-in-out;
        }
        
        .alert i {
            margin-right: 10px;
            animation: pulse 2s infinite;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 20px;
            animation: fadeSlideUp 1s var(--animation-timing) forwards;
        }
        
        .form-group:nth-child(1) {
            animation-delay: 0.4s;
        }
        
        .form-group:nth-child(2) {
            animation-delay: 0.5s;
        }
        
        .form-group input {
            width: 100%;
            height: 54px;
            padding: 15px 45px;
            font-size: 16px;
            border: 2px solid transparent;
            border-radius: 12px;
            background-color: rgba(240, 240, 240, 0.7);
            transition: all 0.3s ease;
            outline: none;
        }
        
        .form-group input:focus {
            border-color: var(--primary);
            background-color: var(--white);
            box-shadow: 0 0 0 4px rgba(78, 84, 200, 0.15);
        }
        
        .form-group .icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 18px;
            transition: all 0.3s ease;
            pointer-events: none;
        }
        
        .form-group input:focus + .icon {
            color: var(--primary);
            transform: translateY(-50%) scale(1.1);
        }
        
        .password-toggle {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--primary-light);
            transition: all 0.3s ease;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
        }
        
        .password-toggle:hover {
            color: var(--primary);
            transform: translateY(-50%) scale(1.1);
        }
        
        .btn-wrapper {
            margin-top: 30px;
            animation: fadeSlideUp 1s var(--animation-timing) forwards;
            animation-delay: 0.6s;
        }
        
        .btn {
            width: 100%;
            height: 54px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(78, 84, 200, 0.3);
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(78, 84, 200, 0.4);
            background: linear-gradient(90deg, var(--primary-light), var(--primary));
        }
        
        .btn i {
            margin-right: 8px;
            font-size: 18px;
        }
        
        /* Ripple effect */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            animation: ripple 0.6s linear;
            transform-origin: center;
        }
        
        @keyframes ripple {
            to {
                transform: scale(2.5);
                opacity: 0;
            }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }
            
            .login-title {
                font-size: 1.8rem;
            }
            
            .form-icon i {
                font-size: 3.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Enhanced Animated Background -->
    <div class="animated-bg">
        <!-- Ambient light effects -->
        <div class="light light-1"></div>
        <div class="light light-2"></div>
        <div class="light light-3"></div>
        
        <!-- Wave effects -->
        <div class="wave"></div>
        <div class="wave"></div>
        <div class="wave"></div>
        
        <!-- Dynamic particles -->
        <script>
            // Create particles dynamically with improved effects
            document.addEventListener('DOMContentLoaded', function() {
                const bg = document.querySelector('.animated-bg');
                
                // Create floating particles
                for (let i = 0; i < 50; i++) {
                    createParticle(bg);
                }
                
                // Create mousemove-reactive particles
                document.addEventListener('mousemove', function(e) {
                    if (Math.random() > 0.85) { // Only create on some moves to avoid overwhelming
                        const mouseX = e.clientX;
                        const mouseY = e.clientY;
                        createMouseParticle(bg, mouseX, mouseY);
                    }
                });
                
                // Function to create standard floating particles
                function createParticle(parent) {
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
                    
                    // Create variety with different shapes
                    if (Math.random() > 0.7) {
                        particle.style.borderRadius = '30% 70% 70% 30% / 30% 30% 70% 70%'; // Blob shape
                    }
                    
                    // Randomize colors - using our theme colors
                    const colorChoice = Math.random();
                    let hue, saturation, lightness;
                    
                    if (colorChoice < 0.33) {
                        // Primary color family (blue-purple)
                        hue = Math.floor(Math.random() * 30) + 230;
                        saturation = 70 + Math.floor(Math.random() * 30);
                        lightness = 65 + Math.floor(Math.random() * 15);
                    } else if (colorChoice < 0.66) {
                        // Secondary color family (cyan)
                        hue = Math.floor(Math.random() * 20) + 180;
                        saturation = 80 + Math.floor(Math.random() * 20);
                        lightness = 60 + Math.floor(Math.random() * 15);
                    } else {
                        // Accent color family (coral/pink)
                        hue = Math.floor(Math.random() * 20) + 350;
                        saturation = 70 + Math.floor(Math.random() * 30);
                        lightness = 70 + Math.floor(Math.random() * 10);
                    }
                    
                    particle.style.background = `hsla(${hue}, ${saturation}%, ${lightness}%, 0.3)`;
                    particle.style.boxShadow = `0 0 ${Math.floor(size/2)}px hsla(${hue}, ${saturation}%, ${lightness}%, 0.3)`;
                    
                    parent.appendChild(particle);
                    
                    // Remove particle after animation completes to avoid memory issues
                    setTimeout(() => {
                        if (particle && particle.parentNode === parent) {
                            parent.removeChild(particle);
                            createParticle(parent); // Create a new one to replace it
                        }
                    }, (animationDuration + animationDelay) * 1000);
                }
                
                // Function to create mouse-reactive particles
                function createMouseParticle(parent, x, y) {
                    const size = Math.random() * 8 + 2;
                    const particle = document.createElement('div');
                    particle.classList.add('particle');
                    
                    // Position at mouse location
                    particle.style.width = size + 'px';
                    particle.style.height = size + 'px';
                    particle.style.left = x + 'px';
                    particle.style.top = y + 'px';
                    particle.style.opacity = 0.8;
                    
                    // Set random direction
                    const angle = Math.random() * Math.PI * 2;
                    const speed = Math.random() * 60 + 30;
                    const vx = Math.cos(angle) * speed;
                    const vy = Math.sin(angle) * speed;
                    
                    // Animated with CSS
                    particle.style.transform = 'translate(0, 0)';
                    particle.style.transition = 'transform 1s cubic-bezier(0.215, 0.61, 0.355, 1), opacity 1s';
                    
                    // Same color theming as regular particles
                    const colorChoice = Math.random();
                    let hue, saturation, lightness;
                    
                    if (colorChoice < 0.33) {
                        hue = Math.floor(Math.random() * 30) + 230;
                        saturation = 70 + Math.floor(Math.random() * 30);
                        lightness = 65 + Math.floor(Math.random() * 15);
                    } else if (colorChoice < 0.66) {
                        hue = Math.floor(Math.random() * 20) + 180;
                        saturation = 80 + Math.floor(Math.random() * 20);
                        lightness = 60 + Math.floor(Math.random() * 15);
                    } else {
                        hue = Math.floor(Math.random() * 20) + 350;
                        saturation = 70 + Math.floor(Math.random() * 30);
                        lightness = 70 + Math.floor(Math.random() * 10);
                    }
                    
                    particle.style.background = `hsla(${hue}, ${saturation}%, ${lightness}%, 0.5)`;
                    particle.style.boxShadow = `0 0 ${Math.floor(size/2)}px hsla(${hue}, ${saturation}%, ${lightness}%, 0.5)`;
                    
                    parent.appendChild(particle);
                    
                    // Animate
                    setTimeout(() => {
                        particle.style.transform = `translate(${vx}px, ${vy}px)`;
                        particle.style.opacity = '0';
                    }, 10);
                    
                    // Remove after animation
                    setTimeout(() => {
                        if (particle && particle.parentNode === parent) {
                            parent.removeChild(particle);
                        }
                    }, 1000);
                }
                
                // Add subtle parallax effect
                document.addEventListener('mousemove', function(e) {
                    const mouseX = e.clientX / window.innerWidth;
                    const mouseY = e.clientY / window.innerHeight;
                    const lights = document.querySelectorAll('.light');
                    
                    lights.forEach((light, index) => {
                        const depth = 0.05 + (index * 0.01);
                        const moveX = mouseX * depth * 100;
                        const moveY = mouseY * depth * 100;
                        light.style.transform = `translate(${moveX}px, ${moveY}px)`;
                    });
                });
            });
        </script>
    </div>
    
    <div class="login-page">
        <div class="login-container">
            <div class="form-icon">
                <i class="fas fa-hearing"></i>
            </div>
            
            <h1 class="login-title">İşitme Cihazı</h1>
            <p class="login-subtitle">Stok Yönetim Sistemine Hoşgeldiniz</p>
            
            <div class="alert-wrapper">
                <?php if ($error): ?>
                    <div class="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <form method="post" action="login.php" id="loginForm">
                <div class="form-group">
                    <input type="text" id="username" name="username" placeholder="Kullanıcı Adı" required>
                    <span class="icon"><i class="fas fa-user"></i></span>
                </div>
                
                <div class="form-group">
                    <input type="password" id="password" name="password" placeholder="Şifre" required>
                    <span class="icon"><i class="fas fa-lock"></i></span>
                    <span class="password-toggle" id="passwordToggle">
                        <i class="far fa-eye"></i>
                    </span>
                </div>
                
                <div class="btn-wrapper">
                    <button type="submit" class="btn" id="loginButton">
                        <i class="fas fa-sign-in-alt"></i> Giriş Yap
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Password visibility toggle with animation
        document.getElementById('passwordToggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
            
            // Add animation
            this.classList.add('animated');
            setTimeout(() => {
                this.classList.remove('animated');
            }, 300);
        });
        
        // Enhanced button click effect
        document.getElementById('loginButton').addEventListener('click', function(e) {
            // Validate form
            const form = document.getElementById('loginForm');
            if (!form.checkValidity()) {
                return;
            }
            
            // Prevent multiple clicks
            if (this.classList.contains('clicked')) {
                return;
            }
            
            // Add visual feedback
            this.classList.add('clicked');
            const originalContent = this.innerHTML;
            const originalWidth = this.offsetWidth;
            
            // Keep button size consistent
            this.style.width = originalWidth + 'px';
            
            // Show loading spinner with animation
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Giriş Yapılıyor...';
            
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
        });
        
        // Add input field animations
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            // Focus effect
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
                const icon = this.parentElement.querySelector('.icon i');
                icon.classList.add('animated');
            });
            
            // Blur effect
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
                const icon = this.parentElement.querySelector('.icon i');
                icon.classList.remove('animated');
            });
            
            // Typing effect
            input.addEventListener('input', function() {
                if (this.value.length > 0) {
                    this.classList.add('has-value');
                } else {
                    this.classList.remove('has-value');
                }
            });
        });
    </script>
    <script src="animations.js"></script>
    <script src="header_animation.js"></script>
</body>
</html> 