<?php
/**
 * @version: $Id: spsection.php 1446 2011-05-29 13:21:34Z Radek Suski $
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
 * $Date: 2011-05-29 15:21:34 +0200 (Sun, 29 May 2011) $
 * $Revision: 1446 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Component/trunk/Site/lib/cms/joomla_common/elements/spsection.php $
 */
defined( '_JEXEC' ) or die();

class JElementSPSection extends JElement
{
    protected $task = null;
    protected $taskName = null;
    protected $oType = null;
    protected $oTypeName = null;
    protected $oName = null;
    protected $sid = null;
    protected $section = null;
    protected $tpl = null;

    public static function & getInstance()
    {
        static $instance = null;
        if ( !( $instance instanceof self ) ) {
            $instance = new self();
        }
        return $instance;
    }

    public function __construct()
    {
        static $loaded = false;
        if ( $loaded ) {
            return true;
        }
        require_once ( implode( DS, array( JPATH_SITE, 'components', 'com_sobipro', 'lib', 'sobi.php' ) ) );
        Sobi::Init( JPATH_SITE, JFactory::getConfig()->getValue( 'config.language' ) );
        SPLoader::loadClass( 'mlo.input' );
        define( 'SOBIPRO_ADM', true );
        define( 'SOBI_ADM_PATH', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_sobipro' );
        $adm = str_replace( JPATH_ROOT, null, JPATH_ADMINISTRATOR );
        define( 'SOBI_ADM_FOLDER', $adm );
        define( 'SOBI_ADM_LIVE_PATH', $adm . '/components/com_sobipro' );

        $head = SPFactory::header();
        $head->addJsFile( array( 'sobipro', 'jquery', 'adm.sobipro', 'adm.jmenu' ) );
        if ( SOBI_CMS != 'joomla3' ) {
            $head->addCssFile( 'bootstrap.bootstrap' )
                    ->addJsFile( array( 'bootstrap' ) )
                    ->addCSSCode(
                '   #jform_request_SOBI_SELECT_SECTION-lbl { margin-top: 8px; }
                            #jform_request_cid-lbl { margin-top: 8px; }
                            #jform_request_eid-lbl { margin-top: 18px; }
                            #jform_request_sid-lbl { margin-top: 22px; }
                            #jform_request_sptpl-lbl { margin-top: 8px; }
                         ' );
        }
        $this->determineTask();
        $strings = array(
            'objects' => array(
                'entry' => Sobi::Txt( 'OTYPE_ENTRY' ),
                'category' => Sobi::Txt( 'OTYPE_CATEGORY' ),
                'section' => Sobi::Txt( 'OTYPE_SECTION' ),
            ),
            'labels' => array(
                'category' => Sobi::Txt( 'SOBI_SELECT_CATEGORY' ),
                'entry' => Sobi::Txt( 'SOBI_SELECT_ENTRY' )
            ),
            'task' => $this->task
        );
        $strings = json_encode( $strings );

        $head->addJsCode( "SPJmenuFixTask( '{$this->taskName}' );" )
                ->addJsFile( 'bootstrap.typeahead' )
                ->addJsCode( "var SPJmenuStrings = {$strings}" );
        $head->send();
        parent::__construct();
        $loaded = true;
    }

    protected function determineTask()
    {
        $link = JModel::getInstance( 'MenusModelItem' )
                ->getItem()
                ->get( 'link' );
        $query = array();
        parse_str( $link, $query );
        $this->task = isset( $query[ 'task' ] ) ? $query[ 'task' ] : null;
        if ( $this->task ) {
            $def = DOMDocument::load( SOBI_PATH . '/metadata.xml' );
            $xdef = new DOMXPath( $def );
            $nodes = $xdef->query( "//option[@value='{$this->task}']" );
            if ( count( $nodes ) ) {
                $this->taskName = 'SobiPro - ' . JText::_( $nodes->item( 0 )->attributes->getNamedItem( 'name' )->nodeValue );
            }
        }
        else {
            $this->taskName = JText::_( 'SOBI_SECTION' );
        }
    }

    public function fetchTooltip( $label, $description, &$node, $control_name, $name )
    {
        if ( $label == 'cid' ) {
            if ( $this->task ) {
                return null;
            }
            $label = JText::_( 'SOBI_SELECT_CATEGORY' );
        }
        return parent::fetchTooltip( $label, $node->attributes( 'msg' ), $node, $control_name, $name );
    }

    private function getCat()
    {
        $params = array(
            'id' => 'sp_category',
            'class' => $this->oType == 'category' ? 'btn input-medium btn-primary' : 'btn input-medium',
            'style' => 'width: 300px'
        );
        if ( $this->task && $this->task != 'entry.add' ) {
            $params[ 'disabled' ] = 'disabled';
        }
        return
                '<div class="SobiPro">' .
                SPHtml_Input::button(
                    'sp_category',
                    $this->oType == 'category' ? $this->oName : Sobi::Txt( 'SOBI_SELECT_CATEGORY' ),
                    $params
                ) .
                '<div class="modal hide" id="spCat" style="width:500px;">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3>' . Sobi::Txt( 'SOBI_SELECT_CATEGORY' ) . '</h3>
              </div>
              <div class="modal-body">
                <div id="spCatsChooser"></div>
              </div>
              <div class="modal-footer">
                <a href="#" class="btn" data-dismiss="modal">' . Sobi::Txt( 'SOBI_CLOSE_WINDOW' ) . '</a>
                <a href="#" id="spCatSelect" class="btn btn-primary" data-dismiss="modal">' . Sobi::Txt( 'CC.JMENU_SAVE' ) . '</a>
              </div>
            </div>
            <input type="hidden" name="selectedCat" id="selectedCat" value=""/>
            <input type="hidden" name="selectedCatName" id="selectedCatName" value=""/>
        </div>';
    }

    private function getEntry()
    {
        $params = array(
            'id' => 'sp_entry',
            'class' => $this->oType == 'entry' ? 'btn input-medium btn-primary' : 'btn input-medium',
            'style' => 'margin-top: 10px; width: 300px'
        );
        if ( $this->task ) {
            $params[ 'disabled' ] = 'disabled';
        }
        return
                '<div class="SobiPro">' .
                SPHtml_Input::button(
                    'sp_entry',
                    $this->oType == 'entry' ? $this->oName : Sobi::Txt( 'SOBI_SELECT_ENTRY' ),
                    $params
                ) .
                '<div class="modal hide" id="spEntry" style="width:500px;">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3>' . Sobi::Txt( 'SOBI_SELECT_ENTRY' ) . '</h3>
              </div>
              <div class="modal-body">
                <p>
                    <label>' . Sobi::Txt( 'SOBI_SELECT_ENTRY_TYPE_TITLE' ) . '</label><input type="text" data-provide="typeahead" autocomplete="off" id="spEntryChooser" class="span3" placeholder="' . Sobi::Txt( 'SOBI_SELECT_ENTRY_TYPE' ) . '">
                </p>
              </div>
              <div class="modal-footer">
                <a href="#" class="btn" data-dismiss="modal">' . Sobi::Txt( 'SOBI_CLOSE_WINDOW' ) . '</a>
                <a href="#" id="spEntrySelect" class="btn btn-primary" data-dismiss="modal">' . Sobi::Txt( 'CC.JMENU_SAVE' ) . '</a>
              </div>
            </div>
            <input type="hidden" name="selectedEntry" id="selectedEntry" value=""/>
            <input type="hidden" name="selectedEntryName" id="selectedEntryName" value=""/>
        </div>';
    }

    public function fetchElement( $name )
    {
        static $sid = 0;
        $selected = 0;
        $db = SPFactory::db();
        if ( !( $sid ) ) {
            // Joomla! 1.5
            $cid = SPRequest::arr( 'cid' );
            // Joomla! 1.6+
            if ( !( count( $cid ) && is_numeric( $cid[ 0 ] ) ) ) {
                $cid = SPRequest::int( 'id', 0 );
            }
            $sid = 0;
            if ( $cid ) {
                $model =& JModel::getInstance( 'MenusModelItem' );
                $table =& $model->getItem();
                if ( strstr( $table->get( 'link' ), 'sid' ) ) {
                    $query = array();
                    parse_str( $table->get( 'link' ), $query );
                    $sid = $query[ 'sid' ];
                    if ( isset( $query[ 'sptpl' ] ) ) {
                        $this->tpl = $query[ 'sptpl' ];
                    }
                }
                $section = SPFactory::object( $sid );
                if ( $section->oType == 'section' ) {
                    $selected = $section->id;
                }
                else {
                    $path = array();
                    $id = $sid;
                    while ( $id > 0 ) {
                        try {
                            $db->select( 'pid', 'spdb_relations', array( 'id' => $id ) );
                            $id = $db->loadResult();
                            if ( $id ) {
                                $path[ ] = ( int )$id;
                            }
                        } catch ( SPException $x ) {
                        }
                    }
                    $path = array_reverse( $path );
                    $selected = $path[ 0 ];
                    $this->section = $selected;
                }
            }
        }
        $this->sid = $sid;
        $this->determineObjectType( $sid );
        if ( $name == 'sid' ) {
            $params = array( 'id' => 'sid', 'class' => 'input-mini ', 'style' => 'text-align: center; margin-top: 10px; margin-left: 10px;', 'readonly' => 'readonly' );
            return '<div class="SobiPro" id="jform_request_sid">'
                    . SPHtml_Input::text( 'type', $this->oTypeName, array( 'id' => 'otype', 'class' => 'input-small', 'style' => 'text-align: center; margin-top: 10px; ', 'readonly' => 'readonly' ) )
                    . SPHtml_Input::text( 'urlparams[sid]', $sid, $params )
                    . '</div>';
        }
        if ( $name == 'cid' ) {
            return $this->getCat();
        }
        if ( $name == 'eid' ) {
            return $this->getEntry();
        }
        if ( $name == 'tpl' ) {
            return $this->getTemplates();
        }
        $sections = array();
        $sout = array();
        try {
            $sections =
                    $db->select( '*', 'spdb_object', array( 'oType' => 'section' ), 'id' )
                            ->loadObjectList();
        } catch ( SPException $x ) {
            trigger_error( 'sobipro|admin_panel|cannot_get_section_list|500|' . $x->getMessage(), SPC::WARNING );
        }
        if ( count( $sections ) ) {
            SPLoader::loadClass( 'models.datamodel' );
            SPLoader::loadClass( 'models.dbobject' );
            SPLoader::loadModel( 'section' );
            $sout[ ] = Sobi::Txt( 'SELECT_SECTION' );
            foreach ( $sections as $section ) {
                if ( Sobi::Can( 'section', 'access', 'valid', $section->id ) ) {
                    $s = new SPSection();
                    $s->extend( $section );
                    $sout[ $s->get( 'id' ) ] = $s->get( 'name' );
                }
            }
        }
        $params = array( 'id' => 'spsection', 'class' => 'required' );
        $field = SPHtml_Input::select( 'sid', $sout, $selected, false, $params );
        return "<div class=\"SobiPro\" style=\"margin-top: 2px;\">{$field}</div>";
    }

    protected function getTemplates()
    {
        $selected = $this->tpl;
        $templates = array();
        $name = $this->tpl ? 'urlparams[sptpl]' : 'urlparams[-sptpl-]';
        $templates[ '' ] = Sobi::Txt( 'SELECT_TEMPLATE_OVERRIDE' );
        $template = SPFactory::db()
                ->select( 'sValue', 'spdb_config', array( 'section' => $this->section, 'sKey' => 'template', 'cSection' => 'section' ) )
                ->loadResult();
        $templateDir = $this->templatePath( $template );
        $this->listTemplates( $templates, $templateDir, $this->oType );
        $params = array( 'id' => 'sptpl' );
        $field = SPHtml_Input::select( $name, $templates, $selected, false, $params );
        return "<div class=\"SobiPro\" style=\"margin-top: 2px;\">{$field}</div>";
    }

    protected function templatePath( $tpl )
    {
        $file = explode( '.', $tpl );
        if ( strstr( $file[ 0 ], 'cms:' ) ) {
            $file[ 0 ] = str_replace( 'cms:', null, $file[ 0 ] );
            $file = SPFactory::mainframe()->path( implode( '.', $file ) );
            $template = SPLoader::path( $file, 'root', false, null );
        }
        else {
            $template = SOBI_PATH . DS . 'usr' . DS . 'templates' . DS . str_replace( '.', DS, $tpl );
        }
        return $template;
    }

    protected function listTemplates( &$arr, $path, $type )
    {
        switch ( $type ) {
            case 'entry':
            case 'entry.add':
            case 'section':
            case 'category':
            case 'search':
                $path = Sobi::FixPath( $path . '/' . $this->oType );
                break;
            default:
                break;
        }
        if ( file_exists( $path ) ) {
            $files = scandir( $path );
            if ( count( $files ) ) {
                foreach ( $files as $file ) {
                    $stack = explode( '.', $file );
                    if ( array_pop( $stack ) == 'xsl' ) {
                        $arr[ $stack[ 0 ] ] = $file;
                    }
                }
            }
        }
    }

    protected function determineObjectType( $sid )
    {
        if ( $this->task ) {
            $this->oTypeName = Sobi::Txt( 'TASK_' . strtoupper( $this->task ) );
            $this->oType = $this->task;
        }
        else {
            $this->oType = SPFactory::db()
                    ->select( 'oType', 'spdb_object', array( 'id' => $sid ) )
                    ->loadResult();
            $this->oTypeName = Sobi::Txt( 'OTYPE_' . strtoupper( $this->oType ) );
        }
        switch ( $this->oType ) {
            case 'entry':
                $this->oName = SPFactory::Entry( $sid )
                        ->get( 'name' );
                break;
            case 'section':
                $this->oName = SPFactory::Section( $sid )
                        ->get( 'name' );
                break;
            case 'category':
                $this->oName = SPFactory::Category( $sid )
                        ->get( 'name' );
                break;
        }
    }
}