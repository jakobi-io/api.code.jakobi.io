<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Comment
 *
 * @licence Copyright &copy; 2020 jakobi.io
 * @package App\Models
 * @author Lukas Jakobi <lukas@jakobi.io>
 * @since 29.10.2020
 */
class Comment extends Model
{
    use HasFactory;

    /** @var string $table */
    protected $table = 'comment';

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
        'userId',
        'pasteId',
        'message',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [

    ];
}
