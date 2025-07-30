<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
   protected $fillable =[
    "documentPath",
    "description",
    "judge",
    "whoMadeIt",
    "dateOfUpload",
    "photo",
    "record_id",
    ];
}
