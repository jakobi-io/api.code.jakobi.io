<?php
namespace App\Http\Managers;

use App\Models\User;

class AuthenticationManager
{

    /**
     * Make Request to the Accounts-API
     *
     * @param $oauthToken
     * @return mixed
     */
    public static function getUserByOAuthToken($oauthToken)
    {
        if ($oauthToken === null) {
            return null;
        }

        return User::where("apitoken", "=", $oauthToken)->first();
    }
}
