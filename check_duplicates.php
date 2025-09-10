<?php

$file = 'app/Http/Controllers/Admin/AdminController.php';
$content = file_get_contents($file);

// Extract all method names using regex
preg_match_all('/(?:public|private|protected)\s+function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $content, $matches, PREG_OFFSET_CAPTURE);

$methods = [];
$duplicates = [];

foreach ($matches[1] as $match) {
    $methodName = $match[0];
    $offset = $match[1];
    
    // Find line number
    $lineNumber = substr_count($content, "\n", 0, $offset) + 1;
    
    if (isset($methods[$methodName])) {
        if (!isset($duplicates[$methodName])) {
            $duplicates[$methodName] = [$methods[$methodName]];
        }
        $duplicates[$methodName][] = $lineNumber;
    } else {
        $methods[$methodName] = $lineNumber;
    }
}

if (!empty($duplicates)) {
    echo "Duplicate methods found:\n";
    foreach ($duplicates as $methodName => $lines) {
        echo "- $methodName: lines " . implode(', ', $lines) . "\n";
    }
} else {
    echo "No duplicate methods found.\n";
}

echo "\nAll methods:\n";
foreach ($methods as $name => $line) {
    echo "- $name (line $line)\n";
}
?>
