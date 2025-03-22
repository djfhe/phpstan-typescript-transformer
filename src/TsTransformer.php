<?php

namespace djfhe\PHPStanTypescriptTransformer;

use djfhe\PHPStanTypescriptTransformer\TsType;
use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use SplObjectStorage;

class TsTransformer
{
  /**
   * @var ?array<class-string<TsTypeTransformerContract>>
   */
  protected static ?array $registry = null;

  /**
   * @var ?SplObjectStorage<Type,TsType>
   */
  protected static ?SplObjectStorage $cache = null;

    /**
     * @return array<class-string<TsTypeTransformerContract>>
     */
    protected static function init(): array
    {
      if (self::$registry !== null) {
        return self::$registry;
      }

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

    /**
     * @return SplObjectStorage<Type,TsType>
     */
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
      $transformers = self::init();

      $cache = self::getCache();

      if ($cache->contains($type)) {
        return $cache[$type];
      }

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