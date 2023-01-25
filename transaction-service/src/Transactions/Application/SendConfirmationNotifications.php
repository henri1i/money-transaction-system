<?php

namespace Src\Transactions\Application;

use App\Jobs\DispatchConfirmationNotification;
use Src\Infrastructure\Events\Entities\TransactionApproved;
use Src\Transactionables\Application\Exceptions\InvalidTransactionableException;
use Src\Transactionables\Application\GetTransactionable;
use Src\Transactions\Domain\ValueObjects\TransactionId;

class SendConfirmationNotifications
{
    public function __construct(private readonly GetTransactionable $getTransactionable)
    {
    }

    /**
     * @throws InvalidTransactionableException
     */
    public function handle(TransactionApproved $event): void
    {
        $transactionId = new TransactionId($event->payload->serialize());

        $transactionable = $this->getTransactionable->byTransaction($transactionId);

        DispatchConfirmationNotification::dispatch($transactionable);
    }
}
