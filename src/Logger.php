<?php

namespace djfhe\PHPStanTypescriptTransformer;

final class Logger {

/**
 * @param string $text The text to colorize
 * @param 'black'|'red'|'green'|'yellow'|'blue'|'purple'|'cyan'|'white'|'gray'|null $foreground
 * @param 'black'|'red'|'green'|'yellow'|'blue'|'purple'|'cyan'|'white'|null $background
 * @param list<'bold'|'dim'|'underline'|'blink'|'reverse'|'hidden'> $options
 * @return string ANSI-formatted string
 */
static function colorize(
  bool|float|int|string $text,
  ?string $foreground = null,
  ?string $background = null,
  array $options = []
): string {
  $colors = [
      'black'   => '0;30',
      'red'     => '0;31',
      'green'   => '0;32',
      'yellow'  => '0;33',
      'blue'    => '0;34',
      'purple'  => '0;35',
      'cyan'    => '0;36',
      'white'   => '0;37',
      'gray'    => '1;30',
  ];

  $backgrounds = [
      'black'   => '40',
      'red'     => '41',
      'green'   => '42',
      'yellow'  => '43',
      'blue'    => '44',
      'purple'  => '45',
      'cyan'    => '46',
      'white'   => '47',
  ];

  $styles = [
      'bold'      => '1',
      'dim'       => '2',
      'underline' => '4',
      'blink'     => '5',
      'reverse'   => '7',
      'hidden'    => '8',
  ];

  $codes = [];

  // @phpstan-ignore isset.offset
  if ($foreground !== null && isset($colors[$foreground])) {
      $codes[] = $colors[$foreground];
  }

// @phpstan-ignore isset.offset
  if ($background !== null && isset($backgrounds[$background])) {
      $codes[] = $backgrounds[$background];
  }
  
  foreach ($options as $option) {
    // @phpstan-ignore isset.offset
      if (isset($styles[$option])) {
          $codes[] = $styles[$option];
      }
  }

  $prefix = $codes !== [] ? "\033[" . implode(';', $codes) . "m" : '';
  $suffix = $prefix !== '' ? "\033[0m" : '';

  $text = is_bool($text) ? ($text ? 'true' : 'false') : (string)$text;

  return $prefix . $text . $suffix;
}
}
