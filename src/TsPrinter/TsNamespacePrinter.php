<?php

namespace djfhe\PHPStanTypescriptTransformer\TsPrinter;

class TsNamespacePrinter
{
    public function __construct(
      public string $name,
      /** @var array<string,string> */
      protected array $declarations = [],
    ) {}

    public function addTsTypePrinter(TsTypePrinterContract $type): void
    {
        $declaration = $type->printTypeString();
        
        if (!str_ends_with($declaration, ';')) {
            $declaration .= ';';
        }

        $this->declarations[$type->getTsName()] = $declaration;
    }

    public function printTypeString(): string
    {
        $declarations = $this->declarations;
        ksort($declarations);

        $printed = implode(PHP_EOL, $declarations);
        
        $name = $this->name;

        if ($name === '') {
            return $printed;
        }

        return "namespace $name {" . PHP_EOL . $printed . PHP_EOL . "}";
    }

    public function compareTo(TsNamespacePrinter $other): int
    {
        $thisLevel = substr_count($this->name, '.');
        $otherLevel = substr_count($other->name, '.');

        if ($thisLevel !== $otherLevel) {
            return $thisLevel - $otherLevel;
        }

        return strcmp($this->name, $other->name);
    }
}