<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsIntersectionType;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsIntersectionTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        return $type instanceof \PHPStan\Type\IntersectionType;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsIntersectionType {
        /** @var \PHPStan\Type\IntersectionType $type */
        $types = array_map(fn($type) => TsTransformer::transform($type, $scope, $reflectionProvider), $type->getTypes());

        return new TsIntersectionType($types);
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
}