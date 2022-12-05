<?php declare(strict_types=1);

namespace FormBuilderBundle\Stream;

class FileStack
{
    protected array $files = [];

    public function __construct(array $files = [])
    {
        $this->files = $files;
    }

    public function addFile(File $file): void
    {
        $this->files[] = $file;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function count(): int
    {
        return count($this->files);
    }
}
