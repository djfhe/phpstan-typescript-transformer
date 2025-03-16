<?php

namespace djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptParser;

use djfhe\ControllerTransformer\PHPStan\Typescript\_TsTypeParserContract;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\_TsType;
use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\TsScalarType;

class LaravelModelParser implements _TsTypeParserContract
{
    public static function canParse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool
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

    public static function parse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): _TsType
    {
      /** @var \PHPStan\Type\ObjectType $type */

      //TODO: Implement parsing of Laravel models
      return new TsScalarType('unknown');
    }

    public static function parsePriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
      return 0;
    }

}