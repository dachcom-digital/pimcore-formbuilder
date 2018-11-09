<?php

namespace FormBuilderBundle\Tool;

use Pimcore\Model\Document;

class FormDependencyLocator
{
    /**
     * @var \Pimcore\Db\Connection
     */
    protected $db;

    /**
     * FormDependencyLocator constructor.
     *
     * @param \Pimcore\Db\Connection $db
     */
    public function __construct(\Pimcore\Db\Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @param int $formId
     * @param int $offset
     * @param int $limit
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findDocumentDependencies(int $formId, int $offset, int $limit)
    {
        $stmt = $this->db->prepare(
            sprintf('SELECT SQL_CALC_FOUND_ROWS `documentId`, `data` 
                        FROM `documents_elements` 
                        WHERE `name` LIKE :name AND `type` = :type AND `data` = :formId 
                        GROUP BY `documentId` 
                        LIMIT %d, %d',
                $offset, $limit
            )
        );

        $stmt->execute([
            'name'   => '%.formName',
            'type'   => 'select',
            'formId' => $formId
        ]);

        $indexCount = (int)$this->db->fetchOne('SELECT FOUND_ROWS()');

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