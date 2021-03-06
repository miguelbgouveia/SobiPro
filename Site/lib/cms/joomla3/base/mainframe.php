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
require_once dirname( __FILE__ ) . '/../../joomla16/base/mainframe.php';
/**
 * Interface between SobiPro and the used CMS
 * @author Radek Suski
 * @version 1.0
 * @created Mon, Jan 14, 2013 06:50:55
 */
class SPJ3MainFrame extends SPJ16MainFrame implements SPMainframeInterface
{
	/**
	 * @return SPJoomlaMainFrame
	 */
	public static function & getInstance()
	{
		static $mf = false;
		if ( !( $mf ) || !( $mf instanceof self ) ) {
			$mf = new self();
		}
		return $mf;
	}

	protected function getMetaDescription( $document )
	{
		return $document->getDescription();
	}
}
