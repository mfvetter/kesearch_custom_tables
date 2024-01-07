<?php


if (!defined("TYPO3_MODE")) {
    die("Access denied.");
}

// Register hooks for indexing additional fields.
$additionContentClassName = 'LaSierra\FluxKesearchIndexer\AdditionalContentFields';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyPageContentFields'][] = $additionContentClassName;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyContentFromContentElement'][] = $additionContentClassName;
