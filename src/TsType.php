<?php

namespace djfhe\StanScript;

use djfhe\StanScript\TsPrinter\NamedTypesRegistry;
use djfhe\StanScript\TsPrinter\TsTypePrinter;

abstract class TsType {

  private ?string $name = null;
  private ?string $typeDefinitionCache = null;

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

  private function _printTypeString(): string
  {
    if ($this->typeDefinitionCache === null) {
      $this->typeDefinitionCache = $this->typeDefinition();
    }

    return $this->typeDefinitionCache;
  }

  final public function printTypeString(): string
  {
    if ($this->name !== null) {
      $identifier = NamedTypesRegistry::registerNamedType($this->definitionKeyword(), $this->name, $this->_printTypeString());
      return $identifier;
    }

    if (TsTypePrinter::$printingTypesStack->contains($this)) {
      throw new \Exception('Circular reference detected in type definition.');
    }

    TsTypePrinter::$printingTypesStack->attach($this);

    return $this->_printTypeString();
  }

  /**
   * @return 'type' | 'interface'
   */
  public function definitionKeyword(): string
  {
    return 'type';
  }
}