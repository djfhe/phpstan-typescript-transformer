<?php

namespace djfhe\StanScript\Base\Transformer;

use djfhe\StanScript\TsTypeTransformerContract;
use djfhe\StanScript\Base\Types\TsNeverType;
use djfhe\StanScript\PHPStan\TsTypeParser;
use djfhe\StanScript\TsType;
use djfhe\StanScript\Base\Types\TsUnionType;
use djfhe\StanScript\TsTransformer;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsUnionTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        return $type instanceof \PHPStan\Type\UnionType;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsType {
        /** @var \PHPStan\Type\UnionType $type */
        $types = array_map(fn($type) => TsTransformer::transform($type, $scope, $reflectionProvider), $type->getTypes());

        $types = array_filter($types, fn($type) => ! $type instanceof TsNeverType);

        return new TsUnionType($types);
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
}