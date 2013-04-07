<?php

$u = $_SERVER['PHP_SELF'];

$u = substr($u, 0, strpos($u, ".php" ) + 4);

echo $u;

?>