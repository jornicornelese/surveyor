<?php

namespace Laravel\StaticAnalyzer\Analysis;

use Laravel\StaticAnalyzer\NodeResolvers\AbstractResolver;
use Laravel\StaticAnalyzer\Types\ClassType;
use Laravel\StaticAnalyzer\Types\Contracts\Type as TypeContract;
use Laravel\StaticAnalyzer\Types\Entities\View;
use Laravel\StaticAnalyzer\Types\Type;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\CallLike;

class ReturnTypeAnalyzer extends AbstractResolver
{
    protected array $returnTypes = [];

    public function analyze(Node\Stmt\ClassMethod $methodNode): array
    {
        $this->returnTypes = [];

        if ($methodNode->returnType) {
            $this->returnTypes[] = $this->from($methodNode->returnType);
        }

        $this->processStatements($methodNode->stmts ?? []);

        // $finalReturnTypes = collect($this->returnTypes)->unique()->values();

        // [$interfaces, $concrete] = $finalReturnTypes->partition(fn(TypeContract $type) => $type instanceof ClassType && $type->isInterface());

        // // Only return interfaces that are not implemented by any concrete class we've already found
        // $interfaces = $interfaces->filter(function (ClassType $type) use ($concrete) {
        //     return $concrete->first(
        //         fn(TypeContract $concreteType) =>
        //         $concreteType instanceof ClassType
        //             && in_array($type->value, class_implements($concreteType->resolved()))
        //     ) === null;
        // })->values();

        // dd($interfaces, $concrete);

        dd($this->returnTypes);

        return $this->returnTypes;
    }

    protected function processStatements(array $statements): void
    {
        foreach ($statements as $stmt) {
            $this->processStatement($stmt);
        }
    }

    protected function processStatement(Node $stmt): void
    {
        switch (true) {
            case $stmt instanceof Node\Stmt\Return_:
                $this->processReturnStatement($stmt);
                break;

            case $stmt instanceof Node\Stmt\If_:
                $this->processIfStatement($stmt);
                break;

            case $stmt instanceof Node\Stmt\While_:
            case $stmt instanceof Node\Stmt\For_:
            case $stmt instanceof Node\Stmt\Foreach_:
                $this->processLoopStatement($stmt);
                break;

            case $stmt instanceof Node\Stmt\Switch_:
                $this->processSwitchStatement($stmt);
                break;

            case $stmt instanceof Node\Stmt\TryCatch:
                $this->processTryCatchStatement($stmt);
                break;

            case $stmt instanceof Node\Stmt\Expression:
                $this->processExpression($stmt->expr);
                break;

            default:
                $this->processGenericStatement($stmt);
        }
    }

    protected function processReturnStatement(Node\Stmt\Return_ $returnStmt): void
    {
        if ($returnStmt->expr) {
            $returnType = $this->from($returnStmt->expr);
        } else {
            $returnType = Type::void();
        }

        $this->returnTypes[] = $this->remapReturnType($returnType, $returnStmt);
    }

    protected function remapReturnType(TypeContract $returnType, Node\Stmt\Return_ $returnStmt): TypeContract
    {
        if (! $returnType instanceof ClassType) {
            return $returnType;
        }

        switch ($returnType->value) {
            case 'Illuminate\Contracts\View\View':
            case 'Illuminate\View\View':
                return $this->mapToView($returnType, $returnStmt);
        }

        dd($returnType);

        return $returnType;
    }

    protected function mapToView(ClassType $returnType, Node\Stmt\Return_ $returnStmt): View
    {
        if ($returnStmt->expr instanceof CallLike) {
            $args = $returnStmt->expr->getArgs();
            $args = collect($args)->map(fn (Arg $arg) => $this->from($arg->value))->toArray();
        } else {
            dd('not call like', $returnStmt->expr);
        }

        return View::from($returnType, $args[0]->value, $args[1]->value);
    }

    protected function processIfStatement(Node\Stmt\If_ $ifStmt): void
    {
        if ($ifStmt->stmts) {
            $this->processStatements($ifStmt->stmts);
        }

        foreach ($ifStmt->elseifs as $elseif) {
            if ($elseif->stmts) {
                $this->processStatements($elseif->stmts);
            }
        }

        if ($ifStmt->else && $ifStmt->else->stmts) {
            $this->processStatements($ifStmt->else->stmts);
        }
    }

    protected function processLoopStatement(Node $loopStmt): void
    {
        if (property_exists($loopStmt, 'stmts') && $loopStmt->stmts) {
            $this->processStatements($loopStmt->stmts);
        }
    }

    protected function processSwitchStatement(Node\Stmt\Switch_ $switchStmt): void
    {
        foreach ($switchStmt->cases as $case) {
            if ($case->stmts) {
                $this->processStatements($case->stmts);
            }
        }
    }

    protected function processTryCatchStatement(Node\Stmt\TryCatch $tryCatchStmt): void
    {
        $this->processStatements($tryCatchStmt->stmts);

        foreach ($tryCatchStmt->catches as $catch) {
            $this->processStatements($catch->stmts);
        }

        if ($tryCatchStmt->finally) {
            $this->processStatements($tryCatchStmt->finally->stmts);
        }
    }

    protected function processExpression(Node\Expr $expr): void
    {
        if ($expr instanceof Node\Expr\Closure) {
            $this->processStatements($expr->stmts);
        } elseif ($expr instanceof Node\Expr\ArrowFunction) {
            $this->returnTypes[] = $this->from($expr->expr);
        }
    }

    protected function processGenericStatement(Node $stmt): void
    {
        dd('generic statement!');
        // Use reflection to find any properties that might contain statements
        $reflection = new \ReflectionObject($stmt);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $value = $property->getValue($stmt);

            if (is_array($value)) {
                foreach ($value as $item) {
                    if ($item instanceof Node) {
                        $this->processStatement($item);
                    }
                }
            } elseif ($value instanceof Node) {
                $this->processStatement($value);
            }
        }
    }
}
