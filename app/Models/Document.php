<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
   protected $fillable =[
    "documentName",
    "folder_id",
    "description",
    "judge",
    "whoMadeIt",
    "dateOfUpload",
    "photo",
    "record",
    ];
}
