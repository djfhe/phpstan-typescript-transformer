<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsLiteralType;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsScalarTransformer implements TsTypeTransformerContract
{

    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool
    {
        return match (true) {
            $type instanceof \PHPStan\Type\Constant\ConstantStringType => true,
            $type instanceof \PHPStan\Type\Constant\ConstantIntegerType => true,
            $type instanceof \PHPStan\Type\Constant\ConstantFloatType => true,
            $type instanceof \PHPStan\Type\Constant\ConstantBooleanType => true,
            $type instanceof \PHPStan\Type\StringType => true,
            $type instanceof \PHPStan\Type\IntegerType => true,
            $type instanceof \PHPStan\Type\FloatType => true,
            $type instanceof \PHPStan\Type\BooleanType => true,
            $type instanceof \PHPStan\Type\NullType => true,
            default => false,
        };
    }


    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsLiteralType {
        return match (true) {
            $type instanceof \PHPStan\Type\Constant\ConstantStringType => new TsLiteralType("'{$type->getValue()}'"), // escape single ticks in string value
            $type instanceof \PHPStan\Type\Constant\ConstantIntegerType => new TsLiteralType((string) $type->getValue()),
            $type instanceof \PHPStan\Type\Constant\ConstantFloatType => new TsLiteralType((string) $type->getValue()),
            $type instanceof \PHPStan\Type\Constant\ConstantBooleanType => new TsLiteralType($type->getValue() ? 'true' : 'false'),
            $type instanceof \PHPStan\Type\StringType => new TsLiteralType('string'),
            $type instanceof \PHPStan\Type\IntegerType => new TsLiteralType('number'),
            $type instanceof \PHPStan\Type\FloatType => new TsLiteralType('number'),
            $type instanceof \PHPStan\Type\BooleanType => new TsLiteralType('boolean'),
            $type instanceof \PHPStan\Type\NullType => new TsLiteralType('null'),
            default => throw new \InvalidArgumentException('Invalid type'),
        };
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
}