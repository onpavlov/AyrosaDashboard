<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "update_info".
 *
 * @property integer $id
 * @property string $last_update
 * @property string $status
 */
class UpdateInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'update_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['last_update'], 'safe'],
            [['status'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'last_update' => 'Last Update',
            'status' => 'Status',
        ];
    }
}
