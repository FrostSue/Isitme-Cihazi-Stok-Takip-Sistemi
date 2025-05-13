<?php
/**
 * update_all_animations.php - Update all PHP files to include animations
 * For the hearing aid stock management system
 */

// Directory to scan
$directory = __DIR__;

// Get all PHP files (excluding this script and check_session.php)
$files = glob($directory . '/*.php');

// Files to skip
$skipFiles = [
    'update_all_animations.php',
    'check_session.php',
    'db_connection.php',
    'logout.php'
];

// Count of updated files
$updatedFiles = 0;
$errorFiles = [];

// Loop through each file
foreach ($files as $file) {
    $filename = basename($file);
    
    // Skip files in the skip list
    if (in_array($filename, $skipFiles)) {
        echo "Skipping $filename (in skip list)<br>\n";
        continue;
    }
    
    // Read the file content
    $content = file_get_contents($file);
    
    // Check if file is an HTML document with a body tag
    if (stripos($content, '</body>') === false) {
        echo "Skipping $filename (no body tag)<br>\n";
        continue;
    }
    
    // Check if animations.js already exists
    $hasAnimationsCss = stripos($content, 'animations.css') !== false;
    $hasAnimationsJs = stripos($content, 'animations.js') !== false;
    $hasHeaderAnimations = stripos($content, 'header_animation.js') !== false;
    
    $modified = false;
    
    // Add animations.css if not present
    if (!$hasAnimationsCss) {
        $cssPattern = '/<link rel="stylesheet" href="style.css">/i';
        if (preg_match($cssPattern, $content)) {
            $cssReplacement = '<link rel="stylesheet" href="style.css">' . "\n" . 
                              '    <link rel="stylesheet" href="animations.css">';
            $content = preg_replace($cssPattern, $cssReplacement, $content);
            $modified = true;
            echo "Added animations.css to $filename<br>\n";
        }
    }
    
    // Add animations.js and header_animation.js if not present
    if (!$hasHeaderAnimations) {
        // If animations.js exists, add header_animation.js after it
        if ($hasAnimationsJs) {
            $jsPattern = '/<script src="animations.js"><\/script>/i';
            if (preg_match($jsPattern, $content)) {
                $jsReplacement = '<script src="animations.js"></script>' . "\n" . 
                                 '    <script src="header_animation.js"></script>';
                $content = preg_replace($jsPattern, $jsReplacement, $content);
                $modified = true;
                echo "Added header_animation.js after existing animations.js to $filename<br>\n";
            }
        } else {
            // If animations.js doesn't exist, add both
            $bodyPattern = '/<\/body>/i';
            if (preg_match($bodyPattern, $content)) {
                $bodyReplacement = '    <script src="animations.js"></script>' . "\n" . 
                                  '    <script src="header_animation.js"></script>' . "\n" . 
                                  '</body>';
                $content = preg_replace($bodyPattern, $bodyReplacement, $content);
                $modified = true;
                echo "Added both animations.js and header_animation.js to $filename<br>\n";
            }
        }
    }
    
    // Save the changes if modified
    if ($modified) {
        try {
            if (file_put_contents($file, $content)) {
                $updatedFiles++;
                echo "Successfully updated $filename<br>\n";
            } else {
                $errorFiles[] = $filename;
                echo "Error writing to $filename<br>\n";
            }
        } catch (Exception $e) {
            $errorFiles[] = $filename;
            echo "Exception updating $filename: " . $e->getMessage() . "<br>\n";
        }
    } else {
        echo "No changes needed for $filename<br>\n";
    }
}

// Show summary
echo "<hr><h3>Update Complete</h3>";
echo "Updated $updatedFiles files successfully.<br>\n";

if (count($errorFiles) > 0) {
    echo "Failed to update " . count($errorFiles) . " files: " . implode(', ', $errorFiles) . "<br>\n";
} 