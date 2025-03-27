<?php

declare(strict_types=1);

namespace djfhe\PHPStanTypescriptTransformer\Laravel\Rules;

use djfhe\PHPStanTypescriptTransformer\Base\Types\TsObjectType;
use djfhe\PHPStanTypescriptTransformer\Base\Types\TsUnionType;
use djfhe\PHPStanTypescriptTransformer\TsPrinter\TsTypePrinter;
use djfhe\PHPStanTypescriptTransformer\TsTransformer;
use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\ReturnStatement;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;

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

        $returns = $node->getReturnStatements();

        /**
         * @var array<array{0: ?\PhpParser\Node\Expr, 1: Scope}>
         */
        $args = array_map(function (ReturnStatement $return) use ($scope) {
            return [$this->getInertiaReturnStatementArgs($return->getReturnNode(), $scope), $return->getScope()];
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

    protected function getInertiaFuncCallArgs(\PhpParser\Node\Expr\FuncCall $funcCall, Scope $scope): ?\PhpParser\Node\Expr
    {
        if (! $funcCall->name instanceof \PhpParser\Node\Name) {
            return null;
        }

        if (!$this->reflectionProvider->hasFunction($funcCall->name, $scope)) {
            return null;
        }

        $type = $this->reflectionProvider->getFunction($funcCall->name, $scope);
        
        if ($type->getName() !== 'inertia') {
            return null;
        }

        if (count($funcCall->args) !== 2) {
            return null;
        }

        $inertiaArg = $funcCall->args[1];

        if (! $inertiaArg instanceof \PhpParser\Node\Arg) {
            return null;
        }

        return $inertiaArg->value;
    }

    protected function getInertiaStaticCallArgs(\PhpParser\Node\Expr\StaticCall $staticCall): ?\PhpParser\Node\Expr
    {
        
        if (! $staticCall->class instanceof \PhpParser\Node\Name\FullyQualified) {
            return null;
        }

        if ($staticCall->class->name !== 'Inertia\\Inertia') {
            return null;
        }

        if (! $staticCall->name instanceof \PhpParser\Node\Identifier) {
            return null;
        }

        if ($staticCall->name->name !== 'render') {
            return null;
        }

        if (count($staticCall->args) !== 2) {
            return null;
        }

        $inertiaArg = $staticCall->args[1];

        if (! $inertiaArg instanceof \PhpParser\Node\Arg) {
            return null;
        }

        return $inertiaArg->value;
    }

    protected function getInertiaReturnStatementArgs(\PhpParser\Node\Stmt\Return_ $returnStatement, Scope $scope): ?\PhpParser\Node\Expr
    {
        $inertiaExpr = $returnStatement->expr;

        if ($inertiaExpr instanceof \PhpParser\Node\Expr\FuncCall) {
            return $this->getInertiaFuncCallArgs($inertiaExpr, $scope);
        }


        if ($inertiaExpr instanceof \PhpParser\Node\Expr\StaticCall) {
            return $this->getInertiaStaticCallArgs($inertiaExpr);
        }

        return null;
    }
}
