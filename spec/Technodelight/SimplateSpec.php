<?php

namespace spec\Technodelight;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SimplateSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith('');
        $this->shouldHaveType('Technodelight\Simplate');
    }

    function it_could_replace_variables_into_content()
    {
        $this->beConstructedWith('var: {{ var }}');
        $this->render(array('var' => 'variable'))->shouldReturn('var: variable');
    }

    function it_could_hide_a_section()
    {
        $this->beConstructedWith('text{{ depends $var > 0 }}I\'m gonna hide myself{{ end_depends }}text');
        $this->render(array('var' => 0))->shouldReturn('texttext');
    }

    function it_shows_a_section()
    {
        $this->beConstructedWith("text{{ depends \$var > 0 }}This will be\nvisible{{ end_depends }}text");
        $this->render(array('var' => 1))->shouldReturn("textThis will be\nvisibletext");
    }

    function it_renders_a_more_complex_template()
    {
        $this->beConstructedWith($this->complexTemplate());
        $this->render(array('var' => 1, 'otherVar' => 234))
            ->shouldReturn($this->expectedComplexTemplate());
    }

    private function complexTemplate()
    {
return <<<'EOF'
{{ depends $var == 1 }}
I'm a variable and my value is {{ var }}
{{ end_depends }}
Value of other var: {{ otherVar }}
I'm depending on other
{{ depends $otherVar !== false }}
If you see me I'm visible :)
{{ end_depends }}
EOF;
    }

    private function expectedComplexTemplate()
    {
return <<<'EOF'

I'm a variable and my value is 1

Value of other var: 234
I'm depending on other

If you see me I'm visible :)

EOF;
    }
}
