<?php

namespace Souravmsh\PasswordPolicy\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordPolicyRules extends Model
{
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = ['id'];
    protected $table = 'password_policy_rules';
}
