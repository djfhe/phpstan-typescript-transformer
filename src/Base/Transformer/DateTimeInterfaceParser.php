<?php

namespace djfhe\StanScript\Base\Transformer;

use djfhe\StanScript\_TsTypeTransformerContract;
use djfhe\StanScript\Base\Types\TsScalarType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;

class DateTimeInterfaceParser implements _TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        if (!$type instanceof \PHPStan\Type\ObjectType) {
            return false;
        }

        $reflection = $type->getClassReflection();

        if ($reflection === null) {
            return false;
        }

        if (!$reflection->implementsInterface('DateTimeInterface')) {
            return false;
        }

        return true;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsScalarType {
        return new TsScalarType('string');
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
  
}