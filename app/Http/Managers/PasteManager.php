<?php
namespace App\Http\Managers;

use App\Http\Hydrators\PasteHydrator;
use App\Models\Paste;
use App\Models\User;
use Carbon\Carbon;

class PasteManager
{

    /** @var PasteHydrator $pasteHydrator */
    private PasteHydrator $pasteHydrator;

    /** @var CryptoManager $cryptoManager */
    private CryptoManager $cryptoManager;

    /**
     * PasteManager constructor.
     */
    public function __construct()
    {
        $this->pasteHydrator = new PasteHydrator();
        $this->cryptoManager = new CryptoManager();
    }

    /**
     * @param string|null $description
     * @param string $code
     * @param User|null $user
     * @param int|null $languageId
     * @param string|null $password
     * @param $deletedAt
     * @return Paste|null
     * @throws \Exception
     */
    public function createPaste(
        ?string $description,
        string $code,
        ?User $user,
        ?int $languageId,
        ?string $password,
        $deletedAt
    ): Paste {
        $paste = new Paste();

        $paste->token = $this->cryptoManager->generateToken(12);
        $paste->description = $this->cryptoManager->encrypt($description);
        $paste->code = $this->cryptoManager->encrypt($code);
        $paste->userId = $user === null ? null : $user->id;
        $paste->languageId = $languageId;
        $paste->password = $password;
        $paste->deleted_at = $deletedAt;

        $paste->save();
        return $paste;
    }

    public function deletePaste(int $token): bool
    {
        $paste = $this->getPasteByToken($token);

        if ($paste === null) {
            return false;
        }

        $paste->active = false;
        $paste->deleted_at = Carbon::now()->toDateTimeString();
        $paste->save();

        return true;
    }

    /**
     * @param User $user
     * @return object|null
     */
    public function getPasteList(User $user): ?object
    {
        $pastes = Paste::where("userId", "=", $user->id)->get();

        if (!count($pastes)) {
            return null;
        }

        foreach ($pastes as $key=>$paste) {
            $pastes[$key] = $this->pasteHydrator->hydrate($paste);
        }

        return $pastes;
    }

    /**
     * @param $token
     * @return Paste|null
     */
    public function getPasteByToken($token): ?Paste
    {
        $paste = Paste::where("token", "=", $token)->first();

        if ($paste === null) {
            return null;
        }

        if ($paste->deleted_at !== null) {
            $carbon = Carbon::createFromFormat('Y-m-d H:i:s', $paste->deleted_at);

            if ($carbon->getTimestamp() <= Carbon::now()->getTimestamp()) {
                $paste->active = false;
            }
        }

        if (!$paste->active) {
            $paste->active = false;
        }

        $paste->views ++;
        $paste->save();

        return $this->pasteHydrator->hydrate($paste);
    }
}