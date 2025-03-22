<?php

namespace djfhe\PHPStanTypescriptTransformer\TsPrinter;

class NamedTypesRegistry
{

    /**
     * @var array<string, string>
     */
    protected static array $typeNameToIdentifier = [];

    /**
     * @var array<string, string>
     */
    protected static array $identifierToTsName = [];

    public static function getNamedTypeIdentifier(string $name): ?string
    {
      return self::$typeNameToIdentifier[$name] ?? null;
    }

    public static function registerNamedType(string $name): string
    {
      if (array_key_exists($name, self::$typeNameToIdentifier)) {
        return self::$typeNameToIdentifier[$name];
      }

      $identifier = '{%' . uniqid($name . '_'). '%}';

      self::$typeNameToIdentifier[$name] = $identifier;

      return $identifier;
    }

    /**
     * @param 'interface'|'type' $keyword
     * @param string[] $genericKeys
     */
    public static function addNamedType(string $identifier, string $keyword, string $name, string $printedType, array $genericKeys = []): string
    {
      $namespace = TsPrinterUtil::getNamespace($name);
      $name = TsPrinterUtil::getName($name);

      TsPrinter::$namespaceCollectionPrinter->addTsTypePrinter(new TsRawTypePrinter(keyword: $keyword, namespace: $namespace, name: $name, genericKeys: $genericKeys, typeDefinition: $printedType));

      $tsName = $namespace === null ? $name : $namespace . '.' . $name;

      self::$identifierToTsName[$identifier] = $tsName;
      
      return $identifier;
    }

    public static function substituteIdentifiers(string $typeString): string
    {
      return str_replace(array_keys(self::$identifierToTsName), array_values(self::$identifierToTsName), $typeString);
    }
  
}