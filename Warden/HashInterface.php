<?php

namespace Warden;

interface HashInterface
{
    public function hashString($string,$salt);
}
