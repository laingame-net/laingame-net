<?php
if(!function_exists('_')){
 function _($string) {
   echo htmlspecialchars($string);
 }
}
