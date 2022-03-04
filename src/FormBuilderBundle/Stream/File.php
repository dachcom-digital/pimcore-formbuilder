<?php declare(strict_types=1);

namespace FormBuilderBundle\Stream;

class File
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $path;

    /**
     * @param string $id
     * @param string $name
     * @param string $path
     */
    public function __construct($id, $name, $path)
    {
        $this->id = $id;
        $this->name = $name;
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
