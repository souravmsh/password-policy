<?php

namespace Souravmsh\PasswordPolicy\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class PasswordPolicyExpiry extends Model
{
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = ['id'];
    protected $table = 'password_policy_expiry';

    public function user()
    {
    	return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
