<?php

namespace djfhe\StanScript;

use djfhe\StanScript\TsType;
use djfhe\StanScript\TsTypeTransformerContract;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use SplObjectStorage;

class TsTransformer
{
  /**
   * @var array<class-string<TsTypeTransformerContract>,TsTypeTransformerContract>
   */
  protected static array $registry = [];
  protected static ?SplObjectStorage $cache = null;

  /**
   * @param class-string<TsTypeTransformerContract> $class
   */
    public static function register(string $transformer): void
    {
      if (in_array($transformer, self::$registry)) {
        return;
      }

      self::$registry[] = $transformer;
    }


    protected static function init(): void
    {
      if (!empty(self::$registry)) {
        return;
      }

      // get classes implementing TsTypeParserContract
      $classes = get_declared_classes();

      
      foreach ($classes as $class) {

        if (!in_array(TsTypeTransformerContract::class, class_implements($class, TsTypeTransformerContract::class), true)) {
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

    public static function transformExpression(\PhpParser\Node\Expr $expr, Scope $scope, ReflectionProvider $reflectionProvider): TsType
    {
      $type = $scope->getType($expr);

      return self::transform($type, $scope, $reflectionProvider);
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsType
    {
      self::init();

      $cache = self::getCache();

      if ($cache->contains($type)) {
        return $cache[$type];
      }

      $candidates = [];
      foreach (self::$registry as $transformer) {
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