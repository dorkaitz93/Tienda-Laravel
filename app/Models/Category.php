<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    // Lista blanca de seguridad
    protected $fillable = ['name', 'slug'];

    // Relación: Una categoría tiene MUCHOS productos (Plural)
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
