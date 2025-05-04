<?php

declare(strict_types=1);

namespace djfhe\PHPStanTypescriptTransformer\Laravel\Rules;

use djfhe\PHPStanTypescriptTransformer\Base\Types\TsObjectType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsRecordType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsUnionType;
use djfhe\PHPStanTypescriptTransformer\Laravel\PhpstanTypes\InertiaReturnType;
use djfhe\PHPStanTypescriptTransformer\TsPrinter\TsTypePrinter;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ReturnStatement;
use PHPStan\Reflection\ReflectionProvider;

/**
 * @implements \PHPStan\Rules\Rule<\PHPStan\Node\MethodReturnStatementsNode>
 */
class ControllerInertiaReturnRule implements \PHPStan\Rules\Rule
{

    public static \Larastan\Larastan\Properties\ModelPropertyHelper $propertyHelper;

    public function __construct(
        private ReflectionProvider $reflectionProvider,
        \Larastan\Larastan\Properties\ModelPropertyHelper $propertyHelper,
    ) {
        self::$propertyHelper = $propertyHelper;
    }

    public function getNodeType(): string
    {
      return \PHPStan\Node\MethodReturnStatementsNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $namespace = $scope->getNamespace();

        if (! is_string($namespace) || ! str_starts_with($namespace, 'App\\Http\\Controllers')) {
            return [];
        }

        /**
         * @var array<array{0: ?\PhpParser\Node\Expr, 1: Scope}>
         */
        $returns = array_map(function (ReturnStatement $return) {
            return [$return->getReturnNode()->expr, $return->getScope()];
        }, $node->getReturnStatements());

        /**
         * @var array<array{0: \PhpParser\Node\Expr, 1: Scope}>
         */
        $args = array_filter($returns, function (array $arg) {
          $expr = $arg[0];

          if ($expr === null) {
              return false;
          }

          return true;
        });

        if (count($args) === 0) {
            return [];
        }

        $returnUnionType = new TsUnionType([]);
        
        foreach ($args as $arg) {
            $returnValue = $arg[0];
            $returnScope = $arg[1];

            $phpstanType = $returnScope->getType($returnValue);

            if (!$phpstanType instanceof InertiaReturnType) {
                continue;
            }

            if ($phpstanType->getProps() === null) {
                $returnUnionType->add(TsRecordType::empty());
            } else {
                $parsedType = TsTransformer::transform($phpstanType->getProps(), $returnScope, $this->reflectionProvider);
                $returnUnionType->add($parsedType);
            }
        }

        $reflection = $scope->getClassReflection();

        if ($reflection === null) {
            return [];
        }
        
        $className = $reflection->getName();
        $methodName = $node->getMethodName();

        if ($returnUnionType->count() === 0) {
            return [
                TsTypePrinter::create($className, $methodName, new TsObjectType())->toPHPStanError(),
            ];
        }

        if ($returnUnionType->count() === 1) {
            return [
                TsTypePrinter::create($className, $methodName, $returnUnionType->get(0))->toPHPStanError(),
            ];
        }

        return [
            TsTypePrinter::create($className, $methodName, $returnUnionType)->toPHPStanError(),
        ];
    }

    /**
     * @param 5 $a
     */
    function test(int $a): int
    {
        return $a;
    }
}
