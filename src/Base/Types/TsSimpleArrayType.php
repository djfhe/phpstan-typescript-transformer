<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\_TsType;

/**
 * A simple homogeneous array type. For example: `string[]`, `number[]`, `(string | number)[]`, `never[]`, etc.
 */
class TsSimpleArrayType extends _TsType
{
    public function __construct(
      protected _TsType $valueType
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
        return new self(_TsType::deserialize($data['valueType']));
    }

    protected function getChildren(): array
    {
        return [$this->valueType];
    }
}