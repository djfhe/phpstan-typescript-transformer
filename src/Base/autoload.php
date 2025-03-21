 <?php

foreach (glob(__DIR__ . '/Transformer/*.php', GLOB_BRACE) as $file) {
  require_once $file;
}

foreach (glob(__DIR__ . '/Types/*.php', GLOB_BRACE) as $file) {
  require_once $file;
}