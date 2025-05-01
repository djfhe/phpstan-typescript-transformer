<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\Base\PhpstanTypes\UndefinedType;
use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsUndefinedType;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsUndefinedTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        return $type instanceof UndefinedType;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsUndefinedType {
        return new TsUndefinedType();
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
}