<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatabaseBackup extends Model
{
    protected $table = 'database_backups';
    protected $primaryKey = 'idBac';

    protected $fillable = [
        'nomBac', 'verBac', 'arcBac', 'obsBac', 'tipBac', 'idUsuBac'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'idUsuBac', 'id');
    }
}
