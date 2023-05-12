<?php
// * Scan all files and subfolders in theme files "__()"
function my_theme_get_translatable_strings($dir)
{
    $strings = array();
    $di = new RecursiveDirectoryIterator($dir);
    foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
        if (is_file($filename)) {
            // * ignore hidden files/directories
            if (substr($file, 0, 1) === '.' || strpos($filename, '/.') !== false) {
                continue;
            }
            $file_contents = file_get_contents($filename);
            preg_match_all('/(?:__|_e|esc_html__|esc_attr__)\(\s*([\'"])(.*?)\1\s*(?:,\s*[\'"](.*?)\3\s*)?\)/', $file_contents, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $strings[] = trim($match[2]);
            }
        }
    }
    return array_unique($strings);
}

// function my_theme_get_translatable_strings($dir, $exclude_prefix = '__')
// {
//     $strings = array();
//     $di = new RecursiveDirectoryIterator($dir);
//     foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
//         if (is_file($filename)) {
//             $file_contents = file_get_contents($filename);
//             preg_match_all('/(?:__|_e|esc_html__|esc_attr__)\(\s*([\'"])(.*?)\1\s*(?:,\s*[\'"](.*?)\3\s*)?\)/', $file_contents, $matches, PREG_SET_ORDER);
//             foreach ($matches as $match) {
//                 $function_name = substr($match[0], 0, strpos($match[0], '('));
//                 $prefix = substr($function_name, 0, strpos($function_name, '_'));
//                 $string = trim($match[2]);
//                 if ($prefix !== $exclude_prefix && substr($prefix, -1) === '_' && substr($prefix, 0, -1) !== $exclude_prefix && substr($string, 0, 2) !== '__') {
//                     $strings[] = $string;
//                 }
//             }
//         }
//     }
//     return array_unique($strings);
// }

// * Creating a translation file "pot"
function my_theme_generate_translation_file($dir, $filename, $domain)
{
    // * create file path
    $path = $dir . "/languages/" . $filename . ".pot";
    // * get text
    $strings = my_theme_get_translatable_strings($dir);
    // * open file
    $file = fopen($path, 'w');
    // * write
    fputs($file, "# " . $domain . " translation file\n");
    fputs($file, "# Generated by My Theme\n\n");
    foreach ($strings as $string) {
        fputs($file, "#: \n");
        fputs($file, "msgid \"" . $string . "\"\n");
        fputs($file, "msgstr \"\"\n\n");
    }
    // * close file
    fclose($file);
}

$dir = get_template_directory(); // * Theme home directory
$filename = 'my-theme'; // * POT file name
$domain = 'my-theme'; // * The theme's translation area

my_theme_generate_translation_file($dir, $filename, $domain);
