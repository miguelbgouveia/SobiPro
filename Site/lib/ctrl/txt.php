<?php
/**
 * @package: SobiPro Library

 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: https://www.Sigsiu.NET

 * @copyright Copyright (C) 2006 - 2015 Sigsiu.NET GmbH (https://www.sigsiu.net). All rights reserved.
 * @license GNU/LGPL Version 3
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License version 3
 * as published by the Free Software Foundation, and under the additional terms according section 7 of GPL v3.
 * See http://www.gnu.org/licenses/lgpl.html and https://www.sigsiu.net/licenses.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'controller' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 15-Jul-2010 18:17:28
 */
class SPJsTxt extends SPController
{
	/**
	 * @var string
	 */
	protected $_defTask = 'js';

	public function __construct()
	{
	}

	/**
	 */
	public function execute()
	{
		$this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		switch ( $this->_task ) {
			case 'js':
				$this->js();
				break;
			case 'messages':
				$this->messages();
				break;
			case 'translate':
				$this->translate();
				break;
		}
	}

	protected function translate()
	{
		$term = Sobi::Txt( SPRequest::cmd( 'term' ) );
		Sobi::Trigger( 'Translate', 'Text', [ &$term ] );
		SPFactory::mainframe()
				->cleanBuffer()
				->customHeader();
		echo json_encode( [ 'translation' => $term ] );
		exit;
	}

	protected function messages()
	{
		$messages = SPFactory::message()->getReports( SPRequest::cmd( 'spsid' ) );
		$response = [];
		if ( count( $messages ) ) {
			foreach ( $messages as $type => $content ) {
				if ( count( $content ) ) {
					foreach ( $content as $message ) {
						$response[ ] = [ 'type' => $type, 'text' => $message ];
					}
				}
			}
		}
		$this->response( null, null, false, SPC::INFO_MSG, [ 'messages' => $response ] );
	}

	protected function js()
	{
		$lang = SPLang::jsLang();
		if ( count( $lang ) ) {
			foreach ( $lang as $term => $text ) {
				unset( $lang[ $term ] );
				$term = str_replace( 'SP.JS_', null, $term );
				$lang[ $term ] = $text;
			}
		}
		if ( !( SPRequest::int( 'deb' ) ) ) {
			SPFactory::mainframe()->cleanBuffer();
			header( 'Content-type: text/javascript' );
		}
		echo 'SobiPro.setLang( ' . json_encode( $lang ) . ' );';
		echo "\n";
		exit( 'SobiPro.setIcons( ' . json_encode( SPFactory::config()->icons() ) . ' );' );
	}
}
