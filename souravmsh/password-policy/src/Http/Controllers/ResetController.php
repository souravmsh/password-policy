<?php

namespace Souravmsh\PasswordPolicy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Souravmsh\PasswordPolicy\Models\PasswordPolicyRules;
use Souravmsh\PasswordPolicy\Models\PasswordPolicyExpiry;
use App\User;
use Souravmsh\PasswordPolicy\Http\Traits\PasswordPolicy;
use Souravmsh\PasswordPolicy\Http\Traits\ApiResponse;
use Auth, Hash;

class ResetController extends Controller
{    
    use PasswordPolicy, ApiResponse;
    /**
     * instance 
     */
    private $request;
    private $user;
    private $passwordPolicyRules;
    private $passwordPolicyExpiry;
    private $viewPath;
    private $page;
    private $redirect;

    public function __construct(Request $request)
    { 
        $this->request  = $request;
        $this->passwordPolicyRules  = new PasswordPolicyRules;
        $this->passwordPolicyExpiry = new PasswordPolicyExpiry;
        $this->user     = new User;
        $this->viewPath = 'password-policy::reset.';
        $this->page     = __('Password Reset');
        $this->redirect = url('/');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function show()
    {
        $data = (object)[
            'page'   => $this->page,
            'method' => 'Update',
            'action' => route('password-policy.password.reset'),
            'item'   => [],
        ];

        return view($this->viewPath.'modal', compact('data'));
    }

    /**
     * pretend login
     * @param Request $request
     * @return Response
     */
    public function reset(Request $request)
    {
        $this->passwordValidate(); 

        try
        { 
            $data = $this->user->find(auth()->user()->id);
            $data->password = Hash::make($request->password);
            $data->save();

            // rules 
            $rules = $this->passwordPolicyRules
                ->where('attribute', 'password_expiry_days')
                ->first();

            // update expiry table
            $exists = $this->passwordPolicyExpiry
                ->where('user_id', auth()->user()->id)
                ->first();

            if ($exists)
            {
                $this->passwordPolicyExpiry
                    ->where('user_id', auth()->user()->id)
                    ->update([
                        'expiry_days' => !empty($rules->password_expiry_days) ? $rules->password_expiry_days : 30,
                        'updated_at'  => date('Y-m-d')
                    ]);
            }
            else
            { 
                $this->passwordPolicyExpiry
                    ->insert([
                        'user_id'     => auth()->user()->id,
                        'expiry_days' => !empty($rules->password_expiry_days) ? $rules->password_expiry_days : 30,
                        'updated_at'  => date('Y-m-d')
                    ]);
            }
            
            // delete all cache
            $this->passwordForgetCache();
            
            return redirect($this->redirect)
                ->with('toast_success', 'Update successful!')
                ->withInput(); 
        }
        catch (\Exception $e)
        {
            return back()
                ->with('toast_error', 'Something went wrong, Internal Server Error')
                ->withInput();
        }
    } 
}
