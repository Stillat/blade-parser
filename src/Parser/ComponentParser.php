<?php

namespace Stillat\BladeParser\Parser;

use Illuminate\Support\Str;
use Stillat\BladeParser\Nodes\Components\ComponentNode;
use Stillat\BladeParser\Nodes\Components\ParameterAttribute;
use Stillat\BladeParser\Nodes\Components\ParameterNode;
use Stillat\BladeParser\Nodes\Components\ParameterType;

class ComponentParser extends AbstractParser
{
    private int $parameterContentStartOffset = 0;

    private function getRelativeContentOffset(ComponentNode $node): int
    {
        $offset = $node->position->startOffset;

        if ($node->isClosingTag) {
            $offset += 4;
        } else {
            $offset += 3;
        }

        return $offset;
    }

    private function getRelativeEndOffset(ComponentNode $node): int
    {
        $offset = $node->position->endOffset;

        if ($node->isSelfClosing) {
            $offset -= 2;
        } else {
            $offset -= 1;
        }

        return $offset;
    }

    public static function extractTagName(string $value): string
    {
        if (Str::startsWith($value, 'slot:')) {
            return Str::before($value, ':');
        }

        return $value;
    }

    public function parse(ComponentNode $node)
    {
        $offset = $this->getRelativeContentOffset($node);
        $endOffset = $this->getRelativeEndOffset($node);

        $this->content = $node->innerContent;
        $this->inputLen = mb_strlen($this->content);
        $this->chunkSize = $this->inputLen;
        $this->prepareParseAt(0);

        $name = $this->extractComponentName();

        $node->name = $name->content;
        $node->namePosition = $this->makePosition($offset - 1, $offset + mb_strlen($name->content) - 2);
        $node->tagName = self::extractTagName($node->name);

        if ($node->isClosingTag && ! $node->isSelfClosing) {
            return;
        }

        $parameterContent = mb_substr($node->innerContent, mb_strlen($name->content));
        $node->parameterContent = $parameterContent;
        $node->parameterContentPosition = $this->makePosition($node->namePosition->endOffset + 1, $endOffset);
        $this->parameterContentStartOffset = $node->parameterContentPosition->startOffset;

        // Reset the parser state.
        $this->content = $parameterContent;
        $this->inputLen = mb_strlen($this->content);
        $this->chunkSize = $this->inputLen;
        $this->prepareParseAt(0);
        $node->parameters = $this->parseParameters();
        $node->parameterCount = count($node->parameters);

        foreach ($node->parameters as $param) {
            $param->setOwnerComponent($node);
        }

        $this->clearParserState();
    }

    private function parseParameter($content, $containsValue, $startIndex): ParameterNode
    {
        $relativeStart = $this->parameterContentStartOffset + $startIndex;
        $parameter = new ParameterNode;
        $parameter->content = $content;
        $parameter->position = $this->makePosition($relativeStart, mb_strlen($content) + $relativeStart - 1);

        if ($containsValue) {
            $breakOn = mb_strpos($content, '=');
            $name = trim(mb_substr($content, 0, $breakOn));

            $parameter->name = $name;
            $parameter->nameNode = new ParameterAttribute;
            $parameter->nameNode->content = $name;
            $parameter->nameNode->position = $this->makePosition($relativeStart, $relativeStart + mb_strlen($name) - 1);

            $value = mb_substr($content, $breakOn + 1);
            $valueContent = trim($value);
            $valueContentLength = mb_strlen($valueContent);
            $diff = mb_strlen($value) - $valueContentLength;

            $valueStart = $relativeStart + $breakOn + $diff;
            $valueEnd = $valueStart + $valueContentLength - 1;

            $parameter->valueNode = new ParameterAttribute;
            $parameter->valueNode->content = $valueContent;
            $parameter->valueNode->position = $this->makePosition($valueStart + 1, $valueEnd + 1);

            $parameter->value = mb_substr($valueContent, 1, -1);
        } else {
            $parameter->name = $content;
            $parameter->nameNode = new ParameterAttribute;
            $parameter->nameNode->content = $content;
            $parameter->nameNode->position = $this->makePosition($relativeStart, $relativeStart + mb_strlen($content) - 1);
            $parameter->type = ParameterType::Attribute;
        }

        $parameter->materializedName = $parameter->name;

        if (Str::startsWith($parameter->name, '::')) {
            $parameter->materializedName = Str::after($parameter->name, ':');
            $parameter->type = ParameterType::EscapedParameter;
        } else {
            if (Str::startsWith($parameter->name, ':')) {
                $parameter->materializedName = Str::after($parameter->name, ':');
                $parameter->type = ParameterType::DynamicVariable;

                if (Str::startsWith($parameter->materializedName, '$')) {
                    $parameter->materializedName = Str::kebab(Str::after($parameter->materializedName, '$'));
                    $parameter->type = ParameterType::ShorthandDynamicVariable;
                }
            }

            if ($parameter->valueNode != null && (
                Str::containsAll($parameter->valueNode->content, ['{{', '}}']) ||
                Str::containsAll($parameter->valueNode->content, ['{!!', '!}}']))
            ) {
                $parameter->type = ParameterType::InterpolatedValue;
            }
        }

        if ($parameter->type == ParameterType::ShorthandDynamicVariable) {
            $parameter->value = mb_substr($parameter->name, 1);
        }

        return $parameter;
    }

