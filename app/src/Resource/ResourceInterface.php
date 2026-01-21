<?php


namespace App\Resource;


interface ResourceInterface extends \JsonSerializable, \ArrayAccess
{
    public function getId();
    public function toJson($options = 0);
    public function jsonSerialize(): array;
}