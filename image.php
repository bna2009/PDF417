<?php
include('bmp.php');
class createimg
{
	var $width;   //ͼƬ��
	var $height;  //ͼƬ�ߣ�
	var $Ptrx;    //��ǰXָ�룻
	var $Ptry;    //��ǰYָ�룻
	var $mwidth;  //ÿ��ģ��Ŀ�
	var $mheight; //ÿ��ģ��ĸߣ�
	var $row;     //ͼƬ������
	var $cow;     //ͼƬ������ 
	var $s ;      //������
	var $x,$y,$z,$v; //�����б�ʶ���ı�����
	public $im, $color, $background;
	function __construct()
	{
		$this->x = 5;
		$this->y = 5;
		$this->f = fopen("code.txt",'w');
	}
	function setrow($row, $cow, $s)
	{
		$this->row = $row;
		$this->cow = $cow;
		$this->s = $s;
		$this->create();
	}
	//�����б�ʶ������ʱ����
	function getxyz($i)
	{
		$this->x = intval(($i-1) / 3);
		$this->y = intval(($this->row - 1) / 3);
		$this->z = $this->s * 3 + ($this->row - 1) % 3;
        $this->v = $this->cow-1;
	}
	//�����б�ʶ������ʱ����

    function create()
	{
		$this->mwidth = intval(348 / (17 * $this->cow + 69)); 
        $this->mwidth = $this->mwidth > 1 ? $this->mwidth : 1;
		$this->mheight = 4 * $this->mwidth; 
		$this->width = (17 * $this->cow + 69) * $this->mwidth;
		$this->height = $this->mheight * ( $this->row ); 
		$this->im = imagecreate($this->width + 10, $this->height + 10);
		$background =  imagecolorallocate($this->im, 255, 255, 255);// ������Ϊ��ɫ
		//imagefilledrectangle($this->im , 0, 0, $this->width + 20, $this->height + 20, $background );
		$this->color = imagecolorallocate($this->im, 0, 0, 0);
		$this->background = imagecolorallocate($this->im, 255, 255, 255);
	}
	function leftline($i)  //$i  Ϊ������
	{
		//echo "<br />";
		$this->getxyz($i);  
		$start = 130728; //��ʼ����
		$this->Ptrx = 5;
		if($i == 1) $this->Ptry = 5;
		else
		$this->Ptry += $this->mheight;
		$this->makepic($start);   //��ӡ��ͼƬ��
		$cu = ($i - 1) % 3 * 3;  //����غţ�
		if($cu == 0 )     $l = $this->y;
		else if($cu == 3) $l = $this->z;
		else if($cu == 6) $l = $this->v;
		$startline = 30 * $this->x + $l;  //���б�ʶ����
		//echo "���б�ʶ��:".getbs($startline,$cu);
		$this->makepic(getbs($cu, $startline));
	}
	function rightline($i)
	{
		$end = 260649; //��ֹ����
		$cu = ($i - 1) % 3 * 3;  //����غţ�
		if($cu == 0 )     $l = $this->v;
		else if($cu == 3) $l = $this->y;
		else if($cu == 6) $l = $this->z;
		$endline = 30 * $this->x + $l;  //���б�ʶ����
		$this->makepic(getbs($cu,$endline));
		$this->makepic($end);   //��ӡ��ͼƬ��
		
		
	}

	function makepic($no)
	{  
		$bs = decbin($no).'';	
		if($this->Ptrx == 5 )
			fwrite($this->f, "\r\n");
		fwrite($this->f, $bs." ");
		$arr = str_split($bs);
		$len = strlen($bs);
		foreach($arr as $key)
		{
			if($key == 1) 
			{
				imagefilledrectangle($this->im , $this->Ptrx, $this->Ptry, $this->Ptrx + $this->mwidth, $this->Ptry + $this->mheight, $this->color );
			}
			else if($key == 0) 
			{
				imagefilledrectangle($this->im , $this->Ptrx, $this->Ptry, $this->Ptrx + $this->mwidth, $this->Ptry + $this->mheight, $this->background ); 
			}
			$this->Ptrx += $this->mwidth;
		}
		imagefilledrectangle($this->im , $this->Ptrx, $this->Ptry, $this->Ptrx + $this->mwidth, $this->Ptry + $this->mheight, $this->background ); 
	}
	function echoimg()
	{
		//header("Content-type: image/jpeg"); 
		//imagebmp($this->im);
		$file = "pic/".date("Ymdhis").rand(1, 100000).".jpg";
		imagejpeg($this->im, $file, 90);
		echo "<img src=$file />";
		echo "<br /><br /><br /><br /><a href='encode/encode.php' >�������ҳ��</a>";
	}
}
?>