<?php


namespace App\Resource;


interface ResourceInterface extends \JsonSerializable, \ArrayAccess
{
    public function getId();
}