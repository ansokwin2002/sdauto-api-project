<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'home_settings';

    protected $fillable = [
        'address',
        'email',
        'phone',
        'logo',
        'title',
        'description',
        'welcome_logo',
        'title_welcome',
        'description_welcome',
        'why_choose_logo',
        'why_choose_title',
        'why_choose_title1',
        'why_choose_description1',
        'why_choose_title2',
        'why_choose_description2',
        'why_choose_title3',
        'why_choose_description3',
        'why_choose_title4',
        'why_choose_description4',
    ];
}
