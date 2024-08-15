<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teams extends Model
{
    use HasFactory;
    // Đặt tên bảng nếu không theo quy tắc mặc định
    protected $table = 'teams';
    protected $fillable = ['name'];

    public function users() {
        return $this->hasMany(User::class);
    }

}
