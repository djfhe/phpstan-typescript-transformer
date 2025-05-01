<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\Laravel\PhpstanTypes\InertiaOptionalPropType;
use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use djfhe\PHPStanTypescriptTransformer\TsType;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class InertiaOptionalPropTypeTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        return $type instanceof InertiaOptionalPropType;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsType {
      /** @var InertiaOptionalPropType $type */

      return TsTransformer::transform($type->getPropType(), $scope, $reflectionProvider)->optional();
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 1;
    }
}