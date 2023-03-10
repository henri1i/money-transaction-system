<?php

namespace Src\Customer\Presentation\Rest\Controllers;

use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\ResponseFactory;
use Src\Customer\Domain\Entities\Customer;
use Src\Customer\Presentation\Rest\Requests\SendTransactionRequest;
use Src\Customer\Presentation\Rest\ViewModels\Transactions\SendTransactionViewModel;
use Src\Infrastructure\Clients\Http\Exceptions\ExternalServiceException;
use Src\Infrastructure\Clients\Http\TransactionsService\Exceptions\ClientException;
use Src\Infrastructure\Clients\Http\TransactionsService\Exceptions\ResourceNotFoundException;
use Src\Transaction\Application\GetBalance;
use Src\Transaction\Application\GetTransactions;
use Src\Transaction\Application\SendTransaction;
use Symfony\Component\HttpFoundation\Response;

class WalletController
{
    public function getBalance(Request $request, GetBalance $getBalance, ResponseFactory $response): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        try {
            $balance = $getBalance->for($customer);
        } catch (ExternalServiceException $e) {
            return $response->json(['error' => $e->getMessage()], Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (ClientException $e) {
            return $response->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (ResourceNotFoundException $e) {
            return $response->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $response->json(['balance' => $balance->amount->value()]);
    }

    public function getTransactions(
        Request $request,
        GetTransactions $getTransactions,
        ResponseFactory $response
    ): JsonResponse {
        /** @var Customer $customer */
        $customer = $request->user();

        try {
            $transactionsResponse = $getTransactions->for($customer);
        } catch (ExternalServiceException $e) {
            return $response->json(['error' => $e->getMessage()], Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (ClientException $e) {
            return $response->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $response->json($transactionsResponse->serialize());
    }

    public function sendTransaction(
        SendTransactionRequest $request,
        SendTransaction $sendTransaction,
        ResponseFactory $response
    ): JsonResponse {
        /** @var Customer $customer */
        $customer = $request->user();

        $payload = SendTransactionViewModel::fromRequest($request);

        try {
            $sendTransaction->send($payload, $customer);
        } catch (ExternalServiceException $e) {
            return $response->json(['error' => $e->getMessage()], Response::HTTP_SERVICE_UNAVAILABLE);
        } catch (ResourceNotFoundException $e) {
            return $response->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (GuzzleClientException $e) {
            return $response->json(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (ClientException|GuzzleException $e) {
            return $response->json(['error' => $e->getMessage()], $e->getCode());
        }

        return $response->json(status: Response::HTTP_OK);
    }
}
