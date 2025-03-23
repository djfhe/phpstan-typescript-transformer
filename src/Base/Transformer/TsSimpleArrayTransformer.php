<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsSimpleArrayType;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Accessory\AccessoryArrayListType;
use PHPStan\Type\ArrayType;

/**
 * A simple homogeneous array type. For example: `string[]`, `number[]`, `(string | number)[]`, `never[]`, etc.
 */
class TsSimpleArrayTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        if (!$type instanceof ArrayType && !$type instanceof AccessoryArrayListType) {
            return false;
        }

        if ($type instanceof ArrayType) {
          $keyType = $type->getKeyType();
        } else {
          $keyType = $type->getIterableKeyType();
        }

        return $keyType instanceof \PHPStan\Type\IntegerType;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsSimpleArrayType {
      /** @var ArrayType|AccessoryArrayListType $type */
      
      if ($type instanceof ArrayType) {
        $valueType = $type->getItemType();
      } else {
        $valueType = $type->getIterableValueType();
      }
      
      return new TsSimpleArrayType(TsTransformer::transform($valueType, $scope, $reflectionProvider));
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
      return 0;
    }
}