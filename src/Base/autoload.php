 <?php

$transformers = glob(__DIR__ . '/Transformer/*.php');

if ($transformers !== false) {
  foreach ($transformers as $file) {
    require_once $file;
  }
}

$types = glob(__DIR__ . '/Types/*.php');

if ($types !== false) {
  foreach ($types as $file) {
    require_once $file;
  }
}