<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsTupleType;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;

class TsTupleTransformer implements TsTypeTransformerContract
{

    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {
      if (!$type instanceof \PHPStan\Type\Constant\ConstantArrayType) {
        return false;
      }

      $keyTypes = $type->getKeyTypes();

      if (count($keyTypes) === 0) {
        return false;
      }

      foreach ($keyTypes as $keyType) {
        if (!$keyType instanceof \PHPStan\Type\Constant\ConstantIntegerType) {
          return false;
        }
      }

      /** @var \PHPStan\Type\Constant\ConstantIntegerType[] $keyTypes */
      
      usort($keyTypes, fn($a, $b) => $a->getValue() - $b->getValue());

      for ($i = 0; $i < count($keyTypes); $i++) {
        if ($keyTypes[$i]->getValue() !== $i) {
          return false;
        }
      }

      return true;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsTupleType {
      /** @var \PHPStan\Type\Constant\ConstantArrayType $type */
      
      $types = [];


      /** @var \PHPStan\Type\Constant\ConstantIntegerType[] */
      $keyTypes = $type->getKeyTypes();
      $values = $type->getValueTypes();

      $items = array_map(fn($key, $value) => [$key, $value], $keyTypes, $values);

      usort($items, fn($a, $b) => $a[0]->getValue() - $b[0]->getValue());

      foreach ($items as $item) {
        $types[] = TsTransformer::transform($item[1], $scope, $reflectionProvider);
      }

      return new TsTupleType($types);
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int {
      return 0;
    }
}