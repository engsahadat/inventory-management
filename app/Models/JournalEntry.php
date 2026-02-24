<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_date',
        'type',
        'description',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    /**
     * Get the journal lines for the entry.
     */
    public function journalLines()
    {
        return $this->hasMany(JournalLine::class);
    }

    /**
     * Get sales associated with this journal entry.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
