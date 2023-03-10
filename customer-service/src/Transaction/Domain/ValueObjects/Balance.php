<?php

namespace Src\Transaction\Domain\ValueObjects;

use Src\Customer\Domain\Entities\Customer;
use Src\Shared\ValueObjects\Money;

final class Balance
{
    public function __construct(
        public readonly Money $amount,
        public readonly Customer $customer
    ) {
    }
}
