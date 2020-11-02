<?php

namespace SmartOysters\Yellowfin\Token;

use SmartOysters\Yellowfin\Token\YellowfinToken;

interface YellowfinStorage
{
    public function setToken(YellowfinToken $token);

    public function getToken();
}
