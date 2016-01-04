<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Helper class for Short URLs
 *
 * @author Fritz Michael Gschwantner <https://github.com/fritzmg>
 */
class ShortURLs
{

	public function __construct()
	{
		// initialize required singletons in the right order
		\FrontendUser::getInstance();
		\Database::getInstance();
	}


	public static function processTarget( $target )
	{
		if( stripos( $target, '{{link_url::' ) === 0 )
		{
			// get the page id
			$pageId = substr( $target, 12, -2 ); 

			// get the page
			if( ( $objPage = \PageModel::findPublishedByIdOrAlias( $pageId ) ) === null )
				return;

			// load details of the page
			$objPage->current()->loadDetails();

			// generate the URL
			$target = \Environment::get('base') . \Controller::generateFrontendUrl( $objPage->row(), null, $objPage->rootLanguage, true );
		}
		elseif( stripos( $target, 'http' ) !== 0 )
		{
			// add base to url
			$target = \Environment::get('base') . $target;
		}

		// return processed target
		return $target;	
	}

	public function checkForShortURL()
	{
		// only do something in the frontend
		if( TL_MODE != 'FE' )
			return;

		// check if we have a Short URL
		if( ( $objShortURL = \ShortURLsModel::findActiveByName( \Environment::get('request') ) ) === null )
			return;

		// check if there is a target set
		if( !$objShortURL->target )
			return;

		// build redirect URL
		$url = self::processTarget( $objShortURL->target );

		// prevent infinite redirects
		if( $url == \Environment::get('base') . \Environment::get('request') )
			return;

		// execute redirect
		\Controller::redirect( $url, $objShortURL->redirect == 'permanent' ? 301 : 302 );
	}

}
