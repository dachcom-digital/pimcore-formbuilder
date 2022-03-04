<?php declare(strict_types=1);

namespace FormBuilderBundle\Stream;

class File
{
    protected string $id;
    protected string $name;
    protected string $path;

    public function __construct(string $id, string $name, string $path)
    {
        $this->id = $id;
        $this->name = $name;
        $this->path = $path;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
