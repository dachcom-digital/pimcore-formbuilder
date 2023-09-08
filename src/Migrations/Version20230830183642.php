<?php

declare(strict_types=1);

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\ORM\EntityRepository;
use FormBuilderBundle\Model\OutputWorkflowChannel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class Version20230830183642 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function doesSqlMigrations(): bool
    {
        return false;
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // migrate mailLayoutData
        $em = $this->container->get('doctrine.orm.entity_manager');

        /** @var EntityRepository $repository */
        $repository = $em->getRepository(OutputWorkflowChannel::class);

        /** @var OutputWorkflowChannel $channel */
        foreach ($repository->findAll() as $channel) {

            if ($channel->getType() !== 'email') {
                continue;
            }

            $this->write(sprintf('Migrate object output channel %d', $channel->getId()));

            $migratedLayout = $this->migrateMailLayout($channel->getConfiguration());

            if ($migratedLayout === null) {
                continue;
            }

            $channel->setConfiguration($migratedLayout);
            $em->persist($channel);
        }

        $em->flush();
    }

    public function down(Schema $schema): void
    {
        // do nothing
    }

    private function migrateMailLayout(array $configuration): ?array
    {
        $changed = false;

        foreach ($configuration as $locale => $configurationData) {

            if (!array_key_exists('mailLayoutData', $configurationData)) {
                continue;
            }

            if ($configurationData['mailLayoutData'] === null) {
                continue;
            }

            if (is_array($configurationData['mailLayoutData'])) {
                continue;
            }

            $mailLayoutData = $configurationData['mailLayoutData'];

            $changed = true;
            $transformedTextLayout = preg_replace_callback($this->getPlaceholderRegex(), [$this, 'parseSquareBracketsTag'], $mailLayoutData);

            $textAwareLayout = [
                'text' => $transformedTextLayout,
                'html' => ''
            ];

            $configuration[$locale]['mailLayoutData'] = $textAwareLayout;
        }

        if ($changed === false) {
            return null;
        }

        return $configuration;
    }

    protected function parseSquareBracketsTag(array $tag): ?string
    {
        $type = $tag[1];
        $config = $this->parseSquareBracketsAttributes($tag[2]);

        // add field value to widget.
        if (isset($config['sub-type'], $this->outputData[$config['sub-type']])) {
            $config['outputData'] = $this->outputData[$config['sub-type']];
        }

        $cleanConfig = [];
        foreach ($config as $key => $value) {
            if ($value === 'true' || $value === 'false') {
                $value = $value === 'true';
            }

            $cleanConfig[$key] = $value;
        }

        if (array_key_exists('sub-type', $cleanConfig) && in_array($cleanConfig['sub-type'], ['fieldset', 'repeater'], true)) {
            return sprintf('[%s NOT MIGRATED]', strtoupper($cleanConfig['sub-type']));
        }

        $fieldSubType = $type === 'fb_field' && array_key_exists('sub-type', $cleanConfig) ? $cleanConfig['sub-type'] : 'null';
        $fieldLabel = $fieldSubType === 'null' ? $type : $fieldSubType;
        $renderType = $type === 'fb_field' ? ' data-render_type="V"' : '';
        $additionalParameter = '';

        foreach ($cleanConfig as $configName => $configValue) {

            if (in_array($configName, ['sub-type', 'show_label'], true)) {
                continue;
            }

            $additionalParameter .= sprintf(' data-%s="%s"', $configName, $configValue);
        }

        $prefix = '';

        if (
            $type === 'fb_field' && (
                (array_key_exists('show_label', $cleanConfig) && $cleanConfig['show_label'] === true) ||
                !array_key_exists('show_label', $cleanConfig)
            )
        ) {
            $prefix = sprintf('<fb-field data-type="fb_field" data-sub_type="%s" data-render_type="L">%s</fb-field>: ', $fieldSubType, $fieldLabel);
        }

        return sprintf('%s<fb-field data-type="%s" data-sub_type="%s"%s%s>%s</fb-field>', $prefix, $type, $fieldSubType, $additionalParameter, $renderType, $fieldLabel);
    }

    protected function parseSquareBracketsAttributes(string $text): array
    {
        $attributes = [];
        $pattern = $this->getSquareBracketsAttributeRegex();
        $text = preg_replace('/[\x{00a0}\x{200b}]+/u', ' ', $text);

        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1])) {
                    $attributes[strtolower($m[1])] = stripcslashes($m[2]);
                } elseif (!empty($m[3])) {
                    $attributes[strtolower($m[3])] = stripcslashes($m[4]);
                } elseif (!empty($m[5])) {
                    $attributes[strtolower($m[5])] = stripcslashes($m[6]);
                } elseif (isset($m[7]) && strlen($m[7])) {
                    $attributes[] = stripcslashes($m[7]);
                } elseif (isset($m[8])) {
                    $attributes[] = stripcslashes($m[8]);
                }
            }
            foreach ($attributes as &$value) {
                if (str_contains($value, '<')) {
                    if (preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value) !== 1) {
                        $value = '';
                    }
                }
            }
        } else {
            $attributes = [ltrim($text)];
        }

        return $attributes;
    }

    protected function getSquareBracketsAttributeRegex(): string
    {
        return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
    }

    protected function getPlaceholderRegex(): string
    {
        $allowedRex = implode('|', ['fb_field', 'date']);

        return '/\\[\\[(' . $allowedRex . ')(.*?)\\]\\]/';
    }
}
