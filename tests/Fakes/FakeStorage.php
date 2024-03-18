<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Tests\Fakes;

use Yii;
use Astrotech\Shared\Domain\Contracts\FileStorage;

final class FakeStorage implements FileStorage
{
    public function storePaymentProof(string $fullFilePath, string $newFileName = ''): string
    {
        return Yii::getAlias('@storage/payment-proof-sample.pdf');
    }
}
