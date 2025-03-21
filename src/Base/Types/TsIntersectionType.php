<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\TsType;

class TsIntersectionType extends TsType
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
        return '(' . implode(' & ', array_map(fn(TsType $type) => $type->toTypeString($inline), $this->types)) . ')';
    }

    protected function getChildren(): array
    {
        return $this->types;
    }
}