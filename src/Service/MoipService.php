<?php

namespace App\Service;

use Moip\Moip;
use Moip\Auth\OAuth;


class MoipService 
{
    private $accessToken;
    private $ambiente;

    public function __construct(string $accessToken, string $ambiente = 'dev')
    {
        $ambiente = $ambiente == 'dev' ? Moip::ENDPOINT_SANDBOX : Moip::ENDPOINT_PRODUCTION;

        $this->accessToken = $accessToken;
        $this->ambiente = $ambiente;
    }

    public function getMoip()
    {
        return new Moip(new OAuth($this->accessToken), $this->ambiente);
    }

    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function setAmbiente(string $ambiente): self
    {
        $this->ambiente = $ambiente;
        return $this;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function getAmbiente(): string
    {
        return $this->ambiente;
    }
}