<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\TsType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsRecordType;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;

class TsAssocArrayTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool
    {
      if (! $type instanceof \PHPStan\Type\ArrayType) {
        return false;
      }

      return $type->getKeyType()->isInteger()->no();
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsType {
      /** @var \PHPStan\Type\ArrayType $type */

      $keyType = $type->getKeyType();

      // Mixed should not result in unknown in a record.
      // Thus we convert it to the union of string and int.
      // These are the only possible key types that PHP produces.
      if ($keyType instanceof MixedType) {
        $keyType = new UnionType([new StringType(), new IntegerType()]);
      }

      $valueType = $type->getItemType();
      
      return new TsRecordType(TsTransformer::transform($keyType, $scope, $reflectionProvider), TsTransformer::transform($valueType, $scope, $reflectionProvider));
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
      return 0;
    }
}