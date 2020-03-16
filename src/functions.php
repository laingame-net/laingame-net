<?php
if(!function_exists('_e')){
 function _e($string) {
   echo htmlspecialchars($string);
 }
}
