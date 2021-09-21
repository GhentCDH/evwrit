<?php

namespace App\Model\Lookup;

interface IdNameInterface
{
    public function getId(): int;
    public function getName(): string;
}
