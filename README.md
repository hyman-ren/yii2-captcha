This is My diy captcha for the Yii framework 2.0
demo
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