<?php

namespace djfhe\PHPStanTypescriptTransformer;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;

interface TsTypeTransformerContract {
  public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool;
  
  public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsType;

  /**
   * @param class-string<TsTypeTransformerContract>[] $candidates
   */
  public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int;
}