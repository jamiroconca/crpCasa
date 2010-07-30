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

Loader :: includeOnce('modules/crpCasa/pnclass/crpCasaUI.php');
Loader :: includeOnce('modules/crpCasa/pnincludes/Picasa.php');

/**
 * crpCasa Object
 */
class crpCasa
{

	function crpCasa()
	{
		$this->ui = new crpCasaUI();
		$this->modvars = pnModGetVar('crpCasa');
	}

	/**
	 * Modify module's configuration
	 */
	function modifyConfig()
	{
		// get all module vars
		$modvars = $this->modvars;

		return $this->ui->modifyConfig($modvars);
	}

	/**
	 * Update module's configuration
	 */
	function updateConfig()
	{
		// Confirm authorisation code
		if(!SecurityUtil :: confirmAuthKey())
			return LogUtil :: registerAuthidError(pnModURL('crpCasa', 'admin', 'main'));

		// Update module variables
		$albumsperpage = (int) FormUtil :: getPassedValue('albumsperpage', 20, 'POST');
		$imagesperpage = (int) FormUtil :: getPassedValue('imagesperpage', 40, 'POST');
		$imagesize = (int) FormUtil :: getPassedValue('imagesize', 800, 'POST');
		$thumbsize = (int) FormUtil :: getPassedValue('thumbsize', 144, 'POST');
		$popimagesize = (int) FormUtil :: getPassedValue('popimagesize', 1024, 'POST');
		$username = (string) FormUtil :: getPassedValue('username', null, 'POST');
		$gallerytitle = (string) FormUtil :: getPassedValue('gallerytitle', 'crpCasa', 'POST');

		if($albumsperpage < 1)
			$albumsperpage = 20;
		if($imagesperpage < 1)
			$imagesperpage = 40;
		if($imagesize < 1)
			$imagesize = 800;
		if($thumbsize < 1)
			$thumbsize = 144;
		if($popimagesize < 1)
			$popimagesize = 1024;

		pnModSetVar('crpCasa', 'albumsperpage', $albumsperpage);
		pnModSetVar('crpCasa', 'imagesperpage', $imagesperpage);
		pnModSetVar('crpCasa', 'imagesize', $imagesize);
		pnModSetVar('crpCasa', 'thumbsize', $thumbsize);
		pnModSetVar('crpCasa', 'popimagesize', $popimagesize);
		pnModSetVar('crpCasa', 'username', $username);
		pnModSetVar('crpCasa', 'gallerytitle', $gallerytitle);

		// Let any other modules know that the modules configuration has been updated
		pnModCallHooks('module', 'updateconfig', 'crpCasa', array('module' => 'crpCasa'));

		// the module configuration has been updated successfuly
		LogUtil :: registerStatus(_CONFIGUPDATED);

		return pnRedirect(pnModURL('crpCasa', 'admin', 'main'));
	}

	/**
	 * Collect navigation input value
	 *
	 * @param int $startnum pager offset
	 *
	 * @return array input values
	 */
	function collectNavigationFromInput()
	{
		// Get parameters from whatever input we need.
		$startnum = (int) FormUtil :: getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : 1, 'GET');
		$id_album = FormUtil :: getPassedValue('id_album', isset($args['id_album']) ? $args['id_album'] : null, 'GET');
		$id_image = FormUtil :: getPassedValue('id_image', isset($args['id_image']) ? $args['id_image'] : null, 'GET');

		// defaults and input validation
		if(!is_numeric($startnum) || $startnum < 1)
			$startnum = 1;

		$data = compact('startnum','id_album','id_image');

