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

final class Version20240819150642 extends AbstractMigration implements ContainerAwareInterface
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

        if ($schema->getTable('formbuilder_forms')->hasColumn('mailLayout')) {
            $this->addSql('ALTER TABLE formbuilder_forms DROP mailLayout;');
        }

        if ($schema->hasTable('formbuilder_double_opt_in_session')) {
            return;
        }

        $this->addSql('CREATE TABLE formbuilder_double_opt_in_session (token BINARY(16) NOT NULL COMMENT "(DC2Type:uuid)", form_definition INT DEFAULT NULL, email VARCHAR(190) NOT NULL, additional_data LONGTEXT DEFAULT NULL COMMENT "(DC2Type:array)", dispatch_location LONGTEXT DEFAULT NULL, applied TINYINT(1) DEFAULT 0 NOT NULL, creationDate DATETIME NOT NULL, INDEX IDX_88815C4F61F7634C (form_definition), INDEX token_form (token, form_definition, applied), UNIQUE INDEX email_form_definition (email, form_definition, applied), PRIMARY KEY(token)) DEFAULT CHARACTER SET UTF8MB4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB;');
        $this->addSql('ALTER TABLE formbuilder_double_opt_in_session ADD CONSTRAINT FK_88815C4F61F7634C FOREIGN KEY (form_definition) REFERENCES formbuilder_forms (id) ON DELETE CASCADE;');
    }

    public function down(Schema $schema): void
    {
    }
}
