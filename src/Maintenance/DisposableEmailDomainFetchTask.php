<?php

namespace FormBuilderBundle\Maintenance;

use Carbon\Carbon;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Validator\EmailChecker\DisposableEmailDomainChecker;
use GuzzleHttp\Client;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Pimcore\Maintenance\TaskInterface;
use Pimcore\Model\Tool\SettingsStore;
use Psr\Log\LoggerInterface;

class DisposableEmailDomainFetchTask implements TaskInterface
{
    protected string $source = 'https://raw.githack.com/disposable/disposable-email-domains/master/domains.json';

    public function __construct(
        protected LoggerInterface $logger,
        protected Configuration $configuration,
        protected FilesystemOperator $formBuilderEmailCheckerStorage,
    ) {
    }

    public function execute(): void
    {
        $now = Carbon::now();
        $lastExecution = null;

        $config = $this->configuration->getConfig('spam_protection');

        if ($config['email_checker']['disposable_email_domains']['enabled'] === false) {
            return;
        }

        $setting = SettingsStore::get('disposable_email_domains_last_fetch', 'form_builder');

        if ($setting instanceof SettingsStore) {
            $lastExecution = Carbon::fromSerialized($setting->getData());
        }

        if ($lastExecution instanceof Carbon && $now->diffInDays($lastExecution) < 1) {
            return;
        }

        try {
            $client = new Client();
            $response = $client->get($this->source, ['connect_timeout' => 10]);
        } catch (\Throwable $e) {

            $this->setFetchStamp($now);
            $this->logger->error(sprintf('[DISPOSABLE EMAIL DOMAINS FETCH] %s', $e->getMessage()));

            return;
        }

        try {
            $this->formBuilderEmailCheckerStorage->write(DisposableEmailDomainChecker::DISPOSABLE_EMAIL_DOMAIN_DATABASE_PATH, $response->getBody()->getContents());
        } catch (FilesystemException $e) {

            $this->setFetchStamp($now);
            $this->logger->error(sprintf('[DISPOSABLE EMAIL DOMAINS FETCH] %s', $e->getMessage()));

            return;
        }

        $this->setFetchStamp($now);
    }

    private function setFetchStamp(Carbon $date): void
    {
        SettingsStore::set('disposable_email_domains_last_fetch', $date->serialize(), 'string', 'form_builder');
    }
}
