<?php

namespace djfhe\ControllerTransformer\PHPStan\Typescript;

use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\_TsType;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsNeverType extends _TsType implements _TsTypeParserContract
{
    public function toTypeDefinition(bool $inline): string
    {
        return 'never';
    }

    public static function canParse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
        return $type instanceof \PHPStan\Type\NeverType;
    }

    public static function parse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): _TsType {
        return new self();
    }

    public static function parsePriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }

    protected function _serialize(): array
    {
        return [];
    }

    protected static function _deserialize(array $data): static
    {
        return new self();
    }

    protected function getChildren(): array
    {
        return [];
    }
}