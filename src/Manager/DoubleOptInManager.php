<?php

namespace FormBuilderBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use FormBuilderBundle\Configuration\Configuration;
use FormBuilderBundle\Event\DoubleOptInSubmissionEvent;
use FormBuilderBundle\Exception\DoubleOptInException;
use FormBuilderBundle\Exception\DoubleOptInUniqueConstraintViolationException;
use FormBuilderBundle\Form\RuntimeData\Provider\DoubleOptInSessionDataProvider;
use FormBuilderBundle\Model\DoubleOptInSession;
use FormBuilderBundle\Model\DoubleOptInSessionInterface;
use FormBuilderBundle\Model\FormDefinitionInterface;
use FormBuilderBundle\Repository\DoubleOptInSessionRepositoryInterface;
use FormBuilderBundle\Repository\FormDefinitionRepositoryInterface;
use Pimcore\Mail;
use Pimcore\Model\Document\Email;
use Symfony\Contracts\Translation\TranslatorInterface;

class DoubleOptInManager
{
    public const DOUBLE_OPT_IN_SESSION_QUERY_IDENTIFIER = 'formBuilderDoubleOptInToken';
    public const REDEEM_MODE_DELETE = 'delete';
    public const REDEEM_MODE_DEVALUE = 'devalue';

    public function __construct(
        protected TranslatorInterface $translator,
        protected Configuration $configuration,
        protected FormDefinitionRepositoryInterface $formDefinitionRepository,
        protected DoubleOptInSessionRepositoryInterface $doubleOptInSessionRepository,
        protected EntityManagerInterface $entityManager
    ) {
    }

    public function requiresDoubleOptInForm(FormDefinitionInterface $formDefinition, array $formRuntimeData): bool
    {
        if ($this->doubleOptInEnabled($formDefinition) === false) {
            return false;
        }

        if (!array_key_exists(DoubleOptInSessionDataProvider::DOUBLE_OPT_IN_SESSION_RUNTIME_DATA_IDENTIFIER, $formRuntimeData)) {
            return true;
        }

        if (null === $sessionToken = $formRuntimeData[DoubleOptInSessionDataProvider::DOUBLE_OPT_IN_SESSION_RUNTIME_DATA_IDENTIFIER]) {
            return true;
        }

        return !$this->isValidNonAppliedFormAwareSessionToken($formDefinition, $sessionToken);
    }

    public function redeemDoubleOptInSessionToken(DoubleOptInSessionInterface $doubleOptInSession): void
    {
        $doubleOptInConfig = $this->configuration->getConfig('double_opt_in');

        if ($doubleOptInConfig['redeem_mode'] === self::REDEEM_MODE_DELETE) {
            $this->deleteDoubleOptInSession($doubleOptInSession);
        } else {
            $this->devalueDoubleOptInSession($doubleOptInSession);
        }
    }

    public function findDoubleOptInSession(FormDefinitionInterface $formDefinition, array $formRuntimeData): ?DoubleOptInSessionInterface
    {
        if ($this->doubleOptInEnabled($formDefinition) === false) {
            return null;
        }

        if (!array_key_exists(DoubleOptInSessionDataProvider::DOUBLE_OPT_IN_SESSION_RUNTIME_DATA_IDENTIFIER, $formRuntimeData)) {
            return null;
        }

        if (null === $sessionToken = $formRuntimeData[DoubleOptInSessionDataProvider::DOUBLE_OPT_IN_SESSION_RUNTIME_DATA_IDENTIFIER]) {
            return null;
        }

        $doubleOptInSession = $this->doubleOptInSessionRepository->findByNonAppliedFormAwareSessionToken($sessionToken, $formDefinition->getId());
        if (!$doubleOptInSession instanceof DoubleOptInSessionInterface) {
            return null;
        }

        return $doubleOptInSession;
    }

    public function isValidNonAppliedFormAwareSessionToken(FormDefinitionInterface $formDefinition, ?string $sessionToken): bool
    {
        $doubleOptInSession = $this->doubleOptInSessionRepository->findByNonAppliedFormAwareSessionToken($sessionToken, $formDefinition->getId());

        return $doubleOptInSession instanceof DoubleOptInSessionInterface;
    }

