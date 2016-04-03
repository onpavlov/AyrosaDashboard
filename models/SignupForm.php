<?php
namespace app\models;

use yii\base\Model;
use Yii;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $email;
    public $password;
    public $firstname;
    public $lastname;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['firstname', 'filter', 'filter' => 'trim'],
            ['firstname', 'string', 'max' => 255],
            ['lastname', 'filter', 'filter' => 'trim'],
            ['lastname', 'string', 'max' => 255],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required', 'message' => 'Email обязательно для заполнения'],
            ['email', 'email', 'message' => 'Некорректный email адрес'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\app\models\Users', 'message' => 'email адрес занят.'],
            ['email', 'exist', 'targetAttribute' => 'bc_email', 'targetClass' => '\app\models\BcUsers', 'message' => 'Email не найден в базе Basecamp'],

            ['password', 'required', 'message' => 'Укажите пароль'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Signs user up.
     *
     * @return Users|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new Users();
        $user->email = $this->email;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->firstname = $this->firstname;
        $user->lastname = $this->lastname;

        $user->save();

        // Set role "user" for new user
        $auth = Yii::$app->authManager;
        $role = $auth->getRole("user");
        $auth->assign($role, $user->getId());
        
        return $user;
    }
}