    private function makeEchoParameter(ParameterType $type, int $startIndex): ParameterNode
    {
        $content = implode($this->currentContent);
        // Rewrite the type.
        if (! $this->containsAttributesVar($content)) {
            if ($type == ParameterType::AttributeRawEcho) {
                $type = ParameterType::UnknownRawEcho;
            } elseif ($type == ParameterType::AttributeTripleEcho) {
                $type = ParameterType::UnknownTripleEcho;
            } elseif ($type == ParameterType::AttributeEcho) {
                $type = ParameterType::UnknownEcho;
            }
        }

        $echoParameter = new ParameterNode;
        $echoParameter->content = $content;
        // TODO: Add test for this position.
        $echoParameter->position = $this->makePosition($this->parameterContentStartOffset + $startIndex, $this->parameterContentStartOffset + $this->currentIndex + mb_strlen($content));
        $echoParameter->type = $type;
        $this->currentContent = [];

        return $echoParameter;
    }

    public function parseOnlyParameters(string $content): array
    {
        $this->content = $content;
        $this->inputLen = mb_strlen($this->content);
        $this->chunkSize = $this->inputLen;
        $this->prepareParseAt(0);

        return $this->parseParameters();
    }

    private function containsAttributesVar(string $content): bool
    {
        return Str::contains(mb_strtolower($content), '$attributes');
    }

    public static $break = false;

    private function parseParameters(): array
    {
        $containsValue = false;
        $startIndex = null;
        $parameters = [];

        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            if (ctype_space($this->cur)) {
                continue;
            }

            if ($startIndex == null) {
                $startIndex = $this->currentIndex;
            }

            if ($this->cur == self::C_LeftCurlyBracket && $this->next == self::C_LeftCurlyBracket && $this->fetchAtRelative($this->currentIndex + 2, 1) == self::C_LeftCurlyBracket) {
                $this->seekToEndOfTripleEcho();
                $parameters[] = $this->makeEchoParameter(ParameterType::AttributeTripleEcho, $startIndex);

                continue;
            } if ($this->cur == self::C_LeftCurlyBracket && $this->next == self::C_ExclamationMark && $this->fetchAtRelative($this->currentIndex + 2, 1) == self::C_ExclamationMark) {
                $this->seekToEndOfRawEcho();
                $parameters[] = $this->makeEchoParameter(ParameterType::AttributeRawEcho, $startIndex);

                continue;
            } elseif ($this->cur == self::C_LeftCurlyBracket && $this->next == self::C_LeftCurlyBracket) {
                self::$break = true;
                $this->seekEndOfEcho();
                $parameters[] = $this->makeEchoParameter(ParameterType::AttributeEcho, $startIndex);

                continue;
            }

            if ($this->isStartingString()) {
                $containsValue = true;
                $this->skipToEndOfString();

                if ($this->next == null) {
                    $parameters[] = $this->parseParameter(implode('', $this->currentContent), $containsValue, $startIndex);
                    $this->currentContent = [];
                    break;
                } else {
                    $parameters[] = $this->parseParameter(implode('', $this->currentContent), $containsValue, $startIndex);
                    $this->currentContent = [];
                    $startIndex = null;
                    $containsValue = false;
                }

                continue;
            }

            $this->currentContent[] = $this->cur;

            if ($this->next != null && ctype_space($this->next)) {
                $peek = $this->peekNextNonWhitespaceAt($this->currentIndex + 2);

                if ($peek != null && $peek->content == self::C_Equals) {
                    $valuePeek = $this->peekNextNonWhitespaceAt($peek->offset + 1);

                    if ($valuePeek != null && ($valuePeek->content == self::C_SingleQuote || $valuePeek->content == self::C_DoubleQuote)) {
                        $this->advance($valuePeek->offset - $this->currentIndex);
                        array_pop($this->currentContent);
                        $this->stringTerminator = $valuePeek->content;
                        $this->skipToEndOfString();

                        $parameters[] = $this->parseParameter(implode('', $this->currentContent), true, $startIndex);
                        $containsValue = false;
                        $startIndex = null;
                        $this->currentContent = [];

                        continue;
                    }
                }
            }

            if ($this->next == null || ctype_space($this->next)) {
                $parameters[] = $this->parseParameter(implode('', $this->currentContent), $containsValue, $startIndex);
                $containsValue = false;
                $startIndex = null;
                $this->currentContent = [];

                continue;
            }
        }

        return $parameters;
    }

    private function extractComponentName(): ?ScanResult
    {
        for ($this->currentIndex; $this->currentIndex < $this->inputLen; $this->currentIndex++) {
            $this->checkCurrentOffsets();

            $this->currentContent[] = $this->cur;

            if ($this->next == null || ctype_space($this->next)) {
                $scanResult = new ScanResult;
                $scanResult->content = implode('', $this->currentContent);
                $scanResult->offset = 0;

                return $scanResult;
            }
        }

        return null;
    }
}
