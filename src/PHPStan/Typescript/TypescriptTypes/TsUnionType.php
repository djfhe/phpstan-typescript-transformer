<?php

namespace djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes;

use djfhe\ControllerTransformer\PHPStan\Typescript\_TsTypeParserContract;
use djfhe\ControllerTransformer\PHPStan\Typescript\TsNeverType;
use djfhe\ControllerTransformer\PHPStan\Typescript\TsTypeParser;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsUnionType extends _TsType implements _TsTypeParserContract
{
    public function __construct(
        /** @var _TsType[] */
        protected array $types
    ) {}

    public function toTypeDefinition(bool $inline): string
    {
        return '(' . implode(' | ', array_map(fn(_TsType $type) => $type->toTypeString($inline), $this->types)) . ')';
    }

    public static function canParse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        return $type instanceof \PHPStan\Type\UnionType;
    }

    public static function parse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): _TsType {
        /** @var \PHPStan\Type\UnionType $type */
        $types = array_map(fn($type) => TsTypeParser::parse($type, $scope, $reflectionProvider), $type->getTypes());

        $types = array_filter($types, fn($type) => ! $type instanceof TsNeverType);

        return new self($types);
    }

    public static function parsePriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }

    protected function _serialize(): array
    {
        return [
            'types' => array_map(fn(_TsType $type) => $type->serialize(), $this->types)
        ];
    }

    protected static function _deserialize(array $data): static
    {
        return new self(array_map(fn($type) => _TsType::deserialize($type), $data['types']));
    }

    protected function getChildren(): array
    {
        return $this->types;
    }
}