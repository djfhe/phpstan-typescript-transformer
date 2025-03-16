<?php

namespace djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptParser;

use djfhe\ControllerTransformer\PHPStan\Typescript\_TsTypeParserContract;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\_TsType;

class ArrayableParser implements _TsTypeParserContract
{
    public static function canParse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        if (!$type instanceof \PHPStan\Type\Generic\GenericObjectType) {
            return false;
        }

        if ($type->getClassName() === 'Illuminate\Contracts\Support\Arrayable') {
            return true;
        }

        $reflection = $type->getClassReflection();

        if ($reflection->implementsInterface('Illuminate\Contracts\Support\Arrayable')) {
            return true;
        }

        return true;
    }

    public static function parse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): _TsType {
      /** @var \PHPStan\Type\Generic\GenericObjectType $type */

      $types = $type->getTypes();

      assert(count($types) === 2);

      $keyType = $types[0];
      $valueType = $types[1];

      return _ArrayLikeParserHelper::parse($keyType, $valueType, $scope, $reflectionProvider);
    }

    public static function parsePriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
      return 1;
    }
  
}