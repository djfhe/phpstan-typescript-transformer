<?php

namespace djfhe\StanScript\Base\Transformer;

use djfhe\StanScript\_TsTypeTransformerContract;
use djfhe\StanScript\_TsType;
use djfhe\StanScript\Base\Types\TsRecordType;
use djfhe\StanScript\TsTransformer;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsAssocArrayTransformer implements _TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool
    {
      if (! $type instanceof \PHPStan\Type\ArrayType) {
        return false;
      }

      if ($type instanceof \PHPStan\Type\Constant\ConstantArrayType) {
        return false;
      }

      $keyType = $type->getKeyType();

      return ! $keyType instanceof \PHPStan\Type\IntegerType;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): _TsType {
      /** @var \PHPStan\Type\ArrayType $type */

      $keyType = $type->getKeyType();
      $valueType = $type->getItemType();
      
      return new TsRecordType(TsTransformer::transform($keyType, $scope, $reflectionProvider), TsTransformer::transform($valueType, $scope, $reflectionProvider));
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
      return 0;
    }
}