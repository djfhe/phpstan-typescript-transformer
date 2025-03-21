<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\_TsType;

class TsObjectType extends _TsType
{
    public function __construct(
      /** @var array<TsObjectPropertyType> */
      protected array $properties
    ) {}

    public function definitionKeyword(): string
    {
        return "interface";
    }

    public function toTypeDefinition(bool $inline): string
    {
        $properties = [];
        foreach ($this->properties as $value) {
            $properties[] = $value->toTypeString($inline);
        }

        return "{ " . implode("; ", $properties) . " }";
    }

    protected function _serialize(): array
    {
        return [
            'properties' => array_map(fn(TsObjectPropertyType $property) => $property->serialize(), $this->properties)
        ];
    }

    protected static function _deserialize(array $data): static
    {
        return new self(array_map(fn($property) => TsObjectPropertyType::deserialize($property), $data['properties']));
    }

    protected function getChildren(): array
    {
        return $this->properties;
    }
}