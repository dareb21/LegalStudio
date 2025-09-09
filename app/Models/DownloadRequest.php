<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DownloadRequest extends Model
{
    protected $fillable =[
    "document_id",
    "document_name",
    "requestDate",
    "requested_by",
    "requested_by_name",
    "status",
    "responded_by",
    "responseDate",
    ];
}
