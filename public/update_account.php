<?php //namespace App\Http\Controllers\Frontend;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

//require '/var/www/html/releases/20190129073809/vendor/autoload.php';
require __DIR__.'/../vendor/autoload.php';

try {
    //debugar($_POST['data']);
    $data = str_replace('MAIS', '+', $_POST['data']);
    $password = 'henriqueeviado';
    //$base64Encrypted = $data;

    $cryptor = new \RNCryptor\RNCryptor\Decryptor;
    $plaintext = $cryptor->decrypt($data, $password);
    $plaintext = strip_tags($plaintext);

    if (str_contains($plaintext, 'Número do usário:')) {
        $plaintext = str_replace(' ', '', $plaintext);
        //quantidade de caracteres até os dois pontos: 15
        $tamanho = strlen($plaintext);
        $inicio_data = strpos($plaintext, 'Data:');
        $user = substr($plaintext, 17, $inicio_data - 17);
        $data = substr($plaintext, $inicio_data + 5, $tamanho);
        //debugar($user." ".$data);
        //echo ($user." ".$data);
        adicionar_mes($user);
    } else {
        echo 'Erro: Não foi possível processar os dados, entre em contato com o nosso financeiro';
    }
} catch(Exception $e) {
    debugar($e);
}

//echo $plaintext;

function debugar($texto)
{
    $fp = fopen('/var/www/html/releases/20190129073809/public/debug.txt', 'a+');
    fwrite($fp, "\r\n ".$texto." \r\n");
    fclose($fp);
}

function adicionar_mes($user)
{
    try {
        $url = 'https://app.onlysat.com.br/update_account_user/'.$user;
        //$data = array('user' => '$user');

        //$context  = stream_context_create($options);
        $result = file_get_contents($url);
        debugar($result);
        echo $result;
    } catch(Exception $e) {
        debugar($e);
    }

    /*

    try {
        $data_ = Carbon::now("-3");
        $data_now = Carbon::now("-3");
        $data_->addMonth(1);
        $data_no_BD = DB::table('users')->where('id', $user)->get(['subscription_expiration']);

        foreach($data_no_BD as $coluna){
            $data_no_BD = Carbon::parse($coluna->subscription_expiration, '+3');
        }

        if($data_no_BD->lessThan($data_now)){
            DB::table('users')->where('id', $user)->update(['subscription_expiration' => $data_]);
            $externo = $data_->format('d/m/Y');
            echo $externo->created_at->toDateString();
        }
        else{
            DB::table('users')->where('id', $user)->update(['subscription_expiration' => $data_no_BD->addMonth(1)]);
            $externo = $data_no_BD->format('d/m/Y');

            echo $externo;
        }
    } catch (Exception $e){
        debugar($e);
        echo "Erro: Não foi possível processar os dados, entre em contato com o nosso financeiro (2).";
    }
    */
}

?>	