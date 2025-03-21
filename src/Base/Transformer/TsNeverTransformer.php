<?php

namespace djfhe\StanScript\Base\Transformer;

use djfhe\StanScript\_TsTypeTransformerContract;
use djfhe\StanScript\Base\Types\TsNeverType;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsNeverTransformer implements _TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        return $type instanceof \PHPStan\Type\NeverType;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsNeverType {
        return new TsNeverType();
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
}