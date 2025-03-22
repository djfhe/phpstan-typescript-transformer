<?php

namespace djfhe\StanScript;

use djfhe\StanScript\TsPrinter\NamedTypesRegistry;
use djfhe\StanScript\TsPrinter\TsTypePrinter;

abstract class TsType {

  private ?string $name = null;

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
    if ($this->getName() !== null) {
      $genericParameters = $this->_genericParameters();

      $identifier = NamedTypesRegistry::getNamedTypeIdentifier($this->getName());

      if ($identifier === null) {
        $genericKeys = array_keys($genericParameters);

        $identifier = NamedTypesRegistry::registerNamedType(
          keyword: $this->definitionKeyword(),
          name: $this->getName(),
          printedType: $this->typeDefinition(),
          genericKeys: $genericKeys
        );
      }

      $genericParameters = array_filter($genericParameters, fn ($value) => $value !== null);

      $genericValuesString = count($genericParameters) > 0 ? '<' . implode(',', array_values($genericParameters)) . '>' : '';
      return $identifier . $genericValuesString;
    }

    if (TsTypePrinter::$printingTypesStack->contains($this)) {
      throw new \Exception('Circular reference detected in type definition.');
    }

    TsTypePrinter::$printingTypesStack->attach($this);

    return $this->typeDefinition();
  }

  /**
   * @return 'type' | 'interface'
   */
  public function definitionKeyword(): string
  {
    return 'type';
  }
}