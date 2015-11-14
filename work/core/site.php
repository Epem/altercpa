<?php

/*******************************************************************************

 *
 * 	AlterVision Core Framework - CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright © 2005-2015 Anton Reznichenko
 *

 *
 *  File: 			core / site.php
 *  Description:	The CORE class
 *  Author:			Anton 'AlterVision' Reznichenko - altervision13@gmail.com
 *

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.

*******************************************************************************/

class Core { // class Site start

	// Public Inner Classes
    public 		$cache		= null;
    public 		$config		= null;
    public		$cron		= null;
    public 		$db			= null;
    public 		$mainline	= null;
    public		$media		= null;
    public		$text		= null;
    public 		$tpl		= null;
    public		$user		= null;

    // Data Storage Variables
    public 		$get 		= array ();
    public 		$post 		= array ();
    public 		$files 		= array ();
    public 		$server 	= array ();
    public		$lang 		= array ();		// Array of Language Variables

	// Session
    protected 	$session	= array ();
	private		$session_id;
	private		$session_changed;
	private		$session_path;

	// Modules
	protected 	$data		= array ();

    // Inner Status Functions
    protected 	$alive 		= false;
    protected	$loaded 	= false;
    public		$mode		= false;

    // Dependencies
    private		$js 		= array ();
    private		$css 		= array ();
    private		$mjs 		= array ();
    private		$mcss 		= array ();
    private		$meta 		= array ();

    // Output
    private   	$gzip 		= false;
    private		$headered 	= false;
    private		$showstat 	= false;
    private		$menu		= array ();
    private		$messages	= array ();

	// Paths
	private		$path_lang;

    // Misc
    private		$timer;
    private		$urls		= array ();

    // Processing
    private		$handlers 	= array ();

    // Constructor
	// Loads All Main Variables
	public function __construct ($configs) {

	    list($usec, $sec) = explode(" ",microtime());
    	$this->timer = ((float)$usec + (float)$sec);

		// Simple Sets
		$this->crypto		= $configs['crypto'];
		$this->path_lang	= $configs['lang_mod'];

		//
		// Variables
		//

        // Checking Magic Quotes
	    if ( get_magic_quotes_gpc() ) {
	        $configs['get']    		= stripslashes_deep($configs['get']   );
	        $configs['post']   		= stripslashes_deep($configs['post']  );
	    }

        // Setting Magic Quotes
	    $this->get		= add_magic_quotes($configs['get']   );
	    $this->post		= add_magic_quotes($configs['post']  );
	    $this->server	= add_magic_quotes($configs['server']);
	    $this->files	= $configs['files'];
	    $this->mode		= 0;

        // Loading Session
        $this->session_path = $configs['session'];
        $this->session_load ( $configs['cookie']['ssid'] );

		//
		// Classes
		//

        // Load Database
        if ($configs['db']) {
	        $this->db = new sql_db ($configs['db']['host'], $configs['db']['user'], $configs['db']['pass'], $configs['db']['base'], $configs['db']['charset'], $configs['db']['collate']);
        }

        // Loading Main Data
        $this->text		= new texter ( $this );
       	$this->cache 	= new CacheControl ( $configs['cache'], isset($configs['mc']) ? $configs['mc'] : null );
        $this->user		= new User ( $this );
        $this->cron		= new CronControl ( $this );
        $this->email	= new eMailSender ( $this );

        // Language
        if ($configs['lang_def'] && $configs['lang_path']) {
			$l = ($this->user->meta['lang']) ? $this->user->meta['lang'] : $configs['lang_def'];
			$lang_file = sprintf ($configs['lang_path'], $l);
            if (file_exists($lang_file)) {
                global $lang;
    			require_once ($lang_file);
    			$this->lang	= $lang;
                unset ($lang);
            }
        }

        // Template
        if ($configs['tpl']) {
        	if ( $this->mode ) {        		$this->tpl = new Template ( $configs['mtpl'] );
        	} else $this->tpl = new Template ($configs['tpl']);
        }

		// Additional Modules
        $this->mainline	= new SiteMainline	($this);

		// Initialize Variables
        $this->alive	= true;

        $web_path = $this->mode ? $configs['webm_path'] : $configs['web_path'];
        $this->urls = array (
	        'index'		     	=> '<a href="'.$web_path.'">%s</a>',
	        'index_u'   		=> $web_path,
	        'link'   		 	=> '<a href="%1$s" title="%2$s">%2$s</a>',
            'css'				=> '<link rel="stylesheet" href="/style/%s.css" type="text/css" />',
            'js'				=> '<script type="text/javascript" src="/style/%s.js"></script>',
            'meta'				=> '<meta name="%s" content="%s" />',
            'logout'			=> '<a href="'.$web_path.'?logout=1">%s</a>',
            'logout_u'			=> $web_path.'?logout=1',
	        'a'					=> $web_path.'a-%s/%d',
	        'm'					=> $web_path.'%s',
	        'i'					=> $web_path.'%s/%d',
	        'mp'				=> $web_path.'%s?%s',
	        'mm'				=> $web_path.'%s?message=%s',
	        'im'				=> $web_path.'%s/%d?message=%s',
        );

	}

