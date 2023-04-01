<?php

namespace LookstarKernel\Application\Central\Contacts\Models;

use Illuminate\Database\Eloquent\Model;

class FieldRule extends Model
{
    protected $table = 'contacts_field_rule';

    protected $fillable = [
        'name',
        'label',
        'description',
        'extend',
    ];

    protected $casts = [
        'extend' => 'json',
    ];
}
