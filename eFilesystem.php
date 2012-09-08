<?php
/**
 * Project: eFilesystem:: Extended File System API
 * File:    eFilesystem.php
 * Depnedency: pear.oops.org/ePrint over 1.0.1
 *
 * The eFilesystem class is supported various extended system function
 *
 * @category    System
 * @package     eFilesystem
 * @author      JoungKyun.Kim <http://oops.org>
 * @copyright   (c) 2009 OOPS.ORG
 * @license     BSD
 * @version     $Id$
 * @link        http://pear.oops.org/package/eFilesystem
 * @since       File available since relase 1.0.0
 * @example     pear_eFilesystem/test.php Sameple codes of eFilesystem class
 * @filesource
 */

/**
 * Dependency on 'pear.oops.org/ePrint' pear package over 1.0.1
 */
require_once 'ePrint.php';

/**
 * Base classes for Extended Filesystem API
 * @package eFilesystem
 */
class eFilesystem extends ePrint {
	// {{{ properties
	const RELATIVE = 1;
	const ABSOLUTE = 2;

	static private $make_ini_callback_key = null;
	// }}}

	// {{{ function file_nr ($f, $use_include_path = false, $resource = null)
	/**
	 * Reads entire file into an array
	 *
	 * file_nr api runs same file function of php. But file_nr has
	 * no \r\n or \n character on array members.
	 *
	 * The examples:
	 * {@example pear_eFilesystem/test.php 26 3}
	 *
	 * @access public
	 * @return array    Array or false if not found file path nor file resource.
	 * @param  string   file path
	 * @param  boolean  (optional) Search file path on include_path of php.
	 *                  Defaults is false.
	 * @param  resource (optional) already opend file description resource
	 *                  Defaults is null.
	 */
	function file_nr ($f, $use_include_path = false, $resource = null) {
		$fp = is_resource ($resource) ? $res : fopen ($f, 'rb', $use_include_path);

		if ( ! is_resource ($fp) )
			return false;

		$i = 0;
		while ( ! feof ($fp) ) {
			$buf = preg_replace ("/\r?\n$/", '', fgets ($fp, 1024));
			$_buf[$i++] = $buf;
		}

		if ( ! is_resource ($resource) )
			fclose ($fp);

		if ( ! $_buf[--$i] ) :
			unset ($_buf[$i]);
		endif;

		return $_buf;
	}
	// }}}

	// {{{ function mkdir_p ($path, $mode)
	/**
	 * Attempts to create the directory specified by pathname.
	 *
	 * If does not parent directory, this API create success.
	 * This means that same operate with mkdir (path, mode, true) of php
	 *
	 * The examples:
	 * {@example pear_eFilesystem/test.php 37 18}
	 *
	 * @access public
	 * @return boolean return false, create error<br>
	 *                 return true, create success.
	 * @param string   given path
	 * @param int      (optional) The mode is 0777 by default, which means the widest
	 *                 possible access. For more information on modes, read
	 *                 the details on the chmod() page.
	 */
	function mkdir_p ($path, $mode = 0777) {
		$_path = realpath ($path);

		if ( file_exists ($path) ) {
			if ( is_dir ($path) )
				return false;
			else
				return false;
		}

		return mkdir ($path, $mode, true);
	}
	// }}}

	// {{{ function safe_unlink ($f)
	/**
	 * Deletes a file. If given file is directory, no error and return false.
	 *
	 * @access public
	 * @return bolean|int return true, success<br>
	 *                    return false, remove false<br>
	 *                    return 2, file not found<br>
	 *                    return 3, file is directory
	 * @param string      given file path
	 */
	function safe_unlink ($f) {
		if ( file_exists ($f) ) {
			if ( is_dir ($f) )
				return 3;

			return @unlink ($f);
		} else
			return 2;

		return $r;
	}
	// }}}

	// {{{ function safe_unlink_msg ($r, $path = 'Given path')
	/**
	 * return message of eFilesystem::safe_unlink method return code
	 *
	 * @access private
	 * @return string
	 * @param  integer return code of safe_unlink method
	 * @param  string  (optional) path
	 */
	private function safe_unlink_msg ($r, $path = 'Given path') {
		if ( $r === true )
			return;

		$func = ' for eFilesystem::safe_unlink()';

		switch ($r) {
			case 2 : return "{$path} not found {$func}"; break;
			case 3 : return "{$path} is directory {$func}"; break;
		}

		return "{$path} don't be removed {$func}";
	}
	// }}}

