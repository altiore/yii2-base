<?php

namespace altiore\base\models;

use altiore\base\contract\ImageSavableInterface;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%image}}".
 *
 * @property integer $id
 * @property string  $title
 * @property string  $url
 * @property string  $ext
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $created_by
 * @property integer $updated_by
 */
class Image extends ActiveRecord
{
    const LARGE_IMAGE = 1024; // width
    const MIDDLE_SIZE = 512;
    const THUMBNAIL_SIZE = 256;
    const SIZE_PATH_BY_NAME = [
        'large' => '1024x683',
        'middle' => '512x342',
        'small' => '256x171',
        'thumbnail' => '256x171',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%image}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'url', 'ext',], 'required'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'safe'],
            [['title', 'url'], 'string', 'max' => 255],
            [['ext'], 'string', 'max' => 4],
            [['url'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'url' => 'Url',
            'ext' => 'Ext',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
        ];
    }

    /**
     * @param $size string
     * @return string
     */
    public function getAbsoluteUrl($size = '')
    {
        return Yii::getAlias('@uploadPath') . '/' . $this->url . ($size ? $size . '.' : '') . $this->ext;
    }

    /**
     * @return bool|string
     */
    public function getOrigin()
    {
        $file = Yii::getAlias('@uploads/' . $this->url . $this->ext);
        if (file_exists($file)) {
            return $file;
        }

        return false;
    }

    /**
     * @param ImageSavableInterface $object
     * @param UploadedFile          $image
     * @throws \Exception
     * @return string|boolean
     */
    public static function saveImage(ImageSavableInterface $object, UploadedFile $image)
    {
        $dir = $object->getImagePath();
        $fileName = '/image.';
        $url = $object->getImagePath() . $fileName;

        $realPath = Yii::getAlias('@uploads/' . $dir);
        $fullFileName = $realPath . $fileName . $image->extension;

        if (!FileHelper::createDirectory($realPath) || !$image->saveAs($fullFileName)) {
            return false;
        }

        $imageObject = $object->image;
        if ($imageObject === null) {
            $imageObject = new self();
        }

        $imageObject->title = $image->baseName;
        $imageObject->ext = $image->extension;
        $imageObject->url = $url;

        if (!$imageObject->save()) {
            return false;
        }
        if (
            $object->image_id !== $imageObject->id
            && !$object->updateAttributes([
                'image_id' => $imageObject->id,
            ])
        ) {
            return false;
        }

        $imageObject->resize(self::LARGE_IMAGE);
        $imageObject->resize(self::MIDDLE_SIZE);
        $imageObject->resize(self::THUMBNAIL_SIZE);

        return realpath($fullFileName);
    }

    /**
     * @param $newWidth
     * @return float|int
     */
    protected function resize($newWidth)
    {
        $origin = $this->getOrigin();
        list($width, $height) = getimagesize($origin);
        $newHeight = round($height / $width * $newWidth);
        $dest = Yii::getAlias('@uploads/' . $this->url) . $newWidth . 'x' . $newHeight . '.' . $this->ext;

        try {
            if ($width > $newWidth) {
                \yii\imagine\Image::text($origin, $newWidth, $newHeight)
                    ->save($dest);
            } else {
                copy($origin, $dest);
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
