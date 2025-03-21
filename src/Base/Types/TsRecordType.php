<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\_TsType;

class TsRecordType extends _TsType
{
    public function __construct(
      protected _TsType $keyType,
      protected _TsType $valueType,
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
        return new self(_TsType::deserialize($data['keyType']), _TsType::deserialize($data['valueType']));
    }

    protected function getChildren(): array
    {
        return [$this->keyType, $this->valueType];
    }
}