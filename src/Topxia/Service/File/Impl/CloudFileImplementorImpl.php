<?php
namespace Topxia\Service\File\Impl;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\File\FileImplementor;
use Topxia\Service\Util\CloudClientFactory;

class CloudFileImplementorImpl extends BaseService implements FileImplementor
{   

    private  $cloudClient;

	public function getFile($file)
	{
       $file['metas'] = $this->decodeMetas($file['metas']);
       $file['metas2'] = $this->decodeMetas($file['metas2']);
	   // $file['path'] = $this->getCloudClient()->getFileUrl($file['hashId'],$file['targetId'],$file['targetType']);
       return $file;
	}

    public function addFile($targetType, $targetId, array $fileInfo=array(), UploadedFile $originalFile=null)
    {
        if (!ArrayToolkit::requireds($fileInfo, array('filename','key', 'size'))) {
            throw $this->createServiceException('参数缺失，添加用户文件失败!');
        }

        $uploadFile = array();
        $uploadFile['targetId'] = $targetId;
        $uploadFile['targetType'] = $targetType; 
        $uploadFile['hashId'] = $fileInfo['key'];
        $uploadFile['filename'] = $fileInfo['filename'];
        $uploadFile['ext'] = pathinfo($uploadFile['filename'], PATHINFO_EXTENSION);
        $uploadFile['size'] = (int) $fileInfo['size'];
        $uploadFile['etag'] = empty($fileInfo['etag']) ? '' : $fileInfo['etag'];

        $uploadFile['metas'] = $this->encodeMetas(empty($fileInfo['metas']) ? array() : $fileInfo['metas']);    
        $uploadFile['metas2'] = $this->encodeMetas(empty($fileInfo['metas2']) ? array() : $fileInfo['metas2']);    

        if (empty($fileInfo['convertId']) or empty($fileInfo['convertKey'])) {
            $uploadFile['convertHash'] = "ch-{$uploadFile['hashId']}";
            $uploadFile['convertStatus'] = 'none';
        } else {
            $uploadFile['convertHash'] = "{$fileInfo['convertId']}:{$fileInfo['convertKey']}";
            $uploadFile['convertStatus'] = 'waiting';
        }

        $uploadFile['type'] = FileToolkit::getFileTypeByMimeType($fileInfo['mimeType']);
        $uploadFile['canDownload'] = empty($uploadFile['canDownload']) ? 0 : 1;
        $uploadFile['storage'] = 'cloud';
        $uploadFile['createdUserId'] = $this->getCurrentUser()->id;
        $uploadFile['updatedUserId'] = $uploadFile['createdUserId'];
        $uploadFile['updatedTime'] = $uploadFile['createdTime'] = time();

        return $uploadFile; 
    }

    public function convertFile($file, $status, $result=null, $callback = null)
    {
        if ($status != 'success') {
            $file['convertStatus'] = $status;
        } else {
            $cmds = $this->getCloudClient()->getVideoConvertCommands();

            $file['metas2'] = array();
            foreach ($result as $item) {
                $type = empty($cmds[$item['cmd']]) ? null : $cmds[$item['cmd']];
                if (empty($type)) {
                    continue;
                }

                if ($item['code'] != 0) {
                    continue;
                }

                if (empty($item['key'])) {
                    continue;
                }

                $file['metas2'][$type] = array('type' => $type, 'cmd' => $item['cmd'], 'key' => $item['key']);
            }

            if (empty($file['metas2'])) {
                $file['convertStatus'] = 'error';
            } else {
                $file['convertStatus'] = 'success';
            }
        }

        $file['metas2'] = $this->encodeMetas(empty($file['metas2']) ? array() : $file['metas2']);

        return $file;
    }

    public function deleteFile($file, $deleteSubFile = true)
    {
        $keys = array($file['hashId']);
        $keyPrefixs = array();

        if ($deleteSubFile) {
            foreach (array('sd', 'hd', 'shd') as $key) {
                if (empty($file['metas2'][$key]) or empty($file['metas2'][$key]['key'])) {
                    continue ;
                }
                $keyPrefixs[] = $file['metas2'][$key]['key'];
            }
        }

        $this->getCloudClient()->deleteFiles($keys, $keyPrefixs);
    }

    private function getFileFullName($file)
    {
        $diskDirectory= $this->getFilePath($file['targetType'],$file['targetId']);
        $filename .= "{$file['hashId']}.{$file['ext']}";
        return $diskDirectory.$filename; 
    }

    private function getFilePath($targetType,$targetId)
    {
        $diskDirectory = $this->getKernel()->getParameter('topxia.disk.local_directory');
        $subDir = DIRECTORY_SEPARATOR.$file['targetType'].DIRECTORY_SEPARATOR;
        $subDir .= "{$file['targetType']}-{$file['targetId']}".DIRECTORY_SEPARATOR;
        return $diskDirectory.$subDir;    	
    }

    private function encodeMetas($metas)
    {
        if(empty($metas) or !is_array($metas)) {
            $metas = array();
        }
        return json_encode($metas);
    }

    private function decodeMetas($metas)
    {
        if (empty($metas)) {
            return array();
        }
        return json_decode($metas, true);
    }

    private function getCloudClient()
    {
        if(empty($this->cloudClient)) {
            $factory = new CloudClientFactory();
            $this->cloudClient = $factory->createClient();
        }
        return $this->cloudClient;
    }

}
