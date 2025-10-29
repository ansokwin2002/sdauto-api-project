<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryPartner extends Model
{
    use HasFactory;

    protected $table = 'delivery_partners';

    protected $fillable = [
        'title',
        'description',
        'image',
        'url_link',
    ];
}
