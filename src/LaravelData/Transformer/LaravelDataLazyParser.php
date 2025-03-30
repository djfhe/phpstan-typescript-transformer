<?php

namespace djfhe\PHPStanTypescriptTransformer\LaravelData\Transformer;

use djfhe\PHPStanTypescriptTransformer\Base\Types\TsNeverType;
use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;


class LaravelDataLazyParser implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {

      if (! $reflectionProvider->hasClass('Spatie\LaravelData\Lazy')) {
        return false;
      }

      if (! $type instanceof \PHPStan\Type\ObjectType) {
        return false;
      }

      if (! $type->isEnum()->no()) {
        return false;
      }
      
      $reflection = $type->getClassReflection();

      if ($reflection === null) {
        return false;
      }

      if (! $reflection->is('Spatie\LaravelData\Lazy')) {
        return false;
      }

      return true;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsNeverType
    {
      return new TsNeverType();
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int
    {
      return 1;
    }
}