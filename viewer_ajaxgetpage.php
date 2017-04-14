<?php

/**
 * Teletext Frame Server
 *
 * This code is designed to be the target of an ajax request.
 *
 * It returns the requested teletext page in json format.
 *
 * Parameters:
 *  	col			collection name (i.e. source fie/folder)
 * 		pn			page number requested. This may be pppnnnn where nn is the
 *					subpage number.
 * 					specifying '*' will return list of pages available.
 *		tt			alternate to pn, looks for 'ttpage'
 * 		sp			if specified with tt, looks for 'subpage'
 *
 *
 * @version 0.01
 * @copyright 2017 Robert O'Donnell.
 *
 */

	include_once('vv.class.php');

	$col = $pn = $tt = $sp = false;


	// validation on here needs sorting. Should not allow . and /
	// in order to avoid directory traversal issues, but need them in
	// order to locate arbiterilly named files on test system.
	if (isset($_GET['col'])
	&& preg_match('/^[a-z0-9 \/.\-\_]+$/i', $_GET['col']) ) {
			$col = $_GET['col'];
	}

	// restrict page number to letters, numbers and periods.
	// (to allow for TG@H pages like 5.500a)
	if (isset($_GET['pn'])
	&& preg_match('/^[a-zA-Z0-9\.\*]+$/i', $_GET['pn']) ) {
		$pn = $_GET['pn'];
	}

	if (isset($_GET['tt'])
	&& preg_match('/^[A-F0-9]+$/i', $_GET['tt']) ) {
		$tt = $_GET['tt'];
	}

	if (isset($_GET['sp'])
	&& preg_match('/^[A-F0-9]+$/i', $_GET['sp']) ) {
		$sp = $_GET['sp'];
	}


	if (!$col || (!$pn && !$tt)) {
		die ('Invalid parameters');
	}


	if (!session_start()) {
		die ('No session');
	}

	// reload previous instance of class.
	if (isset($_SESSION[$col])) {
		$vv = $_SESSION[$col];
	} else {
		// initialise viewer class.
		$vv = new ViewdataViewer();
		$gal = '../galleries/' . $gal;

		$res = false;

		$col = '../galleries/' . $col;
		if (is_file($col)) {
			$res = $vv->LoadFile($col, 0, basename($col));
		} elseif (is_dir($col)) {
			if ($dh = opendir($col)) {
				while (($file = readdir($dh)) !== false) {
					if ($vv->LoadFile($file, 0, basename($file)))
						$res = true;
				}
				closedir($dh);
			}
		}
		if (!$res) {
			die ('Unable to open specified collection');
		}
		if (0 == $vv->framesfound) {
			die ('No frames found in specified collection');
		}
		// No errors? Save to session.
		$_SESSION[$col] = $vv;
	}


	$frames = $vv->frameindex;

	$error = $image = $idx = '';

	// Requested list of pages available.
	if ($pn == '*') {
		$pages = array();
		foreach ($frames as $idx => $value) {
			// is the teletext page number available
			if (isset($value['ttpage'])) {
				// yes.  Does it already exist in table?
				$pg = substr($value['ttpage'],0,3);
				if (isset($pages[$pg])) {
					// yes. Must be a subpage - increment counter
					$pages[$pg]++;
				}else {
					// no - first instance of this page, so set to have one!
					$pages[$pg] = 1;
				}
			} else {
				// not teletext, assume one page per id.
				$pages[$idx] = 1;
			}
		}
//		print_r($pages);
		echo json_encode(array('pages' => $pages));
	} else {


		// indexed by page number (most files..)
		if ($pn !== false && isset($frames[$pn])) {
			$idx = $pn;
		} else {
			// no $pn, must have a $tt
			// page number elsewhere in record.. go hunting..
			foreach ($frames as $key => $value ){
				// if there's a teletext page number that matches, and
				if (isset($value['ttpage']) && $tt == $value['ttpage'] &&
				 // either no subpage requested, none set, or one is and it matches..
				 ($sp === false || !isset($value['subpage']) || $sp == $value['subpage'])) {
					$idx = $key;
					break;
				}
			}
		}

		// return data.
		if ($idx !== '') {
			// this should not return an error, as we know key must exist.
			$image = $vv->ReturnScreen($idx,'internal');

		} else {
			$error = 'Not Found';
		}

		echo json_encode(array_filter(array(
				'page' => $pn . $tt . $sp,
				'index' => $idx,
				'image' => to_hash($image),

				'error'	=> $error
		)));
	}







function to_hash($data, $cset = 0, $blackfg = 0){

	// straight port from https://github.com/rawles/edit-tf/blob/gh-pages/teletext-editor.js
	$b64chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_";

	$encoding = '';

	// Construct the metadata as described above.
	$metadata = $cset;
	if ( $blackfg != 0 ) { $metadata += 8; }
	$encoding .= "$metadata";
	$encoding .= ":";

	// Construct a base-64 array by iterating over each character
	// in the frame.
	$b64 = array();
	for ( $r=0; $r<25; $r++ ) {
		for ( $c=0; $c<40; $c++ ) {
			for ( $b=0; $b<7; $b++ ) {

				// How many bits into the frame information we
				// are.
				$framebit = 7 * (( $r * 40 ) + $c) + $b;

				// Work out the position of the character in the
				// base-64 encoding and the bit in that position.
				$b64bitoffset = $framebit % 6;
				$b64charoffset = (int)(( $framebit - $b64bitoffset ) / 6);

				// Read a bit and write a bit.
				$bitval = ord($data[$r*40+$c]) & ( 1 << ( 6 - $b ));
				if ( $bitval > 0 ) { $bitval = 1; }
				$b64[$b64charoffset] |= $bitval << ( 5 - $b64bitoffset );
			}
		}
	}

	// Encode bit-for-bit.
	for ( $i = 0; $i < 1167; $i++ ) {
		$encoding .= $b64chars[(int)$b64[$i]];
	}
	return $encoding;


}

