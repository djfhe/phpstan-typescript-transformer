<?php

namespace djfhe\StanScript;

use djfhe\StanScript\_TsType;
use djfhe\StanScript\_TsTypeTransformerContract;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use SplObjectStorage;

class TsTransformer
{
  /**
   * @var array<class-string<_TsTypeTransformerContract>,_TsTypeTransformerContract>
   */
  protected static array $registrar = [];
  protected static ?SplObjectStorage $cache = null;

  /**
   * @param class-string<_TsTypeTransformerContract> $class
   */
    public static function register(string $transformer): void
    {
      if (in_array($transformer, self::$registrar)) {
        return;
      }

      self::$registrar[] = $transformer;
    }


    protected static function init(): void
    {
      if (!empty(self::$registrar)) {
        return;
      }

      // get classes implementing _TsTypeParserContract
      $classes = get_declared_classes();

      
      foreach ($classes as $class) {

        if (!in_array(_TsTypeTransformerContract::class, class_implements($class, _TsTypeTransformerContract::class), true)) {
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

    public static function transformExpression(\PhpParser\Node\Expr $expr, Scope $scope, ReflectionProvider $reflectionProvider): _TsType
    {
      $type = $scope->getType($expr);

      return self::transform($type, $scope, $reflectionProvider);
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): _TsType
    {
      self::init();

      $cache = self::getCache();

      if ($cache->contains($type)) {
        return $cache[$type];
      }

      $candidates = [];
      foreach (self::$registrar as $transformer) {
        if ($transformer::canTransform($type, $scope, $reflectionProvider)) {
          $candidates[] = $transformer;
        }
      }

      if (empty($candidates)) {
        $class = $type::class;
        $value = $type->describe(VerbosityLevel::value());

        throw new \Exception("No mapper found for type: {$class} with value: {$value}");
      }

      if (count($candidates) === 1) {
        $transformed = $candidates[0]::transform($type, $scope, $reflectionProvider);

        $cache[$type] = $transformed;

        return $transformed;
      }

      usort($candidates, fn($a, $b) => $b::transformPriority($type, $scope, $reflectionProvider, $candidates) <=> $a::transformPriority($type, $scope, $reflectionProvider, $candidates));

      $transformed = $candidates[0]::transform($type, $scope, $reflectionProvider);

      $cache[$type] = $transformed;

      return $transformed;
    }
}