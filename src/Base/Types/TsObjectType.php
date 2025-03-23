<?php

namespace djfhe\PHPStanTypescriptTransformer\Base\Types;

use djfhe\PHPStanTypescriptTransformer\TsType;

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

    protected function typeDefinition(): string
    {
        $properties = [];
        foreach ($this->properties as $value) {
            $properties[] = $value->printTypeString() . ";";
        }

        return "{" . PHP_EOL . implode(PHP_EOL, $properties) . PHP_EOL . "}";
    }
}