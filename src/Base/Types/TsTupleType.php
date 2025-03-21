<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\_TsType;

class TsTupleType extends _TsType
{
    public function __construct(
      /** @var array<_TsType> */
      protected array $types
    ) {}

    public function toTypeDefinition(bool $inline): string
    {
        $types = [];
        foreach ($this->types as $type) {
            $types[] = $type->toTypeString($inline);
        }
        return "[" . implode(", ", $types) . "]";
    }

    protected function _serialize(): array
    {
        return [
            'types' => array_map(fn(_TsType $type) => $type->serialize(), $this->types)
        ];
    }

    protected static function _deserialize(array $data): static
    {
        return new self(array_map(fn($type) => _TsType::deserialize($type), $data['types']));
    }

    protected function getChildren(): array
    {
        return $this->types;
    }
}