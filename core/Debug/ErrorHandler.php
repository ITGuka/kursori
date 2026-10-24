<?php
declare(strict_types=1);

namespace Core\Debug;

use Core\App;
use Core\Http\HttpException;
use Core\Http\Request;
use Core\Http\Response;
use Core\Support\Log;
use Core\View\View;
use ErrorException;
use Throwable;

/**
 * PHP შეცდომები → ErrorException; გამონაკლისები → ლოგი + შეცდომის გვერდი.
 * debug რეჟიმში 5xx-ზე ჩანს დეტალები, production-ში — views/errors/{status}.php ან errors/default.php.
 */
final class ErrorHandler
{
    public function __construct(private readonly App $app)
    {
    }

    public function register(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '0');

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(function (Throwable $e): void {
            if (PHP_SAPI === 'cli') {
                Log::error($e);
                fwrite(STDERR, get_class($e) . ': ' . $e->getMessage() . PHP_EOL . $e->getFile() . ':' . $e->getLine() . PHP_EOL);
                exit(1);
            }
            $this->render($e, $this->app->request())->send();
        });
    }

    public function render(Throwable $e, ?Request $request = null): Response
    {
        $status = $e instanceof HttpException ? $e->status() : 500;
        $headers = $e instanceof HttpException ? $e->headers() : [];
        $debug = (bool)$this->app->config('app.debug', false);

        if ($status >= 500) {
            Log::error($e, ['url' => $request?->path()]);
        }

        $message = $status < 500 || $debug ? $e->getMessage() : HttpException::reason($status);

        if ($request?->wantsJson()) {
            return Response::json(['error' => $message], $status, $headers);
        }
        if ($debug && $status >= 500) {
            return Response::html($this->debugPage($e), $status, $headers);
        }

        try {
            $view = $this->app->make(View::class);
            $name = $view->exists("errors/$status") ? "errors/$status" : 'errors/default';
            $html = $view->render($name, ['status' => $status, 'message' => $message]);
        } catch (Throwable) {
            $html = '<!doctype html><meta charset="utf-8"><title>' . $status . '</title><h1>' . $status . ' ' . e($message) . '</h1>';
        }
        return Response::html($html, $status, $headers);
    }

    private function debugPage(Throwable $e): string
    {
        $title = e(get_class($e) . ': ' . $e->getMessage());
        $location = e($e->getFile() . ':' . $e->getLine());
        $trace = e($e->getTraceAsString());

        return <<<HTML
            <!doctype html><html><head><meta charset="utf-8"><title>Error</title>
            <style>body{font:14px/1.5 system-ui,sans-serif;margin:0;background:#1e1e24;color:#eee}
            header{background:#b3261e;padding:20px 24px}h1{margin:0;font-size:18px}
            main{padding:20px 24px}pre{background:#111;padding:16px;overflow:auto;border-radius:6px}</style></head>
            <body><header><h1>{$title}</h1><div>{$location}</div></header><main><pre>{$trace}</pre></main></body></html>
            HTML;
    }
}
