<?php
// enable "sysfolder" field
$GLOBALS['TCA']['tx_kesearch_indexerconfig']['columns']['sysfolder']['displayCond'] .= ',' . \LumIT\Typo3bb\Extensions\KeSearch\Indexer\ForumIndexer::$indexerType;