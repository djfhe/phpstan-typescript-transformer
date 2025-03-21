<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\TsType;

class TsScalarType extends TsType
{
    
    public function __construct(
        protected string $value
    ) {}

    public function toTypeDefinition(bool $inline): string
    {
        return $this->value;
    }

    protected function _serialize(): array
    {
        return [
            'value' => $this->value
        ];
    }

    protected static function _deserialize(array $data): static
    {
        return new self($data['value']);
    }

    protected function getChildren(): array
    {
        return [];
    }
}