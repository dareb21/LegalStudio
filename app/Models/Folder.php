<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Folder extends Model
{
      use softDeletes;
      protected $fillable =[
      "folderName",
      "parentFolder",
      "folderPath", 
      "type",
      "important"
    ];
    
}
