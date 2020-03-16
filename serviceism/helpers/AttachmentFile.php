<?php
namespace serviceism\helpers;

use Yii;
use yii\web\UploadedFile;
use yii\helpers\BaseFileHelper;
use yii\db\Query;
use yii\web\HttpException;

/**
 * 
 */
class AttachmentFile
{
	public function getFileInfo($stream)
	{
		$file = base64_decode($stream);
		$f = finfo_open();
		$mime_type = finfo_buffer($f, $file, FILEINFO_MIME_TYPE);
        $ext = BaseFileHelper::getExtensionsByMimeType($mime_type);
		
		if(array_key_exists($ext[0], $this->getAttachType())){
			$result = [];
			$result['file_name'] = "fileupload_".date("Ymd")."_".date("His").".".$ext[0];
			$result['file_type'] = $mime_type;

			return $result;
		} else {
            throw new HttpException('406','File extension type not supported.');
            return false;
		}
	}

	 public static function getAttachType(){
        $type = [
            'doc'   => 'doc',
            'docx'  => 'docx',
            'ppt'   => 'ppt',
            'pptx'  => 'pptx',
            'xls'   => 'xls',
            'xlsx'  => 'xlsx',
            'csv'   => 'csv',
            'pdf'   => 'pdf',
            'jpg'   => 'jpg',
            'jpeg'  => 'jpeg',
            'jpe'  => 'jpe',
            'png'   => 'png',
            'zip'   => 'zip'
        ];

        return $type;
    }

    public function checkImageFile($stream){
        $type = [
            "jpg",
            "jpe",
            "jpeg",
            "png",
        ];

        return in_array($stream,$type);
    }


     public function checkVideoFile($stream){
        $type = [
            "mp4",
            "3gp",
            "OGG",
            "WMV",
            "WEBM",
            "FLV",
            "AVI",
            "QuickTime",
            "HDV",
            "MXF",
            "MPEG",
            "WAV",
            "VOB",
        ];

        return in_array($stream,$type);
    }

}