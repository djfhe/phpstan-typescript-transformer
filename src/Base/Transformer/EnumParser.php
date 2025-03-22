<?php

namespace djfhe\StanScript\Base\Transformer;

use djfhe\StanScript\TsTypeTransformerContract;
use djfhe\StanScript\Base\Types\TsScalarType;
use djfhe\StanScript\Base\Types\TsUnionType;
use djfhe\StanScript\TsTransformer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;

class EnumParser implements TsTypeTransformerContract
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