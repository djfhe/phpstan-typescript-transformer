<?php

namespace djfhe\PHPStanTypescriptTransformer\TsPrinter;

interface TsTypePrinterContract
{
  public function getTsNamespace(): ?string;

  public function getTsName(): string;

  public function printTypeString(): string;
}