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
        $this->beConstructedWith('text{{ depends $var > 0 }}variable{{ end_depends }}text');
        $this->render(array('var' => 0))->shouldReturn('texttext');
    }

    function it_shows_a_section()
    {
        $this->beConstructedWith('text{{ depends $var > 0 }}variable{{ end_depends }}text');
        $this->render(array('var' => 1))->shouldReturn('textvariabletext');
    }
}
