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
   */
  public static function createDeclaration(string $keyword, string $name, string $definition): string
  {
      if ($keyword === 'interface') {
          return 'export interface ' . $name . ' ' . $definition;
      }

      return 'export type ' . $name . ' = ' . $definition . ';';
  }
}