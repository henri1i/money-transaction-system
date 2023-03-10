<?php

namespace Src\Infrastructure\Models;

use Carbon\Carbon;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Src\Shared\ValueObjects\Money;
use Src\Transactionables\Domain\Exceptions\InvalidTransactionableException;
use Src\Transactions\Domain\Entities\Transaction;
use Src\Transactions\Domain\Enums\TransactionStatus;
use Src\Transactions\Domain\ValueObjects\TransactionId;

/**
 * @extends Model<Transaction>
 *
 * @property string $id
 * @property string $receiver_id
 * @property string $sender_id
 * @property int    $amount
 * @property TransactionStatus $status
 * @property TransactionableModel $sender
 * @property TransactionableModel $receiver
 * @property Carbon $created_at
 */
class TransactionModel extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'id',
        'receiver_id',
        'sender_id',
        'amount',
        'status',
    ];

    /** @var array<string, string> */
    protected $casts = ['status' => TransactionStatus::class];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(TransactionableModel::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(TransactionableModel::class);
    }

    /** @throws InvalidTransactionableException */
    public function intoEntity(): Transaction
    {
        $receiver = $this->receiver->intoEntity()->asReceiver();
        $sender = $this->sender->intoEntity()->asSender();

        return new Transaction(
            new TransactionId($this->id),
            $receiver,
            $sender,
            new Money($this->amount),
            $this->status,
            $this->created_at
        );
    }

    protected static function newFactory(): TransactionFactory
    {
        return new TransactionFactory();
    }
}
