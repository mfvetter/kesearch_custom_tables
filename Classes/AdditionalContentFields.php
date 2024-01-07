<?php

namespace LaSierra\FluxKesearchIndexer;

use Doctrine\DBAL\DBALException;
use InvalidArgumentException;
use RecursiveIteratorIterator;
use RecursiveArrayIterator;
use Tpwd\KeSearch\Plugins\ResultlistPlugin;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Service\FlexFormService;
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
        // Add the field "pi_flexform" from the tt_content table, which is normally not indexed, to the list of fields.
        $fields .= ",pi_flexform";
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
        if (is_null($ttContentRow['pi_flexform'])){
            return;
        }
        
        // Get indexable fields from TypoScript
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $configurationManager = $objectManager->get(ConfigurationManager::class);
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $config = $extbaseFrameworkConfiguration['plugin.']['tx_flux_kesearch_indexer.']['config.'] ?? null;
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
                }
            }
        }

        //DebuggerUtility::var_dump($pageIndexer);
        //DebuggerUtility::var_dump($indexFields);

        // Add the content of the field "pi_flexform" to $content, which is, what will be saved to the index.
        $flexform    = '';
        $flexformService = GeneralUtility::makeInstance(FlexFormService::class);
        $flexArr = $flexformService->convertFlexFormContentToArray($ttContentRow['pi_flexform']);

        $iterator  = new RecursiveArrayIterator($flexArr);
        $recursive = new RecursiveIteratorIterator(
            $iterator,
            RecursiveIteratorIterator::SELF_FIRST
        );

        //DebuggerUtility::var_dump($recursive);

        //Indexing fields designated as indexable fields in Typoscript
        //Indexing all fields if  indexable fields didn't set in Typoscript
        //foreach ($recursive as $key => $value) {
        //    if (is_array($value)){continue;}
        //    if (empty($indexFields) ){
        //        $flexform .= "&nbsp;" . $value;
        //    } else if (in_array($key, $indexFields, true)) {
        //        $flexform .= "&nbsp;" . $value;
        //    }
        //}
        //$content .= "&nbsp;" . strip_tags($flexform) . "&nbsp;" ;
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
        //if ($row['type'] === $type) {
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

            //if ($newsRecord) {
            //    $tempMarkerArray['author'] = $newsRecord['author'];
            //}
        //}
    }
}
