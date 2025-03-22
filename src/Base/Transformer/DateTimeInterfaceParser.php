<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsScalarType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;

class DateTimeInterfaceParser implements TsTypeTransformerContract
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