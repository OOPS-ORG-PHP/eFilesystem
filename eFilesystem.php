<?php
/**
 * Project: eFilesystem:: eFilesystem:: 파일 시스템 확장 API<br>
 * File:    eFilesystem.php<br>
 * Depnedency: pear.oops.org/ePrint 1.0.1 이후 버전
 *
 * eFilesystem 클리스는 여러가지 확장된 시스템 function을 제공한다.
 *
 * @category    System
 * @package     eFilesystem
 * @author      JoungKyun.Kim <http://oops.org>
 * @copyright   (c) 2012 OOPS.ORG
 * @license     BSD
 * @version     $Id$
 * @link        http://pear.oops.org/package/eFilesystem
 * @since       File available since relase 1.0.0
 * @example     pear_eFilesystem/test.php Sameple codes of eFilesystem class
 * @filesource
 */

/**
 * myException 의존성
 * pear.oops.org/myException
 */
require_once 'myException.php';

/**
 * eFilesystem API는 pear.oops.org/ePrint pear package에 의존성이 있다.
 * ePrint 패키지는 최소 1.0.1 버전을 필요로 한다.
 */
require_once 'ePrint.php';

/**
 * 파일 시스템 확장 API를위한 기본 Class
 * @package eFilesystem
 */
class eFilesystem extends ePrint {
	// {{{ properties
	const RELATIVE = 1;
	const ABSOLUTE = 2;

	static private $make_ini_callback_key = null;
	// }}}