    // Destructor
    public function __destruct () {

		// Here We Should Call Clear Procedure
        $this->prepare_to_die();

    }

    //
    // Variable Management
    //

    // Get Data Variable
    public function __get ($variable) {
    	return $this->data[$variable];
    }

	// Set Data Variable
    public function __set ($variable, $value) {
    	$this->data[$variable] = $value;
    }

    // Check is Variable IsSet
    public function __isset ($variable) {
    	return isset($this->data[$variable]);
    }

	//
	// Handlers Management
	//

	// Add Handle Function to Array
	public function handle ($name, $function) {

		if (isset($this->handlers[$name])) {
			$this->handlers[$name][] = $function;
		} else $this->handlers[$name] = array($function);
		return true;

	}

	public function cando ( $name ) {
		return isset($this->handlers[$name]);
	}

	// Process Specified Handle Functions
	public function process ($name) {

		$result = false;
		if (isset($this->handlers[$name])) {
			foreach ($this->handlers[$name] as $f) {
				$result = call_user_func ($f, $this) || $result;
			}
		}
		return $result;

	}

	// Process Specified Handlers as Filters
 	public function filter ($name, $data) {

		if (isset($this->handlers[$name])) {
			foreach ($this->handlers[$name] as $f) {
				$data = call_user_func ($f, $this, $data);
			}
		}
		return $data;

 	}

    //
    // Program Flow Management
    //

    // Destcuctor
    protected function prepare_to_die () {

    	if ($this->headered) {
        	$this->footer ();
        }

    	// If not dead already
        if ($this->alive) {

	        $this->alive = false;

	    	// Writing Session and Cookie Variables
	        if (! $this->headered) {
            	$this->session_save ();
            }

            // Kill Children Classes
            unset ($this->cron);
            unset ($this->user);
            unset ($this->tpl);
            unset ($this->mainline);
            unset ($this->cache);
            $dbq = isset( $this->db ) ? $this->db->queries : false;
            unset ($this->db);
			unset ($this->text);

        }

    }

    //
    // Session Management
    //

    // Get Session Variable
    public function session_get ($variable) {
    	return $this->session[$variable];
    }

    // Set Session Variable
    public function session_set ($variable, $value) {
    	$this->session[$variable] = $value;
    }

    // Load Session from Server
    protected function session_load ($ssid) {

		if ($ssid) {
			$this->session_id = $ssid;
			if (file_exists(sprintf($this->session_path, md5($ssid)))) {
				$s = file_get_contents (sprintf($this->session_path, md5($ssid)));
				$this->session = unserialize ($s);
			} else $this->session = array ();
        } else {
			$this->session_id = md5(microtime());
			$this->session = array ();
        }

		setcookie ( 'ssid', $this->session_id, time() + 1000000, '/' );

    }

