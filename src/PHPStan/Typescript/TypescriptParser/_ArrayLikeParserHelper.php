<?php

namespace djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptParser;

use djfhe\ControllerTransformer\PHPStan\Typescript\TsTypeParser;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\_TsType;
use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\TsRecordType;
use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\TsSimpleArrayType;

class _ArrayLikeParserHelper
{
    public static function parse(Type $keyType, Type $valueType, Scope $scope, ReflectionProvider $reflectionProvider): _TsType
    {
        if ($keyType instanceof \PHPStan\Type\IntegerType) {
            return new TsSimpleArrayType(TsTypeParser::parse($valueType, $scope, $reflectionProvider));
        }

        return new TsRecordType(TsTypeParser::parse($keyType, $scope, $reflectionProvider), TsTypeParser::parse($valueType, $scope, $reflectionProvider));
    }
}