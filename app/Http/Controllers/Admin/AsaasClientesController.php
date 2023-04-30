<?php

namespace App\Http\Controllers\Admin;

use App\Services\AsaasService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;

use App\customer;
use Carbon\Carbon;

class AsaasClientesController extends BaseController
{
    protected $asaasService;
    private $section = 'asaas';

    public function __construct(AsaasService $asaasService)
    {
        $this->asaasService = $asaasService;
    }

    public function listarClientes(Request $request)
    {
        $params = $request->all();
        $page = $request->query("page");
        $offset = 0;
        $allCustomers = [];

        do {
            $customers = collect($this->asaasService->get('customers', ['limit' => 100, 'offset' => $offset]))->get('data');
            $allCustomers = array_merge($allCustomers, $customers);
            $offset += 100;
        } while (count($customers) == 100);

        if(request()->has('search_phrase')){
            $searchPhrase = request()->input('search_phrase');
            //dd($searchPhrase);
            
            $clientes = paginate_($allCustomers, 10, $page, [], $searchPhrase);    
        }
        else{
            $clientes = paginate_($allCustomers, 10, $page);    
        }
        //dd($clientes);
        return View::make('admin::Asaas.Clientes.clientes_index')->with(compact('clientes'));
    }

    public function contar()
    {
        $response = collect($this->asaasService->get('customers'))->get('data');
        $count = count($response);        
        return View::make('admin::'.ucfirst($this->section).'.contar');
    }

    public function create()
    {
        return View::make('admin::Asaas.Clientes.create');
    }

