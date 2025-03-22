<?php

declare(strict_types=1);

namespace djfhe\StanScript\Laravel\Rules;

use djfhe\StanScript\Base\Types\TsObjectType;
use djfhe\StanScript\Base\Types\TsUnionType;
use djfhe\StanScript\TsPrinter\TsTypePrinter;
use djfhe\StanScript\TsTransformer;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ReturnStatement;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements \PHPStan\Rules\Rule<\PHPStan\Node\MethodReturnStatementsNode>
 */
class ControllerInertiaReturnRule implements \PHPStan\Rules\Rule
{
    public function __construct(
        private ReflectionProvider $reflectionProvider
    ) {}

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

        $returns = $node->getReturnStatements();

        /**
         * @var array<array{0: ?\PhpParser\Node\Expr, 1: Scope}>
         */
        $args = array_map(function (ReturnStatement $return) {
            return [$this->getInertiaReturnStatementArgs($return->getReturnNode()), $return->getScope()];
        }, $returns);

        /**
         * @var array<array{0: \PhpParser\Node\Expr, 1: Scope}>
         */
        $args = array_filter($args, function (array $arg) {
          $expr = $arg[0];

          if ($expr === null) {
              return false;
          }

          if ($expr instanceof \PhpParser\Node\Expr\Array_) {
              return count($expr->items) > 0;
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

            $type = TsTransformer::transformExpression($returnValue, $returnScope, $this->reflectionProvider);
            
            $returnUnionType->add($type);
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
     * @return array<\PhpParser\Node\Stmt\Return_>
     */
    protected function collectReturnStatements(\PhpParser\Node\Stmt\ClassMethod $method): array
    {
        $returnStatements = [];

        if ($method->stmts === null) {
            return $returnStatements;
        }

        foreach ($method->stmts as $stmt) {
            if (! $stmt instanceof \PhpParser\Node\Stmt\Return_) {
                continue;
            }

            $returnStatements[] = $stmt;
        }

        return $returnStatements;
    }

    protected function getInertiaReturnStatementArgs(\PhpParser\Node\Stmt\Return_ $returnStatement): ?\PhpParser\Node\Expr
    {
        $args = [];

        $inertiaExpr = $returnStatement->expr;

        if (! $inertiaExpr instanceof \PhpParser\Node\Expr\StaticCall) {
            return null;
        }

        if (! $inertiaExpr->class instanceof \PhpParser\Node\Name\FullyQualified) {
            return null;
        }

        if ($inertiaExpr->class->name !== 'Inertia\Inertia') {
            return null;
        }

        if (! $inertiaExpr->name instanceof \PhpParser\Node\Identifier) {
            return null;
        }

        if ($inertiaExpr->name->name !== 'render') {
            return null;
        }

        if (count($inertiaExpr->args) !== 2) {
            return null;
        }

        $inertiaArg = $inertiaExpr->args[1];

        if (! $inertiaArg instanceof \PhpParser\Node\Arg) {
            return null;
        }

        $value = $inertiaArg->value;

        return $value;
    }
}
