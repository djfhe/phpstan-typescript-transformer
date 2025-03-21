<?php

namespace djfhe\StanScript;

use djfhe\StanScript\TsPrinter\TsPrinter;

abstract class TsType {

  private ?string $identifier = null;

  public function getIdentifier(): ?string
  {
    return $this->identifier;
  }

  public function setIdentifier(?string $identifier): static
  {
    $this->identifier = $identifier;
    return $this;
  }

  public abstract function typeDefinition(): string;

  final public function printTypeString(): string
  {
    if ($this->identifier !== null) {
      return '{%' . $this->identifier . '%}';
    }

    if (TsPrinter::$printingTypesStack->contains($this)) {
      throw new \Exception('Circular reference detected in type definition.');
    }

    TsPrinter::$printingTypesStack->attach($this);

    return $this->typeDefinition();
  }

  /**
   * @return TsType[]
   */
  protected abstract function getChildren(): array;

  final public function getRecursiveChildren(): array
  {
    $children = $this->getChildren();
    $result = $children;
    
    foreach ($children as $child) {
      $result = array_merge($result, $child->getRecursiveChildren());
    }

    return $result;
  }

  /**
   * @return 'type' | 'interface'
   */
  public function definitionKeyword(): string
  {
    return 'type';
  }
}