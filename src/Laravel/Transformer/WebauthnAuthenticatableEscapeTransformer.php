<?php

namespace djfhe\PHPStanTypescriptTransformer\Laravel\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsScalarType;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Type;

class WebauthnAuthenticatableEscapeTransformer implements TsTypeTransformerContract
{
  /**
   * @var array<string>
   */
  private static array $escapedTypes = [
    'Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable',
  ];

    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool
    {
      if (!$type instanceof \PHPStan\Type\ObjectType) {
        return false;
      }

      return in_array($type->getClassName(), self::$escapedTypes, true);
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsScalarType
    {
      return new TsScalarType('unknown');
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
      return 10;
    }

  }