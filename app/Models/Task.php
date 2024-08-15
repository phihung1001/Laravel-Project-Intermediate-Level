<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    // Đặt tên bảng nếu không theo quy tắc mặc định
    protected $table = 'tasks';
    // Các thuộc tính có thể được gán hàng loạt
    protected $fillable = [ 'user_id', 'title', 'description', 'completed'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public $timestamps = true;
}