	// {{{ function unlink_r ($path)
	/**
	 * Deletes a file or directory that include some files
	 *
	 * The examples:
	 * {@example pear_eFilesystem/test.php 56 3}
	 *
	 * @access public
	 * @return boolean
	 * @param string   Given path.
	 *                 You can use Asterisk(*) or brace expand({a,b}) on path.
	 */
	function unlink_r ($path) {
		if ( ! trim ($path) ) {
			self::warning ('PATH is null string for eFilesystem::unlink_r()');
			return false;
		}

		/*
		 * support glob and brace expend
		 */
		if ( preg_match ('/([*])|({[^,]+,)/', $path) ) {
			$l = glob ($path, GLOB_BRACE);
			if ( $l === false || empty ($l) ) {
				self::warning ("11 {$path} not found for eFilesystem::unlink_r()");
				return false;
			}

			foreach ( $l as $v ) {
				if ( is_dir ($v) )
					self::unlink_r ($v);
				else {
					self::safe_unlink ($v);
					if ( $r !== true ) {
						self::warning (self::safe_unlink_msg ($r, $path));
						return false;
					}
				}
			}

			return true;
		}

		if ( ! file_exists ($path) ) {
			self::warning ("{$path} not found for eFilesystem::unlink_r()");
			return false;
		}

		/*
		 * path is not directory, remove here.
		 */
		if ( ! is_dir ($path) ) {
			$r = self::safe_unlink ($path);
			if ( $r !== true ) {
				self::warning (self::safe_unlink_msg ($r, $path));
				return false;
			}
			return $r;
		}

		/* path is directory... */
		$dh = @opendir ($path);
		if ( ! is_resource ($dh) )
			return false;

		while ( ($f = @readdir ($dh)) ){
			if ( $f == '.' || $f == '..' )
				continue;

			$fullpath = $path . '/' . $f;
			//echo $fullpath . "\n";
			if ( is_dir ($fullpath) )
				$r = self::unlink_r ($fullpath);
			else {
				$r = self::safe_unlink ($fullpath);
				if ( $r !== true )
					self::warning (self::safe_unlink_msg ($r, $fullpath));
			}

			if ( $r !== true ) {
				closedir ($dh);
				return false;
			}
		}
		closedir ($dh);

		return @rmdir ($path);
	}
	// }}}

	// {{{ function dirlsit ($path, $fullpath = false)
	/**
	 * get dir list for given path
	 *
	 * The examples:
	 * {@example pear_eFilesystem/test.php 60 6}
	 *
	 * @access public
	 * @return array|false
	 * @param   string  given path
	 * @param   integer (optional) Defaults to false.<br>
	 *                  set false, return only file or directory name<br>
	 *                  set eFilesystem::RELATIVE, return relative path<br>
	 *                  set eFilesystem::ABSOLUTE, return absolute path<br>
	 */
	function dirlist ($path, $fullpath = false) {
		if ( ! $path )
			return false;

		$path = preg_replace ('!/$!', '', $path);
		$p = @opendir ($path);

		if ( ! is_resource ($p) )
			return false;

		while ( ($list = readdir ($p)) ) {
			if ( $list == '.' || $list == '..' )
				continue;

			switch ($fullpath) {
				case self::RELATIVE :
					$r[] = "$path/$list";
					break;
				case self::ABSOLUTE :
					$r[] = realpath ("$path/$list");
					break;
				default:
					$r[] = $list;
			}
		}
		closedir ($p);

		return $r;
	}
	// }}}

