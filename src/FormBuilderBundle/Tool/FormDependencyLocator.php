<?php

namespace FormBuilderBundle\Tool;

use Doctrine\DBAL\Driver\Exception;
use Pimcore\Db\Connection;
use Pimcore\Model\Document;

class FormDependencyLocator
{
    protected Connection $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function findDocumentDependencies(int $formId, int $offset, int $limit): array
    {
        $stmt = $this->db->prepare(
            sprintf(
                'SELECT SQL_CALC_FOUND_ROWS `documentId`, `data` 
                        FROM `documents_editables` 
                        WHERE `name` LIKE :name AND `type` = :type AND `data` = :formId 
                        GROUP BY `documentId` 
                        LIMIT %d, %d',
                $offset,
                $limit
            )
        );

        $result = $stmt->executeQuery([
            'name'   => '%.formName',
            'type'   => 'select',
            'formId' => $formId
        ]);

        $indexCount = (int) $this->db->fetchOne('SELECT FOUND_ROWS()');

        $documents = [];
        foreach ($result->fetchAllAssociative() as $data) {

            $document = null;

            try {
                $document = Document::getById($data['documentId']);
            } catch (\Throwable $e) {
                continue;
            }

            if ($document === null) {
                continue;
            }

            $documents[] = [
                'id'      => $document->getId(),
                'type'    => 'document',
                'subtype' => $document->getType(),
                'path'    => $document->getFullPath(),
            ];
        }

        return [
            'total'     => $indexCount,
            'documents' => $documents
        ];
    }
}
