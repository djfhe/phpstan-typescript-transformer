<?php

namespace djfhe\PHPStanTypescriptTransformer\Laravel\DynamicReturnTypeExtensions;

use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;

final class InertiaReturnTypeParserHelper
{
  public static function parseInertiaArg(\PhpParser\Node\Arg $arg, Scope $scope): Type
  {
    $type = $scope->getType($arg->value);

    if (!$type instanceof \PHPStan\Type\ArrayType && !$type instanceof \PHPStan\Type\Constant\ConstantArrayType) {
      return $type;
    }

    if ($type instanceof \PHPStan\Type\ArrayType) {
      $itemTypes = self::mapTypeInCompositeType($type->getItemType(), 
        function (\PHPStan\Type\Type $type) {
          if ($type instanceof \PHPStan\Type\ClosureType) {
            return $type->getReturnType();
          }
          
          return $type;
        }
      );

      return new \PHPStan\Type\ArrayType(
        $type->getKeyType(),
        $itemTypes,
      );
    }

    $valueTypes = array_map(function (\PHPStan\Type\Type $type) {
      if ($type instanceof \PHPStan\Type\ClosureType) {
        return $type->getReturnType();
      }
      return $type;
    }, $type->getValueTypes());

    return new \PHPStan\Type\Constant\ConstantArrayType(
      keyTypes: $type->getKeyTypes(),
      valueTypes: $valueTypes,
      nextAutoIndexes: $type->getNextAutoIndexes(),
      optionalKeys: $type->getOptionalKeys(),
      isList: $type->isList(),
    );
  }

  public static function parseSitePath(\PhpParser\Node\Arg $arg, Scope $scope): ?\PHPStan\Type\StringType
  {
    $type = $scope->getType($arg->value);

    if (!$type instanceof \PHPStan\Type\StringType) {
      return null;
    }

    return $type;
  }

  /**
   * @param \PHPStan\Type\Type $haystack
   * @param \Closure(\PHPStan\Type\Type): \PHPStan\Type\Type $callback
   */
  protected static function mapTypeInCompositeType(\PHPStan\Type\Type $haystack, \Closure $callback): \PHPStan\Type\Type
  {
    if ($haystack instanceof \PHPStan\Type\UnionType) {
      $types = [];
      foreach ($haystack->getTypes() as $type) {
        $types[] = $callback($type);
      }
      return new \PHPStan\Type\UnionType($types);
    }

    if ($haystack instanceof \PHPStan\Type\IntersectionType) {
      $types = [];
      foreach ($haystack->getTypes() as $type) {
        $types[] = $callback($type);
      }
      return new \PHPStan\Type\IntersectionType($types);
    }

    return $callback($haystack);
  }
}