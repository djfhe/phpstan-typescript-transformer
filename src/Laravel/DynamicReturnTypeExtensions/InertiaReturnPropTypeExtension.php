<?php

namespace djfhe\PHPStanTypescriptTransformer\Laravel\DynamicReturnTypeExtensions;

use djfhe\PHPStanTypescriptTransformer\Laravel\PhpstanTypes\InertiaOptionalPropType;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Type;

class InertiaReturnPropTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function getClass(): string {
      // @phpstan-ignore return.type
      return 'Inertia\Inertia';
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool {
      $name = $methodReflection->getName();
      return $name === 'lazy' || $name === 'always' || $name === 'optional' || $name === 'defer';
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type {
      $arg = $methodCall->getArgs()[0];
      $type = $scope->getType($arg->value);

      $optional = $methodReflection->getName() === 'optional' || $methodReflection->getName() === 'defer' || $methodReflection->getName() === 'lazy';
      if ($type instanceof \PHPStan\Type\ClosureType) {
        $type = $type->getReturnType();
      }

      if (!$optional) {
        return $type;
      }

      return new InertiaOptionalPropType($type);
    }

}