	// {{{ function tree ($dir = '.', $prefix = '', $recursive = false)
	/**
	 * get directory tree for given path
	 *
	 * The examples:
	 * {@example pear_eFilesystem/test.php 37 18}
	 *
	 * @access public
	 * @return object obj->file is number of files.<br>
	 *                obj->dir is number of directories.
	 * @param string  (optional) Given path. Defaults to current directory (./).
	 * @param string  (optional) for recursive call. Don't use!
	 * @param boolean (optional) for recursive call. Don't use!
	 */
	function tree ($dir = '.', $prefix = '', $recursive = false) {
		$n->file = 0;
		$n->dir  = 0;

		if ( ! is_dir ($dir) ) return $n;
		$dir = preg_replace ('!/$!', '', $dir);

		if ( $recursive === false ) {
			if ( php_sapi_name () == 'cli' )
				self::aPrintf ("blue", "%s/\n", $dir);
			else
				echo "$dir/\n";
		}

		if ( ($list = self::dirlist ($dir)) === false )
			return $n;

		if ( is_array ($list) ) sort ($list);
		$listno = count ($list);

		for ( $i=0; $i<$listno; $i++ ) {
			$fullpath = $dir . '/' . $list[$i];
			$last = ( $i === ($listno -1 ) ) ? true : false;

			$_prefix = $last ? '`-- ' : '|-- ';

			if ( php_sapi_name () == 'cli' && is_dir ($fullpath) )
				$fname = self::asPrintf ('blue', "%s/", $list[$i]);
			else {
				$fname = $list[$i];
				if ( is_dir ($fullpath) )
					 $fname .= '/';
			}

			printf ("%s%s%s\n", $prefix, $_prefix, $fname);
			$_prefix = $prefix . preg_replace ('/`|-/', ' ', $_prefix);

			if ( is_dir ($fullpath) ) {
				$n->dir++;
				$_n = self::tree ($fullpath, $_prefix, true);
				$n->dir += $_n->dir;
				$n->file += $_n->file;
			} else
				$n->file++;
		}
		return $n;
	}
	// }}}

	// function find ($path = './', $type = '', $norecursive = false) {{{
	/**
	 * get file list that under given path
	 *
	 * The examples:
	 * {@example pear_eFilesystem/test.php 67 4}
	 *
	 * @access public
	 * @return array   return array of file list. If given path is null or don't exist, return false.
	 * @param  string (optional) Given path. Defaults to current directory (./)
	 * @param  string (optional) list type. Defaults to all.<br>
	 *                f (get only files),<br>
	 *                d (get only directories),<br>
	 *                l (get only links),<br>
	 *                fd (get only files and directories),<br>
	 *                fl (get only files and links),<br>
	 *                dl (get only directories and links)<br>
	 *                /regex/ (use regular expression)
	 * @param  boolean (optional) Defaults to false.
	 *                set true, don't recursive search.
	 */
	function find ($path = './', $type= '', $norecursive = false) {
		$path = preg_replace ('!/$!', '', $path);

		$_r = self::dirlist ($path, self::RELATIVE);

		if ( $_r === false || ! count ($_r) )
			return false;

		$file = array ();
		foreach ( $_r as $v ) {
			switch ($type) {
				case 'f' :
					if ( is_file ($v) && ! is_link ($v) )
						$file[] = $v;
					break;
				case 'd' :
					if ( is_dir ($v) )
						$file[] = $v;
					break;
				case 'l' :
					if ( is_link ($v) )
						$file[] = $v;
					break;
				case 'fd' :
					if ( is_file ($v) || is_dir ($v) )
						$file[] = $v;
					break;
				case 'fl' :
					if ( is_file ($v) || is_link ($v) )
						$file[] = $v;
					break;
				case 'dl' :
					if ( is_dir ($v) || is_link ($v) )
						$file[] = $v;
					break;
				default :
					if ( $type ) :
						if ( preg_match ($type, $v) ) :
							$file[] = $v;
						endif;
					else :
						$file[] = $v;
					endif;
			}

			if ( is_dir ($v) && $norecursive === false ) {
				$_rr = self::find ($v, $type);
	
				if ( is_array ($_rr) ) {
					if ( ! $file ) array ();
					$file = array_merge ($file, $_rr);
				}
			}
		}

		return $file;
	}
	// }}}

	// function prompt ($prompt, $hidden = false) {{{
	/**
	 * print shell line prompt and get values
	 * @access public
	 * @return string
	 * @param  string  print prompt string to stdout
	 * @param  boolean (optional) hidden input strings
	 */
	function prompt ($prompt, $hidden = false) {
		$prompt = ! $prompt ? '$ ' : $prompt;

		if ( $hidden === false && function_exists ('readline') )
			return readline ($prompt);

		printf ('%s', $prompt);

		if ( $hidden !== false )
			system ('stty -echo >& /dev/null');

		$str = '';

		while ( $c != "\n" ) {
			if ( ($c = fgetc (STDIN)) != "\n" ) {
				@ob_flush ();
				flush ();
				echo '*';
				$str .= $c;
			}
		}

		if ( $hidden !== false ) {
			system ('stty echo >& /dev/null');
			@ob_flush ();
			flush ();
			echo "\n";
		}

		return $str;
	}
	// }}}

