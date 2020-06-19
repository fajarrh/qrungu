<?php

namespace common\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $audio, $name;

    public function rules()
    {
        return [
            [['audio'], 'file', 'skipOnEmpty' => false],
        ];
    }
    
    public function upload()
    {
        if ($this->validate()) {
            $this->name = uniqid('voice_' . date('Y-m-d').'_',false) . '.' . $this->audio->extension;
            $this->audio->saveAs('audio/' . $this->name);
            return true;
        } else {
            return false;
        }
    }

    public function conversation()
    {
        if ($this->validate()) {
            $this->name = uniqid('conversation_' . date('Y-m-d').'_', false) . '.' . $this->audio->extension;
            $this->audio->saveAs('conversation/' . $this->name);
            return true;
        } else {
            return false;
        }
    }
}
?>