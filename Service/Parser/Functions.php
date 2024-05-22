<?php

namespace Crealoz\EasyAudit\Service\Parser;

use SplFileObject;

class Functions
{
    /**
     * @throws \ReflectionException
     */
    public function getFunctionsFromTokens(array $tokens, string $filePath, callable $condition) : array
    {
        $functions = [];
        $nextStringIsFunc = false;
        $bracesCount = 0;
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if ($token[0] == T_FUNCTION) {
                    $nextStringIsFunc = true;
                } elseif ($nextStringIsFunc && $token[0] == T_STRING) {
                    if ($condition($token[1])) {
                        $functions[$token[1]] = '';
                    }
                    $nextStringIsFunc = false;
                } elseif (
                    !empty($functions)
                    && (
                        $token[0] == T_CURLY_OPEN
                        || $token[0] == T_DOLLAR_OPEN_CURLY_BRACES
                        || $token[0] == T_STRING_VARNAME)
                ) {
                    $bracesCount++;
                }
            } elseif ($token === '{' && !empty($functions)) {
                $bracesCount++;
            } elseif ($token === '}' && !empty($functions)) {
                $bracesCount--;
                if ($bracesCount === 0) {
                    $functionName = array_key_last($functions);
                    $functions[$functionName] = $this->getFunctionContent($filePath, $functionName);
                }
            }
        }
        return $functions;
    }

    /**
     * @throws \ReflectionException
     */
    private function getFunctionContent(string $filePath, string $functionName) : string
    {
        $reflection = new \ReflectionFunction($functionName);
        $startLine = $reflection->getStartLine();
        $endLine = $reflection->getEndLine();
        $length = $endLine - $startLine;

        $file = new SplFileObject($filePath);
        $file->seek($startLine-1);
        $content = '';
        for ($i = 0; $i <= $length; $i++) {
            $content .= $file->current();
            $file->next();
        }
        return $content;
    }
}
