<?php

namespace App\EventListener;

use Elastica\Exception\NotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class JsonExceptionListener
{
    public function __construct(
        private LoggerInterface $logger,
        private bool $debug = false
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        // Only handle JSON requests or API routes
        if (!$this->isJsonRequest($request)) {
            return;
        }

        $exception = $event->getThrowable();

        $responseData = match($exception::class) {
            AccessDeniedException::class,
            AccessDeniedHttpException::class => $this->getResponseData(
                $exception,
                'Access Denied',
                Response::HTTP_FORBIDDEN
            ),
            AuthenticationException::class => $this->getResponseData(
                $exception,
                'Authentication Required',
                Response::HTTP_UNAUTHORIZED
            ),
            NotFoundHttpException::class => $this->getResponseData(
                $exception,
                'Resource Not Found',
                Response::HTTP_NOT_FOUND
            ),
            // Elastica Not Found Exception
            NotFoundException::class => $this->getResponseData(
                $exception,
                'Resource Not Found',
                Response::HTTP_NOT_FOUND
            ),
            default => $this->getResponseData(
                $exception,
                'Ooops, something went wrong.',
                $exception instanceof HttpExceptionInterface
                    ? $exception->getStatusCode()
                    : Response::HTTP_INTERNAL_SERVER_ERROR
            ),
        };

        $this->logger->error('API Exception', [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'status' => $responseData['code'],
            'path' => $request->getPathInfo(),
        ]);

        $response = new JsonResponse($responseData, $responseData['code']);
        $event->setResponse($response);
    }

    private function isJsonRequest($request): bool
    {
        // Check if request expects JSON
        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            return true;
        }

        // Check if request is to API routes
        if (str_starts_with($request->getPathInfo(), '/api/')) {
            return true;
        }

        // Check if request content type is JSON
        if ($request->getContentType() === 'json') {
            return true;
        }

        return false;
    }

    private function getResponseData(\throwable $ex, string $message, int $statusCode): array
    {
        $data = [
            'status' => 'error',
            'message' => $message,
            'code' => $statusCode,
        ];

        if ($this->debug) {
            $data['details'] = [
                'exception' => get_class($ex),
                'file' => $ex->getFile(),
                'line' => $ex->getLine(),
                'trace' => $ex->getTraceAsString(),
            ];
        }

        return $data;
    }
}
