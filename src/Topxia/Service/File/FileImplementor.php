<?php
namespace Topxia\Service\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;


interface FileImplementor
{   
	public function getFile($file);

    public function addFile($targetType, $targetId, array $fileInfo=array(), UploadedFile $originalFile=null);

    public function convertFile($file, $status, $result=null, $callback = null);

    public function deleteFile($file, $deleteSubFile = true);

}