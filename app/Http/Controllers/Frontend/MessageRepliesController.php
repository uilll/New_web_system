<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Controllers;

use App\Http\Requests\CreateMessageReplyRequest;
use App\Http\Requests\UpdateMessageReplyRequest;
use App\message_replies;
use App\Message;
use Illuminate\Http\Request;

class MessageRepliesController extends Controller
{
    public function index($message_id)
    {
        $message = Message::find($message_id);
        if (!$message) {
            abort(404);
        }
        $replies = $message->replies()->get();

        return view('replies.index', compact('message', 'replies'));
    }

    public function create($message_id)
    {
        $message = Message::find($message_id);
        if (!$message) {
            abort(404);
        }

        return view('replies.create', compact('message'));
    }

    public function store(Request $request, $message_id)
    {
        //debugar(true, "inicio");
        try {
            $message = Message::find($message_id);
            if (!$message) {
                abort(404);
            }
            //debugar(true, $message);

            $reply = new message_replies();
            $reply->message_id = $message_id;
            $reply->client_id = $request->input('client_id');
            $reply->company_id = $request->input('company_id');
            $reply->user_id = $request->input('user_id');
            $reply->sender_type = $request->input('sender_type');
            $reply->body = $request->input('body');
            $reply->save();

            //debugar(true, $reply);

            return response()->json([
                'success' => true,
                'message_id' => $message_id,
                'client_id' => $reply->client_id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function edit($message_id, $id)
    {
        $message = Message::find($message_id);
        if (!$message) {
            abort(404);
        }
        $reply = message_replies::find($id);
        if (!$reply) {
            abort(404);
        }

        return view('replies.edit', compact('message', 'reply'));
    }

    public function update(UpdateMessageReplyRequest $request, $message_id, $id)
    {
        $message = Message::find($message_id);
        if (!$message) {
            abort(404);
        }
        $reply = message_replies::find($id);
        if (!$reply) {
            abort(404);
        }

        $reply->client_id = $request->input('client_id');
        $reply->company_id = $request->input('company_id');
        $reply->user_id = $request->input('user_id');
        $reply->sender_type = $request->input('sender_type');
        $reply->body = $request->input('body');
        $reply->save();

        return redirect()->route('admin::Messages.index', $message_id);
    }

    public function destroy($message_id, $id)
    {
        $message = Message::find($message_id);
        if (!$message) {
            abort(404);
        }
        $reply = message_replies::find($id);
        if (!$reply) {
            abort(404);
        }
        $reply->delete();

        return redirect()->route('admin::Messages.index', $message_id);
    }

    public function updateIsRead($id)
    {
        // Encontre todas as respostas associadas à mensagem com o ID fornecido
        $messageReplies = message_replies::where('message_id', $id)->get();

        // Verifique se existem respostas associadas à mensagem
        if ($messageReplies->isEmpty()) {
            return response()->json(['error' => 'Nenhuma resposta encontrada'], 404);
        }

        // Atualize o campo is_read para verdadeiro (1) para todas as respostas
        foreach ($messageReplies as $messageReply) {
            $messageReply->is_read = 1;
            $messageReply->save();
        }

        return response()->json(['success' => 'O estado "Visto" das respostas foi atualizado com sucesso'], 200);
    }


}
