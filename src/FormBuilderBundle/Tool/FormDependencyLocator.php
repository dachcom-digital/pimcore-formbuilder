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
     * @throws Exception|\Doctrine\DBAL\Exception
     */
    public function findDocumentDependencies(int $formId, int $offset, int $limit): array
    {
        $stmt = $this->db->prepare(
            sprintf(
                'SELECT SQL_CALC_FOUND_ROWS `documentId`, `data` 
                        FROM `documents_elements` 
                        WHERE `name` LIKE :name AND `type` = :type AND `data` = :formId 
                        GROUP BY `documentId` 
                        LIMIT %d, %d',
                $offset,
                $limit
            )
        );

        $stmt->execute([
            'name'   => '%.formName',
            'type'   => 'select',
            'formId' => $formId
        ]);

        $indexCount = (int) $this->db->fetchOne('SELECT FOUND_ROWS()');

        $documents = [];
        foreach ($stmt->fetchAll() as $data) {
            try {
                $document = Document::getById($data['documentId']);
            } catch (\Exception $e) {
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
