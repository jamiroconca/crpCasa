<?php
/**
 * crpCasa
 *
 * @copyright (c) 2009 Daniele Conca
 * @link http://code.zikula.org/crpcasa Support and documentation
 * @author Daniele Conca <conca.daniele@gmail.com>
 * @license GNU/LGPL - v.3
 * @package crpCasa
 */

function crpCasa_init()
{
	// Set default pages per page
	pnModSetVar('crpCasa', 'albumsperpage', 20);
	pnModSetVar('crpCasa', 'imagesperpage', 40);
	pnModSetVar('crpCasa', 'imagesize', 800);
	pnModSetVar('crpCasa', 'thumbsize', 144);
	pnModSetVar('crpCasa', 'username', null);

	// Initialisation successful
	return true;
}

function crpCasa_upgrade($oldversion)
{
	switch ($oldversion)
	{
		case "0.1.0" :
			break;
	}
	// Update successful
	return true;
}

function crpCasa_delete()
{
	// Delete any module variables
	pnModDelVar('crpCasa');

	// Deletion successful
	return true;
}
