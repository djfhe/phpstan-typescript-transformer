<?php

namespace djfhe\PHPStanTypescriptTransformer;

use djfhe\PHPStanTypescriptTransformer\Base\Types\TsCyclicType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

class TsTransformer
{
  /**
   * @var array<class-string<TsTypeTransformerContract>>
   */
  protected static array $registry;

  /**
   * @var array<string,TsType>
   */
  protected static array $cache;

  /**
   * @var array<string, null>
   */
  protected static array $visiting;

  /**
   * @var array<string,TsCyclicType>
   */
  protected static array $cyclic;

  /**
   * @return array<class-string<TsTypeTransformerContract>>
   */
  protected static function init(): array
  {
    if (isset(self::$registry)) {
      return self::$registry;
    }

    self::$visiting = [];
    self::$cyclic = [];
    self::$cache = [];

    self::$registry = [];

    // get classes implementing TsTypeParserContract
    $classes = get_declared_classes();

    
    foreach ($classes as $class) {

      if (!in_array(TsTypeTransformerContract::class, class_implements($class), true)) {
        continue;
      }

      /** @var class-string<TsTypeTransformerContract> $class */

      if (in_array($class, self::$registry, true)) {
        continue;
      }

      self::$registry[] = $class;
    }

    return self::$registry;
  }

  public static function printType(Type $type): string
  {
    $description = $type->describe(VerbosityLevel::value());
    $classString = $type::class;
    return "[{$classString}]: {$description}";
  }

  protected static int $logDepth = -1;
  
  /**
   * @param string|array<string|int|float|bool|null> $message
   */
  public static function log(string|array $message, string $glue = ' '): void
  {
    if (self::$logDepth < 0) {
      return;
    }

    if (is_array($message)) {
      $message = array_map(function ($item) {
        if (is_string($item)) {
          return $item;
        }

        if (is_bool($item)) {
          return $item ? 'true' : 'false';
        }

        if (is_int($item) || is_float($item)) {
          return (string)$item;
        }

        return null;
      }, $message);
      $message = array_filter($message, fn($item) => is_string($item) && $item !== '');
    }

    $message = is_array($message) ? implode($glue, $message) : $message;

    echo $message . "\n";
  }

  protected static function cacheKey(Type $type): string
  {
    return $type->describe(VerbosityLevel::cache());
  }

  /**
   * @param Type $type
   * @param string|array<string|int|float|bool|null> $message
   */
  public static function debug(Type $type, string|array $message): void
  {
    if (self::$logDepth < 0) {
      return;
    }

    $indent = str_repeat(' ', self::$logDepth * 2);
    $prefix = self::$logDepth > 0 ? "\u{21B3}" : '';
    $typeString = Logger::colorize(self::printType($type), foreground: 'green');
    
    $message = is_array($message) ? $message : [$message];

    self::log([
      $indent,
      $prefix,
      $typeString,
      '-',
      ...$message,
    ]);
  }

  public static function transformExpression(\PhpParser\Node\Expr $expr, Scope $scope, ReflectionProvider $reflectionProvider): TsType
  {
    $type = $scope->getType($expr);
    self::log([
      'Parsing expression at line:',
      Logger::colorize($expr->getLine(), foreground: 'cyan'),
      'with type:',
      Logger::colorize(self::printType($type), foreground: 'green'),
    ]);

    return self::transform($type, $scope, $reflectionProvider);
  }

  public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsType
  {
    self::$logDepth++;
    $transformers = self::init();

    $typeCacheKey = self::cacheKey($type);

    if (array_key_exists($typeCacheKey, self::$cache)) {
      self::debug($type, [
        Logger::colorize('Cache hit!', foreground: 'yellow'),
      ]);

      self::$logDepth--;
      return clone self::$cache[$typeCacheKey];
    }

    if (array_key_exists($typeCacheKey, self::$visiting)) {
      if (array_key_exists($typeCacheKey, self::$cyclic)) {
        throw new \Exception('Cyclic type not resolved');
      }

      $cyclic = new TsCyclicType();

      self::$cyclic[$typeCacheKey] = $cyclic;
      self::$cache[$typeCacheKey] = $cyclic;

      self::debug($type, [
        Logger::colorize('Cyclic type detected, Reference returned!', foreground: 'yellow'),
      ]);

      self::$logDepth--;
      return $cyclic;
    }

    self::$visiting[$typeCacheKey] = null;

    $candidates = [];
    foreach ($transformers as $transformer) {
      if ($transformer::canTransform($type, $scope, $reflectionProvider)) {
        $candidates[] = $transformer;
      }
    }

    if (count($candidates) === 0) {
      $class = $type::class;
      $value = $type->describe(VerbosityLevel::value());

      throw new \Exception("No mapper found for type: {$class} with value: {$value}");
    }

    $transformed = null;

    if (count($candidates) > 1) {
      usort($candidates, fn($a, $b) => $b::transformPriority($type, $scope, $reflectionProvider, $candidates) <=> $a::transformPriority($type, $scope, $reflectionProvider, $candidates));
    }

    if (count($candidates) === 1) {
      self::debug($type, [
        'using',
        Logger::colorize($candidates[0], foreground: 'yellow'),
      ]);

    }  else {
      self::debug($type, [
        'candidates:',
        Logger::colorize('candidates:' . count($candidates), foreground: 'red'),
        '-',
        'using',
        Logger::colorize($candidates[0], foreground: 'yellow'),
      ]);
    }

    $transformed = $candidates[0]::transform($type, $scope, $reflectionProvider);
    self::$logDepth--;

    if ($transformed instanceof TsCyclicType) {
      // will be returned by the transform function itself and is therefor cached.
      return $transformed;
    }

    self::$cache[$typeCacheKey] = $transformed;

    unset(self::$visiting[$typeCacheKey]);

    if (array_key_exists($typeCacheKey, self::$cyclic)) {
      $cyclic = self::$cyclic[$typeCacheKey];
      $cyclic->referencedType = $transformed;

      unset(self::$cyclic[$typeCacheKey]);
    }
    
    return $transformed;
  }
}