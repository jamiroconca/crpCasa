<?php
/**
 * crpCasa
 *
 * @copyright (c) 2009-2010 Daniele Conca
 * @link http://code.zikula.org/crpcasa Support and documentation
 * @author Daniele Conca <conca.daniele@gmail.com>
 * @license GNU/LGPL - v.3
 * @package crpCasa
 */


Loader :: includeOnce('modules/crpCasa/pnclass/crpCasa.php');

/**
 * return gallery albums
 *
 * @return array
 */
function crpCasa_userapi_list_albums($args=array())
{
	// Security check
	if (!SecurityUtil :: checkPermission('crpCasa::', '::', ACCESS_READ))
	{
		return LogUtil :: registerPermissionError();
	}

	$picasa = new Picasa();
	$crpcasa = new crpCasa();
	$expAlbums = array();

	// get all module vars
	$modvars = $crpcasa->modvars;

	$accountAlbums = $picasa->getAlbumsByUsername($modvars['username'], $args['albumsperpage'], $args['startnum'], "public", $modvars['thumbsize'], $modvars['imagesize']);
	$albums = $accountAlbums->getAlbums();
	foreach ($albums as $kalbum => $valbum)
	{
		$expAlbums[] = $crpcasa->albumExplain($valbum);
	}

	return $expAlbums;
}