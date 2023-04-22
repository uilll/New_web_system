<?php namespace App\Exceptions;

use Exception;
use PDOException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Predis\Cluster\Distributor\EmptyRingException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Exceptions\ResourseNotFoundException;
use App\Exceptions\PermissionException;
use Tobuli\Exceptions\ValidationException;

use Bugsnag\BugsnagLaravel\BugsnagFacade as Bugsnag;


class Handler extends ExceptionHandler {

	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
    protected $dontReport = [
        HttpException::class,
        ModelNotFoundException::class,

        ResourseNotFoundException::class,
        PermissionException::class,
        DeviceLimitException::class,
        DemoAccountException::class,
        ValidationException::class
    ];

	/**
	 * Report or log an exception.
	 *
	 * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	public function report(Exception $e)
	{
        if ($this->reportBugsnag($e))
            return;

        if (app()->environment() != 'local')
            return;

        parent::report($e);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Illuminate\Http\Response
	 */
	public function render($request, Exception $e)
	{
        if ($this->isApiCall($request))
            return $this->renderAPI($request, $e);

        if ($request->ajax())
            return $this->renderAjax($request, $e);

		return parent::render($request, $e);
	}

    public function isApiCall($request)
    {
        return $request->is('api/*');
    }

    public function renderAPI($request, Exception $e)
    {
        return $this->renderJson($request, $e);
    }

    public function renderAjax($request, Exception $e)
    {
        if ($request->wantsJson())
            return $this->renderJson($request, $e);

        $response = $this->getResponse($e);

        return response(view("front::Layouts.partials.modal_warning", [
            'message' => $response['message'],
            'type' => 'danger'
        ]), $response['statusCode']);
    }
    public function renderJson($request, Exception $e)
    {
        // Define the response
        $response = $this->getResponse($e);

        $response['status'] = 0;
        $response['errors'] = ['id' => $response['message']];

        if ($e instanceof ValidationException)
            $response['errors'] = $e->getErrors();

        if ($response['statusCode'] == 403) {
            $response['perm'] = 0;
        }

        // Return a JSON response with the response array and status code
        return response()->json($response, $response['statusCode']);
    }

    public function reportBugsnag(Exception $e)
    {
        if ( ! app()->bound('bugsnag'))
            return false;

        if (app()->environment() == 'local')
            return false;

        if ($this->shouldntReport($e))
            return false;

        if ($e instanceof PDOException && $e->getCode() == 2002)
            return false;

        Bugsnag::setAppVersion(config('tobuli.version'));
        Bugsnag::setMetaData([
            'host' => [
                'ip'   => function_exists('getSomething') ? getSomething() : '',
                'name' => env('server')
            ]
        ]);
        Bugsnag::notifyException($e);

        return true;
    }

    protected function getResponse(Exception $e)
    {
        switch(true) {
            case $e instanceof NotFoundHttpException:
                $message = 'The endpoint you are looking for could not be found.';
                break;
            case $e instanceof ResourseNotFoundException:
            case $e instanceof ValidationException:
            case $e instanceof PermissionException:
            case $e instanceof DemoAccountException:
            case $e instanceof DeviceLimitException:
                $message = $e->getMessage();
                break;

            case env('APP_DEBUG', false):
                $message = $e->getMessage() . ' ' .  $e->getFile() . ' ' . $e->getLine();
                break;

            default:
                $message = 'Opa, parece que algo está errado. Tente novamente, se o problema persistir, <a href="#" onclick="window.location.reload(true);">clique aqui</a> para recarregar a página.';
                $refreshCode = "<script>setTimeout(function(){window.location.reload(true)},5000);</script>";
                $message .= $refreshCode;
                break;
            
        }

        $status = $this->isHttpException($e) ? $e->getStatusCode() : 400;

        return [
            'statusCode'  => $status,
            'message'     => $message,
        ];
    }

}