    // Save Session to Server
    protected function session_save () {

		if (count($this->session)) {
			$s = serialize ($this->session);
			file_put_contents (sprintf($this->session_path, md5($this->session_id)), $s);
		}

    }

    //
    // Messaging Functions
    //

    // General Die
    public function _die ($message = '', $e_file = '', $e_line = '') {

    	$this->prepare_to_die ();
        if ($message) {
	        if ($e_file) $message .= "\n Error occured in $e_file at $e_line line";
	        die ($message);
        } else die ();


    }

	// Show General Message
	public function message ($type, $title, $text, $lnk = '') {

	    if (! $lnk) $lnk = $this->server['HTTP_REFERER'];
        $title 	= $this->lang[$title];
        $text   = $this->lang[$text];

	    echo '<html>
<head>
	<title>'.$title.'</title>
	<meta http-equiv="refresh" content="5;url='.$lnk.'" />
    <style>
		body {
			font: normal 12px Verdana, Tahoma, sans-serif;
            color: #'. ( ($type == 'error') ? 'f22' : '666' ) . '; text-align: center;
        }
        h1 { font: normal 24px Verdana, Tahoma, sans-serif; }
    </style>
</head>
<body>
	<table width="600" height="100%" align="center">
		<tr height="100%" width="100%"><td height="100%" width="100%" align="center" valign="middle">
			<h1>'.$title.'</h1>
            <p>'.$text.'</p>
            <p><small><a href="'.$lnk.'">'.$this->lang['message_redirect'].'</a></small></p>
        </td></tr>
    </table>
</body>
</html>';

	    $this->prepare_to_die ();
	    die ();

	}

	// Adding Info Message to List
	function info ( $type, $text ) {		if ( $this->mode && $type == 'ok' ) $type = 'success';
		if (isset($this->lang[$text])) $text = $this->lang[$text];
		$this->messages [] = array ( $type, $text );
	}

    //
    // Output Processing
    //

	public function go ( $url ) {    	header ( 'Location: '.$url );
    	$this->_die();
	}



	public function header ( $mode = null ) {

    	header ("Content-type: text/html; charset=utf-8");

    	$this->session_save ();

		$this->tpl->load( 'header', 'header' );
		if ( ! $this->mode ) $this->enque_css( 'style' );
		$this->enque_js ( 'jquery' );

        $this->tpl->vars('header', array(

            'site_name'     => $this->mainline->site_name,
            'site_descr'    => $this->mainline->site_descr,
            'site_url'      => $this->mainline->site_url,

            'title'         => $this->mainline->title (),
            'mainline'      => $this->mainline->mainline ( $mode ),

			'user_name'		=> sprintf( $this->lang['user_hello'], $this->user->name ),
			'user_vip'		=> $this->user->vip ? $this->lang['iamvip'] : '',
			'user_ext'		=> $this->user->ext ? $this->lang['iamext'] : '',
			'u_logout'		=> $this->url( 'logout_u' ),
			'logout'		=> $this->lang['logout'],
			'u_profile'		=> $this->url( 'm', 'profile' ),
			'profile'		=> $this->lang['profile'],
			'u_money'		=> $this->url( 'm', 'money' ),
			'money'			=> $this->lang['menu_money'],
			'cash'			=> rur( $this->user->cash ),
			'u_support'		=> $this->url( 'm', 'support' ),
			'support'		=> $this->user->supp_new ? sprintf( '%s: <b>%s</b>', $this->lang['support'], $this->user->supp_new) : $this->lang['support'],

        ));

		if ( $this->user->id ) {
			$this->tpl->block( 'header', 'user_in', array() );
		} else $this->tpl->block( 'header', 'user_out', array() );
		$this->tpl->block( 'header', 'support' );

        foreach ($this->meta as $k => $v) {
            $this->tpl->block ('header', 'meta', array(
                'm' => $this->url('meta', $k, $v),
            ));
        }

        foreach ($this->css as $css) {
			if (is_array($css)) {
	            $this->tpl->block ('header', 'meta', array(
	                'm' => $this->url('mcss', $css[0], $css[1]),
	            ));
	         } else {
	            $this->tpl->block ('header', 'meta', array(
	                'm' => $this->url('css', $css),
	            ));
	         }
        }

        foreach ($this->js as $js) {
			if (is_array($js)) {
	            $this->tpl->block ('header', 'meta', array(
	                'm' => $this->url('mjs', $js[0], $js[1]),
	            ));
			} else {
	            $this->tpl->block ('header', 'meta', array(
	                'm' => $this->url('js', $js),
	            ));
			}
        }

		foreach ( $this->menu as $m ) {
			$this->tpl->block ('header', 'menu', array(
				'link' 		=> ( $this->mode && $m['sub'] ) ? '#" class="dropdown-toggle" data-toggle="dropdown' : $m['link'],
				'name' 		=> $m['name'],
				'div' 		=> $m['div'],
				'hassubs' 	=> $m['sub'] ? 'hassubs' : '',
				'dropbown'	=> $m['sub'] ? 'dropdown' : '',
				'caret'		=> $m['sub'] ? '<b class="caret"></b>' : '',
			));
			if ( count($m['sub']) ) {
				$this->tpl->block ('header', 'menu.sub', array ());
				foreach ($m['sub'] as $ms) {
					$this->tpl->block ('header', 'menu.sub.item', array('link' => $ms['link'], 'name' => $ms['name']));
				}
			}
		}

		if (count($this->messages)) {
         	$this->tpl->block ('header', 'info', array ());
         	foreach ($this->messages as &$ms) {
				$this->tpl->block ('header', 'info.msg', array (
					'type'		=> $ms[0],
					'text'		=> $ms[1],
				));
         	} unset ($ms);
		}

		$this->process ('header');

		$this->tpl->output('header');

		$this->headered = true;
		$this->showstat = true;

	}

