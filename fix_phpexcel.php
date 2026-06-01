<?php
$dir = new RecursiveDirectoryIterator('e:\eventregis\protected\extensions\phpexcel\Classes');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$count = 0;
foreach($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    // Regex to match $var{offset} or $this->var{offset}
    // We make sure not to match string interpolation like "{$var}"
    // So we require a $ followed by variable name (and optional ->property), then {
    $newContent = preg_replace_callback(
        '/(\$[a-zA-Z0-9_]+(?:->[a-zA-Z0-9_]+)*)\{([^}]+)\}/',
        function($matches) {
            // Check if this is just part of string interpolation like " {$var} "
            // If the matched string starts with $ and has {, it's offset access.
            return $matches[1] . '[' . $matches[2] . ']';
        },
        $content,
        -1,
        $replacements
    );

    if ($replacements > 0) {
        file_put_contents($path, $newContent);
        echo "Fixed $replacements occurrences in $path\n";
        $count++;
    }
}
echo "Total files fixed: $count\n";
