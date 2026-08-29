<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonTypeHistory extends Model
{
    protected $guarded = ['id'];
    public function fromType()
    {
        return $this->belongsTo(TypePerson::class, 'from_type_person_id');
    }
    public function toType()
    {
        return $this->belongsTo(TypePerson::class, 'to_type_person_id');
    }
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
