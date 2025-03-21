<?php

namespace djfhe\StanScript\Base\Transformer;

use djfhe\StanScript\_TsTypeTransformerContract;
use djfhe\StanScript\Base\Types\TsIntersectionType;
use djfhe\StanScript\TsTransformer;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsIntersectionTransformer implements _TsTypeTransformerContract
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