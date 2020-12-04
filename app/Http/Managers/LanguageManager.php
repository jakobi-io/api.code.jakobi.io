<?php
namespace App\Http\Managers;

use App\Models\Language;

class LanguageManager
{

    /**
     * PasteManager constructor.
     */
    public function __construct()
    {

    }

    /**
     * @param int $id
     * @return Language|null
     */
    public function getLanguageById(int $id): ?Language
    {
        return Language::where("id", "=", $id)->first();
    }

    public function getLanguageBySlug(string $slug): ?Language
    {
        return Language::where("slug", "=", $slug)->first();
    }
}