		return $data;
	}

	function viewAlbums()
	{
		$picasa = new Picasa();
		$navigationValues = $this->collectNavigationFromInput();

		// get all module vars
		$modvars = $this->modvars;

		$accountAlbums = $picasa->getAlbumsByUsername($modvars['username'], $modvars['albumsperpage'], $navigationValues['startnum'], "public", $modvars['thumbsize'], $modvars['imagesize']);
		$albums = $accountAlbums->getAlbums();
		foreach ($albums as $kalbum => $valbum)
		{
			$expAlbums[] = $this->albumExplain($valbum);
		}
		$albumsCount = $this->countAlbums($modvars);

		return $this->ui->albumsList($expAlbums, $navigationValues['startnum'], $modvars, $albumsCount);
	}

	function viewAlbum()
	{
		$picasa = new Picasa();
		$navigationValues = $this->collectNavigationFromInput();

		// get all module vars
		$modvars = $this->modvars;

		$valbum = $picasa->getAlbumById($modvars['username'], $navigationValues['id_album'], $modvars['imagesperpage'], $navigationValues['startnum'], null, null, $modvars['thumbsize'], $modvars['popimagesize']);

		$album = $this->albumExplain($valbum);

		$albumImages = $valbum->getImages();

		foreach ($albumImages as $kimage => $vimage)
		{
			$expImages[] = $this->imageExplain($vimage, true);
		}

		$imagesCount = $album['numphotos'];

		return $this->ui->imagesList($album, $expImages, $navigationValues['startnum'], $modvars, $imagesCount);
	}

	function viewImage()
	{
		$picasa = new Picasa();
		$navigationValues = $this->collectNavigationFromInput();

		// get all module vars
		$modvars = $this->modvars;

		$valbum = $picasa->getAlbumById($modvars['username'], $navigationValues['id_album'], $modvars['imagesperpage'], $navigationValues['startnum'], null, null, $modvars['thumbsize'], $modvars['imagesize']);
		$album = $this->albumExplain($valbum);

		$vimage = $picasa->getImageById($modvars['username'], $navigationValues['id_album'], $navigationValues['id_image'], $modvars['thumbsize'], $modvars['imagesize']);
		$maxvimage = $picasa->getImageById($modvars['username'], $navigationValues['id_album'], $navigationValues['id_image'], $modvars['thumbsize'], $modvars['popimagesize']);

		$image = $this->imageExplain($vimage, true);
		$maximage = $this->imageExplain($maxvimage, true);

		foreach ($image['comments'] as $kcomment => $vcomment)
		{
			$singleComment = $this->commentExplain($vcomment);
			$singleComment['author'] = $this->authorExplain($singleComment['author']);
			$expComments[] = $singleComment;
		}

		$next = ($vimage->getNext())?$vimage->getNext()->getIdnum():'';
		$previous = ($vimage->getPrevious())?$vimage->getPrevious()->getIdnum():'';

		return $this->ui->imageDisplay($album, $image, $maximage, $expComments, $next, $previous, $modvars);
	}

	function countAlbums()
	{
		$picasa = new Picasa();
		// get all module vars
		$modvars = $this->modvars;
		$accountAlbums = $picasa->getAlbumsByUsername($modvars['username']);

		return count($accountAlbums->getAlbums());
	}

	function albumExplain($valbum)
	{
		$album= array();
		$album['title'] = $valbum->getTitle();
		$album['subtitle'] = $valbum->getSubTitle();
		$album['icon'] = $valbum->getIcon();
		$album['numphotos'] = $valbum->getNumphotos();
		$album['author'] = $valbum->getPicasaAuthor()->getName();
		$album['id'] = $valbum->getIdnum();
		$album['date'] = $valbum->getPublished();

		return $album;
	}

	function imageExplain($vimage, $content=false)
	{
		$image= array();
		$image['title'] = $vimage->getTitle();
		$image['thumb'] = $vimage->getSmallThumb();
		$image['width'] = $vimage->getWidth();
		$image['height'] = $vimage->getHeight();
		$image['commentcount'] = $vimage->getCommentCount();
		$image['id'] = $vimage->getIdnum();
		$image['content'] = ($content)?$vimage->getContent():'';
		$image['comments'] = $vimage->getComments();

		return $image;
	}

	function commentExplain($vcomment)
	{
		$image= array();
		$comment['title'] = $vcomment->getTitle();
		$comment['published'] = $vcomment->getPublished();
		$comment['author'] = $vcomment->getAuthor();
		$comment['photoid'] = $vcomment->getPhotoid();
		$comment['id'] = $vcomment->getIdnum();
		$comment['content'] = $vcomment->getContent();

		return $comment;
	}

	function authorExplain($vauthor)
	{
		$image= array();
		$comment['nickname'] = $vauthor->getNickname();
		$comment['thumbnail'] = $vauthor->getThumbnail();

		return $comment;
	}

}