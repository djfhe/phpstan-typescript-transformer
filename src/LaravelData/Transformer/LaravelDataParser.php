<?php

namespace djfhe\PHPStanTypescriptTransformer\LaravelData\Transformer;

use djfhe\PHPStanTypescriptTransformer\TsTypeTransformerContract;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use djfhe\PHPStanTypescriptTransformer\TsType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsObjectPropertyType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsObjectType;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\AttributeReflection;
use PHPStan\Type\UnionType;
use PHPStan\Type\VerbosityLevel;
use ReflectionClass;
use ReflectionProperty;
use Spatie\LaravelData\Support\DataClass;
use Spatie\LaravelData\Support\DataConfig;
use Spatie\LaravelData\Support\Lazy\ClosureLazy;

class LaravelDataParser implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {

      if (! $reflectionProvider->hasClass('Spatie\LaravelData\Contracts\BaseData')) {
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

      if (! $reflection->isSubclassOfClass($reflectionProvider->getClass('Spatie\LaravelData\Contracts\BaseData'))) {
        return false;
      }

      return true;
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsObjectType
    {

      

      /** @var \PHPStan\Type\ObjectType $type */

      $reflection = $type->getClassReflection();

      if ($reflection === null) {
        throw new \Exception('Reflection is null');
      }

      /** @var DataConfig $dataConfig */
      $dataConfig = app(DataConfig::class);

      $dataClass = $dataConfig->getDataClass($type->getClassName());

      // TODO: Implement our own Hidden / Optional Attributes
      $isOptional = $dataClass->attributes->has('Spatie\TypeScriptTransformer\Attributes\Optional');
      
      $properties = [];

      /** @var ReflectionClass<\Spatie\LaravelData\Data> $nativeReflection */
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

    protected static function parseProperty(string $name, \PHPStan\Reflection\ExtendedPropertyReflection $property, Scope $scope, ReflectionProvider $reflectionProvider, DataClass $dataClass, bool $isOptional): TsObjectPropertyType|null {
      $attributes = $property->getAttributes();

      // TODO: This could maybe be simplified by using the $dataProperty->attributes as below. Need to check why this is done in the original code
      $isHidden = array_any($attributes, fn (AttributeReflection $attribute) => $attribute->getName() === 'Spatie\TypeScriptTransformer\Attributes\Hidden');

      if ($isHidden) {
          return null;
      }

      $tsType = self::parseLiteralTypescriptAttribute($attributes, $scope, $reflectionProvider);

      /** @var \Spatie\LaravelData\Support\DataProperty $dataProperty */
      $dataProperty = $dataClass->properties[$name];

      $isOptional = $isOptional
          || $dataProperty->attributes->has('Spatie\TypeScriptTransformer\Attributes\Optional')
          || ($dataProperty->type->lazyType !== null && $dataProperty->type->lazyType !== ClosureLazy::class)
          || $dataProperty->type->isOptional;

      $propertyName = $dataProperty->outputMappedName ?? $dataProperty->name;

      if ($tsType === null) {
        dump("Transforming " . $dataClass->name . '->' . $name . " => " . $property->getReadableType()->describe(VerbosityLevel::typeOnly()));


        $tsType = TsTransformer::transform($property->getReadableType(), $scope, $reflectionProvider);
      }

      return new TsObjectPropertyType($propertyName, $tsType, $isOptional);
    }

    /**
     * @param array<int, \PHPStan\Reflection\AttributeReflection> $attributes
     */
    protected static function parseLiteralTypescriptAttribute(array $attributes, Scope $scope, ReflectionProvider $reflectionProvider): ?TsType {
      if (count($attributes) === 0) {
        return null;
      }

      /**
       * @var \PHPStan\Reflection\AttributeReflection[]
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