<?php

namespace Technodelight;

use UnexpectedValueException;

class Simplate
{
    protected $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public static function fromFile($path)
    {
        if (!is_readable($path)) {
            throw new UnexpectedValueException(sprintf('File %s could not be opened', $path));
        }
        return new self(file_get_contents($path));
    }

    /**
     * @param array $variables key => value pairs
     *
     * @return string
     */
    public function render(array $variables = [])
    {
        return $this->replaceContent($variables, $this->hideDepends($variables, $this->content));
    }

    private function replaceContent(array $variables, $content)
    {
        $keys = array_map(
            function($key) {
                return sprintf('{{ %s }}', $key);
            },
            array_keys($variables)
        );

        return strtr($content, array_combine($keys, array_values($variables)));
    }

    private function hideDepends(array $variables, $content)
    {
        $idx = 0;
        $endIdx = 0;
        $pos = 0;
        $endPos = 0;

        do {
            $idx = strpos($content, '{{ depends');
            if ($idx !== false) {
                $endIdx = strpos($content, '}}', $idx);
                $endIdx-= $idx - 10;

                if ($endIdx < 0) {
                    throw new UnexpectedValueException(
                        sprintf(
                            "A 'depends' tag misses the double curly braces closing at %d",
                            $idx
                        )
                    );
                }

                // get expression contents
                $expression = trim(substr($content, $idx + 10, $endIdx));

                // get end_depends position
                $pos = strpos($content, '{{ end_depends', $endIdx + 2);
                var_dump($pos);
                if ($pos < 0) {
                    throw new UnexpectedValueException(
                        sprintf(
                            "No 'end_depends' found for 'depends' tag started from char %d",
                            $idx
                        )
                    );
                }

                $endPos = strpos($content, '}}', $pos);
                if ($endPos < 0) {
                    throw new UnexpectedValueException(
                        sprintf(
                            "An 'end_depends' tag misses the double curly braces closing at %d",
                            $pos
                        )
                    );
                }
                $endPos += 2;

                $expression = $this->replaceContent($variables, $expression);

                // if expression evals to true then show section, else hide
                if ($this->evalExpression($variables, $expression)) {
                    $content = substr($content, 0, $idx)
                        . substr($content, $endIdx + 2, $pos)
                        . substr($content, $endPos);
                } else {
                    $content = substr($content, 0, $idx)
                        . substr($content, $endPos);
                }
            }
        } while ($idx !== false);

        return $content;
    }

    private function evalExpression(array $variables, $expression)
    {
        extract($variables);
        return eval("return ($expression) === true;");
    }
}