    public function cadastrarCliente(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'cpfCnpj' => 'required',
        ]);
        
        $response = $this->asaasService->post('customers', [
            'name' => $request->input('name'),
            'cpfCnpj' => $request->input('cpfCnpj'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'mobilePhone' => $request->input('mobilePhone'),
            'address' => $request->input('address'),
            'addressNumber' => $request->input('addressNumber'),
            'complement' => $request->input('complement'),
            'province' => $request->input('province'),
            'postalCode' => $request->input('postalCode'),
            'externalReference' => $request->input('externalReference'),
            'notificationDisabled' => $request->input('notificationDisabled'),
            'additionalEmails' => $request->input('additionalEmails'),
        ]);

        return Response::json(['status'=>1]);
    }

    public function consultarCliente($id)
    {
        $response = $this->asaasService->get("customers/{$id}");

        return response()->json($response);
    }

    public function edit($id) {
        $item = $this->asaasService->get("customers/{$id}");

        //dd($item);
        
        return View::make('admin::Asaas.Clientes.edit')->with(compact('item'));
    }
  
    public function atualizarCliente(Request $request)
    {
        /*$validator = Validator::make($request->all(), [
            'nome' => 'required',
            'cpfCnpj' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect('asaas/clientes/'.$request->id.'/edit') 
                        ->withErrors($validator)
                        ->withInput();
        }*/

        $this->validate($request, [
            'name' => 'required',
            'cpfCnpj' => 'required',
        ]);

        try{
            $response = $this->asaasService->put("customers/{$request->input('id')}", [
                'name' => $request->input('name'),
                'cpfCnpj' => $request->input('cpfCnpj'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'mobilePhone' => $request->input('mobilePhone'),
                'address' => $request->input('address'),
                'addressNumber' => $request->input('addressNumber'),
                'complement' => $request->input('complement'),
                'province' => $request->input('province'),
                'postalCode' => $request->input('postalCode'),
                'externalReference' => $request->input('externalReference'),
                'notificationDisabled' => $request->input('notificationDisabled'),
                'additionalEmails' => $request->input('additionalEmails'),
            ]);

        }
        catch (Exception $e){
            debugar(true,$e);
        }

        //debugar(true, json_encode($response));
        return Response::json(['status' => 1]);
        
    }

    public function delete($id)
    {
        //$idd = $request->input('id');
        //dd($id);
        return View::make('admin::Asaas.Clientes.delete')->with(compact('id'));
    }

    public function excluirCliente(Request $request)
    {
        //debugar (true, $request->input('id'));
        //dd('oi');
        $this->asaasService->delete("customers/{$request->input('id')}");
        return Response::json(['status' => 1]);
    }

    public function listarCobranças(Request $request)
    {
        $params = $request->all();
        $page = $request->query("page");
        $offset = 0;
        $allPayments = [];
        

        do {
            $Payments = collect($this->asaasService->get('payments', ['limit' => 100, 'offset' => $offset]))->get('data');
            $allPayments = array_merge($allPayments, $Payments);
            $offset += 100;
            //dd($Payments);
        } while (count($Payments) == 100);
        
        $allPayments = collect($allPayments)->map(function ($payment) {
            $customerid = $payment['customer'];
            $jsonResponse = $this->consultarCliente($customerid);
            $content = json_decode($jsonResponse->getContent(), true);
            $payment['name'] = $content['name'];
            return $payment;
        })->toArray();
          
        //dd($allPayments);
        if(request()->has('search_phrase')){
            $searchPhrase = request()->input('search_phrase');
            //dd($searchPhrase);
            
            $cobranças = paginate_($allPayments, 39, $page, [], $searchPhrase);    
        }
        else{
            $cobranças = paginate_($allPayments, 39, $page);    
        }
        
        //print_r($cobranças->items);
        //dd($cobranças);
        return View::make('admin::Asaas.Cobranças.cobranças_index')->with(compact('cobranças'));
    }

    public function cobrar(Request $request)
    {   
        $offset = 0;
        do {
            $customers = collect($this->asaasService->get('customers', ['limit' => 100, 'offset' => $offset]))->get('data');
            $offset += 100;
        } while (count($customers) == 100);

        $id = array_column($customers, 'name', 'id');
        //dd($billingType);
        return View::make('admin::Asaas.Cobranças.create')->with(compact('id'));
    }

    public function criarCobrança(Request $request)
    {
        /*$this->validate($request, [
            'name' => 'required',
            'cpfCnpj' => 'required',
        ]);*/
        
        $response = $this->asaasService->post('payments', [
            'customer' => $request->input('customer'),
            'billingType' => $request->input('billingType'),
            'value' => $request->input('value'),
            'dateCreated' => $request->input('dateCreated'),
            'dueDate' => $request->input('dueDate'),
            'description' => $request->input('description'),
            'installmentCount' => $request->input('installmentCount'),
            'installmentValue' => $request->input('installmentValue'),
            'externalReference' => $request->input('externalReference'),
        ]);

        return Response::json(['status'=>1]);
    }

    public function editCo($id) {
        $item = $this->asaasService->get("payments/{$id}");

        //dd($item);
        return View::make('admin::Asaas.Cobranças.edit')->with(compact('item'));
    }
  
    public function atualizarCobrança(Request $request)
    {
        /*$validator = Validator::make($request->all(), [
            'nome' => 'required',
            'cpfCnpj' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect('asaas/clientes/'.$request->id.'/edit') 
                        ->withErrors($validator)
                        ->withInput();
        }*/

        /*$this->validate($request, [
            'name' => 'required',
            'cpfCnpj' => 'required',
        ]);*/

        $response = $this->asaasService->put("payments/{$request->input('id')}", [
                'name'=> $request->input('name'),
                'billingType' => $request->input('billingType'),
                'value' => $request->input('value'),
                'dateCreated' => $request->input('dateCreated'),
                'dueDate' => $request->input('dueDate'),
                'description' => $request->input('description'),
                'installmentCount' => $request->input('installmentCount'),
                'installmentValue' => $request->input('installmentValue'),
                'externalReference' => $request->input('externalReference'),
        ]);

        //debugar(true, json_encode($response));
        //dd('oi');
        return Response::json(['status' => 1]);
        
    }

    public function deleteCo($id)
    {
        return View::make('admin::Asaas.Cobranças.delete')->with(compact('id'));
    }

    public function excluirCobrança(Request $request)
    {
        //debugar (true, $request->input('id'));
        //dd('oi');
        $this->asaasService->delete("payments/{$request->input('id')}");
        return Response::json(['status' => 1]);
    }

    public function pagar($id) {
        $item = $this->asaasService->get("payments/{$id}");
        
        //dd($item);
        return View::make('admin::Asaas.Cobranças.pagar')->with(compact('item'));
    }

    public function receiveInCash(Request $request)
    {
        $response = $this->asaasService->put("payments/{$request->input('id')}/receiveInCash", [
                'value' => $request->input('value'),
                'paymentDate' => $request->input('paymentDate'),
                'description' => $request->input('description'),
                'notifyCustomer' => $request->input('notifyCustomer'),
        ]);

        //debugar(true, json_encode($response));
        //dd('oi');
        return Response::json(['status' => 1]);
        
    }

    public function teste()
    {
        $clientes = Customer::get()->pluck('cpf_cnpj');
        foreach($clientes as $cpf)
        {
            if(!empty($cpf))
            {
                $customer = collect($this->asaasService->get('customers', ['cpfCnpj' => $cpf]))->get('data');
                $payments = collect($this->asaasService->get("payments", ['customer' => $customer['0']['id'], 'status' => 'OVERDUE']))->get('data'); 
                if(!empty($payments))
                {
                    foreach($payments as $pay){ 
                        if($pay['dueDate'] < Carbon::now()->subDays(30))
                        {
                            dd('bloqueio');
                        }
                    }
                } 
                else
                {
                    dd('desbloqueia');
                }
            }  
        }
    }
}