	// {{{ static public (array) file_nr ($f, $use_include_path = false, $resource = null)
	/**
	 * 파일을 읽어서 각 라인을 배열로 만들어 반환
	 *
	 * file_nr mothod는 php의 file 함수와 동일하게 작동을 한다. 하지만
	 * file_nr method는 file 함수와는 달리 각 행의 개행을 포함하지 않는다.
	 *
	 * 예제:
	 * {@example pear_eFilesystem/test.php 27 3}
	 *
	 * @access public
	 * @return array|false 파일의 각 행을 배열로 반환. 파일이 존재하지 않거나
	 *                     파일이 아니면 false를 반환한다.
	 * @param  string      파일 경로
	 * @param  boolean     (optional) true로 설정이 되면, php의 include_path
	 *                     에서 파일을 찾는다. 기본값은 false.
	 * @param  resource    (optional) file description resource가 지정이 되면,
	 *                     첫번째 파일 경로 인자의 값을 무시하고, 이 file
	 *                     description에서 파일의 내용을 읽는다. 기본값은 null
	 *                     이다.
	 */
	static public function file_nr ($f, $use_include_path = false, $resource = null) {
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

	// {{{ static public (bool) mkdir_p ($path, $mode)
	/**
	 * 주어진 경로에 디렉토리를 생성한다.
	 *
	 * 부모 디렉토리가 존재하지 않더라도, 이 API는 디렉토리를 생성하는데
	 * 실패 하지 않는다. 이 method는 php 의 mkdir (path, mode, true)와
	 * 동일하게 동작 한다. 시스템상에서 'mkdir -p path'와 같이 실행하는
	 * 것과 동일한 결과를 가진다.
	 *
	 *
	 * 예제:
	 * {@example pear_eFilesystem/test.php 38 18}
	 *
	 * @access public
	 * @return boolean 생성에 실패하면 false를 반환하고, 성공하면 true를 
	 *                 환한다.
	 * @param string   생성할 경로
	 * @param int      (optional) 기본값 0777. 이 의미는 누구나 접근 및
	 *                 쓰기가 가능함을 의미한다. mode에 대한 더 많은 정보는
	 *                 php의 {@link http://php.net/manual/en/function.chmod.php chmod()}
	 *                 문서를 참고 한다.
	 * @since 버전 1.0.2 부터 return 값이 boolean으로만 반환
	 */
	static public function mkdir_p ($path, $mode = 0777) {
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

	// {{{ static public (bool|int) safe_unlink ($f)
	/**
	 * Deletes a file. If given file is directory, no error and return false.
	 * 파일을 삭제 한다.
	 *
	 * 주어진 값이 존재하지 않거나 디렉토리일 경우에도 에러를 발생 시키지
	 * 않는다.
	 *
	 * @access public
	 * @return bolean|int 성공시에 true를 반환<br>
	 *                    삭제 실패시에 false를 반환<br>
	 *                    삭제할 파일이 없을 경우 2를 반환<br>
	 *                    삭제할 파일이 디렉토리일 경우 삭제하지 않고 3을 반환
	 * @param string      삭제할 경로
	 */
	static public function safe_unlink ($f) {
		if ( file_exists ($f) ) {
			if ( is_dir ($f) )
				return 3;

			return @unlink ($f);
		} else
			return 2;

		return $r;
	}
	// }}}

	// {{{ private (string) safe_unlink_msg ($r, $path = 'Given path')
	/**
	 * safe_unlink method의 반환 값을 문자열로 반환
	 *
	 * 이 함수는 eFilesystem Class 내부적으로 사용하기 위한 API이다.
	 *
	 * @access private
	 * @return string
	 * @param  integer safe_unlink method의 반환 값
	 * @param  string  (optional) 경로
	 */
	private function safe_unlink_msg ($r, $path = 'Given path') {
		if ( $r === true )
			return;

		#$func = ' for eFilesystem::safe_unlink()';

		switch ($r) {
			case 2 : return "{$path} not found {$func}"; break;
			case 3 : return "{$path} is directory {$func}"; break;
		}

		return "{$path} don't be removed {$func}";
	}
	// }}}

	// {{{ static public (bool) unlink_r ($path)
	/**
	 * 주어진 경로의 파일이나 디렉토리를 삭제
	 *
	 * 주어진 경로의 파일이나 디렉토리를 삭제 합니다. 디렉토리 삭제시에, 해당
	 * 디렉토리에 파일이나 하위 디렉토리가 포함하더라도 모두 삭제를 한다.
	 *
	 * 예제:
	 * {@example pear_eFilesystem/test.php 57 3}
	 *
	 * @access public
	 * @return boolean
	 * @param  string  삭제할 경로
	 *                 경로에 아스트리크(*)나 쉘 확장({a,b})을 사용할 수 있다.
	 */
	static public function unlink_r ($path) {
		if ( ! trim ($path) ) {
			#parent::warning ('PATH is null string for eFilesystem::unlink_r()');
			throw new myException ('PATH is empty string', E_USER_WARNING);
			return false;
		}

		// support glob and brace expend
		if ( preg_match ('/([*])|({[^,]+,)/', $path) ) {
			$l = glob ($path, GLOB_BRACE);
			if ( $l === false || empty ($l) ) {
				#parent::warning ("11 {$path} not found for eFilesystem::unlink_r()");
				throw new myException (sprintf ('%s not found', $path), E_USER_WARNING);
				return false;
			}

			foreach ( $l as $v ) {
				if ( is_dir ($v) )
					self::unlink_r ($v);
				else {
					self::safe_unlink ($v);
					if ( $r !== true ) {
						#parent::warning (self::safe_unlink_msg ($r, $path));
						throw new myException (self::safe_unlink_msg ($r, $path), E_USER_ERROR);
						return false;
					}
				}
			}

			return true;
		}

		if ( ! file_exists ($path) ) {
			#parent::warning ("{$path} not found for eFilesystem::unlink_r()");
			throw new myException (sprintf ('%s not found', $path), E_USER_WARNING);
			return false;
		}

		// path is not directory, remove here.
		if ( ! is_dir ($path) ) {
			$r = self::safe_unlink ($path);
			if ( $r !== true ) {
				#parent::warning (self::safe_unlink_msg ($r, $path));
				throw new myException (self::safe_unlink_msg ($r, $path), E_USER_WARNING);
				return false;
			}
			return $r;
		}

		// path is directory...
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
				if ( $r !== true ) {
					#parent::warning (self::safe_unlink_msg ($r, $fullpath));
					throw new myException (self::safe_unlink_msg ($r, $fullpath), E_USER_WARNING);
				}
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

	// {{{ static public (array) dirlsit ($path, $fullpath = false)
	/**
	 * 주어진 디렉토리 하위의 리스트를 배열로 반환
	 *
	 * 예제:
	 * {@example pear_eFilesystem/test.php 61 6}
	 *
	 * @access public
	 * @return array|false
	 * @param  string  리스트를 얻을 디렉토리 경로
	 * @param  integer (optional) 기본값 false.<br>
	 *                 false일 경우, 파일 또는 디렉토리의 이름만 반환<br>
	 *                 eFilesystem::RELATIVE일 경우, 상대 경로로 반환<br>
	 *                 eFilesystem::ABSOLUTE일 경우, 절대 경로로 반환
	 */
	static public function dirlist ($path, $fullpath = false) {
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

	// {{{ static public (object) tree ($dir = '.', $prefix = '', $recursive = false)
	/**
	 * 지정한 경로의 디렉토리 tree를 출력
	 *
	 * 시스템상의 tree 명령의 결과와 비슷하게 출력한다.
	 *
	 * 예제:
	 * {@example pear_eFilesystem/test.php 38 18}
	 *
	 * @access public
	 * @return stdClass 파일과 디렉토리 수를 반환
	 *   <pre>
	 *   stdClass Object
	 *   (
	 *       [file] => 파일 수
	 *       [dir]  => 디렉토리 수
	 *   )
	 *   </pre>
	 * @param string  (optional) 주어진 경로. 기본값은 현재 디렉토리(./)
	 * @param string  (optional) 재귀 호출을 위해 사용. 이 파라미터는 사용하지
	 *                않는다.
	 * @param boolean (optional) 재귀 호출을 위해 사용. 이 파라미터는 사용하지
	 *                않는다.
	 */
	static public function tree ($dir = '.', $prefix = '', $recursive = false) {
		$n = new stdClass;
		$n->file = 0;
		$n->dir  = 0;

		if ( ! is_dir ($dir) ) return $n;
		$dir = preg_replace ('!/$!', '', $dir);

		if ( $recursive === false ) {
			if ( php_sapi_name () == 'cli' )
				parent::aPrintf ("blue", "%s/\n", $dir);
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
				$fname = parent::asPrintf ('blue', "%s/", $list[$i]);
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

	// {{{ static public (array) find ($path = './', $type = '', $norecursive = false)
	/**
	 * 주어진 경로 하위의 디렉토리/파일 리스트를 배열로 반환
	 *
	 * 주어진 경로 하위의 디렉토리/파일들을 조건에 맞게 탐색을 하여 결과를
	 * 배열로 반환한다.
	 *
	 * 예제:
	 * {@example pear_eFilesystem/test.php 68 4}
	 *
	 * @access public
	 * @return array|false 파일 리스트를 배열로 반환. 경로를 지정하지 않았거나,
	 *                또는 주어진 경로가 존재하지 않으면 false를 반환
	 * @param  string (optional) 탐색할 경로. 기본값은 현재 디렉토리(./)
	 * @param  string (optional) 탐색 조건. 기본값은 모든 파일/디렉토리를
	 *                탐색한다.<br>
	 *                - f (파일만 탐색)
	 *                - d (디렉토리만 탐색)
	 *                - l (링크만 탐색)
	 *                - fd (파일과 디렉토리만 탐색)
	 *                - fl (파일과 링크만 탐색)
	 *                - dl (디렉토리와 링크만 탐색)
	 *                - /regex/ (파일/디렉토리 이름을 정규식으로 탐색)
	 * @param  boolean (optional) 기본값 false. true로 설정하면, 재귀 검색을
	 *                하지않고, 지정된 디렉토리의 리스트만 반환 한다.
	 */
	static public function find ($path = './', $type= '', $norecursive = false) {
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

	// {{{ static public (string) prompt ($prompt, $hidden = false)
	/**
	 * 쉘 라인 프롬프트를 출력하고 입력된 값을 반환한다.
	 *
	 * @access public
	 * @return string
	 * @param  string  stdout으로 출력할 프롬프트 문자열
	 * @param  boolean (optional) input 문자열을 hidden 처리 한다.
	 */
	static public function prompt ($prompt, $hidden = false) {
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

	// {{{ static public (array|stdClass) eFilesystem::parse_ini ($f)
	/**
	 * 설정 파일 또는 설정 문자열을 분석
	 *
	 * @access  public
	 * @return  array|stdClass 성공시에, 분석된 설정 내용을 배열로 반환 한다.
	 *                  실패시에 빈 배열을 반환한다.
	 * @param   string  설정 파일 또는 설정 문자열
	 * @param   bool    (optional) true로 선언시에, member들을 stdClass로
	 *                  반환한다. (1.0.3부터 지원)
	 * @since   버전 1.0.1
	 */
	static public function parse_ini ($f, $obj = false) {
		if ( is_array ($f) || is_object ($f) ) {
			#parent::warning ('Invalid type of argument 1. File or string is valid');
			throw new myException ('Invalid type of argument 1. File or string is valid', E_USER_WARNING);
			return array ();
		}

		if ( $obj )
			return self::parse_ini_obj ($f);

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
					if ( preg_match ('/^(true|false|on|off|null|[01])$/', $_value, $matches) ) {
						switch ($matches[1]) {
							case 'true' :
							case 'on' :
							case '1' :
								$var .= ' = 1;';
								break;
							case 'null' :
								$var .= ' = null;';
								break;
							default :
								$var .= ' = 0;';
						}
					} else
						$var .= ' = \'' . $_value . "';";
				} else {
					$_varname_r = explode ('.', $_varname);
					for ( $i=0; $i<count ($_varname_r); $i++ ) {
						$var_quote = is_numeric ($_varname_r[$i]) ? '' : '\'';
						$var .= '[' . $var_quote . $_varname_r[$i] . $var_quote . ']';
					}

					if ( preg_match ('/^(true|false|on|off|null|[01])$/', $_value, $matches) ) {
						switch ($matches[1]) {
							case 'true' :
							case 'on' :
							case '1' :
								$var .= ' = 1;';
								break;
							case 'null' :
								$var .= ' = null;';
								break;
							default :
								$var .= ' = 0;';
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

	// {{{ private (stdClass) eFilesystem::parse_ini_obj ($f)
	/**
	 * 설정 파일 또는 설정 문자열을 분석
	 *
	 * @access  public
	 * @return  stdClass 성공시에, 분석된 설정 내용을 stdClass로 반환 한다. 실패시에
	 *                   빈 stdClass를 반환한다.
	 * @param   string   설정 파일 또는 설정 문자열
	 * @since   버전 1.0.3
	 */
	private static function parse_ini_obj (&$f) {
		if ( is_array ($f) || is_object ($f) ) {
			#parent::warning ('Invalid type of argument 1. File or string is valid');
			throw new myException ('Invalid type of argument 1. File or string is valid', E_USER_WARNING);
			return array ();
		}

		$contents = file_exists ($f) ? self::file_nr ($f) : preg_split ('/[\r\n]+/', $f);
		if ( $contents === false || ! is_array ($contents) )
			return array ();

		$ret = new stdClass;

		foreach ( $contents as $r ) {
			$r = preg_replace ('/[ \t]*;.*/', '', $r);

			if ( ! $r )
				continue;

			if ( preg_match ('/^\[([^\]]+)\]$/', $r, $matches) ) {
				/* new variable */
				$varname = $matches[1];
				eval ('$ret->' . $varname . ' = new stdClass;');
				#echo '$ret->' . $varname . ' = new stdClass;' . PHP_EOL;
			} else {
				/**
				 * invalid format
				 * must variable = value format
				 */
				if ( ! preg_match ('/^([^=]+)=(.*)$/', $r, $matches) )
					continue;

				$_varname = trim ($matches[1]);
				$_value   = trim ($matches[2]);

				$var = '$ret->' . $varname;
				if ( $_varname == 'value' ) {
					if ( preg_match ('/^(true|false|on|off|null|[01])$/', $_value, $matches) ) {
						switch ($matches[1]) {
							case 'true' :
							case 'on' :
							case '1' :
								$var .= ' = 1;';
								break;
							case 'null' :
								$var .= ' = null;';
								break;
							default :
								$var .= ' = 0;';
						}
					} else
						$var .= ' = \'' . $_value . "';";
				} else {
					$_varname_r = explode ('.', $_varname);
					for ( $i=0; $i<count ($_varname_r); $i++ ) {
						$var_brace_start = is_numeric ($_varname_r[$i]) ? '{' : '';
						$var_brace_end   = is_numeric ($_varname_r[$i]) ? '}' : '';
						$var .= '->' . $var_brace_start . $_varname_r[$i] . $var_brace_end;
					}

					if ( preg_match ('/^(true|false|on|off|null|[01])$/', $_value, $matches) ) {
						switch ($matches[1]) {
							case 'true' :
							case 'on' :
							case '1' :
								$var .= ' = 1;';
								break;
							case 'null' :
								$var .= ' = null;';
								break;
							default :
								$var .= ' = 0;';
						}
					} else
						$var .= ' = \'' . $_value . "';";
				}
				#echo $var . "\n";
				eval ($var);
			}
		}

		return $ret;
	}
	// }}}

	// {{{ static public (string) eFilesystem::make_ini ($array)
	/**
	 * eFilesystem::parse_ini method에 대응되는 설정을 생성한다.
	 *
	 * @access public
	 * @return string 생성된 설정 문자열
	 * @param  array|stdClass  eFilesystem::parse_ini와 동일한 형식을 가진 설정 배열
	 * @since  버전 1.0.2
	 */
	static public function make_ini ($input) {
		if ( ! is_array ($input) && ! is_object ($input) ) {
			#parent::warning ('Invalid type of argument 1. Array is valid');
			throw new myException ('Invalid type of argument 1. Array is valid', E_USER_WARNING);
			return false;
		}

		$buf = '';
		foreach ( $input as $key => $v ) {
			$r = "[{$key}]\n";

			if ( ! is_array ($v) && ! is_object ($v) ) {
				#parent::warning ('Invalid array data format');
				throw new myException ('Invalid array data format', E_USER_WARNING);
				return false;
			}

			self::make_ini_callback ($r, $v);
			$buf .= preg_replace ('/\.([\s]*)=[\s]* /', '\\1= ', $r) . "\n";
			#$buf .= $r . "\n";
		}

		return $buf;
	}
	// }}}

	// {{{ private (void) eFilesystem::make_ini_callback (&$buf, $v)
	private function make_ini_callback (&$buf, $v) {
		if ( ! is_array ($v) && ! is_object ($v) ) {
			if ( ! is_numeric ($v) && ! $v )
				$v = 'null';
			$buf .= sprintf (" = %s\n", $v);
			return;
		}

		foreach ( $v as $key => $val ) {
			if ( ! is_array ($val) && ! is_object ($val) ) {
				$keyname = sprintf ('%s%s.', self::$make_ini_callback_key, $key);
				$buf .= sprintf ('  %-20s', $keyname);
			}

			self::$make_ini_callback_key .= $key . '.';
			self::make_ini_callback ($buf, $val);
			self::$make_ini_callback_key = preg_replace ('/[^.]+\.$/', '', self::$make_ini_callback_key);
		}

	}
	// }}}
}

?>
