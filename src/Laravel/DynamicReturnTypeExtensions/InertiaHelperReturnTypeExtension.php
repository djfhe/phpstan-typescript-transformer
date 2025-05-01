<?php

namespace djfhe\PHPStanTypescriptTransformer\Laravel\DynamicReturnTypeExtensions;

use djfhe\PHPStanTypescriptTransformer\Laravel\PhpstanTypes\InertiaReturnType;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\DynamicFunctionReturnTypeExtension;
use PHPStan\Type\Type;

class InertiaHelperReturnTypeExtension implements DynamicFunctionReturnTypeExtension
{
  public function isFunctionSupported(FunctionReflection $functionReflection): bool {
    return $functionReflection->getName() === 'inertia';
  }

  public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): ?Type {
    $args = $functionCall->getArgs();

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