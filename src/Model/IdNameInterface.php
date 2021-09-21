<?php

namespace App\Model;

interface IdNameInterface
{
    public function getId(): int;
    public function getName(): string;
}
