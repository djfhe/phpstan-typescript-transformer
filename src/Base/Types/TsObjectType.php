<?php

namespace djfhe\StanScript\Base\Types;

use djfhe\StanScript\TsType;

class TsObjectType extends TsType
{
    public function __construct(
      /** @var TsObjectPropertyType[] */
      protected array $properties = [],
    ) {}

    public function add(TsObjectPropertyType $property): void {
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

    protected function typeDefinition(): string
    {
        $properties = [];
        foreach ($this->properties as $value) {
            $properties[] = $value->printTypeString();
        }

        return "{ " . implode("; ", $properties) . " }";
    }
}