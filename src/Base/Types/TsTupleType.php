<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\TsType;

class TsTupleType extends TsType
{
    public function __construct(
        /** @var array<_TsType> */
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

    protected function typeDefinition(): string
    {
        $types = [];
        foreach ($this->types as $type) {
            $types[] = $type->toTypeString();
        }
        return "[" . implode(", ", $types) . "]";
    }
}