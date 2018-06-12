<?php

namespace App\Contracts;

interface HelperContract
{
    public function getRandomByWeight(array $weightValuesArr);
}