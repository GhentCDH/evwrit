<?php

namespace App\Model;

interface IdNameModelInterface
{
    public function getId(): int;
    public function getName(): string;
}
