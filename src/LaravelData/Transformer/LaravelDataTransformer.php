<?php

namespace djfhe\PHPStanTypescriptTransformer\LaravelData\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use PHPStan\Reflection\AttributeReflection;
use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use djfhe\PHPStanTypescriptTransformer\TsType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsObjectPropertyType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsObjectType;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use PHPStan\Analyser\OutOfClassScope;
use ReflectionProperty;

class LaravelDataTransformer implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {

      if (! $reflectionProvider->hasClass('Spatie\LaravelData\Contracts\BaseData')) {
        return false;
      }

      if (! $type instanceof ObjectType) {
        return false;
      }

      if (! $type->isEnum()->no()) {
        return false;
      }

      $reflection = $type->getClassReflection();

      if ($reflection === null) {
        return false;
      }

      if (! $reflection->isSubclassOfClass($reflectionProvider->getClass('Spatie\LaravelData\Contracts\BaseData'))) {
        return false;
      }

      return true;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsObjectType
    {
      /** @var ObjectType $type */
      $reflection = $type->getClassReflection();

      if ($reflection === null) {
        throw new \Exception('Reflection is null');
      }

      /**
       * @var \Spatie\LaravelData\Support\DataConfig $dataConfig
       * @phpstan-ignore function.notFound (This is available in the Laravel application)
       */
      $dataConfig = app(\Spatie\LaravelData\Support\DataConfig::class);

      $dataClass = $dataConfig->getDataClass($type->getClassName());

      // Check if the entire class is marked as optional
      $isOptional = $dataClass->attributes->has('Spatie\TypeScriptTransformer\Attributes\Optional');
      
      $properties = [];

      $nativeReflection = $reflection->getNativeReflection();
      
      $nativeProperties = array_filter(
          $nativeReflection->getProperties(ReflectionProperty::IS_PUBLIC),
          fn (ReflectionProperty $property) => ! $property->isStatic()
      );

      $accessScope = new OutOfClassScope();

    
      foreach ($nativeProperties as $nativeProperty) {
        $name = $nativeProperty->getName();

        $property = $reflection->getProperty($name, $accessScope);

        $parsedProperty = self::parseProperty($name, $property, $scope, $reflectionProvider, $dataClass, $isOptional);

        if ($parsedProperty !== null) {
          $properties[] = $parsedProperty;
        } 
      }

      $parsed = new TsObjectType($properties);
      $parsed->setName($type->getClassName());

      return $parsed;
    }

    protected static function parseProperty(string $name, ExtendedPropertyReflection $property, Scope $scope, ReflectionProvider $reflectionProvider, \Spatie\LaravelData\Support\DataClass $dataClass, bool $isOptional): TsObjectPropertyType|null {
      $attributes = $property->getAttributes();

      /** @var \Spatie\LaravelData\Support\DataProperty $dataProperty */
      $dataProperty = $dataClass->properties[$name];

      // FIXME: Move all TypeScriptTransformer related handling into a generic implementation for TypeScriptTransformer in general, once implemented
      $isHidden = $dataProperty->attributes->has('Spatie\TypeScriptTransformer\Attributes\Hidden');

      if ($isHidden) {
          return null;
      }

      $tsType = self::parseLiteralTypescriptAttribute($attributes, $scope, $reflectionProvider);

      $isOptional = $isOptional
          || $dataProperty->attributes->has('Spatie\TypeScriptTransformer\Attributes\Optional')
          || ($dataProperty->type->lazyType !== null && $dataProperty->type->lazyType !== 'Spatie\LaravelData\Support\ClosureLazy')
          || $dataProperty->type->isOptional;

      $propertyName = $dataProperty->outputMappedName ?? $dataProperty->name;

      if ($tsType === null) {
        $tsType = TsTransformer::transform($property->getReadableType(), $scope, $reflectionProvider);
      }

      return new TsObjectPropertyType($propertyName, $tsType, $isOptional);
    }

    /**
     * @param array<int, AttributeReflection> $attributes
     */
    protected static function parseLiteralTypescriptAttribute(array $attributes, Scope $scope, ReflectionProvider $reflectionProvider): ?TsType {
      if (count($attributes) === 0) {
        return null;
      }

      /**
       * @var AttributeReflection[] $literalTypescriptAttribute
       */
      $literalTypescriptAttribute = array_filter($attributes, fn($attribute) => $attribute->getName() === 'Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType');

      if (count($literalTypescriptAttribute) === 0) {
        return null;
      }

      $first = $literalTypescriptAttribute[0];

      $arg = $first->getArgumentTypes()['typeScript'] ?? null;

      assert($arg !== null);

      return TsTransformer::transform($arg, $scope, $reflectionProvider);
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int
    {
      return 1;
    }
}