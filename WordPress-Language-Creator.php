<?php

// * Scan all files and subfolders in theme files "__()"
function my_theme_get_translatable_strings($dir)
{
    $strings = array();
    $di = new RecursiveDirectoryIterator($dir);
    $dirLength = strlen($dir);
    foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
        if (is_file($filename)) {
            // * ignore hidden files/directories
            if (substr($file, 0, 1) === '.' || strpos($filename, '/.') !== false) {
                continue;
            }
            $file_contents = file_get_contents($filename);
            // * out "text", "my-theme"
            // preg_match_all('/(?:__|_e|esc_html__|esc_attr__)\(\s*([\'"])(.*?)\1\s*(?:,\s*[\'"](.*?)\3\s*)?\)/', $file_contents, $matches, PREG_SET_ORDER);
            // * out "text"
            preg_match_all('/__\(\s*([\'"])(.*?)\1\s*(?:,\s*[^\)]+)?\)/', $file_contents, $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                // * add the match
                $string = trim($match[2]);
                $file_path = substr($filename, $dirLength + 1);
                $line_number = my_get_line_number($file_contents, $match[0]);
                $string_info = array(
                    'string' => $string,
                    'file_path' => $file_path,
                    'line_number' => $line_number,
                );
                // * Check if the string is already present
                $is_duplicate = false;
                foreach ($strings as $existing_string_info) {
                    if ($existing_string_info['string'] === $string_info['string']) {
                        $is_duplicate = true;
                        break;
                    }
                }
                if (!$is_duplicate) {
                    $strings[] = $string_info;
                }
            }
        }
    }
    return $strings;
}

// * get files location and line number
function my_get_line_number($file_contents, $match)
{
    $lines = explode("\n", $file_contents);
    $line_number = false;
    foreach ($lines as $index => $line) {
        if (strpos($line, $match) !== false) {
            $line_number = $index + 1;
            break;
        }
    }
    return $line_number;
}

// * additional filtering
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
    // * get text and file/line information
    $strings_with_info = my_theme_get_translatable_strings($dir);
    // * open file
    $file = fopen($path, 'w');
    // * write
    fputs($file, '#, fuzzy');
    fputs($file, "msgid \"\"\n");
    fputs($file, "msgstr \"\"\n");
    fputs($file, "\"Project-Id-Version: " . $filename . "\\n\"\n");
    fputs($file, "\"Report-Msgid-Bugs-To: \\n\"\n");
    fputs($file, "\"POT-Creation-Date: " . date('Y-m-d H:iO') . "\\n\"\n");
    fputs($file, "\"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n\"\n");
    fputs($file, "\"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n\"\n");
    fputs($file, "\"Language: \\n\"\n");
    fputs($file, "\"MIME-Version: 1.0\\n\"\n");
    fputs($file, "\"Content-Type: text/plain; charset=UTF-8\\n\"\n");
    fputs($file, "\"Content-Transfer-Encoding: 8bit\\n\"\n");
    // fputs($file, "\"Plural-Forms: nplurals=INTEGER; plural=EXPRESSION;\\n\"\n");
    fputs($file, "\"Plural-Forms: nplurals=2; plural=(n != 1);\\n\"\n");
    fputs($file, "\"X-Generator: EWT WordPress-Language-Creator\\n\"\n");
    fputs($file, "\"X-Creator-Version: 1.0\\n\"\n");
    fputs($file, "\"X-Domain: " . $domain . "\"\n");
    fputs($file, "\n");

    foreach ($strings_with_info as $string_info) {
        $string = $string_info['string'];
        $file_path = $string_info['file_path'];
        $line_number = $string_info['line_number'];

        fputs($file, "#: " . $file_path . ":" . $line_number . "\n");
        fputs($file, "msgid \"" . $string . "\"\n");
        fputs($file, "msgstr \"\"\n\n");
    }
    // * close file
    fclose($file);
}

$dir = get_template_directory(); // * Theme home directory
$filename = 'demo'; // * POT file name
$domain = 'my-theme'; // * The theme's translation area

// ! pot to po mo create name -> en_EN.po | en.po error..

my_theme_generate_translation_file($dir, $filename, $domain);
