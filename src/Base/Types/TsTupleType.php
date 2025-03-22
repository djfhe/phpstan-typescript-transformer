<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Types;

use djfhe\PHPStanTypescriptTransformer\TsType;

class TsTupleType extends TsType
{
    public function __construct(
        /** @var array<TsType> */
        protected array $types = [],
    ) {}

    public function add(TsType $type): void {
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
            $types[] = $type->printTypeString();
        }
        return "[" . implode(", ", $types) . "]";
    }
}