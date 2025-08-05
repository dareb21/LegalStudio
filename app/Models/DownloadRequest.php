<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadRequest extends Model
{
    protected $fillable =[
    "document_id",
    "requestDate",
    "requested_by",
    "status",
    "responded_by",
    "responseDate",
    ];
}
