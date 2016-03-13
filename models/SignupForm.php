<?php
namespace app\models;

use yii\base\Model;
use Yii;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
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
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required', 'message' => 'Имя пользователя обязательно для заполнения'],
            ['username', 'unique', 'targetClass' => '\app\models\Users', 'message' => 'Имя пользователя занято.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['firstname', 'filter', 'filter' => 'trim'],
            ['firstname', 'string', 'max' => 255],
            ['lastname', 'filter', 'filter' => 'trim'],
            ['lastname', 'string', 'max' => 255],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required', 'message' => 'Email обязательно для заполнения'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\app\models\Users', 'message' => 'email адрес занят.'],

            ['password', 'required', 'message' => 'Укажите пароль'],
            ['password', 'string', 'min' => 6],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new Users();
        $user->username = $this->username;
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
