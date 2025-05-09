<?php

declare(strict_types=1);

namespace djfhe\PHPStanTypescriptTransformer\TsPrinter;

use PHPStan\Command\AnalysisResult;
use PHPStan\Command\Output;

class TsPrinter implements \PHPStan\Command\ErrorFormatter\ErrorFormatter
{
    public static TsNamespaceCollectionPrinter $namespaceCollectionPrinter;

    public function __construct() {
        self::$namespaceCollectionPrinter = new TsNamespaceCollectionPrinter();
    }


    public function formatErrors(AnalysisResult $analysisResult, Output $output): int
    {
        foreach ($analysisResult->getFileSpecificErrors() as $error) {
            if ($error->getIdentifier() !== TsTypePrinter::$error_identifier) {
                continue;
            }

            $printer = TsTypePrinter::fromPHPStanError($error);

            $message = $error->getMessage();

            if ($message !== '') {
                $output->writeRaw('// ' . $message . PHP_EOL);
            }

            self::$namespaceCollectionPrinter->addTsTypePrinter($printer);
        }

        $output->writeRaw('/*' . PHP_EOL);
        $output->writeRaw(' * This file was generated by the PHPStan PHPStanTypescriptTransformer extension.' . PHP_EOL);
        $output->writeRaw(' * Do not edit this file directly.' . PHP_EOL);
        $output->writeRaw(' */' . PHP_EOL . PHP_EOL);

        $typeString = self::$namespaceCollectionPrinter->printTypeString();
        $typeString = NamedTypesRegistry::substituteIdentifiers($typeString);


        $output->writeRaw($typeString);
        
        return 0;
    }
}
