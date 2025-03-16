<?php

namespace djfhe\ControllerTransformer\PHPStan\Typescript\TypescriptTypes;

class TsObjectPropertyType extends _TsType
{
    public function __construct(
        public string $key,
        public _TsType $value,
        public bool $optional = false
    ) {}

    public function toTypeDefinition(bool $inline): string
    {
        return $this->key . ($this->optional ? '?' : '') . ': ' . $this->value->toTypeString($inline);
    }

    protected function _serialize(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value->serialize(),
            'optional' => $this->optional,
        ];
    }

    protected static function _deserialize(array $data): static
    {
        return new self($data['key'], _TsType::deserialize($data['value']), $data['optional']);
    }

    protected function getChildren(): array
    {
        return [$this->value];
    }
}