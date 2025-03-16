<?php

namespace djfhe\ControllerTransformer\PHPStan\Typescript;

use djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes\_TsType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;

interface _TsTypeParserContract {
  public static function canParse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool;
  
  public static function parse(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): _TsType;

  /**
   * @param _TsType[] $candidates
   */
  public static function parsePriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int;
}