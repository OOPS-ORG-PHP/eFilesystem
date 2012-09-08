<?php
/**
 * Extended File system API
 *
 * eFilesyste class
 *
 * @category	System
 * @package		eFilesystem
 * @author		JoungKyun.Kim <http://oops.org>
 * @copyright	(c) 2009 OOPS.ORG
 * @license		BSD
 * @version		$Id$
 * @link		http://pear.oops.org/package/ePrint
 * @since		File available since relase 0.0.1
 */

require_once 'eFilesystem.php';

function answer ($v) {
	echo ( $v === true ) ? 'OK' : 'FALSE';
	echo "\n";
}

echo "##### SELF test #############################################\n";

echo '1. check file_nr ... ';
$r = eFilesystem::file_nr ('eFilesystem.php');
answer (is_array ($r) ? true : false);
/*
if ( is_array ($r) ) {
	ePrint::echoi ($r, 4);
	echo "\n";
}
 */
unset ($r);

echo '2. check mkdir_p ... ';
#echo ePrint::whiteSpace (4, true) . "indent 4\n";
$r = eFilesystem::mkdir_p ('./aaa/bbb');
answer ($r);
if ( $r === true ) {
	chdir ('./aaa/bbb');
	touch ('11.txt');
	touch ('1.txt');
	touch ('2.txt');
	chdir ('../../');

	echo "   check tree ... \n";
	ob_start ();
	eFilesystem::tree ('./aaa');
	$capture = ob_get_contents ();
	ob_end_clean ();
	ePrint::echoi ($capture, 4);
}

echo '3. check unlink_r ... ';
$r = eFilesystem::unlink_r ('./aaa');
answer ($r);

echo "4. check dirlist ...\n";
$r = eFilesystem::dirlist ('./');
$r = array_merge ($r, eFilesystem::dirlist ('./', eFilesystem::RELATIVE));
$r = array_merge ($r, eFilesystem::dirlist ('./', eFilesystem::ABSOLUTE));
ePrint::echoi ($r, 4);
echo "\n";

echo "5. check find ... \n";
$r = eFilesystem::find ('./');
ePrint::echoi ($r, 4);
echo "\n\n";

echo "##### OBJ  test #############################################\n";

echo '1. check file_nr ... ';
$fs = new eFilesystem;

$r = $fs->file_nr ('eFilesystem.php');
answer (is_array ($r) ? true : false);
/*
if ( is_array ($r) ) {
	ePrint::echoi ($r, 4);
	echo "\n";
}
 */
unset ($r);

echo '2. check mkdir_p ... ';
#echo ePrint::whiteSpace (4, true) . "indent 4\n";
$r = $fs->mkdir_p ('./aaa/bbb');
answer ($r);
if ( $r === true ) {
	chdir ('./aaa/bbb');
	touch ('11.txt');
	touch ('1.txt');
	touch ('2.txt');
	chdir ('../../');

	echo "   check tree ... \n";
	ob_start ();
	$fs->tree ('./aaa');
	$capture = ob_get_contents ();
	ob_end_clean ();
	ePrint::echoi ($capture, 4);
}

echo '3. check unlink_r ... ';
$r = $fs->unlink_r ('./aaa');
answer ($r);

echo "4. check dirlist ...\n";
$r = $fs->dirlist ('./');
$r = array_merge ($r, $fs->dirlist ('./', eFilesystem::RELATIVE));
$r = array_merge ($r, $fs->dirlist ('./', eFilesystem::ABSOLUTE));
ePrint::echoi ($r, 4);
echo "\n";

echo "5. check find ... \n";
$r = $fs->find ('./');
ePrint::echoi ($r, 4);
echo "\n";
?>