	public function footer ( $mode = null ) {

		$this->headered = false;
		$this->tpl->load('footer', 'footer');

 		$this->tpl->vars('footer', array(
            'title'         => $this->mainline->title (),
            'mainline'      => $this->mainline->mainline (),
            'copyright'     => $this->mainline->copyright (),
            'altervision'	=> $this->lang['cms_powered_by'],
   		));

   		if ( $this->user->level ) $this->tpl->block( 'footer', 'debugga', array() );

		$this->process ('footer');
		$this->headered = false;

        // Work Timing
	    list($usec, $sec) = explode(" ",microtime());
    	$endtimer = ((float)$usec + (float)$sec);
        $worktime = sprintf("%1.3f", $endtimer - $this->timer);

 		$this->tpl->vars('footer', array(
            'pr_time'       => $worktime,
            'pr_sql'        => $this->db->queries,
            'pr_mem'		=> mkb_out( function_exists('memory_get_peak_usage') ? memory_get_peak_usage() : 0 ),
            'pr_date'		=> date( 'd.m.Y H:i:s' ),
   		));

		$this->tpl->output('footer');

	}

	// Resets Current Admin Menu Config
	public function setmenu ( $menu ) {
		$this->menu = $menu;
	}

    public function form ($formname, $action, $method, $title, $field, $button) {

        $this->tpl->load ($formname, 'form');

	    $this->tpl->vars($formname, array(
	        'name' 				=> $formname,
	        'action' 			=> $action,
	        'method' 			=> ($method) ? $method : 'post',
	        'title'   			=> $title,
	        'mce'				=> $this->url ( 'js', 'tiny_mce' ) . $this->url ( 'js', 'jquery.tinymce' ),
	        'codemirror'		=> $this->url ( 'js', 'codemirror/codemirror' ) . $this->url ( 'css', 'codemirror/codemirror' ),
	        'datejs'			=> $this->url ( 'js', 'jquery.datepicker' ) . $this->url ( 'js', 'jquery.datepicker.ru' ) . $this->url ( 'css', 'ui' ),
        ));

        $hasfiles	= false;
        $hasmce		= false;
        $hasdate	= false;
        $hascode	= false;

		foreach ($field as &$f) {

			$this->tpl->block($formname, 'field', array());

        	switch ($f['type']) {

			  case 'head':
        		$this->tpl->block($formname, 'field.head', array(
	                'value' 	=> $f['value'],
                ));
               	break;

              case 'line':
        		$this->tpl->block($formname, 'field.line', array(
	                'value' 	=> $f['value'],
	            ));
                break;

              case 'hidden':
         		$this->tpl->block($formname, 'field.hidden', array(
	                'name'		=> $f['name'],
	                'value' 	=> $f['value'],
	            ));
               	break;

              case 'text':
         		$this->tpl->block($formname, 'field.text', array(
	                'head' 		=> $f['head'],
					'descr'		=> $f['descr'],
	                'name' 		=> $f['name'],
	                'value' 	=> $f['value'],
	                'maxwidth' 	=> $f['length'],
	            ));
               	break;

              case 'file':
         		$this->tpl->block($formname, 'field.file', array(
	                'head' 		=> $f['head'],
					'descr'		=> $f['descr'],
	                'name' 		=> $f['name'])
   	            );
                $hasfiles = true;
               	break;

              case 'pass':
         		$this->tpl->block($formname, 'field.pass', array(
	                'head' 		=> $f['head'],
					'descr'		=> $f['descr'],
	                'name' 		=> $f['name'],
	                'value' 	=> $f['value'],
	                'maxwidth' 	=> $f['length'],
                ));
               	break;

              case 'checkbox':
              case 'vcheckbox':
         		$this->tpl->block($formname, 'field.checkbox', array(
	                'head' 		=> $f['head'],
					'descr'		=> $f['descr'],
	                'name' 		=> $f['name'],
	                'value' 	=> ($f['value']) ? ' value="'.$f['value'].'" ' : '',
	                'checked' 	=> ($f['checked']) ? ' checked="checked" ' : '',
	            ));
               	break;

               case 'textarea':
         		$this->tpl->block($formname, 'field.textarea', array(
	                'head' 		=> $f['head'],
					'descr'		=> $f['descr'],
	                'name' 		=> $f['name'],
	                'value' 	=> $f['value'],
	                'rows' 		=> $f['rows'],
	            ));
               	break;

               case 'bbcode':
         		$this->tpl->block($formname, 'field.bbcode', array(
	                'head' 		=> $f['head'],
					'descr'		=> $f['descr'],
	                'name' 		=> $f['name'],
	                'value' 	=> $f['value'],
	                'rows' 		=> $f['rows'],
	            ));
             	break;

              case 'mces':
         		$this->tpl->block($formname, 'field.mces', array(
	                'head' 		=> $f['head'],
					'descr'		=> $f['descr'],
	                'name' 		=> $f['name'],
	                'value' 	=> $f['value'],
	            ));
	            $hasmce = true;
               	break;

			  case 'mcea':
         		$this->tpl->block($formname, 'field.mcea', array(
	                'head' 		=> $f['head'],
					'descr'		=> $f['descr'],
	                'name' 		=> $f['name'],
	                'value' 	=> $f['value'],
	            ));
	            $hasmce = true;
               	break;

			  case 'code':
         		$this->tpl->block($formname, 'field.code', array(
	                'head' 		=> $f['head'],
					'descr'		=> $f['descr'],
	                'name' 		=> $f['name'],
	                'value' 	=> $f['value'],
					'lang'		=> ( $f['lang'] ) ? $f['lang'] : 'generic',
					'rows'		=> ( $f['rows'] ) ? $f['rows'] : 15,
	            ));
	            $hascode = true;
	            $codelang[$f['lang'] ? $f['lang'] : 'php'] = true;
               	break;

              case 'select':
         		$this->tpl->block($formname, 'field.select', array(
	                'head' 		=> $f['head'],
					'descr'		=> $f['descr'],
	                'name' 		=> $f['name'],
	            ));
				foreach ($f['value'] as &$fld) {
	                $this->tpl->block($formname, 'field.select.option', array(
	                    'name'		=> $fld['name'],
	                    'value' 	=> $fld['value'],
	                    'select' 	=> ($fld['select']) ? ' selected="selected" ' : '',
	                ));
                } unset ($fld);
               	break;

              case 'radio':
         		$this->tpl->block($formname, 'field.radio', array(
	                'head' 		=> $f['head'],
					'descr'		=> $f['descr'],
	                'name' 		=> $f['name'],
	            ));
				foreach ($f['value'] as &$fld) {
	                $this->tpl->block($formname, 'field.radio.option', array(
	                    'name'		=> $fld['name'],
	                    'value' 	=> $fld['value'],
	                    'select' 	=> ($fld['select']) ? ' checked="checked" ' : '',
	                ));
                } unset ($fld);
               	break;

              case 'radioline':
         		$this->tpl->block($formname, 'field.radioline', array(
	                'head' 		=> $f['head'],
	                'name' 		=> $f['name'],
	            ));
				foreach ( $f['options'] as &$fld ) {
	                $this->tpl->block($formname, 'field.radioline.option', array(
	                    'name'		=> $fld['name'],
	                    'value' 	=> $fld['value'],
	                    'select' 	=> ($fld['value'] == $f['value']) ? ' checked="checked" ' : '',
	                ));
                } unset ($fld);
               	break;

              case 'captcha':
         		$this->tpl->block($formname, 'field.captcha', array(
					'head'		=> $f['head'],
					'descr'		=> $f['descr'],
	                'image'		=> $f['image'],
	                'name'		=> $f['name'],
				));
                break;


              case 'date':
         		$this->tpl->block($formname, 'field.date', array(
	                'head' 		=> $f['head'],
					'descr'		=> $f['descr'],
	                'name' 		=> $f['name'],
	                'value' 	=> $f['value'],
	            ));
	            $hasdate = true;
                break;

            }

        } unset ($f);

        if ($hasfiles) $this->tpl->vars($formname, array('encoding' => ' enctype="multipart/form-data" '));
        if ($hasmce) $this->tpl->block($formname, 'mce', array());
        if ($hasdate)	$this->tpl->block( $formname, 'dates', array() );
        if ($hascode)	{
        	$this->tpl->block( $formname, 'codemirror', array() );
        	foreach( $codelang as $l => $t ) {
            	$this->tpl->block( $formname, 'codemirror.lang', array(
					'path'	=> $this->url ( 'js', "codemirror/mode/$l/$l" )
            	));
        	}
        }
		foreach ($button as &$b) {

			$this->tpl->block($formname, 'buttons', array());

        	switch ($b['type']) {
				case 'submit':	$this->tpl->block($formname, 'buttons.submit',	array('name' => $b['name'], 'value' => $b['value'])); break;
				case 'reset':		$this->tpl->block($formname, 'buttons.reset',	array('name' => $b['name'], 'value' => $b['value'])); break;
				case 'cancel':    $this->tpl->block($formname, 'buttons.cancel',	array('name' => $b['name'], 'value' => $b['value'])); break;
            }

        } unset ($b);

		$this->tpl->output($formname);

    }

    //
    // URL Printing Functions
    //

    public function url () {

	    $arguments = func_get_args();

        $url_id = $arguments[0];
        $arguments[0] = $this->urls[$url_id];

        return call_user_func_array ('sprintf', $arguments);

    }

    // Simply Merge URL Lists
    public function url_add ($url_list) {

    	// Using foreach seems faster than using array_merge ... %)  (01.08.2009 03:54am)
		foreach ($url_list as $u => $v) {
			$this->urls[$u] = $v;
        }

    }

    //
    // Registration and Enquery Functions
    //

    // Load JS In Header
    public function enque_js ($js) {

    	if (! in_array($js, $this->js)) {
			$this->js[] = $js;
        }

    }

    // Load CSS In Header
    public function enque_css ($css) {

    	if (! in_array($css, $this->css)) {
			$this->css[] = $css;
        }

    }

    // Add Meta
    public function enque_meta ($name, $value) {
    	$this->meta[$name] = $value;
    }

} // class Site end

?>