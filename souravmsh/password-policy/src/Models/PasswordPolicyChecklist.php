<?php

namespace Souravmsh\PasswordPolicy\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordPolicyChecklist extends Model
{
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = ['id'];
    protected $table = 'password_policy_checklist';
}
