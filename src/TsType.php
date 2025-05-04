<?php

namespace djfhe\PHPStanTypescriptTransformer;

use djfhe\PHPStanTypescriptTransformer\TsPrinter\NamedTypesRegistry;
use djfhe\PHPStanTypescriptTransformer\TsPrinter\TsTypePrinter;

abstract class TsType {

  private ?string $name = null;
  private bool $optional = false;

  /**
   * @var array<string,?string>
   */
  private ?array $genericParametersCache = null;

  public function getName(): ?string
  {
    return $this->name;
  }

  public function setName(?string $name): static
  {
    $this->name = $name;
    return $this;
  }

  protected abstract function typeDefinition(): string;

  /**
   * @return array<string,?string>
   */
  protected function genericParameters(): array
  {
    return [];
  }

  public function optional(bool $optional = true): static
  {
    $this->optional = $optional;
    return $this;
  }

  public function isOptional(): bool
  {
    return $this->optional;
  }

  public function clone(): static
  {
    $this->genericParametersCache = null;
    $this->optional = false;
    return clone $this;
  }

  /**
   * @return array<string,?string>
   */
  final public function _genericParameters(): array
  {
    if ($this->genericParametersCache !== null) {
      return $this->genericParametersCache;
    }

    $this->genericParametersCache = $this->genericParameters();

    return $this->genericParametersCache;
  }

  final public function printTypeString(): string
  { 
    $name = $this->getName();

    if ($name !== null) {
      $genericParameters = $this->_genericParameters();

      $identifier = NamedTypesRegistry::getNamedTypeIdentifier($name);

      if ($identifier === null) {
        $identifier = NamedTypesRegistry::registerNamedType($name);

        $genericKeys = array_keys($genericParameters);

        TsTypePrinter::$printingTypesStack->attach($this);

        $identifier = NamedTypesRegistry::addNamedType(
          identifier: $identifier,
          keyword: $this->definitionKeyword(),
          name: $name,
          printedType: $this->typeDefinition(),
          genericKeys: $genericKeys
        );

        TsTypePrinter::$printingTypesStack->detach($this);
      }

      $genericParameters = array_filter($genericParameters, fn ($value) => $value !== null);

      $genericValuesString = count($genericParameters) > 0 ? '<' . implode(',', array_values($genericParameters)) . '>' : '';
      return $identifier . $genericValuesString;
    }

    if (TsTypePrinter::$printingTypesStack->contains($this)) {
      throw new \Exception('Circular reference type without a name detected in type definition.');
    }

    TsTypePrinter::$printingTypesStack->attach($this);

    $definition = $this->typeDefinition();

    TsTypePrinter::$printingTypesStack->detach($this);

    return $definition;
  }

  /**
   * @return 'type' | 'interface'
   */
  public function definitionKeyword(): string
  {
    return 'type';
  }
}