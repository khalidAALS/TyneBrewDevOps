<?php
function testPageLoads($path) {
    $url = "http://localhost" . $path;
    $output = @file_get_contents($url);
    if ($output === false) {
        echo "Failed to load $path\n";
        return false;
    } else {
        echo "$path loaded successfully\n";
        return true;
    }
}

function testPageContains($path, $expected) {
    $url = "http://localhost" . $path;
    $output = @file_get_contents($url);
    if (strpos($output, $expected) !== false) {
        echo "$path contains expected text: '$expected'\n";
        return true;
    } else {
        echo "$path does not contain expected text: '$expected'\n";
        return false;
    }
}

// Run tests
$results = [];

$results[] = testPageLoads("/");
$results[] = testPageLoads("/product.php");
$results[] = testPageContains("/", "TyneBrew");
$results[] = testPageContains("/product.php", "Our Products");

// Final result
$failed = array_filter($results, fn($r) => !$r);
if (count($failed) > 0) {
    echo "\nOne or more tests failed.\n";
    exit(1);
} else {
    echo "\nAll tests passed.\n";
    exit(0);
}
