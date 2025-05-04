<?php

namespace djfhe\PHPStanTypescriptTransformer\Laravel\DynamicReturnTypeExtensions;

use djfhe\PHPStanTypescriptTransformer\Laravel\PhpstanTypes\InertiaReturnType;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Type;

class InertiaRenderReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{ 
  public function getClass(): string {
    // @phpstan-ignore return.type
    return 'Inertia\Inertia';
  }

  public function isStaticMethodSupported(MethodReflection $methodReflection): bool {
    return $methodReflection->getName() === 'render';
  }

  public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, Scope $scope): ?Type {
    $args = $methodCall->getArgs();

    if (count($args) === 0) {
      return null;
    }

    $sitePathType = InertiaReturnTypeParserHelper::parseSitePath($args[0], $scope);

    if ($sitePathType === null) {
      return null;
    }

    if (count($args) === 1) {
      return new InertiaReturnType(
        sitePath: $sitePathType,
        props: null,
      );
    }

    return new InertiaReturnType(
      sitePath: $sitePathType,
      props: InertiaReturnTypeParserHelper::parseInertiaArg($args[1], $scope),
    );
  }

}