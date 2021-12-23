<?php
 
namespace Souravmsh\PasswordPolicy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Souravmsh\PasswordPolicy\Models\PasswordPolicyChecklist;
use Souravmsh\PasswordPolicy\Http\Traits\PasswordPolicy;
use Souravmsh\PasswordPolicy\Http\Traits\ApiResponse;

class ChecklistController extends Controller
{    
    use PasswordPolicy, ApiResponse;

    private $request;
    private $password_policy_checklist;
    private $viewPath;
    private $page;
    private $redirect;

    public function __construct(Request $request)
    {
        $this->password_policy_checklist = new PasswordPolicyChecklist;
        $this->request  = $request;
        $this->viewPath = 'password-policy::checklist.';
        $this->page     = __('Password Checklist');
        $this->redirect = route('password-policy.checklist');
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $page   = $this->page;
        $result = $this->getQuery()->paginate(10);

        return view($this->viewPath.'index', compact('page','result'));
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
            'action' => route('password-policy.checklist.save'),
            'item'   => [],
        ];

        return view($this->viewPath.'form', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'password' => 'required|between:1,255|unique:password_policy_checklist,password',
            'status'   => 'required|in:0,1'
        ]);

        try
        {
            $data = $this->password_policy_checklist;
            $data->password = $request->password; 
            $data->status   = $request->status;
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
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        $data = (object)[
            'page'   => $this->page,
            'method' => 'Update',
            'action' => route('password-policy.checklist.update', ['id' => $id]),
            'item'   => $this->password_policy_checklist->findOrFail($id)
        ];
        
        return view($this->viewPath.'form', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|between:1,255|unique:password_policy_checklist,password,'.$id,
            'status'   => 'required',
        ]);

        try
        {
            $data = $this->password_policy_checklist->find($id);
            $data->password  = $request->password;
            $data->status    = $request->status;
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
        return $this->password_policy_checklist
            ->where(function($q){
                if (!empty($this->request->password)) {
                    $q->where('password', $this->request->password);
                }
                if (!empty($this->request->status)) {
                    $q->where('status', ($this->request->status=='Active')?'1':'0');
                }
            })
            ->orderBy('password', 'asc');
    } 
}
