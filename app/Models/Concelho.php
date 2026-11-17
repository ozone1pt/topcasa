<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Concelho extends Model
{
    use HasFactory;

    protected $fillable = ['distrito_id', 'nome'];

    public function distrito(): BelongsTo
    {
        return $this->belongsTo(Distrito::class);
    }
}
