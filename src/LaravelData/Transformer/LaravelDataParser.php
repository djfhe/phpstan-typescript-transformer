<?php

namespace djfhe\StanScript\LaravelData\Transformer;

use djfhe\StanScript\TsTypeTransformerContract;
use PHPStan\Type\Type;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use djfhe\StanScript\TsType;
use djfhe\StanScript\Base\Types\TsObjectPropertyType;
use djfhe\StanScript\Base\Types\TsObjectType;
use djfhe\StanScript\TsTransformer;
use PHPStan\Analyser\OutOfClassScope;
use ReflectionProperty;

class LaravelDataParser implements TsTypeTransformerContract
{
    public static function canTransform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): bool {

      if (!$reflectionProvider->hasClass('Spatie\LaravelData\Data')) {
        return false;
      }

      if (! $type instanceof \PHPStan\Type\ObjectType) {
        return false;
      }

      if (!$type->isEnum()->no()) {
        return false;
      }

      $reflection = $type->getClassReflection();

      if ($reflection === null) {
        return false;
      }

      if (!$reflection->isSubclassOfClass($reflectionProvider->getClass('Spatie\LaravelData\Data'))) {
        return false;
      }

      return true;
    }

    /**
     * @var ?string[]
     */
    protected static ?array $laravelDataInternalPropertiesCache = null;

    /**
     * @return string[]
     */
    protected static function getLaravelDataInternalProperties(ReflectionProvider $provider): array
    {
      if (self::$laravelDataInternalPropertiesCache !== null) {
        return self::$laravelDataInternalPropertiesCache;
      }

      $reflection = $provider->getClass('Spatie\LaravelData\Data');

      $propertyNames = array_map(fn(ReflectionProperty $property) => $property->getName(), $reflection->getNativeReflection()->getProperties());
      self::$laravelDataInternalPropertiesCache = $propertyNames;

      return self::$laravelDataInternalPropertiesCache;
    }

    /**
     * @param string[] $propertyNames
     * @return string[]
     */
    protected static function filterLaravelDataInternalProperties(array $propertyNames, ReflectionProvider $reflectionProvider): array {
      $internalProperties = self::getLaravelDataInternalProperties($reflectionProvider);

      return array_filter($propertyNames, fn(string $name) => !in_array($name, $internalProperties, true));
    }

    public static function transform(Type $type, Scope $scope, ReflectionProvider $reflectionProvider): TsObjectType
    {
      /** @var \PHPStan\Type\ObjectType $type */

      $reflection = $type->getClassReflection();

      if ($reflection === null) {
        throw new \Exception('Reflection is null');
      }

      $properties = [];
      
      $nativePropertyNames = array_map(fn(ReflectionProperty $property) => $property->getName(), $reflection->getNativeReflection()->getProperties());
      $nativePropertyNames = self::filterLaravelDataInternalProperties($nativePropertyNames, $reflectionProvider);
      $accessScope = new OutOfClassScope();
      foreach ($nativePropertyNames as $name) {
        $property = $reflection->getProperty($name, $accessScope);
        $properties[] = self::parseProperty($name, $property, $scope, $reflectionProvider);
      }

      $properties = self::mapPropertyNames($properties, $type, $scope, $reflectionProvider);

      $parsed = new TsObjectType($properties);
      $parsed->setName($type->getClassName());

      return $parsed;
    }

    /**
     * @param array<TsObjectPropertyType> $properties
     * @return array<TsObjectPropertyType>
     */
    protected static function mapPropertyNames(array $properties, \PHPStan\Type\ObjectType $type, Scope $scope, ReflectionProvider $reflectionProvider): array {
      $attributes = $type->getClassReflection()?->getAttributes() ?? [];
      $nameMapper = self::parseNameMappingAttribute($attributes, $scope, $reflectionProvider);
      
      if ($nameMapper === null) {
        return $properties;
      }

      foreach ($properties as $property) {
        $property->key = $nameMapper($property->key);
      }

      return $properties;
    }

    protected static function parseProperty(String $name, \PHPStan\Reflection\ExtendedPropertyReflection $property, Scope $scope, ReflectionProvider $reflectionProvider): TsObjectPropertyType {
      $attributes = $property->getAttributes();
      $type = self::parseLiteralTypescriptAttribute($attributes, $scope, $reflectionProvider);
      $nameMapper = self::parseNameMappingAttribute($attributes, $scope, $reflectionProvider);

      if ($nameMapper !== null) {
        $name = $nameMapper($name);
      }

      if ($type !== null) {
        return new TsObjectPropertyType($name, $type);
      }

      $tsType = TsTransformer::transform($property->getReadableType(), $scope, $reflectionProvider);

      return new TsObjectPropertyType($name, $tsType);
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

    /**
     * @var string[]
     */
    protected static array $laravelDataAttributeNameMapper = [
      'Spatie\LaravelData\Attributes\MapName',
      'Spatie\LaravelData\Attributes\MapInputName',
      'Spatie\LaravelData\Attributes\MapOutputName',
    ];

    /**
     * @param \PHPStan\Reflection\AttributeReflection[] $attributes
     * @return ?\Closure(string): string
     */
    protected static function parseNameMappingAttribute(array $attributes, Scope $scope, ReflectionProvider $reflectionProvider): ?\Closure
    {
      if (count($attributes) === 0) {
        return null;
      }

      /**
       * @var \PHPStan\Reflection\AttributeReflection[]
       */
      $mapInputNameAttribute = array_filter($attributes, fn($attribute) => in_array($attribute->getName(), self::$laravelDataAttributeNameMapper, true));

      if (count($mapInputNameAttribute) === 0) {
        return null;
      }

      $first = $mapInputNameAttribute[0];

      $arg = $first->getArgumentTypes()['output'] ?? $first->getArgumentTypes()['input'] ?? null;

      assert ($arg !== null);

      if ($arg instanceof \PHPStan\Type\Constant\ConstantStringType) {
        $argValue = $arg->getValue();

        if ($argValue === 'Spatie\LaravelData\Mappers\CamelCaseMapper') {
          return fn(string $name) => lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
        }

        if ($argValue === 'Spatie\LaravelData\Mappers\LowerCaseMapper') {
          return fn(string $name) => strtolower($name);
        }

        if ($argValue === 'Spatie\LaravelData\Mappers\UpperCaseMapper') {
          return fn(string $name) => strtoupper($name);
        }

        if ($argValue === 'Spatie\LaravelData\Mappers\SnakeCaseMapper') {
          return function (string $name) {
            $snake = preg_replace('/([a-z])([A-Z])/', '$1_$2', $name); // lower followed by upper

            if ($snake === null) {
              return $name;
            }

            $snake = preg_replace('/([A-Z])([A-Z][a-z])/', '$1_$2', $snake); // acronym followed by normal word
            
            if ($snake === null) {
              return $name;
            }

            // Convert to lowercase
            $snake = strtolower($snake);

            return $snake;
          };
        }

        if ($argValue === 'Spatie\LaravelData\Mappers\StudlyCaseMapper') {
          return fn(string $name) => str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
      }
      }

      if ($arg instanceof \PHPStan\Type\ObjectType && $arg->getClassName() === 'Spatie\LaravelData\Mappers\ProvidedNameMapper') {
        $mapTo = $arg->getClassReflection()?->getNativeProperty('name');

        if ($mapTo === null) {
          return null;
        }

        $mapToValue = $mapTo->getReadableType();

        if ($mapToValue instanceof \PHPStan\Type\Constant\ConstantStringType) {
          return fn(string $name) => $mapToValue->getValue();
        }

        return null;
      }

      return null;
    }

    public static function transformPriority(Type $type, Scope $scope, ReflectionProvider $reflectionProvider, array $candidates): int
    {
      return 1;
    }
}