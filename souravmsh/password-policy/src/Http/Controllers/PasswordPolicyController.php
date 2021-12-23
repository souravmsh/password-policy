<?php

namespace Souravmsh\PasswordPolicy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Souravmsh\PasswordPolicy\Models\PasswordPolicyExpiry;
use Souravmsh\PasswordPolicy\Models\PasswordPolicyRules;
use Souravmsh\PasswordPolicy\Http\Traits\PasswordPolicy;
use Souravmsh\PasswordPolicy\Http\Traits\ApiResponse;
use App\User; 
use Carbon\Carbon;

class PasswordPolicyController extends Controller
{     
    use PasswordPolicy, ApiResponse;

    private $request;
    private $passwordPolicyExpiry;
    private $passwordPolicyRules;
    private $user;
    private $viewPath;
    private $page;
    private $redirect;

    public function __construct(Request $request)
    {
        $this->request   = $request;
        $this->passwordPolicyExpiry = new PasswordPolicyExpiry;
        $this->passwordPolicyRules  = new PasswordPolicyRules;
        $this->user                   = new User;
        $this->viewPath = 'password-policy::expiry.';
        $this->page     = __('User Expiration');
        $this->redirect = route('password-policy.expiry');
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $page   = $this->page;
        $users = $this->passwordPolicyExpiry
            ->with('user:id,name,email')
            ->get()
            ->map(function($item, $key){
                return [
                    'id'   => ($item->user->id ?? null),
                    'name' => ($item->user ? ($item->user->name.' <'.$item->user->email.'>') : null)
                ];
            })
            ->pluck('name', 'id')
            ->reject(function($item){
                return $item === null;
            }); 
        $result = $this->getQuery()->paginate(10);

        return view($this->viewPath.'index', compact('page', 'users', 'result'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {   
        $data = (object)[
            'page'   => $this->page,
            'method' => 'Create',
            'action' => route('password-policy.expiry.save'),
            'item'   => [],
            'users'  => $this->user
                ->selectRaw('CONCAT(name, "  <", email, ">") AS name, id')
                ->pluck('name', 'id'),
            'rules'  => $this->passwordPolicyRules->where('attribute', 'password_expiry_days')->first()
        ];

        return view($this->viewPath . 'form', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'      => 'required|unique:password_policy_expiry,user_id',
            'expiry_days'  => 'required|numeric|max:365',
            'updated_at'   => 'required|date_format:d/m/Y'
        ]);

        try
        {
            $data = $this->passwordPolicyExpiry;
            $data->user_id      = $request->user_id;
            $data->expiry_days  = $request->expiry_days;
            $data->updated_at   = Carbon::parse(str_replace('/', '-', $request->updated_at))->format('Y-m-d');
            $data->save();

            // delete all cache
            $this->passwordForgetCache();

            return $this->ajaxSuccess($data, __('Saved successfully'), $this->redirect);
        }
        catch (\Exception $e)
        {
            return $this->ajaxError($e, __('Something went wrong, Internal Server Error'), $this->redirect); 
        }
    }


    /**
     * Show the form for updating a resource.
     * @return Response
     */
    public function edit($id)
    {
        $data = (object)[
            'page'   => $this->page,
            'method' => 'Update',
            'action' => route('password-policy.expiry.update', ['id' => $id]),
            'item'   => $this->passwordPolicyExpiry->find($id),
            'users'  => $this->user
                ->selectRaw('CONCAT(name, "  <", email, ">") AS name, id')
                ->pluck('name', 'id'),
            'rules'  => $this->passwordPolicyRules->where('attribute', 'password_expiry_days')->first()
        ]; 

        return view($this->viewPath . 'form', compact('data'));
    }


    /**
     * update a resource in storage.
     * @param Request $request
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id'      => 'required|unique:password_policy_expiry,user_id,'.$id,
            'expiry_days'  => 'required|numeric|max:365',
            'updated_at'   => 'required|date_format:d/m/Y'
        ]);

        try
        {
            $data = $this->passwordPolicyExpiry->find($id);
            $data->user_id      = $request->user_id;
            $data->expiry_days  = $request->expiry_days;
            $data->updated_at   = Carbon::parse(str_replace('/', '-', $request->updated_at))->format('Y-m-d');
            $data->save();
            
            // delete all cache
            $this->passwordForgetCache();

            return $this->ajaxSuccess($data, __('Update successfully'), $this->redirect);
        }
        catch (\Exception $e)
        {
            return $this->ajaxError($e, __('Something went wrong, Internal Server Error'), $this->redirect); 
        }
    }

    private function getQuery()
    {
        return $this->passwordPolicyExpiry
            ->selectRaw('*, DATE_ADD(updated_at, INTERVAL expiry_days DAY) AS expiry_date')
            ->with('user:id,name,email')
            ->whereHas('user', function ($q) {
                if (!empty($this->request->user_id)) {
                    $q->where('id', $this->request->user_id);
                }
            })
            ->where(function($q){ 
                if (!empty($this->request->status)) {
                    if ($this->request->status == 'valid')
                    {
                        $q->whereRaw('DATE_ADD(updated_at, INTERVAL expiry_days DAY) >= ?', [Carbon::now()->format('Y-m-d')]);
                    }
                    else
                    {
                        $q->whereRaw('DATE_ADD(updated_at, INTERVAL expiry_days DAY) < ? ', [Carbon::now()->format('Y-m-d')]);
                    } 
                } 
            });
    } 
}
