<?php

/**
 * This is the model class for table "{{User}}".
 *
 * The followings are the available columns in table '{{User}}':
 * @property integer $id
 * @property string $creation_date
 * @property string $change_date
 * @property string $first_name
 * @property string $last_name
 * @property string $nick_name
 * @property string $email
 * @property integer $gender
 * @property string $avatar
 * @property string $password
 * @property string $salt
 * @property integer $status
 * @property integer $access_level
 * @property string $last_visit
 * @property string $registration_date
 * @property string $registration_ip
 * @property string $activation_ip
 */
class User extends CActiveRecord
{
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;
    const GENDER_THING = 0;

    const STATUS_ACTIVE = 1;
    const STATUS_BLOCK = 0;
    const STATUS_NOT_ACTIVE = 2;

    const EMAIL_CONFIRM_YES = 1;
    const EMAIL_CONFIRM_NO = 0;

    const ACCESS_LEVEL_USER = 0;
    const ACCESS_LEVEL_ADMIN = 1;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{user}}';
    }

    public function validatePassword($password)
    {
        if ($this->password === $this->hashPassword($password, $this->salt))
            return true;

        return false;
    }

    public function getAccessLevel()
    {
        $data = $this->getAccessLevelsList();
        return array_key_exists($this->access_level, $data) ? $data[$this->access_level] : Yii::t('user', '*нет*');
    }

    public function getAccessLevelsList()
    {
        return array(
            self::ACCESS_LEVEL_ADMIN => Yii::t('user', 'Admin'),
            self::ACCESS_LEVEL_USER => Yii::t('user', 'User')
        );
    }

    public function getStatusList()
    {
        return array(
            self::STATUS_ACTIVE => Yii::t('user', 'Active'),
            self::STATUS_BLOCK => Yii::t('user', 'Blocked'),
            self::STATUS_NOT_ACTIVE => Yii::t('user', 'Not activated')
        );
    }

    public function getStatus()
    {
        $data = $this->getStatusList();
        return array_key_exists($this->status, $data)
            ? $data[$this->status]
            : Yii::t('user', 'mmm...no such status');
    }

    public function getGendersList()
    {
        return array(
            self::GENDER_FEMALE => Yii::t('user', 'female'),
            self::GENDER_MALE => Yii::t('user', 'male'),
            self::GENDER_THING => Yii::t('user', 'unknown')
        );
    }

    public function getGender()
    {
        $data = $this->getGendersList();
        return array_key_exists($this->gender, $data)
            ? $data[$this->gender]
            : Yii::t('user', 'mmm..no such gender');
    }

    public function getEmailConfirmStatusList()
    {
        return array(
            self::EMAIL_CONFIRM_YES => Yii::t('user', 'Yes'),
            self::EMAIL_CONFIRM_NO => Yii::t('user', 'No'),
        );
    }

    public function getEmailConfirmStatus()
    {
        $data = $this->getEmailConfirmStatusList();

        return isset($data[$this->email_confirm])
            ? $data[$this->email_confirm]
            : Yii::t('user', '*unknown*');
    }
    public function getEmailExistsStatus($email) // А есть ли такой email ? True - есть
    {
         if (User::model()->find('email=:email',array(':email'=>$email))==null)
             return false;
         else
             return true;


     }
    /**
     * Returns the static model of the specified AR class.
     * @return User the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $module = Yii::app()->getModule('user');

        return array(
            //@formatter:off
            array('birth_date, site, about, location, online_status, nick_name, first_name, last_name, email', 'filter', 'filter' => 'trim'),
            array('birth_date, site, about, location, online_status, nick_name, first_name, last_name, email', 'filter', 'filter' => array($obj = new CHtmlPurifier(), 'purify')),
            array('nick_name, email, password', 'required'),
            array('nick_name', 'match', 'pattern' => '/^[A-Za-z0-9]{2,50}$/', 'message' => Yii::t('user', 'Invalid format "{attribute}" only letters and numbers allowed, from 2 to 20 symbols')),
            array('first_name, last_name, nick_name, email', 'length', 'max' => 50),
            array('password, salt, activate_key', 'length', 'max' => 32),
            array('site', 'length', 'max' => 100),
            array('site', 'url', 'allowEmpty' => true),
            array('about', 'length', 'max' => 300),
            array('location, online_status', 'length', 'max' => 150),
            array('registration_ip, activation_ip, registration_date', 'length', 'max' => 20),
            array('gender, status, access_level, use_gravatar, email_confirm', 'numerical', 'integerOnly' => true),
            array('email', 'email'),
            array('email', 'unique', 'message' => Yii::t('user', 'This email is used by another user')),
            array('nick_name', 'unique', 'message' => Yii::t('user', 'This nickname is used by another user')),
            array('avatar', 'file', 'types' => implode(',', $module->avatarExtensions), 'maxSize' => $module->avatarMaxSize, 'allowEmpty' => true),
            array('email_confirm', 'in', 'range' => array_keys($this->getEmailConfirmStatusList())),
            array('use_gravatar', 'in', 'range' => array(0, 1)),
            array('gender', 'in', 'range' => array_keys($this->getGendersList())),
            array('status', 'in', 'range' => array_keys($this->getStatusList())),
            array('access_level', 'in', 'range' => array_keys($this->getAccessLevelsList())),
            array('id, creation_date, change_date, first_name, last_name, nick_name, email, gender, avatar, password, salt, status, access_level, last_visit, registration_date, registration_ip, activation_ip', 'safe', 'on' => 'search'),
            //@formatter:on
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => Yii::t('user', 'Id'),
            'creation_date' => Yii::t('user', 'Activation date'),
            'change_date' => Yii::t('user', 'Modify date'),
            'first_name' => Yii::t('user', 'Name'),
            'last_name' => Yii::t('user', 'Last name'),
            'nick_name' => Yii::t('user', 'Nickname'),
            'email' => Yii::t('user', 'Email'),
            'gender' => Yii::t('user', 'Sex'),
            'password' => Yii::t('user', 'Password'),
            'salt' => Yii::t('user', 'salt'),
            'status' => Yii::t('user', 'Status'),
            'access_level' => Yii::t('user', 'Access'),
            'last_visit' => Yii::t('user', 'Las visit'),
            'registration_date' => Yii::t('user', 'Registration date'),
            'registration_ip' => Yii::t('user', 'Ip reg'),
            'activation_ip' => Yii::t('user', 'Ip activation'),
            'activate_key' => Yii::t('user', 'Activation code'),
            'avatar' => Yii::t('user', 'Avatar'),
            'use_gravatar' => Yii::t('user', 'Gravatar'),
            'email_confirm' => Yii::t('user', 'Email confirmed'),
            'birth_date' => Yii::t('user', 'Birth date'),
            'site' => Yii::t('user', 'Webisite'),
            'location' => Yii::t('user', 'Location'),
            'about' => Yii::t('user', 'About me'),
        );
    }

    public function search()
    {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('creation_date', $this->creation_date, true);
        $criteria->compare('change_date', $this->change_date, true);
        $criteria->compare('first_name', $this->first_name, true);
        $criteria->compare('last_name', $this->last_name, true);
        $criteria->compare('nick_name', $this->nick_name, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('gender', $this->gender);
        $criteria->compare('password', $this->password, true);
        $criteria->compare('salt', $this->salt, true);
        $criteria->compare('status', $this->status);
        $criteria->compare('access_level', $this->access_level);
        $criteria->compare('last_visit', $this->last_visit, true);
        $criteria->compare('registration_date', $this->registration_date, true);
        $criteria->compare('registration_ip', $this->registration_ip, true);
        $criteria->compare('activation_ip', $this->activation_ip, true);

        return new CActiveDataProvider('User', array('criteria' => $criteria));
    }

    public function beforeSave()
    {
        $this->change_date = new CDbExpression('NOW()');

        if ($this->isNewRecord)
        {
            $this->creation_date = $this->change_date;
            $this->activate_key = $this->generateActivationKey();
            $this->registration_ip = Yii::app()->request->userHostAddress;
        }

        if ($this->birth_date === '')
            unset($this->birth_date);

        return parent::beforeSave();
    }

    public function scopes()
    {
        return array(
            'active' => array(
                'condition' => 'status = :status',
                'params' => array(':status' => self::STATUS_ACTIVE)
            ),
            'blocked' => array(
                'condition' => 'status = :status',
                'params' => array(':status' => self::STATUS_BLOCK)
            ),
            'notActivated' => array(
                'condition' => 'status = :status',
                'params' => array(':status' => self::STATUS_NOT_ACTIVE)
            ),
            'admin' => array(
                'condition' => 'access_level = :access_level',
                'params' => array(':access_level' => self::ACCESS_LEVEL_ADMIN)
            ),
            'user' => array(
                'condition' => 'access_level = :access_level',
                'params' => array(':access_level' => self::ACCESS_LEVEL_USER)
            ),
        );
    }

    public function hashPassword($password, $salt)
    {
        return md5($salt . $password);
    }

    public function generateSalt()
    {
        return md5(uniqid('', true));
    }

    public function generateRandomPassword($length = null)
    {
        if (!$length)
            $length = Yii::app()->getModule('user')->minPasswordLength;

        return substr(md5(uniqid(mt_rand(), true) . time()), 0, $length);
    }

    public function generateActivationKey()
    {
        return md5(time() . $this->email . uniqid());
    }

    /**
     * Получить аватарку пользователя в виде тега IMG.
     * @param array $htmlOptions HTML-опции для создаваемого тега
     * @param int $size требуемый размер аватарки в пикселях
     * @return string код аватарки
     */
    public function getAvatar($size = 64, $htmlOptions = array())
    {
        $size = intval($size);
        $size || ($size = 64);

        if (!is_array($htmlOptions))
            throw new CException(Yii::t('user', "htmlOptions must be array or not specified!"));

        isset($htmlOptions['width']) || ($htmlOptions['width'] = $size);
        isset($htmlOptions['height']) || ($htmlOptions['height'] = $size);

        // если это граватар
        if ($this->use_gravatar && $this->email)
            return CHtml::image('http://gravatar.com/avatar/' . md5($this->email) . "?d=mm&s=" . $size, $this->nick_name, $htmlOptions);
        elseif ($this->avatar)
        {
            $avatarsDir = Yii::app()->getModule('user')->avatarsDir;
            $basePath = Yii::app()->basePath . "/../" . $avatarsDir;
            $sizedFile = str_replace(".", "_" . $size . ".", $this->avatar);

            // Посмотрим, есть ли у нас уже нужный размер? Если есть - используем его
            if (file_exists($basePath . "/" . $sizedFile))
                return CHtml::image(Yii::app()->baseUrl . $avatarsDir . "/" . $sizedFile, $this->nick_name, $htmlOptions);

            if (file_exists($basePath . "/" . $this->avatar))
            {
                // Есть! Можем сделать нужный размер
                $image = Yii::app()->image->load($basePath . "/" . $this->avatar);
                if ($image->ext != 'gif' || $image->config['driver'] == "ImageMagick")
                    $image->resize($size, $size, CImage::AUTO)->crop($size, $size)->quality(75)->sharpen(20)->save($basePath . "/" . $sizedFile);
                else
                    @copy($basePath . "/" . $this->avatar, $basePath . "/" . $sizedFile);

                return CHtml::image(Yii::app()->baseUrl . $avatarsDir . "/" . $sizedFile, $this->nick_name, $htmlOptions);
            }
        }

        // Нету аватарки, печалька :(
        return Yii::app()->getModule('user')->defaultAvatar;
    }

    public function getFullName($separator = ' ')
    {
        return $this->first_name || $this->last_name
            ? $this->last_name . $separator . $this->first_name
            : $this->nick_name;
    }

    public function createAccount($nick_name, $email, $password = null, $salt = null, $status = self::STATUS_NOT_ACTIVE, $emailConfirm = self::EMAIL_CONFIRM_NO, $first_name = '', $last_name = '',$birth_date='',$gender='',$location='')
    {
        $salt = is_null($salt) ? $this->generateSalt() : $salt;

        $password = is_null($password) ? $this->generateRandomPassword() : $password;

        $this->setAttributes(array(
            'nick_name' => $nick_name,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'salt' => $salt,
            'password' => $this->hashPassword($password, $salt),
            'registration_date' => new CDbExpression('NOW()'),
            'registration_ip' => Yii::app()->request->userHostAddress,
            'activation_ip' => Yii::app()->request->userHostAddress,
            'status' => $status,
            'email_confirm' => $emailConfirm,
            'birth_date' =>$birth_date,
            'gender'=>$gender,
            'location'=>$location
        ));
        // если не определен емэйл то генерим уникальный
        $setemail = empty($email);
        $this->email = $setemail ? 'user-' . $this->generateSalt() . '@' . $_SERVER['HTTP_HOST'] : $email;

        $this->save(false);

        // для красоты
        if ($setemail)
        {
            $this->email = "user-{$this->id}@{$_SERVER['HTTP_HOST']}";
            $this->update(array('email'));
        }
    }

    public function changePassword($password)
    {
        $this->password = $this->hashPassword($password, $this->salt);

        return $this->update(array('password'));
    }

    public function activate()
    {
        $this->activation_ip = Yii::app()->request->userHostAddress;
        $this->status = self::STATUS_ACTIVE;
        $this->email_confirm = self::EMAIL_CONFIRM_YES;

        return $this->save();
    }

    public function beforeValidate()
    {
        if ($this->site = '')
            $this->site = null;

        return parent::beforeValidate();
    }

}
