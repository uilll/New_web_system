<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers;

use Illuminate\Http\Request;
use App\Message;
use App\message_replies;
use App\Http\Requests\CreateMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class MessageController extends Controller
{
    public function index_client()
    {   
        $user_id = Auth::user()->id; // Obter o ID do cliente logado
         // Buscar o objeto Customer associado ao usuário logado
         $customer = customer::select('id', 'all_users')
            ->whereNotNull('all_users')
            ->where('all_users', 'LIKE', '%"'.$user_id.'"%')
            ->first();
        //dd($customers);
         $client_id = null;

        if (!$customer) {
            // Tratar o caso em que o usuário logado não está associado a um Customer
            // Você pode redirecionar para uma página de erro, por exemplo
            $messages = collect(); // Cria uma coleção vazia
        } else {
            $client_id = $customer->id;
            // Buscar as mensagens associadas ao cliente
            $messages = Message::where('client_id', $client_id)->orderBy('created_at', 'desc')->get();
            //dd($messages);
        }
        
        $messages = Message::where('client_id', $client_id)->orderBy('created_at', 'desc')->get();
        return view('front::Message.index')->with(compact('messages'));
    }


    public function index_admin()
    {
        if(Auth::user()->isAdmin())
            $customers = customer::orderby('id', 'asc')
                    ->where('manager_id', 0)
                    ->get();
        else
            $customers = customer::orderby('id', 'asc')
            ->where('manager_id', Auth::User()->id)
            ->get();
        $messages = Message::orderBy('created_at', 'desc')
            ->get();
        
        //dd ($messages);
        $replies = message_replies::all();
        return view('admin::Messages.index')->with(compact('messages','replies','customers'));
    }

    public function create()
    {
        return view('admin::Messages.create');
    }

    public function store(Request $request)
    {
        if(Auth::user()->isAdmin())
            $company_id = 0;
        else    
            $company_id = Auth::User()->id;

        //debugar(true, $request->input('customer_id'));
        $message = new Message();
        $message->client_id = $request->input('customer_id');
        $message->company_id = $company_id;
        $message->user_id = Auth::User()->id;
        $message->is_to_client = $request->input('is_to_client');
        $message->subject = $request->input('subject');
        $message->body = $request->input('message');
        $message->save();

        return Response::json(['status' => 1]);
        //return redirect()->route('messages.show', $message->id);
    }

    public function show($id)
    {
        $message = Message::find($id);
        if (!$message) {
            abort(404);
        }
        $message->load('client');

        return view('messages.show', compact('message'));
    }

    public function edit($id)
    {
        $message = Message::find($id);
        if (!$message) {
            abort(404);
        }

        return view('messages.edit', compact('message'));
    }

    public function update(UpdateMessageRequest $request, $id)
    {
        $message = Message::find($id);
        if (!$message) {
            abort(404);
        }

        $message->client_id = $request->input('client_id');
        $message->company_id = $request->input('company_id');
        $message->user_id = $request->input('user_id');
        $message->is_to_client = $request->input('is_to_client');
        $message->subject = $request->input('subject');
        $message->body = $request->input('body');
        $message->save();

        return redirect()->route('messages.show', $message->id);
    }

    public function destroy($id)
    {
        $message = Message::find($id);
        if (!$message) {
            abort(404);
        }
        $message->delete();

        return redirect()->route('messages.index');
    }

    public function getMessagesByClientAndSubject($client_id, $subject)
    {
        try {
            $message = Message::where('client_id', $client_id)
                            ->where('id', $subject)
                            ->firstOrFail(['id', 'subject', 'client_id', 'body', 'is_to_client']);
            
            $messages = message_replies::where('message_id', $message->id)
                                    ->orderBy('created_at', 'asc')
                                    ->get(['id','body', 'sender_type', 'is_read']);
            //dd($messages );
            if ($message) {
                //dd($messages );
                return view('admin::Messages.messages', compact('message', 'messages'));
            } else {
                return response()->json(['message' => 'Nenhuma mensagem encontrada'], 404);
            }
        } catch (\Exception $e) {
            debugar(true, $e);
            return response()->json(['message' => 'Erro ao obter as mensagens'], 500);
        }
    }



}
