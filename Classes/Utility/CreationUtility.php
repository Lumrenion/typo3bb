<?php

namespace LumIT\Typo3bb\Utility;

use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator;


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
class CreationUtility
{

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\Argument $topicArgument The topic controller argument
     * @param array $topic The topic from the request
     */
    public static function prepareTopicForValidation($topicArgument, &$topic)
    {
        if (!empty($topic['poll'])) {
            CreationUtility::setDateTimePropertyMapperToIsoDate($topicArgument, 'poll.starttime');
            CreationUtility::setDateTimePropertyMapperToIsoDate($topicArgument, 'poll.endtime');
        }
    }

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\Argument $argument Argument to evaluate that was passed to the controller
     * @param string $property The datetime property of the argument to change
     */
    public static function setDateTimePropertyMapperToIsoDate($argument, $property)
    {
        $argument->getPropertyMappingConfiguration()
            ->setTypeConverterOption(
                'TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter',
                PersistentObjectConverter::CONFIGURATION_CREATION_ALLOWED,
                true
            )->forProperty($property)->setTypeConverterOption(
                'TYPO3\\CMS\\Extbase\\Property\\TypeConverter\\DateTimeConverter',
                DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                'Y-m-d'
            );
    }

    /**
     * This method prepares a to-be-created or to-be-edited poll for validation as controller argument
     *
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\Argument $pollArgument The poll controller argument
     * @param array $poll The poll from the request
     */
    public static function preparePollForValidation($pollArgument, &$poll)
    {
        CreationUtility::setDateTimePropertyMapperToIsoDate($pollArgument, 'starttime');
        CreationUtility::setDateTimePropertyMapperToIsoDate($pollArgument, 'endtime');

        if (empty($poll)) {
            $poll = null;
            return;
        }
        if (empty($poll['__identity'])) {
            if (empty($poll['question'])) {
                $poll = null;
                return;
            }
            foreach ($poll['choices'] as $index => $choice) {
                if (empty($choice['text'])) {
                    unset($poll['choices'][$index]);
                }
            }
        } else {
            // if the endtime is not set, it should not be changed automatically
            if (!MathUtility::canBeInterpretedAsInteger($poll['endtime'])) {
                unset($poll['endtime']);
                return;
            }
        }

        // the fallback for endtime interval is 1 Day
        if (MathUtility::canBeInterpretedAsInteger($poll['endtime'])) {
            $poll['endtime'] = (int)$poll['endtime'];
        } else {
            $poll['endtime'] = 1;
        }
        $poll['endtime'] = (new \DateTime())->add(new \DateInterval('P' . (int)$poll['endtime'] . 'D'));
    }

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\Argument $postArgument The post controller argument
     * @param array $post The post from the request
     */
    public static function preparePostForValidation($postArgument, &$post)
    {
        if (!$GLOBALS['TSFE']->loginUser) {
            CreationUtility::setAuthorPropertyMapperForAnonymousUser($postArgument, 'authorName');
        } else {
            $postArgument->getPropertyMappingConfiguration()->allowProperties('author', 'authorName');
            $post['author']['__identity'] = $GLOBALS['TSFE']->fe_user->user['uid'];
            $post['authorName'] = $GLOBALS['TSFE']->fe_user->user['username'];
        }
    }

    /**
     * @param \TYPO3\CMS\Extbase\Mvc\Controller\Argument $argument The controller argument that holds the author name
     * @param string $property The property name of given argument that represents the author name
     */
    public static function setAuthorPropertyMapperForAnonymousUser($argument, $property)
    {
        /** @var ConjunctionValidator $conjunctionValidator */
        $conjunctionValidator = $argument->getValidator();
        /** @var ConjunctionValidator $validator */
        foreach ($conjunctionValidator->getValidators() as $innerConjunctionValidator) {
            foreach ($innerConjunctionValidator->getValidators() as $validator) {
                if ($validator instanceof GenericObjectValidator) {
                    $validator->addPropertyValidator($property, new NotEmptyValidator());
                    $validator->addPropertyValidator($property, new StringLengthValidator(['minimum' => 3]));
                }
            }
        }
    }
}