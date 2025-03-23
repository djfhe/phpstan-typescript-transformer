<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsIntersectionType;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Accessory\AccessoryType;
use RuntimeException;

class TsIntersectionTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        return $type instanceof \PHPStan\Type\IntersectionType;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsIntersectionType {
        /** @var \PHPStan\Type\IntersectionType $type */

        // Accessory Types cannot be rendered on their own.
        // PHPStan uses these in an intersection type to add more detailed info. E.g. the `list` type
        // is an intersection of an array type and the accessory list type.
        // @phpstan-ignore phpstanApi.interface
        $intersectionTypes = array_filter($type->getTypes(), fn (Type $intersectionTypes) => ! $intersectionTypes instanceof AccessoryType);

        if (count($intersectionTypes) === 0) {
            throw new RuntimeException('Intersection does not have any types. This should not happen!');
        }

        $types = array_map(fn($type) => TsTransformer::transform($type, $scope, $reflectionProvider), $intersectionTypes);

        return new TsIntersectionType($types);
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
}