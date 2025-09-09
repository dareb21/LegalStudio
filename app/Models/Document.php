<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use softDeletes;
   protected $fillable =[
    "documentName",
    "folder_id",
    "description",
    "judge",
    "whoMadeIt",
    "dateOfUpload",
    "isSensitive",
    "photo",
    "record",
    "important",
    "hardDelete",
    "folderPath",
    "deleted_by",
    "deleted_by_name"
    ];
}
