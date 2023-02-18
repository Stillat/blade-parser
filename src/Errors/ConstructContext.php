<?php

namespace Stillat\BladeParser\Errors;

use Stillat\BladeParser\Nodes\AbstractNode;
use Stillat\BladeParser\Nodes\CommentNode;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\DirectiveNode;
use Stillat\BladeParser\Nodes\EchoNode;
use Stillat\BladeParser\Nodes\EchoType;
use Stillat\BladeParser\Nodes\PhpBlockNode;
use Stillat\BladeParser\Nodes\PhpTagNode;
use Stillat\BladeParser\Nodes\PhpTagType;
use Stillat\BladeParser\Nodes\VerbatimNode;

enum ConstructContext
{
    case Echo;
    case RawEcho;
    case TripleEcho;
    case Comment;
    case Verbatim;
    case DirectiveArguments;
    case BladePhpBlock;
    case ComponentTag;
    case PhpShortOpen;
    case PhpOpen;
    case Condition;
    case Literal;
    case Directive;

    public static function fromNode(AbstractNode $node): ConstructContext
    {
        if ($node instanceof  DirectiveNode) {
            if ($node->getIsConditionDirective()) {
                return ConstructContext::Condition;
            }

            return ConstructContext::Directive;
        }

        if ($node instanceof EchoNode) {
            if ($node->type == EchoType::TripleEcho) {
                return ConstructContext::TripleEcho;
            }
            if ($node->type == EchoType::RawEcho) {
                return ConstructContext::RawEcho;
            }

            return ConstructContext::Echo;
        }

        if ($node instanceof CommentNode) {
            return ConstructContext::Comment;
        }
        if ($node instanceof VerbatimNode) {
            return ConstructContext::Verbatim;
        }
        if ($node instanceof PhpBlockNode) {
            return ConstructContext::BladePhpBlock;
        }
        if ($node instanceof ComponentNode) {
            return ConstructContext::ComponentTag;
        }

        if ($node instanceof PhpTagNode) {
            if ($node->type == PhpTagType::PhpOpenTagWithEcho) {
                return ConstructContext::PhpShortOpen;
            }

            return ConstructContext::PhpOpen;
        }

        return ConstructContext::Literal;
    }

    public function value(): int
    {
        return match ($this) {
            self::Echo => 1,
            self::Comment => 2,
            self::Verbatim => 3,
            self::DirectiveArguments => 4,
            self::BladePhpBlock => 5,
            self::ComponentTag => 6,
            self::RawEcho => 7,
            self::TripleEcho => 8,
            self::PhpShortOpen => 9,
            self::PhpOpen => 10,
            self::Condition => 11,
            self::Literal => 12,
            self::Directive => 13,
        };
    }
}
