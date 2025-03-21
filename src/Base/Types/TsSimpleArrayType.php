<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\TsType;

/**
 * A simple homogeneous array type. For example: `string[]`, `number[]`, `(string | number)[]`, `never[]`, etc.
 */
class TsSimpleArrayType extends TsType
{
    public function __construct(
      protected TsType $valueType
    ) {}

    public function toTypeDefinition(bool $inline): string
    {
        return "{$this->valueType->toTypeString($inline)}[]";
    }

    protected function _serialize(): array
    {
        return [
            'valueType' => $this->valueType->serialize()
        ];
    }

    protected static function _deserialize(array $data): static
    {
        return new self(TsType::deserialize($data['valueType']));
    }

    protected function getChildren(): array
    {
        return [$this->valueType];
    }
}