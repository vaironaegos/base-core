<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Tests\Fakes;

use Astrotech\Customer\Domain\Entity\Invoice;
use Astrotech\Shared\Domain\Contracts\FileStorage;

final class FakeFileStorage implements FileStorage
{
    public function storePaymentProof(string $sourceFilePath, Invoice $invoice): string
    {
        return 'fake-filename.pdf';
    }
}
