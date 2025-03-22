<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsScalarType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsUnionType;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;

class EnumTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {

      if (!$type instanceof \PHPStan\Type\ObjectType) {
        return false;
      }

      if ($type->isEnum()->no()) {
        return false;
      }

      return true;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsUnionType {

      /** @var \PHPStan\Type\ObjectType $type */

      $cases = $type->getEnumCases();

      $tsCases = [];
      
      for ($i = 0; $i < count($cases); $i++) {
        $backingType = $cases[$i]->getBackingValueType();
        $tsType = $backingType !== null ? TsTransformer::transform($backingType, $scope, $reflectionProvider) : new TsScalarType((string) $i);
        $tsCases[] = $tsType;
      }

      return (new TsUnionType($tsCases))->setName($type->getClassName());
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
}