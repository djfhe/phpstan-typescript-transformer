<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\_TsType;

class TsUnionType extends _TsType
{
    public function __construct(
        /** @var _TsType[] */
        protected array $types = [],
    ) {}

    public function add(_TsType $type) {
        $this->types[] = $type;
    }

    public function get(int $index): _TsType
    {
        return $this->types[$index];
    }

    public function count(): int
    {
        return count($this->types);
    }

    public function toTypeDefinition(bool $inline): string
    {
        return '(' . implode(' | ', array_map(fn(_TsType $type) => $type->toTypeString($inline), $this->types)) . ')';
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