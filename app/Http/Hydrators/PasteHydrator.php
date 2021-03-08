<?php
namespace App\Http\Hydrators;

use App\Http\Managers\LanguageManager;
use App\Models\Paste;

class PasteHydrator
{

    private LanguageManager $languageManager;

    public function __construct()
    {
        $this->languageManager = new LanguageManager();
    }

    /**
     * @param Paste $paste
     * @return Paste
     */
    public function hydrate(Paste $paste): ?Paste
    {
        if ($paste->languageId !== null) {
            $paste->language = $this->languageManager->getLanguageById($paste->languageId)->displayname;
        }

        return $paste;
    }
}