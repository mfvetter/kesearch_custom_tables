<?php

namespace LaSierra\KeSearchCustomTables;

use Doctrine\DBAL\DBALException;
use InvalidArgumentException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;


class AdditionalContentFields {

    /**
     * @param $fields
     * @param $pageIndexer
     * @return void
     */
    public function modifyPageContentFields(&$fields, $pageIndexer): void
    {
        // Nothing to do here at the moment.
    }

    /**
     * @param string $content
     * @param array $ttContentRow
     * @param $pageIndexer
     * @throws DBALException
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws InvalidConfigurationTypeException
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \UnexpectedValueException
     */
    public function modifyContentFromContentElement(string &$content, array $ttContentRow, $pageIndexer): void
    {
        // Get indexable fields from TypoScript
        // Another possibility is to develop an extension configuration so indexing via scheduled cron jobs would read the configuration from it.
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $configurationManager = $objectManager->get(ConfigurationManager::class);
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $config = $extbaseFrameworkConfiguration['plugin.']['tx_kesearch_custom_tables.']['config.'] ?? null;
        $indexFields =  array();

        //DebuggerUtility::var_dump($ttContentRow);

        if ($config !== NULL) {
            foreach ($config['elements.'] as $key => $value) {
                $type = str_replace('.' , '' , $key);
                if ( $type !== $ttContentRow['CType'] ){
                    continue;
                }

                foreach ($value as $key2 => $value2) {
                    if ( $key2 === 'fields') {
                        $indexFields = explode(',' , $value2);
                        $indexFields = array_map('trim', $indexFields);
                    }

                    $content = $this->additionalTableContent($value['table'], $indexFields, $ttContentRow);
                    //DebuggerUtility::var_dump($content);
                }
            }
        }
    }

    /**
     * @param string $table
     * @param array $indexFields
     * @param array $row
     * @return string
     * @throws DBALException
     * @throws InvalidArgumentException
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \UnexpectedValueException
     */
    public function additionalTableContent(string $table, array $indexFields, array $row): string
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilder = $connectionPool->getQueryBuilderForTable($table);
        $content = $queryBuilder
            ->select('uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('tt_content', $queryBuilder->createNamedParameter($row['uid'], \PDO::PARAM_INT))
            );

        foreach ($indexFields as $field) {
            $content->addSelect($field);
        }

        $result = $content->execute()->fetchAllAssociative();

        //DebuggerUtility::var_dump($result);

        $indexContent = [];
        foreach ($result as $item) {
            //DebuggerUtility::var_dump($item);
            unset($item['uid']);
            $indexContent[] = implode(' ', $item);
            //DebuggerUtility::var_dump($indexContent);
        }

        return strip_tags(implode(' ', $indexContent));
    }
}
