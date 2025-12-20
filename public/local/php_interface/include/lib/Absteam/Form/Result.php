<?php

namespace Absteam\Form;

use Absteam\Helpers\Site;
use Absteam\Orm\FormResultAnswerTable;
use CFile;

/**
 *
 */
class Result
{

    /**
     *
     */
    public const FIELD_FILE_ID = 64;

    /**
     * @var string
     */
    private string $dataManager;

    /**
     *
     */
    public function __construct(
        private readonly int $resultId
    )
    {
        $this->dataManager = FormResultAnswerTable::class;
    }

    /**
     * @param int $fieldId
     * @return array
     */
    public function getFieldValues(int $fieldId = self::FIELD_FILE_ID): array
    {
        $items = $this->dataManager::getList([
            'filter' => [
                'RESULT_ID' => $this->resultId,
                'FIELD_ID' => $fieldId,
            ],
        ])->fetchCollection();

        $result = [];
        foreach ($items as $item) {
            $fileId = (int)$item->getUserFileId();
            if ($fileId <= 0) {
                continue;
            }

            $result[] = [
                'fileId' => (int)$item->getUserFileId(),
                'fileName' => $item->getUserFileName(),
                'url' => Site::getSiteUrl(CFile::GetPath($fileId)),
            ];
        }

        return $result;
    }

}