<?php

namespace djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes;

use djfhe\ControllerTransformer\PHPStan\Typescript\_TsTypeParserContract;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsScalarType extends _TsType implements _TsTypeParserContract
{
    
    public function __construct(
        protected string $value
    ) {}

    public static function canParse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool
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


    public static function parse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): _TsType {
        return match (true) {
            $type instanceof \PHPStan\Type\Constant\ConstantStringType => new self("'{$type->getValue()}'"),
            $type instanceof \PHPStan\Type\Constant\ConstantIntegerType => new self((string) $type->getValue()),
            $type instanceof \PHPStan\Type\Constant\ConstantFloatType => new self((string) $type->getValue()),
            $type instanceof \PHPStan\Type\Constant\ConstantBooleanType => new self($type->getValue() ? 'true' : 'false'),
            $type instanceof \PHPStan\Type\StringType => new self('string'),
            $type instanceof \PHPStan\Type\IntegerType => new self('number'),
            $type instanceof \PHPStan\Type\FloatType => new self('number'),
            $type instanceof \PHPStan\Type\BooleanType => new self('boolean'),
            $type instanceof \PHPStan\Type\NullType => new self('null'),
        };
    }

    public static function parsePriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }

    public function toTypeDefinition(bool $inline): string
    {
        return $this->value;
    }

    protected function _serialize(): array
    {
        return [
            'value' => $this->value
        ];
    }

    protected static function _deserialize(array $data): static
    {
        return new self($data['value']);
    }

    protected function getChildren(): array
    {
        return [];
    }
}