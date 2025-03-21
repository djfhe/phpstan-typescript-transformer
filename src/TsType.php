<?php

namespace djfhe\StanScript;

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

  public abstract function toTypeDefinition(bool $inline): string;

  final public function toTypeString(bool $inline): string
  {
    if (!$inline && $this->identifier !== null) {
      return '{%' . $this->identifier . '%}';
    }

    return $this->toTypeDefinition($inline);
  }

  protected abstract static function _deserialize(array $data): static;
  protected abstract function _serialize(): array;

  final public function serialize(): array
  {
    return [
      'identifier' => $this->identifier,
      'class' => static::class,
      'data' => $this->_serialize()
    ];
  }

  final public static function deserialize(array $data): static
  {
    return $data['class']::_deserialize($data['data'])->setIdentifier($data['identifier']);
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