<?php

$output_template = <<<EOT
//
//  mine.h
//  Mine crypto library
//
//  Copyright 2017 Muflihun Labs
//
//  https://github.com/muflihun/mine
//

#ifndef MINE_H
#define MINE_H

{{includes}}
namespace mine {
{{code}}
} // namespace mine
#endif // MINE_H

EOT;

$src_list = array(
    "src/rsa.h",
    "src/aes.h"
);

$includes = array();
$lines = "";

foreach ($src_list as $filename) {
    $fd = @fopen($filename, "r");
    if ($fd) {
		$namespace_started = false;
        while (($line = fgets($fd, 2048)) !== false) {
            if ($pos = (strpos($line, "#include")) === 0) {
                $includes[] = substr($line, $pos);
            } else if ($pos = (strpos($line, "namespace mine {")) === 0) {
				$namespace_started = true;
            } else if ($pos = (strpos($line, "} // end namespace mine")) === 0) {
				$namespace_started = false;
            } else if ($namespace_started) {
            	$lines .= $line;
            }
        }
        if (!feof($fd)) {
            die("Error: unexpected fgets() fail");
        }

        fclose($fd);
    }
    
}
$includes = array_unique($includes, SORT_STRING);
$includes_str = "";
foreach ($includes as $incl) {
	$includes_str .= "#include $incl";
}

$final = str_replace("{{includes}}", $includes_str, $output_template);

$final = str_replace("{{code}}", $lines, $final);

file_put_contents("include/mine.h", $final);
