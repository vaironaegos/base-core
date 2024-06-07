<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Contracts;

interface FileStorage
{
    /**
     * Stores a file in the storage system defined by the implementation of the interface.
     * This method is responsible for storing a file in the specific storage system defined
     * by the implementation of the interface.
     *
     * @param string $destinationPath The full destination path where the file will be stored.
     * @param string $newName (Optional) The new name of the file. If not specified, the original name of the file
     * will be retained.
     * @return string The name of the uploaded file.
     */
    public function store(string $destinationPath, string $newName = ''): string;

    public function delete(string $destinationPathWithFileName): bool;
}