	// {{{ (array) eFilesystem::parse_ini ($f)
	/**
	 * parse configuration file or string
	 * @access public
	 * @return array The settings are returned as an associative array on success,
	 *               and return empty array on failure.
	 * @param string configuraion file or strings
	 */
	function parse_ini ($f) {
		if ( is_array ($f) || is_object ($f) ) {
			self::warning ('Invalid type of argument 1. File or string is valid');
			return array ();
		}

		$contents = file_exists ($f) ? self::file_nr ($f) : preg_split ('/[\r\n]+/', $f);
		if ( $contents === false || ! is_array ($contents) )
			return array ();

		foreach ( $contents as $r ) {
			$r = preg_replace ('/[ \t]*;.*/', '', $r);

			if ( ! $r )
				continue;

			if ( preg_match ('/^\[([^\]]+)\]$/', $r, $matches) ) {
				/* new variable */
				$varname = $matches[1];
			} else {
				/**
				 * invalid format
				 * must variable = value format
				 */
				if ( ! preg_match ('/^([^=]+)=(.*)$/', $r, $matches) )
					continue;

				$_varname = trim ($matches[1]);
				$_value   = trim ($matches[2]);

				$var = '$ret[\'' . $varname . '\']';
				if ( $_varname == 'value' ) {
					if ( preg_match ('/^(true|false|on|off|[01])$/', $_value, $matches) ) {
						switch ($matches[1]) {
							case 'true' :
							case 'on' :
							case '1' :
								$var .= ' = true;';
								break;
							default :
								$var .= ' = false;';
						}
					} else
						$var .= ' = \'' . $_value . "';";
				} else {
					$_varname_r = explode ('.', $_varname);
					for ( $i=0; $i<count ($_varname_r); $i++ ) {
						$var_quote = is_numeric ($_varname_r[$i]) ? '' : '\'';
						$var .= '[' . $var_quote . $_varname_r[$i] . $var_quote . ']';
					}

					if ( preg_match ('/^(true|false|on|off|[01])$/', $_value, $matches) ) {
						switch ($matches[1]) {
							case 'true' :
							case 'on' :
							case '1' :
								$var .= ' = true;';
								break;
							default :
								$var .= ' = false;';
						}
					} else
						$var .= ' = \'' . $_value . "';";
				}
				//echo $var . "\n";
				eval ($var);
			}
		}

		return is_array ($ret) ? $ret : array ();
	}
	// }}}

	// {{{ (array) eFilesystem::make_ini ($array)
	/**
	 * Make configuration that collespond on parse_ini method
	 *
	 * @access public
	 * @return string make configuration strings
	 * @param  array  configuraion array that has same foramt on result of parse_ini
	 */
	function make_ini ($array) {
		if ( ! is_array ($array) ) {
			self::warning ('Invalid type of argument 1. Array is valid');
			return false;
		}

		$buf = '';
		foreach ( $array as $key => $v ) {
			$r = "[{$key}]\n";

			if ( ! is_array ($v) ) {
				self::warning ('Invalid array data format');
				return false;
			}

			self::make_ini_callback ($r, $v);
			$buf .= preg_replace ('/\. = /', ' = ', $r) . "\n";
			#$buf .= $r . "\n";
		}

		return $buf;
	}
	// }}}

	// {{{ (void) eFilesystem::make_ini_callback (&$buf, $v)
	private function make_ini_callback (&$buf, $v) {
		if ( ! is_array ($v) ) {
			$buf .= sprintf (" = %s\n", $v);
			return;
		}

		foreach ( $v as $key => $val ) {
			if ( ! is_array ($val) )
				$buf .= sprintf ('%s%s.', self::$make_ini_callback_key, $key);

			self::$make_ini_callback_key .= $key . '.';
			self::make_ini_callback ($buf, $val);
			self::$make_ini_callback_key = preg_replace ('/[^.]+\.$/', '', self::$make_ini_callback_key);
		}

	}
	// }}}
}

?>
