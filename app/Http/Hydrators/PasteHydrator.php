<?php
namespace App\Http\Hydrators;

use App\Http\Managers\AuthenticationManager;
use App\Http\Managers\CommentManager;
use App\Http\Managers\LanguageManager;
use App\Models\Paste;

class PasteHydrator
{

    private CommentManager $commentManager;
    private AuthenticationManager $authenticationManager;
    private LanguageManager $languageManager;

    public function __construct()
    {
        $this->commentManager = new CommentManager();
        $this->authenticationManager = new AuthenticationManager();
        $this->languageManager = new LanguageManager();
    }

    /**
     * @param Paste $paste
     * @return Paste
     */
    public function hydrate(Paste $paste): ?Paste
    {
        // add comments
        $paste->comments = $this->commentManager->getPasteComments($paste->token);

        if ($paste->languageId !== null) {
            $paste->language = $this->languageManager->getLanguageById($paste->languageId);
        }

        if ($paste->userId !== null) {
            $paste->user = $this->authenticationManager->getUserById($paste->userId);
        }

        return $paste;
    }
}