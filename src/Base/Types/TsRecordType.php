<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\TsType;

class TsRecordType extends TsType
{
    public function __construct(
      protected TsType $keyType,
      protected TsType $valueType,
    ) {}

    public function toTypeDefinition(bool $inline): string
    {
        return "Record<{$this->keyType->toTypeString($inline)}, {$this->valueType->toTypeString($inline)}>";
    }

    protected function _serialize(): array
    {
        return [
            'keyType' => $this->keyType->serialize(),
            'valueType' => $this->valueType->serialize(),
        ];
    }

    protected static function _deserialize(array $data): static
    {
        return new self(TsType::deserialize($data['keyType']), TsType::deserialize($data['valueType']));
    }

    protected function getChildren(): array
    {
        return [$this->keyType, $this->valueType];
    }
}