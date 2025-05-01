<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\Base\PhpstanTypes\LiteralTypescriptType;
use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\TsType;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsLiteralTypescriptTypeTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        return $type instanceof LiteralTypescriptType;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsType {
        /** @var LiteralTypescriptType $type */
        return $type->tsType();
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
}