<?php
namespace App\Http\Managers;

use App\Models\User;

class AuthenticationManager
{

    public function __construct()
    {

    }

    /**
     * @param int $id
     * @return User|null
     */
    public function getUserById(int $id): ?User
    {
        if ($id === null) {
            return null;
        }

        return User::where("id", "=", $id)->first();
    }

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
