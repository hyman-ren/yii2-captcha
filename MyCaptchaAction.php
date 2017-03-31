<?php

namespace hyman\captcha;

use Yii;
use yii\captcha\CaptchaAction;
use yii\base\InvalidConfigException;

class MyCaptchaAction extends CaptchaAction
{
    public $disturbCharCount=2;

    protected function renderImageByGD($code)
    {
        $image = imagecreatetruecolor($this->width, $this->height);

        $backColor = imagecolorallocate(
            $image,mt_rand(188,255), mt_rand(188,255), mt_rand(188,255)
        );
        imagefilledrectangle($image, 0, 0, $this->width - 1, $this->height - 1, $backColor);
        imagecolordeallocate($image, $backColor);

        if ($this->transparent) {
            imagecolortransparent($image, $backColor);
        }

        $foreColor = imagecolorallocate(
            $image,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156)
        );

        $length = strlen($code);
        $box = imagettfbbox(30, 0, $this->fontFile, $code);
        $w = $box[4] - $box[0] + $this->offset * ($length - 1);
        $h = $box[1] - $box[5];
        $scale = min(($this->width - $this->padding * 2) / $w, ($this->height - $this->padding * 2) / $h);
        $x = 10;

        imagecolordeallocate($image, $foreColor);

        ob_start();
        //画干扰点
        $this->_createLine($image);

        $this->_writeNoise($image);
        $this->_createFont($code,$image);
        
        imagepng($image);
        imagedestroy($image);

        return ob_get_clean();
    }

    /**
    * 画杂点
    * 往图片上写不同颜色的字母或数字
    */
    private function _writeNoise($image) {
       $codeSet = '012345678abcdefhijklmnpqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
       $len=strlen($codeSet)-1;
       $path = __dir__ . DIRECTORY_SEPARATOR . 'ttfs' . DIRECTORY_SEPARATOR;
       for($i = 0; $i < $this->disturbCharCount; $i++){
           //杂点颜色
           $noiseColor = imagecolorallocate($image, mt_rand(136,225), mt_rand(136,225), mt_rand(136,225));
           for($j = 0; $j < 10; $j++) {
               // 绘杂点
               imagettftext($image, mt_rand(10, 14), mt_rand(-30, 30), mt_rand(-10, $this->width),  mt_rand(-10, $this->height), $noiseColor, $path.'/'.mt_rand(0, 25).'.ttf', $codeSet[mt_rand(0, $len)]);
           }
       }
    }
    /** 
     * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(你可以改成更帅的曲线函数) 
     *      
     *      高中的数学公式咋都忘了涅，写出来
     *      正弦型函数解析式：y=Asin(ωx+φ)+b
     *      各常数值对函数图像的影响：
     *        A：决定峰值（即纵向拉伸压缩的倍数）
     *        b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
     *        φ：决定波形与X轴位置关系或横向移动距离（左加右减）
     *        ω：决定周期（最小正周期T=2π/∣ω∣）
     *
     */
    // 验证码字体随机颜色
    // $this->_color = imagecolorallocate($this->_image, mt_rand(1,150), mt_rand(1,150), mt_rand(1,150));
    private function _writeCurve($image) {
        $px = $py = 0;
        
        // 曲线前部分
        $A = mt_rand(1, $this->height/2);                  // 振幅
        $b = mt_rand(-$this->height/4, $this->height/4);   // Y轴方向偏移量
        $f = mt_rand(-$this->height/4, $this->height/4);   // X轴方向偏移量
        $T = mt_rand($this->height, $this->width*2);  // 周期
        $w = (2* M_PI)/$T;
                        
        $px1 = 0;  // 曲线横坐标起始位置
        $px2 = mt_rand($this->width/2, $this->width * 0.8);  // 曲线横坐标结束位置

        for ($px=$px1; $px<=$px2; $px = $px + 1) {
            if ($w!=0) {
                $py = $A * sin($w*$px + $f)+ $b + $this->height/2;  // y = Asin(ωx+φ) + b
                $i = (int) (15/5);
                while ($i > 0) {    
                    imagesetpixel($image, $px + $i , $py + $i, imagecolorallocate($image, mt_rand(166,255), mt_rand(166,255), mt_rand(166,255)));  // 这里(while)循环画像素点比imagettftext和imagestring用字体大小一次画出（不用这while循环）性能要好很多               
                    $i--;
                }
            }
        }
        
        // 曲线后部分
        $A = mt_rand(1, $this->height/2); // 振幅        
        $f = mt_rand(-$this->height/4, $this->height/4); // X轴方向偏移量
        $T = mt_rand($this->height, $this->width*2);  // 周期
        $w = (2* M_PI)/$T;      
        $b = $py - $A * sin($w*$px + $f) - $this->height/2;
        $px1 = $px2;
        $px2 = $this->width;

        for ($px=$px1; $px<=$px2; $px=$px+ 1) {
            if ($w!=0) {
                $py = $A * sin($w*$px + $f)+ $b + $this->height/2;  // y = Asin(ωx+φ) + b
                $i = (int) (15/5);
                while ($i > 0) {            
                    imagesetpixel($image, $px + $i, $py + $i, imagecolorallocate($image, mt_rand(156,255), mt_rand(156,255), mt_rand(156,255)));    
                    $i--;
                }
            }
        }
    }

     //生成线条、雪花
    private function _createLine($image) {
        //线条
        for ($i=0;$i<8;$i++) {
            $color = imagecolorallocate($image,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
            imageline($image,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
            // $this->_writeCurve($image);
        }
        //雪花
        for ($i=0;$i<100;$i++) {
            $color = imagecolorallocate($image,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
            imagestring($image,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'*',$color);
        }
    }

    private function _createFont($code,$image) {
        $length = strlen($code);
        $_x = ($this->width - $this->padding*($length+1)) / $length;
        $path = Yii::getAlias('@yii/captcha/ttfs');
        $fontSize = (int) ($_x * 1.1);    
        for ($i=0;$i<$length;$i++) {
            $fontcolor = imagecolorallocate($image,mt_rand(0,136),mt_rand(0,136),mt_rand(0,136));
            $fontfile = $path.'/25.ttf';
            $padding = $i==0 ? $this->padding : 0;
            imagettftext($image,$fontSize,mt_rand(-30,30),($_x+$this->padding)*$i+$this->padding,$this->height / 1.4,$fontcolor,$fontfile,$code[$i]);
        }
     }

}
