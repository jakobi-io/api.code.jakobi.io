<?php
namespace App\Http\Managers\OAuth;

use App\Models\User;

class OAuthManager
{
    private OAuthLogger $oAuthLogger;

    public function __construct()
    {
        $this->oAuthLogger = new OAuthLogger();
    }

    /**
     * @return OAuthLogger
     */
    public function getOAuthLogger(): OAuthLogger
    {
        return $this->oAuthLogger;
    }

    public function authenticate($oAuthToken): ?User
    {
        if ($oAuthToken === null) {
            return null;
        }

        $user = User::where('oauthtoken', '=', $oAuthToken)->first();

        if ($user === null) {
            return null;
        }

        $user->oauthtoken = null;
        $user->save();

        // log user sign in
        $this->oAuthLogger->log($user, $oAuthToken);

        return $user;
    }

    public function getUserById(int $id): ?User
    {
        if ($id === null) {
            return null;
        }

        return User::where("id", "=", $id)->first();
    }

    public function getUserBySlug(string $slug): ?User
    {
        if ($slug === null) {
            return null;
        }

        return User::where("slug", "=", $slug)->first();
    }
}