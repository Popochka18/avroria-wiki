<?php
// --- Configuration ---
define('PAGES_DIR', 'pages/');

// --- Process Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get page name and content from the form
    $pageName = isset($_POST['pageName']) ? basename($_POST['pageName']) : null;
    $content = isset($_POST['content']) ? $_POST['content'] : ''; // Get content from textarea

    if ($pageName) {
        $fileName = str_replace(' ', '_', $pageName) . '.html';
        $filePath = PAGES_DIR . $fileName;

        // **CRITICAL SECURITY WARNING:**
        // Saving raw POST data directly to an HTML file is EXTREMELY DANGEROUS.
        // A real wiki needs robust sanitization (like HTMLPurifier) or a
        // markup language (like Markdown) that is converted to safe HTML.
        // For this *basic example*, we save directly, but DO NOT do this in production.

        // **Conceptual Sanitization (Highly Insufficient):**
        // $allowed_tags = '<p><a><h1><h2><h3><h4><h5><h6><br><img><strong><em><ul><ol><li><table><th><tr><td>';
        // $content = strip_tags($content, $allowed_tags);
        // This is NOT enough for real security. Use a library like HTMLPurifier.

        // Save the content to the file
        if (file_put_contents($filePath, $content) !== false) {
            // Redirect back to the view page after saving
            header('Location: index.php?page=' . urlencode($pageName));
            exit; // Important to stop script execution after redirect
        } else {
            // Error saving file (check permissions!)
            echo "Error: Could not save the page. Check directory permissions for '" . htmlspecialchars(PAGES_DIR) . "'.";
            exit;
        }

    } else {
        echo "Error: Page name not provided.";
        exit;
    }

} else {
    // If accessed directly without POST, redirect to homepage
    header('Location: index.php');
    exit;
}
?>