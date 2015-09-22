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
}
