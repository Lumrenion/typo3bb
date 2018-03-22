<?php

namespace LumIT\Typo3bb\Utility;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Philipp Seßner <philipp.sessner@gmail.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
class RteUtility
{

    /**
     * @param string $html
     * @return string
     */
    public static function sanitizeHtml(string $html)
    {
        require_once ExtensionManagementUtility::extPath('typo3bb') . 'Libraries/HTMLPurifier/HTMLPurifier.auto.php';
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed',
            '*[style|class|id|title],a[target|ping|media|href|hreflang|type],blockquote[cite],big,br,code,dd,div,dl,dt,em,i,footer,h1,h2,h3,h4,h5,h6,hr,img[alt|src|ismap|usemap|width|height],li[value],mark,ol[reversed|start],p,pre,q[cite],small,span,strong,b,sub,sup,table,tbody,td[colspan|rowspan|headers],tfoot,th[colspan|rowspan|headers|scope],thead,tr,u,ul,s');
        $config->getHTMLDefinition(true)->addElement('footer', 'Block', 'Flow', 'Common');
        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($html);
    }

    /**
     * This method adds a javascript array of smileys for TinyMCE Smiley-Plugin to the header data.
     * It gets the files from the directory specified in the plugin settings emoticonPath
     * @param string $emoticonPath
     * @return array
     */
    public static function prepareSmilies(string $emoticonPath)
    {
        /** @var Folder $emoticonFolder */
        $emoticonFolder = ResourceFactory::getInstance()->retrieveFileOrFolderObject($emoticonPath);
        $emoticons = $emoticonFolder->getFiles();
        $emoticonArray = [];
        $emoticonStack = [];
        /** @var File $emoticon */
        foreach ($emoticons as $emoticon) {
            if (StringUtility::beginsWith($emoticon->getMimeType(), 'image')) {
                $singleEmoticon = [];
                $singleEmoticon['title'] = $emoticon->getNameWithoutExtension();
                $singleEmoticon['url'] = '/' . $emoticon->getPublicUrl();
                $singleEmoticon['shortcut'] = (empty($emoticon->getProperty('title')) ? '(' . $singleEmoticon['title'] . ')' : $emoticon->getProperty('title'));
                $emoticonStack[] = $singleEmoticon;
                if (count($emoticonStack) >= 7) {
                    $emoticonArray[] = $emoticonStack;
                    $emoticonStack = [];
                }
            }
        }
        if (count($emoticonStack) > 0) {
            $emoticonArray[] = $emoticonStack;
        }

        return $emoticonArray;
    }

    /**
     * Creates a html quote
     *
     * @param string $content
     * @param string $author
     * @param \DateTime|null $date
     * @param string $url
     * @return string
     */
    public static function getQuote(string $content, string $author = '', \DateTime $date = null, string $url = '')
    {
        $quote = '<br><blockquote>' . $content;
        if (!empty($author)) {
            $quoteInfo = $author;
            if ($date != null) {
                $quoteInfo .= ', ' . $date->format('d.m.Y H:i');
            }
            if (!empty($url) && GeneralUtility::isValidUrl($url)) {
                $quoteInfo = '<a href="' . $url . '">' . $quoteInfo . '</a>';
            }
            $quoteInfo = '<footer>' . $quoteInfo . '</footer>';
            $quote .= $quoteInfo;
        }
        $quote .= '</blockquote><br>';

        return $quote;
    }
}