    /**
     * @throws DoubleOptInException
     * @throws \Exception
     */
    public function processOptInSubmission(DoubleOptInSubmissionEvent $submissionEvent): void
    {
        $formData = $submissionEvent->getForm()->getData();
        $doubleOptInConfig = $submissionEvent->getFormDefinition()->getDoubleOptInConfig();
        $locale = $submissionEvent->getLocale() ?? $submissionEvent->getRequest()->getLocale();

        $email = $formData['emailAddress'] ?? null;

        if (empty($email)) {
            throw new DoubleOptInException('no email address given');
        }

        unset($formData['emailAddress']);

        if ($submissionEvent->getDispatchLocation() !== null) {
            $dispatchLocation = $submissionEvent->getDispatchLocation();
        } else {
            $dispatchLocation = $submissionEvent->getRequest()->getUri();
            if ($submissionEvent->getRequest()->isXmlHttpRequest()) {
                $dispatchLocation = $submissionEvent->getRequest()->headers->get('referer');
            }
        }

        if (empty($dispatchLocation)) {
            throw new DoubleOptInException('invalid double-opt-in dispatch location');
        }

        try {
            $doubleOptInSession = $this->create(
                $submissionEvent->getFormDefinition(),
                $email,
                $formData,
                $dispatchLocation
            );
        } catch (DoubleOptInUniqueConstraintViolationException) {
            throw new DoubleOptInException($this->translator->trans('form_builder.form.double_opt_in.duplicate_session'));
        }

        $this->sendDoubleOptInMessage($doubleOptInSession, $doubleOptInConfig, $locale, $email);

        $submissionEvent->addMessage(
            'success',
            $this->translator->trans(
                $doubleOptInConfig['confirmationMessage'] ?? 'form_builder.double_opt_in_success',
                ['%token%' => $doubleOptInSession->getTokenAsString()]
            )
        );
    }

    public function create(
        FormDefinitionInterface $formDefinition,
        string $email,
        ?array $additionalData,
        string $dispatchLocation
    ): DoubleOptInSessionInterface {

        $doubleOptInConfig = $this->configuration->getConfig('double_opt_in');
        $allowMultipleUserSessions = $doubleOptInConfig['allowMultipleUserSessions'] ?? true;

        if ($allowMultipleUserSessions === false) {

            $doubleOptInSession = $this->doubleOptInSessionRepository->findOneBy([
                'applied'        => false,
                'email'          => $email,
                'formDefinition' => $formDefinition
            ]);

            if ($doubleOptInSession instanceof DoubleOptInSessionInterface) {
                throw new DoubleOptInUniqueConstraintViolationException();
            }
        }

        $doubleOptInSession = new DoubleOptInSession();

        $doubleOptInSession->setFormDefinition($formDefinition);
        $doubleOptInSession->setEmail($email);
        $doubleOptInSession->setAdditionalData($additionalData);
        $doubleOptInSession->setDispatchLocation($dispatchLocation);
        $doubleOptInSession->setCreationDate(new \DateTime());
        $doubleOptInSession->setApplied(false);

        $this->entityManager->persist($doubleOptInSession);
        $this->entityManager->flush();

        return $doubleOptInSession;
    }

    public function deleteDoubleOptInSession(DoubleOptInSessionInterface $doubleOptInSession): void
    {
        $this->entityManager->remove($doubleOptInSession);
        $this->entityManager->flush();
    }

    public function devalueDoubleOptInSession(DoubleOptInSessionInterface $doubleOptInSession): void
    {
        $doubleOptInSession->setApplied(true);

        $this->entityManager->persist($doubleOptInSession);
        $this->entityManager->flush();
    }

    public function doubleOptInEnabled(?FormDefinitionInterface $formDefinition = null): bool
    {
        $doubleOptInConfig = $this->configuration->getConfig('double_opt_in');

        if ($doubleOptInConfig['enabled'] === false) {
            return false;
        }

        if (!$formDefinition instanceof FormDefinitionInterface) {
            return true;
        }

        return $formDefinition->isDoubleOptInActive() === true;
    }

