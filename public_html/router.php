<?php
require '../vendor/autoload.php';
use \eftec\routeone\RouteOne;

$route=new RouteOne('.',null,false);  // null means automatic type
$route->fetch();
$route->controller = ucfirst(strtolower($route->controller));
$route->action = ucfirst(strtolower($route->action));

$debug = true;
if($debug){
	header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
}

try {
  $ret = $route->callObject('Lain\\%sController', false);
  if(null != $ret)
  {
    echo "sometthing went wrong (return 404 page??) ".$ret."\n";
  }
} catch (Exception $ex) {
  echo "sometthing went wrong (return 404 page?) ".$ex."\n";
}
