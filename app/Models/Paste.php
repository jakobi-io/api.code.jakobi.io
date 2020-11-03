<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Paste
 *
 * @licence Copyright &copy; 2020 jakobi.io
 * @package App\Models
 * @author Lukas Jakobi <lukas@jakobi.io>
 * @since 29.10.2020
 */
class Paste extends Model
{
    use HasFactory;

    /** @var string $table */
    protected $table = 'paste';

    /** @var bool $incrementing */
    public $incrementing = true;

    /** @var bool $timestamps */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'token',
        'description',
        'code',
        'languageId',
        'userId',
        'password',
        'active',
        'views',
        'deleted_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'languageId',
    ];
}
