<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    protected $guarded = ['id'];
    protected function casts(): array { return ['context' => 'array']; }
    public function person() { return $this->belongsTo(Person::class); }
}
