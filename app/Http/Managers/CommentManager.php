<?php
namespace App\Http\Managers;

use App\Models\Comment;
use App\Models\Paste;
use App\Models\User;

class CommentManager
{

    private AuthenticationManager $authenticationManager;

    /** @var CryptoManager $cryptoManager */
    private CryptoManager $cryptoManager;

    public function __construct()
    {
        $this->authenticationManager = new AuthenticationManager();
        $this->cryptoManager = new CryptoManager();
    }

    /**
     * @param Paste $paste
     * @param User $user
     * @param string $message
     * @return Comment
     */
    public function createComment(Paste $paste, User $user, string $message): ?Comment
    {
        if (!isset($user->id, $paste->id) || $paste === null) {
            return null;
        }

        $comment = new Comment();

        $comment->pasteId = $paste->id;
        $comment->userId = $user->id;
        $comment->message = $this->cryptoManager->encrypt($message);

        $comment->save();
        return $comment;
    }

    /**
     * @param $token
     * @return Comment[]
     */
    public function getPasteComments($token): ?object
    {
        $paste = Paste::where("token", "=", $token)->first();

        if ($paste === null) {
            return [];
        }

        $commentList = Comment::select("id", "userId", "message", "likes", "created_at", "updated_at")
            ->where("pasteId", "=", $paste->id)
            ->get();

        foreach ($commentList as $comment) {
            $comment->user = $this->authenticationManager->getUserById($comment->userId);
            unset($comment->userId);
        }

        return $commentList;
    }

    /**
     * @param $token
     * @param $comment
     * @return Comment
     */
    public function getPasteComment(string $token, string $comment): ?Comment
    {
        $paste = Paste::where("token", "=", $token)->first();

        if ($paste === null) {
            return null;
        }

        return Comment::where([
            "pasteId" => $paste->id,
            "id" => $comment
        ])->first();
    }
}
