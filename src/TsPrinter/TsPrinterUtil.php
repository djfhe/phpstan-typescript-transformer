<?php

namespace djfhe\StanScript\TsPrinter;

class TsPrinterUtil
{
  public static function getNamespace(string $name): ?string
  {
      $parts = explode('\\', $name);

      if (count($parts) === 1) {
          return null;
      }

      return implode('.', array_slice($parts, 0, -1));
  }

  public static function getName(string $name): string
  {
      $parts = explode('\\', $name);

      return end($parts);
  }

  /**
   * @param 'type' | 'interface' $keyword
   * @param string[] $genericKeys
   */
  public static function createDeclaration(string $keyword, string $name, array $genericKeys, string $definition): string
  {
    $genericKeysString = count($genericKeys) > 0 ? '<' . implode(',', $genericKeys) . '>' : '';

      if ($keyword === 'interface') {
          return 'export interface ' . $name . $genericKeysString . ' ' . $definition;
      }

      return 'export type ' . $name. $genericKeysString  . ' = ' . $definition . ';';
  }
}