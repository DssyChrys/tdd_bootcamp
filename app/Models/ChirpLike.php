<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChirpLike extends Model
{
    use HasFactory;

    // Autoriser l'attribution de masse pour ces colonnes
    protected $fillable = ['chirp_id', 'user_id'];

    // Relation : un like appartient à un Chirp
    public function chirp()
    {
        return $this->belongsTo(Chirp::class);
    }

    // Relation : un like appartient à un utilisateur
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