    public function getOutDatedDoubleOptInSessions(): array
    {
        $doubleOptInConfig = $this->configuration->getConfig('double_opt_in');
        $expiration = $doubleOptInConfig['expiration'];

        $qb = $this->doubleOptInSessionRepository->getQueryBuilder();

        if ($expiration['open_sessions'] === 0 && $expiration['redeemed_sessions'] === 0) {
            return [];
        }

        if ($expiration['open_sessions'] > 0) {

            $expiredOpenSessionDate = new \DateTime();
            $expiredOpenSessionDate->modify(sprintf('-%d hour', $expiration['open_sessions']));

            $qb->orWhere(
                $qb->expr()->andX(
                    $qb->expr()->eq('s.applied', 0),
                    $qb->expr()->lt('s.creationDate', ':expiredOpenSession'),
                )
            );

            $qb->setParameter('expiredOpenSession', $expiredOpenSessionDate);
        }

        if ($expiration['redeemed_sessions'] > 0) {

            $expiredRedeemedSessionDate = new \DateTime();
            $expiredRedeemedSessionDate->modify(sprintf('-%d hour', $expiration['redeemed_sessions']));

            $qb->orWhere(
                $qb->expr()->andX(
                    $qb->expr()->eq('s.applied', 1),
                    $qb->expr()->lt('s.creationDate', ':expiredRedeemedSession'),
                )
            );

            $qb->setParameter('expiredRedeemedSession', $expiredRedeemedSessionDate);
        }

        return $qb->getQuery()->getResult();
    }

    private function sendDoubleOptInMessage(DoubleOptInSessionInterface $doubleOptInSession, array $doubleOptInConfig, ?string $locale, string $recipient): void
    {
        $mailTemplate = null;
        $mailDocument = null;
        $mailTemplates = $doubleOptInConfig['mailTemplate'] ?? [];

        foreach ([$locale, 'default'] as $layoutLocale) {
            if (!empty($mailTemplates[$layoutLocale]['id'])) {
                $mailTemplate = $mailTemplates[$layoutLocale]['id'];
                break;
            }
        }

        if ($mailTemplate !== null) {
            $mailDocument = Email::getById($mailTemplate);
        }

        if (!$mailDocument instanceof Email) {
            throw new \Exception('No email template found');
        }

        $mail = new Mail();
        $mail->setDocument($mailDocument);
        $mail->addTo($recipient);
        $mail->setParam('token', $doubleOptInSession->getTokenAsString());
        $mail->setParam('link', $this->generateDoubleOptInSessionAwareLink($doubleOptInSession));

        $mail->send();
    }

    private function generateDoubleOptInSessionAwareLink(DoubleOptInSessionInterface $doubleOptInSession): string
    {
        $params = [];
        $dispatchLocationUrl = parse_url($doubleOptInSession->getDispatchLocation());

        if (!empty($dispatchLocationUrl['query'])) {
            parse_str($dispatchLocationUrl['query'], $params);
            if (array_key_exists(self::DOUBLE_OPT_IN_SESSION_QUERY_IDENTIFIER, $params)) {
                unset($params[self::DOUBLE_OPT_IN_SESSION_QUERY_IDENTIFIER]);
            }
        }

        $params[self::DOUBLE_OPT_IN_SESSION_QUERY_IDENTIFIER] = $doubleOptInSession->getTokenAsString();

        return $this->buildUrl($dispatchLocationUrl, $params);
    }

    private function buildUrl(array $url, array $params = []): string
    {
        $url['query'] = http_build_query($params, '', '&');

        $scheme = isset($url['scheme']) ? $url['scheme'] . '://' : '';
        $host = $url['host'] ?? '';
        $port = isset($url['port']) ? ':' . $url['port'] : '';
        $user = $url['user'] ?? '';
        $pass = isset($url['pass']) ? ':' . $url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = $url['path'] ?? '';
        $query = $url['query'] ? '?' . $url['query'] : '';
        $fragment = isset($url['fragment']) ? '#' . $url['fragment'] : '';

        return $scheme . $user . $pass . $host . $port . $path . $query . $fragment;
    }
}
