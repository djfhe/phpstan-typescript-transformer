<?php

namespace djfhe\StanScript\TsPrinter;

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

    public static function registerNamedType(string $keyword, string $name, string $printedType): string
    {
      if (array_key_exists($name, self::$typeNameToIdentifier)) {
        return self::$typeNameToIdentifier[$name];
      }

      $identifier = '{%' . uniqid($name . '_'). '%}';

      self::$typeNameToIdentifier[$name] = $identifier;

      $namespace = TsPrinterUtil::getNamespace($name);
      $name = TsPrinterUtil::getName($name);

      TsPrinter::$namespaceCollectionPrinter->addTsTypePrinter(new TsRawTypePrinter($keyword, $namespace, $name, $printedType));

      $tsName = $namespace === null ? $name : $namespace . '.' . $name;

      self::$identifierToTsName[$identifier] = $tsName;

      return $identifier;
    }

    public static function substituteIdentifiers(string $typeString): string
    {
      return str_replace(array_keys(self::$identifierToTsName), array_values(self::$identifierToTsName), $typeString);
    }
  
}