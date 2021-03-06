<?php
namespace App\Http\Managers;

/**
 * Class CryptoManager
 *
 * @licence Copyright &copy; 2020 jakobi.io
 * @package App\Http\Managers
 * @author Lukas Jakobi <lukas@jakobi.io>
 * @since 30.10.2020
 */
class CryptoManager
{

    /**
     * base64 decrypt
     *
     * @param $clearText
     * @return null|string
     */
    public function encrypt($clearText): ?string
    {
        if (null === $clearText) {
            return null;
        }

        return base64_encode($clearText);
    }

    /**
     * base64 encrypt
     *
     * @param $base64
     * @return null|string
     */
    public function decrypt($base64): ?string
    {
        if (null === $clearText) {
            return null;
        }

        return base64_decode($base64);
    }

    /**
     * generate random token
     *
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public function generateToken($length = 32): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
