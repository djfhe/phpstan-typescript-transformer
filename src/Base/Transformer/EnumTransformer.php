<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsType;
use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsLiteralType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsUnionType;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Enum\EnumCaseObjectType;
use PHPStan\Type\Type;

class EnumTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
      if (!$type instanceof \PHPStan\Type\ObjectType) {
        return false;
      }

      return $type->isEnum()->yes();
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsType {
      assert($type instanceof \PHPStan\Type\ObjectType);

      $cases = $type->getEnumCases();

      /** @var list<TsType> $tsCases */
      $tsCases = [];
      
      for ($i = 0; $i < count($cases); $i++) {
        $backingType = $cases[$i]->getBackingValueType();
        $tsType = $backingType !== null ? TsTransformer::transform($backingType, $scope, $reflectionProvider) : new TsLiteralType((string) $i);
        $tsCases[] = $tsType;
      }

      if (count($tsCases) === 1) {
          $tsType = $tsCases[0];
      } else {
          $tsType = new TsUnionType($tsCases);
      }

      // If this is a single enum case, we do not want it to be the globally named type for this enum class
      if ($type instanceof EnumCaseObjectType) {
          return $tsType;
      }

      // This represents all cases of the enum, so we name the type to reuse and reference it
      return $tsType->setName($type->getClassName());
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
}