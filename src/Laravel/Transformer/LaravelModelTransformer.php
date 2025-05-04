<?php

namespace djfhe\PHPStanTypescriptTransformer\Laravel\Transformer;

use djfhe\PHPStanTypescriptTransformer\Base\Types\TsObjectPropertyType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsObjectType;
use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsLiteralType;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use Illuminate\Database\Eloquent\Model;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;

class LaravelModelTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool
    {
      if (!$type instanceof \PHPStan\Type\ObjectType) {
        return false;
      }

      $reflection = $type->getClassReflection();

      if ($reflection === null) {
        return false;
      }
      
      return $reflection->is(Model::class);
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsLiteralType|TsObjectType
    {
      /** @var \PHPStan\Type\ObjectType $type */

      $classReflection = $type->getClassReflection();

      if ($classReflection === null) {
        return new TsLiteralType('unknown');
      }

      $props = LaravelModelHelper::getModelProperties($classReflection);
      $accessors = LaravelModelHelper::getAccessorProperties($classReflection);
      $relations = LaravelModelHelper::getModelRelations($classReflection);

      if ($props === null && $accessors === null && $relations === null) {
        return new TsLiteralType('unknown');
      }

      $props = $props ?? [];
      $accessors = $accessors ?? [];
      $relations = $relations ?? [];

      $hidden = LaravelModelHelper::hiddenAttributes($classReflection);
      $visible = LaravelModelHelper::visibleAttributes($classReflection);
      $appends = LaravelModelHelper::appendedAttributes($classReflection);

      $props = array_filter($props, fn ($key) => !in_array($key, $hidden, true), ARRAY_FILTER_USE_KEY);
      $accessors = array_filter($accessors, fn ($key) => !in_array($key, $hidden, true), ARRAY_FILTER_USE_KEY);
      $relations = array_filter($relations, fn ($key) => !in_array($key, $hidden, true), ARRAY_FILTER_USE_KEY);

      if (count($visible) > 0) {
        $props = array_filter($props, fn ($key) => in_array($key, $visible, true), ARRAY_FILTER_USE_KEY);
        $accessors = array_filter($accessors, fn ($key) => in_array($key, $visible, true), ARRAY_FILTER_USE_KEY);
        $relations = array_filter($relations, fn ($key) => in_array($key, $visible, true), ARRAY_FILTER_USE_KEY);
      }

      $tsProps = array_map(fn (\PHPStan\Type\Type $prop, string $key) => new TsObjectPropertyType($key, TsTransformer::transform($prop, $scope, $reflectionProvider)), $props, array_keys($props));
      $tsAccessors = array_map(fn (\PHPStan\Type\Type $accessor, string $key) => new TsObjectPropertyType($key, TsTransformer::transform($accessor, $scope, $reflectionProvider), !in_array($key, $appends, true)), $accessors, array_keys($accessors));
      $tsRelations = array_map(fn (\PHPStan\Type\Type $relation, string $key) => new TsObjectPropertyType($key, TsTransformer::transform($relation, $scope, $reflectionProvider), !in_array($key, $appends, true)), $relations, array_keys($relations));
      $tsProps = array_merge($tsProps, $tsAccessors, $tsRelations);

      return (new TsObjectType($tsProps))->setName($type->getClassName());
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
      return 1;
    }

}