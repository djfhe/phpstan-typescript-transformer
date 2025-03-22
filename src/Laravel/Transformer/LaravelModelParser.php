<?php

namespace djfhe\PHPStanTypescriptTransformer\Laravel\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsScalarType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;
use ReflectionClass;
use ReflectionProperty;

class LaravelModelParser implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool
    {
      if (!$reflectionProvider->hasClass('Illuminate\Database\Eloquent\Model')) {
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

      $modelClass = $reflectionProvider->getClass('Illuminate\Database\Eloquent\Model');
      
      return $reflection->isSubclassOfClass($modelClass);
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsScalarType
    {
      /** @var \PHPStan\Type\ObjectType $type */

      if ($type->getClassName() !== 'App\Models\User') {
        return new TsScalarType('unknown');
      }

      $classReflection = $type->getClassReflection();

      if ($classReflection === null) {
        return new TsScalarType('unknown');
      }

      // $nativeReflection = $type->getClassReflection()?->getNativeReflection();

      // if (! $nativeReflection instanceof ReflectionClass) {
      //   return new TsScalarType('unknown');
      // }

      // $properties = $nativeReflection->getProperties(ReflectionProperty::IS_PUBLIC);

      // $properties = array_filter($properties, function (ReflectionProperty $property) {
      //   return ! $property->isStatic();
      // });

      /**
       * Returns the PHPDoc at the top of the class
       */
      //dd( $type->getClassReflection()->getResolvedPhpDoc()->getPhpDocString());

      // He knows that "name" exists....
      //dd($type->hasProperty('name'), $type->getClassReflection()->hasProperty('name'));


      //TODO: Implement parsing of Laravel models
      return new TsScalarType('unknown');
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
      return 0;
    }

}