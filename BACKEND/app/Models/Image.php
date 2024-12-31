<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = ['class_id', 'file_path', 'order'];

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }
}
