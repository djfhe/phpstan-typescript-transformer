<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\TsType;

class TsObjectType extends TsType
{
    public function __construct(
      /** @var array<TsObjectPropertyType> */
      protected array $properties = [],
    ) {}

    public function add(TsObjectPropertyType $property) {
        $this->properties[] = $property;
    }

    public function get(int $index): TsObjectPropertyType
    {
        return $this->properties[$index];
    }

    public function count(): int
    {
        return count($this->properties);
    }

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