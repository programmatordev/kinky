<?php

use ProgrammatorDev\Kinky\Kinky;

function kinky(array $options = []): Kinky
{
    return Kinky::instance($options);
}