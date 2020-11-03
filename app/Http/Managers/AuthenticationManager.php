<?php
namespace App\Http\Managers;

use App\Models\User;

class AuthenticationManager
{

    /**
     * Make Request to the Accounts-API
     *
     * @param $oauthToken
     * @return User
     */
    public static function getUserByOAuthToken($oauthToken): ?User
    {
        return User::where("apitoken", "=", $oauthToken)->first();
    }
}
