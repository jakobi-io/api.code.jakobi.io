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

    /**
     * PasteManager constructor.
     */
    public function __construct()
    {
        $this->pasteHydrator = new PasteHydrator();
    }

    /**
     * @param User $user
     * @return array|null
     */
    public function getPasteList(User $user): ?array
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
                $paste->deleted = true;
            }
        }

        if (!$paste->active) {
            $paste->deleted = true;
        }

        return $this->pasteHydrator->hydrate($paste);
    }
}