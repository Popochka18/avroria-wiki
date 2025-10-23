<?php
// --- Configuration ---
define('PAGES_DIR', 'pages/'); // Directory where pages are stored
define('DEFAULT_PAGE', 'HomePage'); // Default page to show

// --- Get Requested Page Name ---
// Sanitize the page name to prevent directory traversal attacks
$pageName = isset($_GET['page']) ? basename($_GET['page']) : DEFAULT_PAGE;
// Replace spaces with underscores for filename consistency (optional)
$fileName = str_replace(' ', '_', $pageName) . '.html';
$filePath = PAGES_DIR . $fileName;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageName); ?> - My Wiki</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .infobox { border: 1px solid #aaa; background-color: #f9f9f9; padding: 10px; margin-bottom: 15px; float: right; width: 250px; font-size: 0.9em; }
        .infobox th { background-color: #eee; text-align: center; font-weight: bold; padding: 5px; }
        .infobox td { padding: 3px; border-top: 1px solid #eee; }
        nav { margin-bottom: 15px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
    </style>
</head>
<body>

<nav>
    <a href="index.php">Home</a> |
    <a href="edit.php?page=<?php echo urlencode($pageName); ?>">Edit this page</a>
</nav>

<h1><?php echo htmlspecialchars($pageName); ?></h1>

<div id="wiki-content">
<?php
// --- Display Page Content ---
if (file_exists($filePath)) {
    // Read the HTML content from the file
    $content = file_get_contents($filePath);

    // **VERY BASIC Link Parsing** (Replace [[Page Name]] with links)
    // A robust solution needs more complex regex or a dedicated parser
    $content = preg_replace_callback(
        '/\[\[([^\]]+)\]\]/', // Matches [[Page Name]]
        function ($matches) {
            $linkPageName = trim($matches[1]);
            $linkUrl = 'index.php?page=' . urlencode(str_replace(' ', '_', $linkPageName));
            return '<a href="' . $linkUrl . '">' . htmlspecialchars($linkPageName) . '</a>';
        },
        $content
    );
     
    // **CONCEPTUAL Infobox Parsing**
    // This is highly simplified. A real system needs better parsing and templating.
    $content = preg_replace_callback(
        '/\{\{Infobox\s*([^}]+)\}\}/s', // Matches {{Infobox ... }}
        function ($matches) {
            $params_str = trim($matches[1]);
            $lines = explode('|', $params_str);
            $infobox_data = [];
            $infobox_title = 'Infobox'; // Default title

            foreach ($lines as $line) {
                $parts = explode('=', $line, 2);
                if (count($parts) == 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    if ($key === 'title') {
                        $infobox_title = $value;
                    } else {
                        $infobox_data[$key] = $value;
                    }
                }
            }

            // Generate basic HTML for the infobox
            $html = '<table class="infobox">';
            $html .= '<tr><th colspan="2">' . htmlspecialchars($infobox_title) . '</th></tr>';
            foreach ($infobox_data as $key => $value) {
                // Basic link parsing within infobox values
                $value_linked = preg_replace_callback(
                    '/\[\[([^\]]+)\]\]/', 
                    function ($val_matches) {
                         $linkPageName = trim($val_matches[1]);
                         $linkUrl = 'index.php?page=' . urlencode(str_replace(' ', '_', $linkPageName));
                         return '<a href="' . $linkUrl . '">' . htmlspecialchars($linkPageName) . '</a>';
                    }, 
                    htmlspecialchars($value)
                );
                $html .= '<tr><td><strong>' . htmlspecialchars(str_replace('_', ' ', $key)) . '</strong></td><td>' . $value_linked . '</td></tr>';
            }
            $html .= '</table>';
            return $html;
        },
        $content
    );

    // **SECURITY WARNING:** Displaying raw HTML like this is dangerous if
    // the content isn't properly sanitized during saving.
    echo $content;

} else {
    // Page does not exist
    echo '<p>This page does not exist yet.</p>';
    echo '<p><a href="edit.php?page=' . urlencode($pageName) . '">Click here to create it.</a></p>';
}
?>
</div>

</body>
</html>