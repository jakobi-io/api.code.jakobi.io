<?php
namespace App\Http\Managers\OAuth;

use App\Models\User;

class OAuthLogger
{

    public function __construct()
    {

    }

    public function log(User $user, string $oAuthToken): void
    {
        // add database sign in log
    }
}