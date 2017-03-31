<h4>This is My diy captcha for the Yii framework 2.0 我自己定制的Yii2.0验证码</h4>
<h4>install 安装</h4>
<p>composer require hyman-ren/yii2-captcha</p>
<h4>demo</h4>
<pre>
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'hyman\captcha\MyCaptchaAction',
                'fixedVerifyCode' => null,
                'height' => 40,
                'width' => 100,
                'padding' => 5,
                'minLength' => 4,
                'maxLength' => 4,
	            'transparent' => false,
            ],
        ];
    }
</pre>