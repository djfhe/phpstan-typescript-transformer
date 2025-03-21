<?php

namespace djfhe\StanScript\Laravel\Transformer;

use djfhe\StanScript\TsTypeTransformerContract;
use djfhe\StanScript\Base\Types\TsScalarType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;

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

      //TODO: Implement parsing of Laravel models
      return new TsScalarType('unknown');
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
      return 0;
    }

}