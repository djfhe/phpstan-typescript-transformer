<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsNeverType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsRecordType;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsClosureTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        return $type instanceof \PHPStan\Type\ClosureType;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsRecordType {
        return new TsRecordType(new TsNeverType(), new TsNeverType());
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
}