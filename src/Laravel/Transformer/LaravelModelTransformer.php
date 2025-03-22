<?php

namespace djfhe\PHPStanTypescriptTransformer\Laravel\Transformer;

use djfhe\PHPStanTypescriptTransformer\Base\Types\TsObjectPropertyType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsObjectType;
use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsScalarType;
use djfhe\PHPStanTypescriptTransformer\Laravel\Rules\ControllerInertiaReturnRule;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use Illuminate\Database\Eloquent\Model;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;

class LaravelModelTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool
    {
      if (!$reflectionProvider->hasClass(Model::class)) {
        return false;
      }

      if (!$type instanceof \PHPStan\Type\ObjectType) {
        return false;
      }

      /** @var \PHPStan\Type\ObjectType $type */

      if (!$type->isEnum()->no()) {
        return false;
      }

      $reflection = $type->getClassReflection();

      if ($reflection === null) {
        return false;
      }

      $modelClass = $reflectionProvider->getClass(Model::class);
      
      return $reflection->isSubclassOfClass($modelClass) || $reflection->getName() === Model::class;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsScalarType|TsObjectType
    {
      /** @var \PHPStan\Type\ObjectType $type */

      $classReflection = $type->getClassReflection();

      if ($classReflection === null) {
        return new TsScalarType('unknown');
      }

      $props = LaravelModelHelper::getModelProperties($classReflection);
      $relations = LaravelModelHelper::getModelRelations($classReflection);

      if ($props === null && $relations === null) {
        return new TsScalarType('unknown');
      }

      $props = $props ?? [];
      $relations = $relations ?? [];

      $tsProps = array_map(fn (\PHPStan\Type\Type $prop, string $key) => new TsObjectPropertyType($key, TsTransformer::transform($prop, $scope, $reflectionProvider)), $props, array_keys($props));
      $tsRelations = array_map(fn (\PHPStan\Type\Type $relation, string $key) => new TsObjectPropertyType($key, TsTransformer::transform($relation, $scope, $reflectionProvider), true), $relations, array_keys($relations));
      $tsProps = array_merge($tsProps, $tsRelations);

      return (new TsObjectType($tsProps))->setName($type->getClassName());
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
      return 1;
    }

}