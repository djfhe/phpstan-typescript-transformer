<?php

namespace djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptParser;

use djfhe\ControllerTransformer\PHPStan\Typescript\_TsTypeParserContract;
use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\_TsType;
use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\TsScalarType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;

class DateTimeInterfaceParser implements _TsTypeParserContract
{
    public static function canParse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
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

    public static function parse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): _TsType {
        return new TsScalarType('string');
    }

    public static function parsePriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
  
}