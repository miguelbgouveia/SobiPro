<?php
/**
 * @version: $Id: field.php 2291 2012-03-09 19:13:30Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2012-03-09 20:13:30 +0100 (Fri, 09 Mar 2012) $
 * $Revision: 2291 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/models/adm/field.php $
 */
/**
 * @author Radek Suski
 * @version 1.0
 * @created 09-Mar-2009 12:00:45 PM
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadModel( 'field' );

final class SPAdmField extends SPField
{
	/**
	 * @var array
	 */
	private $_translatable = array( 'name', 'description' );

	public function save ( $attr )
	{
		/* @var SPdb $db */
		$db =& SPFactory::db();
		$base = $attr;
		$this->loadType();
		/* clean input */
		if( isset( $attr[ 'name' ] ) )
			$base[ 'name' ] = $db->escape( $attr[ 'name' ] );
		else
			$base[ 'name' ] = 'missing name - something went wrong';
		if( isset( $attr[ 'nid' ] ) )
			$base[ 'nid' ] = $this->nid( $db->escape( preg_replace( '/[^[:alnum:]\-\_]/', null, $attr[ 'nid' ] ) ), false );
		if( isset( $attr[ 'cssClass' ] ) )
			$base[ 'cssClass' ] = $db->escape( preg_replace( '/[^[:alnum:]\-\_ ]/', null, $attr[ 'cssClass' ] ) );
		if( isset( $attr[ 'notice' ] ) )
			$base[ 'notice' ] = $db->escape( $attr[ 'notice' ] );
		if( isset( $attr[ 'showIn' ] ) )
			$base[ 'showIn' ] = $db->escape( preg_replace( '/[^[:alnum:]\.\-\_]/', null, $attr[ 'showIn' ] ) );
		if( isset( $attr[ 'filter' ] ) )
			$base[ 'filter' ] = preg_replace( '/[^[:alnum:]\.\-\_]/', null, $attr[ 'filter' ] );
		if( isset( $attr[ 'fieldType' ] ) )
			$base[ 'fieldType' ]= preg_replace( '/[^[:alnum:]\.\-\_]/', null, $attr[ 'fieldType' ] );
		if( isset( $attr[ 'type' ] ) )
			$base[ 'fieldType' ] = preg_replace( '/[^[:alnum:]\.\-\_]/', null, $attr[ 'type' ] );
		if( isset( $attr[ 'enabled' ] ) )
			$base[ 'enabled' ] = ( int ) $attr[ 'enabled' ];
		if( isset( $attr[ 'required' ] ) )
			$base[ 'required' ] = ( int ) $attr[ 'required' ];
		if( isset( $attr[ 'adminField' ] ) )
			$base[ 'adminField' ] = ( int ) $attr[ 'adminField' ];
		if( $attr[ 'adminField' ] ) {
			$attr[ 'required' ] = false;
		}
		if( isset( $attr[ 'editable' ] ) )
			$base[ 'editable' ] = ( int ) $attr[ 'editable' ];
		if( isset( $attr[ 'inSearch' ] ) )
			$base[ 'inSearch' ] = ( int ) $attr[ 'inSearch' ];
		if( isset( $attr[ 'editLimit' ] ) )
			$base[ 'editLimit' ] = ( int ) $attr[ 'editLimit' ];
		$base[ 'editLimit' ] = $base[ 'editLimit' ] > 0 ? $base[ 'editLimit' ] : -1;
		if( isset( $attr[ 'isFree' ] ) )
			$base[ 'isFree' ] = ( int ) $attr[ 'isFree' ];
		if( isset( $attr[ 'withLabel' ] ) )
			$base[ 'withLabel' ] = ( int ) $attr[ 'withLabel' ];
		if( isset( $attr[ 'fee' ] ) )
			$base[ 'fee' ]= ( double ) str_replace( ',', '.', $attr[ 'fee' ] );
		if( isset( $attr[ 'addToMetaDesc' ] ) )
			$base[ 'addToMetaDesc' ] = ( int ) $attr[ 'addToMetaDesc' ];
		if( isset( $attr[ 'addToMetaKeys' ] ) )
			$base[ 'addToMetaKeys' ] = ( int ) $attr[ 'addToMetaKeys' ];
		if( isset( $attr[ 'uniqueData' ] ) )
			$base[ 'uniqueData' ] = ( int ) $attr[ 'uniqueData' ];
        /* both strpos are removed because it does not allow to have one parameter only */
//      if( isset( $attr[ 'allowedAttributes' ] ) && strpos( $attr[ 'allowedAttributes' ], '|' ) )
		if( isset( $attr[ 'allowedAttributes' ] ) )
			$base[ 'allowedAttributes' ] = SPConfig::serialize( explode( '|', $attr[ 'allowedAttributes' ] ) );
		//if( isset( $attr[ 'allowedTags' ] ) && strpos( $attr[ 'allowedTags' ], '|' ) )
        if( isset( $attr[ 'allowedTags' ] ) )
			$base[ 'allowedTags' ] = SPConfig::serialize( explode( '|', $attr[ 'allowedTags' ] ) );
		if( isset( $attr[ 'admList' ] ) )
			$base[ 'admList' ] = ( int ) $attr[ 'admList' ];
		if( isset( $attr[ 'description' ] ) )
			$base[ 'description' ] = $db->escape( $attr[ 'description' ] );
		else
			$base[ 'description' ] = null;
		if( isset( $attr[ 'suffix' ] ) )
			$base[ 'suffix' ] = $db->escape( $attr[ 'suffix' ] );
		else
			$base[ 'suffix' ] = null;
		$this->version++;
		$base[ 'version' ] = $this->version;

		/* section id is needed only if it was new field */
		if( !( ( isset( $attr[ 'section' ] ) && $attr[ 'section' ] ) ) ) {
			if( !( SPRequest::int( 'fid' ) ) ) {
				$base[ 'section' ] = SPRequest::sid();
			}
		}

		/* bind attributes to this object */
		foreach ( $attr as $a => $v ) {
			$a = trim( $a );
			if ( $this->has( $a ) ) {
				$this->$a = $v;
			}
		}

		if( $this->_type && method_exists( $this->_type, 'save' ) ) {
			$this->_type->save( $base );
		}

		/* get database colums and their ordering */
		$cols	= $db->getColumns( 'spdb_field' );
		$values = array();

		/* and sort the properties in the same order */
		foreach ( $cols as $col ) {
			if( key_exists( $col, $base ) ) {
				$values[ $col ] = $base[ $col ];
			}
		}
		/* save field */
		try {
			$db->update( 'spdb_field', $values, array( 'fid' => $this->fid ) );
		}
		catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}

		/* save language dependend properties */
		$labels = array();
		$defLabels = array();
		$labels[] = array( 'sKey' => 'name', 'sValue' => $base[ 'name' ], 'language' => Sobi::Lang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid );
		$labels[] = array( 'sKey' => 'description', 'sValue' => $base[ 'description' ], 'language' => Sobi::Lang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid );
		$labels[] = array( 'sKey' => 'suffix', 'sValue' => $base[ 'suffix' ], 'language' => Sobi::Lang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid );
		if( Sobi::Lang() != Sobi::DefLang() ) {
			$defLabels[] = array( 'sKey' => 'name', 'sValue' => $base[ 'name' ], 'language' => Sobi::DefLang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid );
			$defLabels[] = array( 'sKey' => 'suffix', 'sValue' => $base[ 'suffix' ], 'language' => Sobi::DefLang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid );
			$defLabels[] = array( 'sKey' => 'description', 'sValue' => $base[ 'description' ], 'language' => Sobi::DefLang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid );
		}
		if( count( $labels ) ) {
			try {
				if( Sobi::Lang() != Sobi::DefLang() ) {
					$db->insertArray( 'spdb_language', $defLabels, false, true );
				}
				$db->insertArray( 'spdb_language', $labels, true );
			}
			catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SAVE_FIELD_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
		}
		SPFactory::cache()->cleanSection();
	}

	private function nid( $nid, $new )
	{
		$c = 1;
		$a = 2;
		$db = SPFactory::db();
		while ( $c ) {
			/* field alias has to be unique */
			try {
				$cond = array( 'nid' => $nid, 'section' => Sobi::Section() );
				if( !( $new ) ) {
					$cond[ '!fid' ] = $this->id;
				}
				$db->select( 'COUNT( nid )', 'spdb_field', $cond );
				$c = $db->loadResult();
				if( $c > 0 ) {
					$nid = $nid.'_'.$a++;
				}
			}
			catch ( SPException $x ) {}
		}
		return $nid;
	}

	/**
	 * Adding new field
	 * Save base data and redirect to the edit function when the field type has been chosed
	 * @return integer
	 */
	public function saveNew( $attr )
	{
		$db =& SPFactory::db();

		/* cast all needed data and clean - it is possible just in admin panel but "strzeżonego pan Bóg strzeże" ;-) */
		$base = array();
		$base[ 'section' ] = ( isset( $attr[ 'section'] ) && $attr[ 'section'] ) ? $attr[ 'section'] : SPRequest::sid();
		if( isset( $attr[ 'name' ] ) )
			$base[ 'name' ] = $db->escape( $attr[ 'name' ] );
		if( isset( $attr[ 'description' ] ) )
			$base[ 'description' ] = $db->escape( $attr[ 'description' ] );
		else
			$base[ 'description' ] = null;
		if( isset( $attr[ 'suffix' ] ) )
			$base[ 'suffix' ] = $db->escape( $attr[ 'suffix' ] );
		else
			$base[ 'suffix' ] = null;
		if( isset( $attr[ 'nid' ] ) )
			$base[ 'nid' ] = $this->nid( $db->escape( preg_replace( '/[^[:alnum:]\-\_]/', null, $attr[ 'nid' ] ) ), true );
		if( isset( $attr[ 'cssClass' ] ) )
			$base[ 'cssClass' ] = $db->escape( preg_replace( '/[^[:alnum:]\-\_ ]/', null, $attr[ 'cssClass' ] ) );
		if( isset( $attr[ 'notice' ] ) )
			$base[ 'notice' ] = $db->escape( $attr[ 'notice' ] );
		if( isset( $attr[ 'showIn' ] ) )
			$base[ 'showIn' ] = $db->escape( preg_replace( '/[^[:alnum:]\.\-\_]/', null, $attr[ 'showIn' ] ) );
		if( isset( $attr[ 'fieldType' ] ) )
			$base[ 'fieldType' ] = preg_replace( '/[^[:alnum:]\.\-\_]/', null, $attr[ 'fieldType' ] );
		if( isset( $attr[ 'type' ] ) )
			$base[ 'fieldType' ] = preg_replace( '/[^[:alnum:]\.\-\_]/', null, $attr[ 'type' ] );
		if( isset( $attr[ 'description' ] ) )
			$base[ 'description' ]  = $db->escape( $attr[ 'description' ] );
		if( isset( $attr[ 'enabled' ] ) )
			$base[ 'enabled' ] = ( int ) $attr[ 'enabled' ];
		if( isset( $attr[ 'required' ] ) )
			$base[ 'required' ] = ( int ) $attr[ 'required' ];
		if( isset( $attr[ 'adminField' ] ) )
			$base[ 'adminField' ] = ( int ) $attr[ 'adminField' ];
		if( isset( $attr[ 'adminField' ] ) && $attr[ 'adminField' ] ) {
			$attr[ 'required' ] = false;
		}
		if( isset( $attr[ 'editable' ] ) )
			$base[ 'editable' ] = ( int ) $attr[ 'editable' ];
		if( isset( $attr[ 'editLimit' ] ) ) {
			$base[ 'editLimit' ] = ( int ) $attr[ 'editLimit' ];
			$base[ 'editLimit' ] = $base[ 'editLimit' ] > 0 ? $base[ 'editLimit' ] : -1;
		}
		if( isset( $attr[ 'isFree' ] ) )
			$base[ 'isFree' ] = ( int ) $attr[ 'isFree' ];
		if( isset( $attr[ 'withLabel' ] ) )
			$base[ 'withLabel' ] = ( int ) $attr[ 'withLabel' ];
		if( isset( $attr[ 'inSearch' ] ) )
			$base[ 'inSearch' ] = ( int ) $attr[ 'inSearch' ];
		if( isset( $attr[ 'admList' ] ) )
			$base[ 'admList' ] = ( int ) $attr[ 'admList' ];
		if( isset( $attr[ 'fee' ] ) )
			$base[ 'fee' ] = ( double ) $attr[ 'fee' ];
		if( isset( $attr[ 'section' ] ) )
			$base[ 'section' ] = ( int ) $attr[ 'section' ];
		$base[ 'version' ] = 1;

		/* determine the right position */
		try {
			$db->select( 'MAX( position )', 'spdb_field', array( 'section' => SPRequest::sid() ) );
			$base[ 'position' ] = ( int ) $db->loadResult() + 1;
			if( !$base[ 'position' ] ) {
				$base[ 'position' ] = 1;
			}
		}
		catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELD_POSITION_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
		}
		/* get database colums and their ordering */
		$cols   = $db->getColumns( 'spdb_field' );
		$values = array();

		/* and sort the properties in the same order */
		foreach ( $cols as $col ) {
			$values[ $col ] = key_exists( $col, $base ) ? $base[ $col ] : '';
		}

		/* save new field */
		try {
			$db->insert( 'spdb_field', $values );
			$this->fid = $db->insertid();
		}
		catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_GET_FIELD_POSITION_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			trigger_error( 'sobipro|db_field|cannot_insert_field|500|'.$x->getMessage(), SPC::ERROR );
		}

		/* save language dependend properties */
		$labels = array();
		$defLabels = array();
		$labels[] = array( 'sKey' => 'name', 'sValue' => $base[ 'name' ], 'language' => Sobi::Lang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid );
		$labels[] = array( 'sKey' => 'description', 'sValue' => $base[ 'description' ], 'language' => Sobi::Lang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid );
		$labels[] = array( 'sKey' => 'suffix', 'sValue' => $base[ 'suffix' ], 'language' => Sobi::Lang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid );
		if( Sobi::Lang() != Sobi::DefLang() ) {
			$defLabels[] = array( 'sKey' => 'name', 'sValue' => $base[ 'name' ], 'language' => Sobi::DefLang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid );
			$defLabels[] = array( 'sKey' => 'suffix', 'sValue' => $base[ 'suffix' ], 'language' => Sobi::DefLang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid );
			$defLabels[] = array( 'sKey' => 'description', 'sValue' => $base[ 'description' ], 'language' => Sobi::DefLang(), 'id' => 0, 'oType' => 'field', 'fid' => $this->fid );
		}
		if( count( $labels ) ) {
			try {
				if( Sobi::Lang() != Sobi::DefLang() ) {
					$db->insertArray( 'spdb_language', $defLabels, false, true );
				}
				$db->insertArray( 'spdb_language', $labels, true );
			}
			catch ( SPException $x ) {
				Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SAVE_FIELD_DB_ERR', $x->getMessage() ), SPC::ERROR, 500, __LINE__, __FILE__ );
			}
		}
		SPFactory::cache()->cleanSection();
		return $this->fid;
	}

	/* (non-PHPdoc)
	 * @see Site/lib/models/SPField#loadType()
	 */
	public function loadType( $type = null )
	{
		if( $type ) {
			$this->type = $type;
		}
		else {
			$this->type =& $this->fieldType;
		}
		if( $this->type && SPLoader::translatePath( 'opt.fields.adm.'.$this->type ) ) {
			SPLoader::loadClass( 'opt.fields.fieldtype' );
			$fType = SPLoader::loadClass( 'opt.fields.adm.'.$this->type );
			$this->_type = new $fType( $this );
		}
		elseif( $this->type && SPLoader::translatePath( 'opt.fields.'.$this->type ) ) {
			SPLoader::loadClass( 'opt.fields.fieldtype' );
			$fType = SPLoader::loadClass( 'opt.fields.'.$this->type );
			$this->_type = new $fType( $this );
		}
		else {
			parent::loadType();
		}
	}

	public function onFieldEdit( &$view )
	{
		$this->loadType();
		$this->editLimit = $this->editLimit > 0 ? $this->editLimit : 0;
		$this->fee = SPLang::currency( $this->fee, false );
		if( $this->_type && method_exists( $this->_type, 'onFieldEdit' ) ) {
			 $this->_type->onFieldEdit( $view );
		}
	}
}
?>
