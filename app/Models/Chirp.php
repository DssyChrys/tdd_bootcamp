<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Events\ChirpCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chirp extends Model
{

    use HasFactory;

    protected $fillable = [

        'message',
        'user_id',

    ];
    protected $dispatchesEvents = [

        'created' => ChirpCreated::class,

    ];
    public function user(): BelongsTo

    {

        return $this->belongsTo(User::class);

    }
    public function likes()
    {
        return $this->hasMany(ChirpLike::class);
    }
    

}
