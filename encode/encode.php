<?php
include_once("../common.data.php");
include_once("common.func.php");
 error_reporting(0);
class encode
{
    private $data; //需解码的 码字数组，包括左行标识符，数组码字，右行标识符；(二维数组)
    private $datacode; //需解码的 码字数组，删除左行标识符和右行标识符（一维数组）；
    private $row, $cow; //码字的行数，和列数；
    private $pic ; //上传图片存储路径；
    function __construct()
    {
        if ($this->updatepic()) {
            $this->getdatafrompic();
            $this->encodecode();
        }
    }
    function getdatafrombs() //图片的01序列转换为码字序列；
    {
        $str = file_get_contents('bs1.txt');
        $mode = "/11111111010101000(.*)111111101000101001/";
        preg_match_all($mode, $str, $match);
        array_walk($match[1], "trimrn"); //删除字符两端空白；
        foreach ($match[1] as &$value) {
            $arr[] = explode(" ", $value);
        }
        $this->row = count($arr);
        $this->cow = count($arr[0]) - 2;
 
        foreach ($arr as $n => &$row) {
            foreach ($row as &$value) {
                $value = '0x'.dechex(bindec($value));  //将条空序列转换成16进制
                $cu = $n % 3 *3;
                $bsarr = getbs($cu);
                $value = array_search($value, $bsarr);  //获取码字；
            }
        }
        print_r($arr);
        $this->data = $arr;
    }
    public function updatepic()
    {
        if (!$_FILES['userfile']) {
            return 0;
        }
        $uploaddir = dirname(__FILE__).'/pic/';
        $uploadfile = $uploaddir . date("Ymdhis").basename($_FILES['userfile']['name']);
        if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
            return $this->pic = $uploadfile;
        } else {
                echo "上传图片出错！";
                return 0;
        }

    }
    public function getdatafrompic() //直接从图片获取信息序列；
    {
        $type = strrchr($this->pic, '.');
        switch ($type) {
            case ".png":
                $res = imagecreatefrompng($this->pic);
                break;
            case ".jpeg":
                $res = imagecreatefromjpeg($this->pic);
                break;
            case ".jpg":
                $res = imagecreatefromjpeg($this->pic);
                break;
            case ".gif":
                $res = imagecreatefromgif($this->pic);
                break;
            default:
                echo "图片格式不支持！";
                exit();
        }
        $size = getimagesize($this->pic);
    //print_r($size);
        for ($i = 0; $i< $size[1]; $i++) {
            for ($j = 0; $j < $size[0]; $j ++) {
                $rgb = imagecolorat($res, $j, $i);
                $rgbarray = imagecolorsforindex($res, $rgb);
                if ($this->isblock($rgbarray)) {
                    $end = 1;
                    break;
                }
            }
            if ($end) {
                break;
            }
        }
        $startx = $j;
        $starty = $i;   //计算图像开始位置；

        $end = 0 ;
        for ($i = $size[1] - 1; $i > 0; $i --) {
            for ($j = $size[0] - 1; $j > 0; $j --) {
                $rgb = imagecolorat($res, $j, $i);
                $rgbarray = imagecolorsforindex($res, $rgb);
                if ($this->isblock($rgbarray)) {
                    $end = 1;
                    break;
                }
            }
            if ($end) {
                break;
            }
        }
        $endx = $j;
        $endy = $i; //计算图像结束位置；
    
    //计算每个模块占得宽度；
        for ($j = $startx; j < $size[0]; $j ++) {
            $rgb = imagecolorat($res, $j, $startx);
                $rgbarray = imagecolorsforindex($res, $rgb);
            if (!$this->isblock($rgbarray)) {
                break;
            }
        }
        $width = $j - $startx % 8 == 0 ? ($j - $startx) /8: "";
        if (($j - $startx )% 8 == 0) {
            $width = ($j - $startx) /8;
        } else {
            echo "计算模块宽度出错！";
            exit();
        }

        for ($i = $starty; $i <= $endy; $i ++) {
            for ($j = $startx; $j <= $endx; $j += $width) {
                $rgb = imagecolorat($res, $j, $i);
                $rgbarray = imagecolorsforindex($res, $rgb);
                $data[$i] .= $this->isblock($rgbarray);
            }
        }
        $arr = array_values(array_unique($data));
        array_walk($arr, "isright"); //判断01序列是否合法，返回01条空数组；
    
        $this->row = count($arr);
        $this->cow = count($arr[0]) - 2;

        foreach ($arr as $n => &$row) {
            foreach ($row as &$value) {
                $value = '0x'.dechex(bindec($value));  //将条空序列转换成16进制
                $cu = $n % 3 *3;
                $bsarr = getbs($cu);
                $value = array_search($value, $bsarr);  //获取码字；
            }
        }
    //print_r($arr);
        $this->data = $arr;
    }


    public function isblock($rgbarray)
    {
        if ($rgbarray['red'] < 125 || $rgbarray['green']<125  || $rgbarray['blue'] < 125) {
            $data=1;
        } else {
            $data=0;
        }
        return $data;
    }


    function encodecode()
    {
        $specialcode = array(900, 901, 924, 902, 913);
        foreach ($this->data as $key => $value) {
            foreach ($value as $m => $n) {
                if ($m != 0 & $m != $this->cow + 1) {
                    $data[] = $n;
                }
            }
        }
        $this->datacode = $data;
 
        $count = array_shift($data); //码字个数，不包括纠错码字；

        $correctcount =  count($data) - $count + 1; //纠错码字个数
        for ($i = 0; $i < $correctcount; $i ++) {
            $corrdata[] = array_pop($data); //纠错码字数组；
        }
        do {
            $char = array_pop($data);
        } while ($char == 900);
        echo "解码出来的信息为：<hr /><pre>";
        $data[] = $char;  //从数据码字中删除填充字符
        $nextmode = "encodeTc";
        $ret = array();
        while (count($data)) {
            $char = array_shift($data);
            if (in_array($char, $specialcode)) {
                switch ($char) {
                    case 900:
                        $mode = "encodeTc";
                        break;
                    case 901:
                        $mode = "encodeBc";
                        break;
                    case 924:
                        $mode = "encodeBC";
                        break;
                    case 902:
                        $mode = "encodeNc";
                        break;
                    case 913:
                        $mode = "encodeBc";
                        break;
                }
                if (!empty($ret)) {
                    call_user_func($nextmode, $ret);
                    $ret = array();
                }
                $nextmode = $mode;
            } else {
                $ret[] = $char;
            }
        }
        call_user_func($nextmode, $ret);
        echo "</pre><hr />解码结束！";
    }
}
function encodeNc($code)
{
      //echo "<br />NC code:<br />";
      $arr = $code ;
    foreach ($arr as $key => $value) {
        $nccode[intval($key / 15)][] = $value;
    }
    foreach ($nccode as $key => $value) {
        $nc .= switch900to10($value) ;
    }
        echo $nc;
}
function encodeTc($code)
{
      //echo "<br />TC code:<br />";
      //print_r($code);
    foreach ($code as $value) {
        $c[] = intval($value / 30);
        $c[] = $value % 30;
    }
       //print_r($c);
        $pad = array_pop($c);
    if ($pad != 29) {
        array_push($c, $pad);
    }
        
        $mix = array(48,49,50,51,52,53,54,55,56,57,38,35,43,37,61,94);  //混合型字符ASCII列表
        $punc = array(59,60,62,64,91,92,93,95,96,126,33,10,34,124,40,41,63,123,125,39); //标点型字符ASCII列表；
        $rep  = array(13,09,44,58,45,46,36,47,42,32); //混合型与标点型 重复字符列表； 另外加32（空格）
        
       //各模式下的特殊字符；
        $alpha = array('27' => 'lower', '28' => 'mix', '29' => 'puncs');
        $lower = array('27' => 'alpha', '28' => 'mix', '29' => 'puncs');
        $mix   = array('25' => 'punc', '27' => 'lower', '28' => 'alpha', '29' => 'puncs');
        $punc  = array('29' => 'alpha');
        
        $curmode = 'alpha';
    foreach ($c as $key) {
        $mode = $$curmode;
        if ($flg) {
            $str .= getchar($key, $curmode);
            $flg = '';
            $curmode = $temp;
            continue;
        }
        if (array_key_exists($key, $mode)) {
            $temp = $curmode;
            $curmode = $mode[$key];
        } else {
            $str .= getchar($key, $curmode);
        }
        if ($curmode == "puncs") {
            $flg = 'puncs';
        }
    }
        echo $str;
}
function getchar($char, $mode)
{
    $mix = array('48', '49', '50', '51', '52', '53', '54', '55', '56', '57', '38', '13', '09', '44', '58', '35', '45', '46', '36', '47', '43', '37', '42', '61', '94', '', '32');
    $punc = array('59', '60', '62', '64', '91', '92', '93', '95', '96', '126', '33', '13', '9', '44', '58', '10', '45', '46', '36', '47', '34', '124', '42', '40', '41', '63', '123', '125', '39');
    if ($mode == "alpha") {
        if ($char  == 26) {
            $ret = chr("32");
        } else {
            $ret = chr($char + 65);
        }
    } else if ($mode == "lower") {
        if ($char  == 26) {
            $ret = chr("32");
        } else {
            $ret = chr($char +97);
        }
    } else if ($mode == "mix") {
        $ret = chr($mix[$char]);
    } else if ($mode = "punc") {
        $ret = chr($punc[$char]);
    }
    return $ret;
}
function encodeBc($code)
{
        //echo "<br />BC code:<br />";
        $arr = $code ;
        $bc = array();
    foreach ($arr as $key => $value) {
        $nccode[intval($key / 5)][] = $value;
    }
    foreach ($nccode as $key => $value) {
        //print_r($value);
        $bc = array_merge($bc, switch900to256($value));
    }
        //print_r($bc);
    foreach ($bc as $key) {
            $str .= chr($key);
    }
            echo $str;
}
 
function trimrn(&$value)  //回调函数，删除空格、换行
{
    $value = trim($value);
}
$encode = new encode();
?>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>PDF417解码</title>
<form enctype="multipart/form-data" action="" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="200000" />
    上传PDF417图片: <input name="userfile" type="file" />
    <input type="submit" value="解码" />
</form>
