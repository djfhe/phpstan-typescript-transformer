<?php

namespace djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptParser;

use djfhe\ControllerTransformer\PHPStan\Typescript\_TsTypeParserContract;
use djfhe\ControllerTransformer\PHPStan\Typescript\TsTypeParser;
use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\_TsType;
use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\TsScalarType;
use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\TsUnionType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;

class EnumParser implements _TsTypeParserContract
{
    public static function canParse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
      if (!$type->isEnum()->yes()) {
        return false;
      }

      return $type instanceof \PHPStan\Type\ObjectType;
    }

    public static function parse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): _TsType {

      /** @var \PHPStan\Type\ObjectType $type */

      $cases = $type->getEnumCases();

      $tsCases = [];
      
      for ($i = 0; $i < count($cases); $i++) {
        $backingType = $cases[$i]->getBackingValueType();
        $tsType = $backingType !== null ? TsTypeParser::parse($backingType, $scope, $reflectionProvider) : new TsScalarType((string) $i);
        $tsCases[] = $tsType;
      }

      return (new TsUnionType($tsCases))->setIdentifier($type->getClassName());
    }

    public static function parsePriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
        return 0;
    }
}