<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    protected $guarded = ['id'];
    protected function casts(): array { return ['context' => 'array']; }
    public function person() { return $this->belongsTo(Person::class); }
    public function getSafeMessageAttribute(): string { return \App\Services\SensitiveDataRedactor::text($this->message) ?? ''; }
    public function getSafeContextAttribute(): mixed { return \App\Services\SensitiveDataRedactor::context($this->context); }
}
