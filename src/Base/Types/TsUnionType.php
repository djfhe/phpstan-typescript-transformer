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
        if (count($this->types) === 1) {
            // get first element of array, this can be sparse, so we need to use array_pop
            /** @var TsType $type */
            $type = array_pop($this->types);

            return $type->printTypeString();
        }

        return '(' . implode(' | ', array_map(fn(TsType $type) => $type->printTypeString(), $this->types)) . ')';
    }
}