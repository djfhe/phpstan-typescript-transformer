<?php

namespace djfhe\StanScript\TsPrinter;

class TsNamespaceCollectionPrinter
{
  public function __construct(
    /** @var array<string,TsNamespacePrinter> */
    public array $namespaces = [],
  ) {}

  public function addTsTypePrinter(TsTypePrinterContract $type): void
  {
    $namespace = $type->getTsNamespace();


    if ($namespace === null) {
      $namespace = '';
    }

    if (!array_key_exists($namespace, $this->namespaces)) {
      $this->namespaces[$namespace] = new TsNamespacePrinter($namespace);
    }

    $this->namespaces[$namespace]->addTsTypePrinter($type);
  }

  public function printTypeString(): string
  {
    $namespaces = $this->namespaces;
    uasort($namespaces, fn (TsNamespacePrinter $a, TsNamespacePrinter $b) => $a->compareTo($b));

    $printedNamespaces = array_map(fn (TsNamespacePrinter $namespace) => $namespace->printTypeString(), $namespaces);
    $printed = implode(PHP_EOL . PHP_EOL, $printedNamespaces);

    return $printed;
  }
}