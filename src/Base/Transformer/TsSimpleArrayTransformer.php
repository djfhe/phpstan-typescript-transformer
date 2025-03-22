<?php

namespace djfhe\StanScript\Base\Transformer;

use djfhe\StanScript\TsTypeTransformerContract;
use djfhe\StanScript\Base\Types\TsSimpleArrayType;
use djfhe\StanScript\TsTransformer;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

/**
 * A simple homogeneous array type. For example: `string[]`, `number[]`, `(string | number)[]`, `never[]`, etc.
 */
class TsSimpleArrayTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        if (!$type instanceof \PHPStan\Type\ArrayType) {
            return false;
        }

        $keyType = $type->getKeyType();

        return $keyType instanceof \PHPStan\Type\IntegerType;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsSimpleArrayType {
      /** @var \PHPStan\Type\ArrayType $type */

      $valueType = $type->getItemType();
      
      return new TsSimpleArrayType(TsTransformer::transform($valueType, $scope, $reflectionProvider));
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
      return 0;
    }
}