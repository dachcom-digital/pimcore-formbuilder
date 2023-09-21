<?php

namespace FormBuilderBundle\Storage;

use FormBuilderBundle\Model\FormStorageData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid as Uid;

class SessionStorageProvider implements StorageProviderInterface
{
    public function __construct(protected SerializerInterface $serializer)
    {
    }

    public function store(Request $request, FormStorageData $formStorageData): string
    {
        $token = $this->generateToken();
        $session = $request->getSession();

        // clean-up?

        $session->set($token, $this->serializer->serialize($formStorageData, 'json', ['groups' => ['OutputWorkflow']]));

        return $token;
    }

    public function update(Request $request, string $token, FormStorageData $formStorageData): void
    {
        $session = $request->getSession();

        $session->set($token, $this->serializer->serialize($formStorageData, 'json', ['groups' => ['OutputWorkflow']]));
    }

    public function flush(Request $request, string $token): void
    {
        $session = $request->getSession();

        if (!$session->has($token)) {
            return;
        }

        $session->remove($token);
    }

    public function fetch(Request $request, string $token): ?FormStorageData
    {
        $data = $request->getSession()->get($token);

        if ($data === null) {
            return null;
        }

        return $this->serializer->deserialize($data, FormStorageData::class, 'json', ['groups' => ['OutputWorkflow']]);
    }

    protected function generateToken(): string
    {
        $uuid = Uid::v1()->toRfc4122();

        return sprintf('fbst-%s', $uuid);
    }
}
