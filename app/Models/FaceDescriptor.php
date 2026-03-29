<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceDescriptor extends Model
{
    protected $fillable = [
        'user_id',
        'descriptor',
        'label',
    ];

    protected function casts(): array
    {
        return [
            'descriptor' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
