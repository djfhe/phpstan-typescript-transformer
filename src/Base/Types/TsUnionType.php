<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\TsType;

class TsUnionType extends TsType
{
    public function __construct(
        /** @var TsType[] */
        protected array $types = [],
    ) {}

    public function add(TsType $type) {
        $this->types[] = $type;
    }

    public function get(int $index): TsType
    {
        return $this->types[$index];
    }

    public function count(): int
    {
        return count($this->types);
    }

    public function toTypeDefinition(bool $inline): string
    {
        return '(' . implode(' | ', array_map(fn(TsType $type) => $type->toTypeString($inline), $this->types)) . ')';
    }


    protected function _serialize(): array
    {
        return [
            'types' => array_map(fn(TsType $type) => $type->serialize(), $this->types)
        ];
    }

    protected static function _deserialize(array $data): static
    {
        return new self(array_map(fn($type) => TsType::deserialize($type), $data['types']));
    }

    protected function getChildren(): array
    {
        return $this->types;
    }
}