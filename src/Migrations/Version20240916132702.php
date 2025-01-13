<?php

declare(strict_types=1);

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace FormBuilderBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use FormBuilderBundle\Tool\Install;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class Version20240916132702 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $installer = $this->container->get(Install::class);
        $installer->updateTranslations();

        if (!$schema->hasTable('formbuilder_double_opt_in_session')) {
            return;
        }

        if (!$schema->getTable('formbuilder_double_opt_in_session')->hasIndex('email_form_definition')) {
            return;
        }

        $this->addSql('DROP INDEX email_form_definition ON formbuilder_double_opt_in_session;');
    }

    public function down(Schema $schema): void
    {
    }
}
