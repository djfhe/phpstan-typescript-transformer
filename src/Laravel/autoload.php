 <?php

foreach (glob(__DIR__ . '/Transformer/*.php') as $file) {
  require_once $file;
}

foreach (glob(__DIR__ . '/Types/*.php') as $file) {
  require_once $file;
}