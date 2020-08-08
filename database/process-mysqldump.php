<?php
// Usage: cat dump.sql | php process-mysqldump.php
// http://blog.lavoie.sl/2014/06/split-mysqldump-extended-inserts.html
// https://gist.github.com/colinmollenhour/cf23b0f7e955267ed1107c9edb07f7c2
while (false !== ($line = fgets_big_buffer())) {
    if (substr($line, 0, 6) === 'INSERT') {
        process_line($line);
    } else {
        echo $line;
    }
}
function fgets_big_buffer()
{
    $ret = fgets(STDIN, 1 * 1024 * 1024);
    if ($ret === false || $ret[strlen($ret) - 1] === "\n") {
        return $ret;
    }
    while (1) {
        $tmp = fgets(STDIN, 1 * 1024 * 1024);
        if ($tmp === false) {
            return $ret;
        }
        $ret .= $tmp;
        if ($tmp[strlen($tmp) - 1] === "\n") {
            return $ret;
        }
    }
}
function process_line($line)
{
    $length = strlen($line);
    $pos = strpos($line, ' VALUES ') + 8;
    $ret = substr($line, 0, $pos);
    $parenthesis = false;
    $quote = false;
    $escape = false;
    for ($i = $pos; $i < $length; ++ $i) {
        switch ($line[$i]) {
            case '(':
                if (! $quote) {
                    if ($parenthesis) {
                       throw new \Exception ( 'double open parenthesis' );
                    } else {
                        $ret .= "\n";
                        $parenthesis = true;
                    }
                }
                $escape = false;
                break;
            case ')':
                if (! $quote) {
                    if ($parenthesis) {
                        $parenthesis = false;
                    } else {
                         throw new Exception ( 'closing parenthesis without open' );
                    }
                }
                $escape = false;
                break;
            case '\\':
                $escape = ! $escape;
                break;
            case "'":
                if ($escape) {
                    $escape = false;
                } else {
                    $quote = ! $quote;
                }
                break;
            default:
                $escape = false;
                break;
        }
        $ret .= $line[$i];
        $to = strcspn($line, '()\\\'', $i + 1)-1;
        if ($to > 0) {
            $ret .= substr($line, $i + 1, $to);
            $i += ($to);
        }
    }
    echo $ret;
}
