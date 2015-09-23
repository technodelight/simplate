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
        $startDepends = 0;
        $closeDepends = 0;
        $endDepends = 0;

        do {
            $startDepends = strpos($content, '{{ depends');
            if ($startDepends !== false) {
                $closeDepends = strpos($content, '}}', $startDepends);

                if ($closeDepends === false) {
                    throw new UnexpectedValueException(
                        sprintf(
                            "A 'depends' tag misses the double curly braces closing at %d",
                            $startDepends
                        )
                    );
                }

                // get expression contents
                $expression = $this->slice(
                    $content,
                    $startDepends + strlen('{{ depends'),
                    $closeDepends
                );

                // get end_depends position
                $endDepends = strpos($content, '{{ end_depends }}', $closeDepends + 2);
                if ($endDepends === false) {
                    throw new UnexpectedValueException(
                        sprintf(
                            "No 'end_depends' found for 'depends' tag started from char %d",
                            $startDepends
                        )
                    );
                }

                // if expression evals to true then show section, else hide
                if ($this->evalExpression($variables, $expression)) {
                    $content = $this->slice($content, 0, $startDepends)
                        . $this->slice($content, $closeDepends + 2, $endDepends)
                        . $this->slice($content, $endDepends + strlen('{{ end_depends }}'), strlen($content));
                } else {
                    $content = $this->slice($content, 0, $startDepends)
                        . $this->slice($content, $endDepends + strlen('{{ end_depends }}'), strlen($content));
                }
            }
        } while ($startDepends !== false);

        return $content;
    }

    private function evalExpression(array $variables, $expression)
    {
        extract($variables);
        return eval("return ($expression) === true;");
    }

    private function slice($text, $start, $end)
    {
        return substr($text, $start, $end - $start);
    }
}
