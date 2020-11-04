<?php
namespace App\Http\Managers\Object;

use App\Models\Comment;
use App\Models\Paste;
use App\Models\User;

class CommentManager
{

    /**
     * @param $token
     * @return Comment[]
     */
    public static function getPasteComments($token)
    {
        $paste = Paste::where("token", "=", $token)->first();

        if ($paste === null) {
            return [];
        }

        $commentList = Comment::select("id", "userId", "message", "likes", "created_at", "updated_at")->where("pasteId", "=", $paste->id)->get();

        foreach ($commentList as $comment) {
            $comment->user = $comment->userId === null ? null : User::where("id", "=", $comment->userId)->first();
            unset($comment->userId);
        }

        return $commentList;
    }

    /**
     * @param $token
     * @param $comment
     * @return Comment
     */
    public static function getPasteComment(string $token, string $comment): ?Comment
    {
        $paste = Paste::where("token", "=", $token)->first();

        if ($paste === null) {
            return null;
        }

        return Comment::select("id", "userId", "message", "likes", "created_at", "updated_at")->where([
            "pasteId" => $paste->id,
            "id" => $comment
        ])->first();
    }
}
