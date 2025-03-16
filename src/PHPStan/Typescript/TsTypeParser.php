<?php

namespace djfhe\ControllerTransformer\PHPStan\Typescript;

use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\_TsType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use SplObjectStorage;

class TsTypeParser
{
  /**
   * @var array<class-string<_TsTypeParserContract>,>
   */
  protected static array $registra = [];
  protected static ?SplObjectStorage $cache = null;

  /**
   * @param class-string<_TsTypeParserContract> $class
   */
    public static function register(string $parser): void
    {
      if (in_array($parser, self::$registra)) {
        return;
      }

      self::$registra[] = $parser;
    }

    public static function autoload(): void
    {
      self::autoloadTypes();
      self::autoloadParsers();
    }

    protected static function autoloadTypes(): void
    {
      foreach (glob(__DIR__ . '/TypescriptTypes/{*,*/*}.php', GLOB_BRACE) as $file) {
        require_once $file;
      }
    }

    protected static function autoloadParsers(): void
    {
      foreach (glob(__DIR__ . '/TypescriptParser/{*,*/*}.php', GLOB_BRACE) as $file) {
        require_once $file;
      }
    }

    protected static function init(): void
    {
      if (!empty(self::$registra)) {
        return;
      }

      self::autoload();

      // get classes implementing _TsTypeParserContract
      $classes = get_declared_classes();

      
      foreach ($classes as $class) {

        if (!in_array(_TsTypeParserContract::class, class_implements($class, _TsTypeParserContract::class), true)) {
          continue;
        }

        self::register($class);
      }
    }

    protected static function getCache(): SplObjectStorage
    {
      if (self::$cache === null) {
        self::$cache = new SplObjectStorage();
      }

      return self::$cache;
    }

    public static function parseExpression(\PhpParser\Node\Expr $expr, Scope $scope, ReflectionProvider $reflectionProvider): _TsType
    {
      $type = $scope->getType($expr);

      return self::parse($type, $scope, $reflectionProvider);
    }

    public static function parse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): _TsType
    {
      self::init();

      $cache = self::getCache();

      if ($cache->contains($type)) {
        return $cache[$type];
      }

      $candidates = [];
      foreach (self::$registra as $parser) {
        if ($parser::canParse($type, $scope, $reflectionProvider)) {
          $candidates[] = $parser;
        }
      }

      if (empty($candidates)) {
        $class = $type::class;
        $value = $type->describe(VerbosityLevel::value());

        throw new \Exception("No mapper found for type: {$class} with value: {$value}");
      }

      if (count($candidates) === 1) {
        $parsed = $candidates[0]::parse($type, $scope, $reflectionProvider);

        $cache[$type] = $parsed;

        return $parsed;
      }

      usort($candidates, fn($a, $b) => $b::parsePriority($type, $scope, $reflectionProvider, $candidates) <=> $a::parsePriority($type, $scope, $reflectionProvider, $candidates));

      $parsed = $candidates[0]::parse($type, $scope, $reflectionProvider);

      $cache[$type] = $parsed;

      return $parsed;
    }
}