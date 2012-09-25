<?php
class LoginForm extends CFormModel
{
    public $email;

    public $password;

    private $_identity;

    public function rules()
    {
        return array(
            array('email, password', 'required'),            
            array('email','email'),
            array('password', 'authenticate')
        );
    }

    public function attributeLabels()
    {
        return array(
            'email'   => Yii::t('user', 'Email'),
            'password'=> Yii::t('user', 'Password'),
        );
    }

    public function authenticate()
    {
        if (!$this->hasErrors())
        {
            $this->_identity = new UserIdentity($this->email, $this->password);

            if (!$this->_identity->authenticate())            
                $this->addError('password', Yii::t('user', 'Email or password is incorrect!'));
            else            
                Yii::app()->user->login($this->_identity);            
        }
    }
}