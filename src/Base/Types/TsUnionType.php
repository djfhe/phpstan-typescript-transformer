<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Types;

use djfhe\PHPStanTypescriptTransformer\TsType;

class TsUnionType extends TsType
{
    public function __construct(
        /** @var TsType[] */
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
        return '(' . implode(' | ', array_map(fn(TsType $type) => $type->printTypeString(), $this->types)) . ')';
    }
}