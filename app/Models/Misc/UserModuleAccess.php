<?php

namespace App\Models\Misc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserModuleAccess extends Model
{
    use HasFactory;

    protected $table = 'user_module_access';
    protected $fillable = ["user_id", "module_id", "has_access"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
