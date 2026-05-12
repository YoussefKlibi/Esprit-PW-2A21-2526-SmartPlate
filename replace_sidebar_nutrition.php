<?php
$files = [
    'c:/xampp/htdocs/integration/view/Suivi_Nutritionnel/BackOffice/admin_dashboard.php',
    'c:/xampp/htdocs/integration/view/Suivi_Nutritionnel/BackOffice/admin_objectifs.php',
    'c:/xampp/htdocs/integration/view/Suivi_Nutritionnel/BackOffice/admin_journaux.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Find the start of the <aside> tag
        $startPos = strpos($content, '<aside class="admin-sidebar">');
        
        // Find the end of the <script> tag for the toggleSubMenu function
        $endPos = strpos($content, '</script>', strpos($content, 'function toggleSubMenu'));
        
        if ($startPos !== false && $endPos !== false) {
            $endPos += strlen('</script>');
            
            $before = substr($content, 0, $startPos);
            $after = substr($content, $endPos);
            
            $includeCode = "<?php include __DIR__ . '/../../admin_sidebar.php'; ?>\n";
            
            $newContent = $before . $includeCode . $after;
            
            file_put_contents($file, $newContent);
            echo "Updated " . basename($file) . "\n";
        } else {
            echo "Could not find sidebar boundaries in " . basename($file) . "\n";
        }
    }
}
echo "Done.\n";
