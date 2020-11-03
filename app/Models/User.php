<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * User Paste
 *
 * @licence Copyright &copy; 2020 jakobi.io
 * @package App\Models
 * @author Lukas Jakobi <lukas@jakobi.io>
 * @since 01.11.2020
 */
class User extends Model
{
    use HasFactory;

    /** @var string $table */
    protected $table = 'accounts';

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
        'email',
        'verifycode',
        'apitoken',
        'username',
        'active',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'languageId',
        'verifycode',
        'apitoken',
        'active',
    ];
}
