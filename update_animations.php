<?php
/**
 * update_animations.php
 * Script to automatically add animations.css and animations.js to all PHP files
 */

// Directory to scan
$directory = __DIR__;

// Get all PHP files
$files = glob($directory . '/*.php');

// Count of updated files
$updatedFiles = 0;

foreach ($files as $file) {
    // Skip the current script
    if (basename($file) === 'update_animations.php') {
        continue;
    }
    
    // Read the file content
    $content = file_get_contents($file);
    
    // Check if file is an HTML document (has DOCTYPE and HTML tags)
    if (stripos($content, '<!DOCTYPE') !== false && stripos($content, '<html') !== false) {
        $modified = false;
        
        // Check if animations.css already exists
        if (stripos($content, 'animations.css') === false) {
            // Add animations.css after the last CSS link
            $pattern = '/<link[^>]*rel=["|\']stylesheet["|\'][^>]*>/i';
            if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                // Get the position after the last stylesheet link
                $lastMatch = end($matches[0]);
                $position = $lastMatch[1] + strlen($lastMatch[0]);
                
                // Insert animations.css link
                $content = substr_replace(
                    $content, 
                    "\n    <link rel=\"stylesheet\" href=\"animations.css\">", 
                    $position, 
                    0
                );
                $modified = true;
            }
        }
        
        // Check if animations.js already exists
        if (stripos($content, 'animations.js') === false) {
            // Add animations.js before the </body> closing tag
            $bodyPos = stripos($content, '</body>');
            if ($bodyPos !== false) {
                $content = substr_replace(
                    $content,
                    "    <script src=\"animations.js\"></script>\n", 
                    $bodyPos, 
                    0
                );
                $modified = true;
            }
        }
        
        // Save modified content
        if ($modified) {
            file_put_contents($file, $content);
            $updatedFiles++;
            echo "Updated: " . basename($file) . "<br>";
        }
    }
}

echo "<p>Completed! Updated $updatedFiles files.</p>";
echo "<p><a href='product_list.php'>Return to Product List</a></p>"; 